<?php

namespace App\Http\Controllers\Api\Platform;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Store;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

/**
 * Read-only commercial analytics for the super-admin: platform-wide health and
 * per-store deep dives (trending products, top customers, recent orders). No
 * store context is active for platform requests, so the tenant scope is inert
 * and these queries deliberately span/target stores by explicit store_id.
 */
class AnalyticsController extends Controller
{
    private const COMPLETE = OrderStatus::COMPLETE->value;

    /** GET /platform/analytics — the whole platform's commercial picture. */
    public function platform(): JsonResponse
    {
        $completed = Order::query()->where('status', self::COMPLETE);

        $gmv = (float) (clone $completed)->sum('total');
        $orders = (clone $completed)->count();

        $topStores = Order::query()
            ->where('orders.status', self::COMPLETE)
            ->join('stores', 'stores.id', '=', 'orders.store_id')
            ->selectRaw('stores.id, stores.name, count(*) as orders, sum(orders.total) as revenue')
            ->groupBy('stores.id', 'stores.name')
            ->orderByDesc('revenue')
            ->limit(8)
            ->get()
            ->map(fn ($r) => ['id' => (int) $r->id, 'name' => $r->name, 'orders' => (int) $r->orders, 'revenue' => (float) $r->revenue]);

        return response()->json([
            'data' => [
                'gmv' => $gmv,
                'orders' => $orders,
                'aov' => $orders ? round($gmv / $orders, 2) : 0,
                'new_customers_30d' => Customer::query()->where('created_at', '>=', now()->subDays(30))->count(),
                'top_stores' => $topStores,
                'trending_products' => $this->trending(Order::query()),
                'revenue_last_30_days' => $this->revenueByDay(Order::query()),
            ],
        ]);
    }

    /** GET /platform/stores/{store}/analytics — one store's numbers. */
    public function store(Store $store): JsonResponse
    {
        $completed = $store->orders()->where('status', self::COMPLETE);
        $revenue = (float) (clone $completed)->sum('total');
        $orders = (clone $completed)->count();

        $recent = $store->orders()->with('customer:id,name')->latest()->limit(8)->get()
            ->map(fn (Order $o) => [
                'order_number' => $o->order_number,
                'total' => (float) $o->total,
                'status' => $o->status->value,
                'customer' => $o->customer?->name ?? $o->customer_name,
                'date' => $o->created_at?->toISOString(),
            ]);

        $topCustomers = $store->customers()->orderByDesc('total_spent')->limit(8)->get()
            ->map(fn (Customer $c) => ['name' => $c->name, 'orders' => (int) $c->total_orders, 'spent' => (float) $c->total_spent]);

        return response()->json([
            'data' => [
                'revenue' => $revenue,
                'orders' => $orders,
                'aov' => $orders ? round($revenue / $orders, 2) : 0,
                'customers' => $store->customers()->count(),
                'new_customers_30d' => $store->customers()->where('created_at', '>=', now()->subDays(30))->count(),
                'trending_products' => $this->trending($store->orders()->getQuery()),
                'top_customers' => $topCustomers,
                'recent_orders' => $recent,
                'revenue_last_30_days' => $this->revenueByDay($store->orders()->getQuery()),
            ],
        ]);
    }

    /**
     * Best-selling products (by units) over completed orders in the given scope.
     *
     * @param  Builder<Order>  $orders
     */
    private function trending(Builder $orders): array
    {
        $orderIds = (clone $orders)->where('status', self::COMPLETE)->select('id');

        return OrderItem::query()
            ->whereIn('order_id', $orderIds)
            ->selectRaw('product_name, sum(quantity) as qty, sum(line_total) as revenue')
            ->groupBy('product_name')
            ->orderByDesc('qty')
            ->limit(8)
            ->get()
            ->map(fn ($r) => ['name' => $r->product_name, 'qty' => (int) $r->qty, 'revenue' => (float) $r->revenue])
            ->all();
    }

    /**
     * Completed-order revenue per day for the last 30 days, grouped in PHP so the
     * query stays portable across SQLite (tests) and Postgres (prod).
     *
     * @param  Builder<Order>  $orders
     */
    private function revenueByDay(Builder $orders): array
    {
        $since = now()->subDays(29)->startOfDay();

        $byDay = (clone $orders)
            ->where('status', self::COMPLETE)
            ->where('created_at', '>=', $since)
            ->get(['created_at', 'total'])
            ->groupBy(fn (Order $o) => $o->created_at->toDateString())
            ->map(fn ($rows) => (float) $rows->sum('total'));

        $out = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = now()->subDays($i)->toDateString();
            $out[] = ['date' => $day, 'revenue' => $byDay[$day] ?? 0.0];
        }

        return $out;
    }
}
