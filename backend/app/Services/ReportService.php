<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Read-only aggregates for the Finances and Analytics pages. Periods resolve to a
 * placed_at range in the store timezone; cancelled orders are excluded from revenue;
 * gross profit uses the cost snapshot on order_items. See docs 02 (Pages 8–9).
 */
class ReportService
{
    /** @return array<string, mixed> */
    public function finance(Store $store, string $period, ?string $from, ?string $to): array
    {
        [$start, $end] = $this->range($store, $period, $from, $to);

        $completed = fn () => $this->applyRange(Order::query()->where('status', 'COMPLETE'), $start, $end);

        $gross = (float) $completed()->sum('total');
        $tax = (float) $completed()->sum('tax_amount');
        $discount = (float) $completed()->sum('discount');
        $pending = (float) $this->applyRange(Order::query()->whereNotIn('status', ['COMPLETE', 'CANCELLED']), $start, $end)->sum('total');

        $byMethod = [];
        foreach (['CASH', 'CARD', 'TRANSFER', 'WHATSAPP'] as $method) {
            $byMethod[$method] = $this->money((float) (clone $completed())->where('payment_method', $method)->sum('total'));
        }

        $entries = $this->applyRange(Order::query()->with('items'), $start, $end)
            ->where('status', '!=', 'CANCELLED')
            ->orderByDesc('placed_at')->limit(50)->get()
            ->map(fn (Order $o) => [
                'order_id' => $o->id,
                'order_number' => $o->order_number,
                'customer_name' => $o->customer_name,
                'items_count' => $o->items->count(),
                'date' => $o->placed_at?->clone()->setTimezone($store->timezone ?: 'UTC')->format('Y-m-d H:i'),
                'payment_method' => $o->payment_method->value,
                'tax_amount' => $o->tax_amount,
                'total' => $o->total,
            ]);

        return [
            'gross_revenue' => $this->money($gross),
            'net_revenue' => $this->money($gross - $tax),
            'tax_collected' => $this->money($tax),
            'tax_rate' => (float) $store->tax_rate,
            'pending_revenue' => $this->money($pending),
            'discount_total' => $this->money($discount),
            'completed_orders' => $completed()->count(),
            'by_method' => $byMethod,
            'entries' => $entries,
        ];
    }

    /** @return array<string, mixed> */
    public function analytics(Store $store, string $period): array
    {
        [$start, $end] = $this->range($store, $period, null, null);

        $completed = fn () => $this->applyRange(Order::query()->where('status', 'COMPLETE'), $start, $end);
        $revenue = (float) $completed()->sum('total');
        $orders = $completed()->count();

        $itemsInPeriod = fn () => OrderItem::query()
            ->whereIn('order_id', $this->applyRange(Order::query()->where('status', 'COMPLETE'), $start, $end)->select('id'));

        $grossProfit = (float) (clone $itemsInPeriod())
            ->selectRaw('COALESCE(SUM((unit_price - unit_cost) * quantity), 0) as p')->value('p');

        $byMethod = [];
        foreach (['CASH', 'CARD', 'TRANSFER'] as $method) {
            $byMethod[$method] = $this->money((float) (clone $completed())->where('payment_method', $method)->sum('total'));
        }

        $topProducts = (clone $itemsInPeriod())
            ->selectRaw('product_id, MAX(product_name) as name, SUM(quantity) as quantity_sold, SUM(line_total) as revenue')
            ->whereNotNull('product_id')
            ->groupBy('product_id')->orderByDesc('revenue')->limit(5)->get()
            ->map(fn ($r) => [
                'id' => $r->product_id,
                'name' => $r->name,
                'quantity_sold' => (int) $r->quantity_sold,
                'revenue' => $this->money((float) $r->revenue),
            ]);

        $lowStock = Product::query()->where('track_stock', true)
            ->whereColumn('stock', '<=', 'min_stock')->where('stock', '>', 0)
            ->orderBy('stock')->limit(5)->get(['id', 'name', 'stock', 'min_stock']);

        $byCategory = Product::query()->with('category')
            ->selectRaw('category_id, COUNT(*) as products_count')
            ->groupBy('category_id')->get()
            ->map(fn ($r) => [
                'id' => $r->category_id,
                'name' => optional($r->category)->name ?? 'Uncategorized',
                'products_count' => (int) $r->products_count,
            ]);

        return [
            'kpis' => [
                'revenue' => $this->money($revenue),
                'orders' => $orders,
                'avg_order_value' => $this->money($orders > 0 ? $revenue / $orders : 0),
                'gross_profit' => $this->money($grossProfit),
            ],
            'by_method' => $byMethod,
            'inventory' => [
                'total_products' => Product::query()->where('is_active', true)->count(),
                'low_stock_count' => Product::query()->where('track_stock', true)->whereColumn('stock', '<=', 'min_stock')->where('stock', '>', 0)->count(),
                'out_of_stock_count' => Product::query()->where('track_stock', true)->where('stock', '<=', 0)->count(),
                'low_stock' => $lowStock,
            ],
            'top_products' => $topProducts,
            'by_category' => $byCategory,
        ];
    }

    /** @return array{0: ?Carbon, 1: ?Carbon} UTC boundaries (null = unbounded) */
    private function range(Store $store, string $period, ?string $from, ?string $to): array
    {
        $tz = $store->timezone ?: 'UTC';
        $now = Carbon::now($tz);

        [$start, $end] = match (strtolower($period)) {
            'daily', 'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'weekly', 'week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'monthly', 'month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            default => [null, null],
        };

        if ($from) {
            $start = Carbon::parse($from, $tz)->startOfDay();
        }
        if ($to) {
            $end = Carbon::parse($to, $tz)->endOfDay();
        }

        return [$start?->utc(), $end?->utc()];
    }

    private function applyRange(Builder $query, ?Carbon $start, ?Carbon $end): Builder
    {
        if ($start) {
            $query->where('placed_at', '>=', $start);
        }
        if ($end) {
            $query->where('placed_at', '<=', $end);
        }

        return $query;
    }

    private function money(float $value): string
    {
        return number_format(round($value, 2), 2, '.', '');
    }
}
