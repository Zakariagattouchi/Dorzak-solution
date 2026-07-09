<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Exceptions\DomainConflictException;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Scopes\StoreScope;
use App\Models\StockMovement;
use App\Models\User;

/**
 * The only path that mutates stock. Every change writes a signed stock_movements
 * row in the same transaction as the cached `stock` update. TP-04 covers INITIAL
 * and ADJUSTMENT; TP-06 adds SALE / CANCEL_RETURN via deduct/restore. See docs 03 §4.
 */
class StockService
{
    public function writeMovement(
        Product $product,
        StockMovementType $type,
        int $quantityChange,
        int $stockAfter,
        ?int $variantId = null,
        ?User $user = null,
        ?int $orderId = null,
        ?string $note = null,
    ): StockMovement {
        return StockMovement::create([
            'store_id' => $product->store_id,
            'product_id' => $product->id,
            'variant_id' => $variantId,
            'type' => $type,
            'quantity_change' => $quantityChange,
            'stock_after' => $stockAfter,
            'order_id' => $orderId,
            'user_id' => $user?->id,
            'note' => $note,
        ]);
    }

    /** Record the opening balance for a freshly created product / its variants. */
    public function initializeProduct(Product $product, ?User $user = null): void
    {
        $variants = $product->variants;

        if ($variants->isNotEmpty()) {
            foreach ($variants as $variant) {
                if ($variant->stock !== 0) {
                    $this->writeMovement($product, StockMovementType::INITIAL, $variant->stock, $variant->stock, $variant->id, $user);
                }
            }

            return;
        }

        if ($product->track_stock && $product->stock !== 0) {
            $this->writeMovement($product, StockMovementType::INITIAL, $product->stock, $product->stock, null, $user);
        }
    }

    /** Set a variant-free product's stock to an absolute value, recording the diff. */
    public function adjustProductStock(Product $product, int $newStock, ?User $user = null, ?string $note = null): void
    {
        $diff = $newStock - $product->stock;
        $product->stock = $newStock;
        $product->save();

        if ($diff !== 0) {
            $this->writeMovement($product, StockMovementType::ADJUSTMENT, $diff, $newStock, null, $user, note: $note);
        }
    }

    /** Set a variant's stock to an absolute value, recording the diff. */
    public function adjustVariantStock(Product $product, ProductVariant $variant, int $newStock, ?User $user = null): void
    {
        $diff = $newStock - $variant->stock;
        $variant->stock = $newStock;
        $variant->save();

        if ($diff !== 0) {
            $this->writeMovement($product, StockMovementType::ADJUSTMENT, $diff, $newStock, $variant->id, $user);
        }
    }

    /**
     * Deduct an order's tracked items with row locks, rejecting the whole order if
     * any line is short. Returns the products whose stock crossed the low threshold.
     *
     * @return list<Product> products now at/below min_stock
     */
    public function deductForOrder(Order $order, ?User $user = null): array
    {
        $shortages = [];
        $plans = [];

        foreach ($order->items as $item) {
            if ($item->product_id === null) {
                continue;
            }

            $product = Product::withoutGlobalScope(StoreScope::class)->lockForUpdate()->find($item->product_id);
            if ($product === null || ! $product->track_stock) {
                continue;
            }

            if ($item->variant_id !== null) {
                $variant = ProductVariant::lockForUpdate()->find($item->variant_id);
                $available = $variant?->stock ?? 0;
                if ($available < $item->quantity) {
                    $shortages[] = ['product_id' => $product->id, 'requested' => $item->quantity, 'available' => $available];
                }
                $plans[] = [$product, $variant, $item->quantity];
            } else {
                if ($product->stock < $item->quantity) {
                    $shortages[] = ['product_id' => $product->id, 'requested' => $item->quantity, 'available' => $product->stock];
                }
                $plans[] = [$product, null, $item->quantity];
            }
        }

        if ($shortages !== []) {
            throw new DomainConflictException('INSUFFICIENT_STOCK', 'Not enough stock to complete this sale.', $shortages);
        }

        $lowStock = [];
        foreach ($plans as [$product, $variant, $qty]) {
            if ($variant !== null) {
                $variant->stock -= $qty;
                $variant->save();
                $product->stock = $product->variants()->sum('stock');
                $product->save();
                $this->writeMovement($product, StockMovementType::SALE, -$qty, $variant->stock, $variant->id, $user, $order->id);
            } else {
                $product->stock -= $qty;
                $product->save();
                $this->writeMovement($product, StockMovementType::SALE, -$qty, $product->stock, null, $user, $order->id);
            }

            if ($product->stock <= $product->min_stock) {
                $lowStock[$product->id] = $product;
            }
        }

        return array_values($lowStock);
    }

    /** Restore stock for a cancelled order (mirror of the deduction). */
    public function restoreForOrder(Order $order, ?User $user = null): void
    {
        foreach ($order->items as $item) {
            if ($item->product_id === null) {
                continue;
            }

            $product = Product::withoutGlobalScope(StoreScope::class)->lockForUpdate()->find($item->product_id);
            if ($product === null || ! $product->track_stock) {
                continue;
            }

            if ($item->variant_id !== null && ($variant = ProductVariant::lockForUpdate()->find($item->variant_id))) {
                $variant->stock += $item->quantity;
                $variant->save();
                $product->stock = $product->variants()->sum('stock');
                $product->save();
                $this->writeMovement($product, StockMovementType::CANCEL_RETURN, $item->quantity, $variant->stock, $variant->id, $user, $order->id);
            } else {
                $product->stock += $item->quantity;
                $product->save();
                $this->writeMovement($product, StockMovementType::CANCEL_RETURN, $item->quantity, $product->stock, null, $user, $order->id);
            }
        }
    }
}
