<?php

namespace App\Services;

use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Events\OrderCancelled;
use App\Events\OrderCompleted;
use App\Events\OrderCreated;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

/**
 * Order lifecycle: create (POS deducts stock immediately; online defers to
 * completion), status transitions, stock + customer-counter side effects. All money
 * is recomputed server-side by OrderTotalsService — client totals are ignored.
 * See docs 02 (Page 1–2), 03 §2–6.
 */
class OrderService
{
    public function __construct(
        private readonly OrderTotalsService $totals,
        private readonly StockService $stock,
    ) {}

    public function create(Store $store, array $data, ?User $user = null): Order
    {
        return DB::transaction(function () use ($store, $data, $user): Order {
            // Lock the store row so order_number stays sequential under concurrency.
            Store::whereKey($store->id)->lockForUpdate()->first();

            $status = OrderStatus::from($data['status'] ?? OrderStatus::COMPLETE->value);
            $source = OrderSource::from(strtoupper($data['source'] ?? OrderSource::POS->value));
            $paymentStatus = PaymentStatus::from($data['payment_status']
                ?? ($status === OrderStatus::COMPLETE ? PaymentStatus::PAID->value : PaymentStatus::UNPAID->value));

            $customer = ! empty($data['customer_id']) ? Customer::find($data['customer_id']) : null;

            [$lines, $itemRows] = $this->buildItems($data['items']);

            $totals = $this->totals->compute(
                $lines,
                (float) ($data['discount'] ?? 0),
                [
                    'charge_sales_tax' => (bool) $store->charge_sales_tax,
                    'tax_rate' => (float) $store->tax_rate,
                    'tax_included_in_price' => (bool) $store->tax_included_in_price,
                ],
                (float) ($data['delivery_fee'] ?? 0),
            );

            $order = $store->orders()->create([
                'order_number' => 'ORD-'.(1000 + $store->orders()->count()),
                'customer_id' => $customer?->id,
                'customer_name' => $customer?->name ?? ($data['customer_name'] ?? 'Walk-in Customer'),
                'customer_phone' => $customer?->phone ?? ($data['customer_phone'] ?? null),
                'status' => $status,
                'payment_method' => PaymentMethod::from($data['payment_method']),
                'payment_status' => $paymentStatus,
                'currency_code' => $store->currency,
                'source' => $source,
                'fulfillment' => isset($data['fulfillment']) ? strtoupper($data['fulfillment']) : null,
                'table_number' => $data['table_number'] ?? null,
                'delivery_address' => $data['delivery_address'] ?? null,
                'delivery_address_details' => $data['delivery_address_details'] ?? null,
                'delivery_city' => $data['delivery_city'] ?? null,
                'delivery_latitude' => $data['delivery_latitude'] ?? null,
                'delivery_longitude' => $data['delivery_longitude'] ?? null,
                'subtotal' => $totals['subtotal'],
                'discount' => round((float) ($data['discount'] ?? 0), 2),
                'tax_rate' => $totals['tax_rate'],
                'tax_amount' => $totals['tax_amount'],
                'delivery_fee' => round((float) ($data['delivery_fee'] ?? 0), 2),
                'delivery_provider_id' => $data['delivery_provider_id'] ?? null,
                'delivery_provider_name' => $data['delivery_provider_name'] ?? null,
                'delivery_distance_km' => $data['delivery_distance_km'] ?? null,
                'delivery_fee_status' => $data['delivery_fee_status'] ?? null,
                'total' => $totals['total'],
                'notes' => $data['notes'] ?? null,
                'payment_reference' => $data['payment_reference'] ?? null,
                'payment_proof_path' => $data['payment_proof_path'] ?? null,
                'placed_at' => $data['placed_at'] ?? now(),
                'created_by' => $user?->id,
            ]);

            foreach ($itemRows as $row) {
                $order->items()->create($row);
            }
            $order->load('items');

            // Completed POS orders affect stock + customer stats immediately.
            if ($status === OrderStatus::COMPLETE) {
                $lowStock = $this->stock->deductForOrder($order, $user);
                $this->applyCustomerStats($order, 1);
                $order->forceFill(['completed_at' => now()])->save();
                $this->notifyLowStock($store, $lowStock);
            }

            OrderCreated::dispatch($order);

            return $order->load(['items', 'customer', 'creator']);
        });
    }

    /**
     * Merchant sets (or corrects) the delivery fee — the back-office half of the
     * WhatsApp manual-quote loop. Safe total math: OrderTotalsService never taxes
     * the delivery fee, so total = total − old_fee + new_fee.
     */
    public function setDeliveryFee(Order $order, float $fee, ?User $user = null): Order
    {
        if ($order->fulfillment !== 'DELIVERY') {
            throw ValidationException::withMessages(['delivery_fee' => ['Only delivery orders carry a delivery fee.']]);
        }
        if ($order->payment_status === PaymentStatus::PAID) {
            throw ValidationException::withMessages(['delivery_fee' => ['This order is already paid — the fee can no longer change.']]);
        }
        if ($order->status === OrderStatus::CANCELLED) {
            throw ValidationException::withMessages(['delivery_fee' => ['This order was cancelled.']]);
        }

        $fee = round($fee, 2);

        $order->update([
            'delivery_fee' => $fee,
            'total' => round((float) $order->total - (float) $order->delivery_fee + $fee, 2),
            'delivery_fee_status' => 'SET',
        ]);

        return $order->fresh(['items', 'customer']);
    }

    public function updateStatus(Order $order, OrderStatus $to, ?User $user = null): Order
    {
        return DB::transaction(function () use ($order, $to, $user): Order {
            $from = $order->status;
            $order->load('items');

            match (true) {
                $from === OrderStatus::CONFIRMING && $to === OrderStatus::ACCEPTED => $this->advance($order, $to),
                $from === OrderStatus::ACCEPTED && $to === OrderStatus::PREPARING => $this->advance($order, $to),
                $from === OrderStatus::PREPARING && $to === OrderStatus::OUT_FOR_DELIVERY
                    && $order->fulfillment === 'DELIVERY' => $this->advance($order, $to),
                $from === OrderStatus::PREPARING && $to === OrderStatus::COMPLETE
                    && $order->fulfillment !== 'DELIVERY' => $this->complete($order, $user),
                $from === OrderStatus::OUT_FOR_DELIVERY && $to === OrderStatus::COMPLETE => $this->complete($order, $user),
                $from !== OrderStatus::COMPLETE && $from !== OrderStatus::CANCELLED && $to === OrderStatus::CANCELLED => $this->cancel($order, restore: false),
                $from === OrderStatus::COMPLETE && $to === OrderStatus::CANCELLED => $this->cancel($order, restore: true),
                default => throw ValidationException::withMessages([
                    'status' => ["Cannot change status from {$from->value} to {$to->value}."],
                ]),
            };

            return $order->fresh(['items', 'customer', 'creator']);
        });
    }

    private function complete(Order $order, ?User $user): void
    {
        $low = $this->stock->deductForOrder($order, $user);
        $this->applyCustomerStats($order, 1);
        $order->forceFill(['status' => OrderStatus::COMPLETE, 'completed_at' => now()])->save();
        $this->notifyLowStock($order->store, $low);
        OrderCompleted::dispatch($order);
    }

    private function advance(Order $order, OrderStatus $to): void
    {
        if (
            $order->payment_method === PaymentMethod::FAWRAN
            && $to === OrderStatus::ACCEPTED
            && $order->payment_status !== PaymentStatus::PAID
        ) {
            throw ValidationException::withMessages([
                'payment_status' => ['Verify the Fawran payment before accepting this order.'],
            ]);
        }

        $order->forceFill(['status' => $to])->save();
    }

    private function cancel(Order $order, bool $restore): void
    {
        if ($restore) {
            $this->stock->restoreForOrder($order);
            $this->applyCustomerStats($order, -1);
        }
        $order->forceFill(['status' => OrderStatus::CANCELLED, 'cancelled_at' => now()])->save();
        OrderCancelled::dispatch($order);
    }

    /**
     * @return array{0: list<array{unit_price: float, quantity: int, taxable: bool}>, 1: list<array<string, mixed>>}
     */
    private function buildItems(array $items): array
    {
        $lines = [];
        $rows = [];

        foreach ($items as $item) {
            $product = Product::active()->findOrFail($item['product_id']);
            $unitPrice = (float) $product->effectivePrice();
            $variantId = null;
            $variantName = null;

            if (! empty($item['variant_id'])) {
                $variant = $product->variants()->findOrFail($item['variant_id']);
                $unitPrice = (float) $variant->price;
                $variantId = $variant->id;
                $variantName = $variant->name;
            }

            $qty = (int) $item['quantity'];
            $lineTotal = round($unitPrice * $qty, 2);

            $lines[] = ['unit_price' => $unitPrice, 'quantity' => $qty, 'taxable' => (bool) $product->taxable];
            $rows[] = [
                'product_id' => $product->id,
                'variant_id' => $variantId,
                'product_name' => $product->name,
                'variant_name' => $variantName,
                'unit_price' => $unitPrice,
                'unit_cost' => $product->cost,
                'quantity' => $qty,
                'line_total' => $lineTotal,
                'taxable' => (bool) $product->taxable,
            ];
        }

        return [$lines, $rows];
    }

    private function applyCustomerStats(Order $order, int $sign): void
    {
        if ($order->customer_id === null) {
            return;
        }

        $customer = Customer::find($order->customer_id);
        if ($customer === null) {
            return;
        }

        $customer->total_orders = max(0, $customer->total_orders + $sign);
        $customer->total_spent = max(0, (float) $customer->total_spent + $sign * (float) $order->total);
        $customer->save();
    }

    /** @param list<Product> $products */
    private function notifyLowStock(Store $store, array $products): void
    {
        if ($products === []) {
            return;
        }

        $recipients = $store->users()
            ->wherePivotIn('role', ['OWNER', 'MANAGER'])
            ->wherePivot('is_active', true)
            ->get();

        foreach ($products as $product) {
            Notification::send($recipients, new LowStockNotification($product));
        }
    }
}
