<?php

namespace App\Http\Controllers\Api\Platform;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\PlatformAuditLog;
use App\Models\Product;
use App\Models\Store;
use App\Services\CustomerImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Cross-tenant data browsing, export and import for the super-admin. Platform
 * requests carry no store context, so the tenant scope is inert and these
 * queries deliberately span every store. Exports stream CSV (opens natively in
 * Excel), matching the merchant-side export style.
 */
class DataController extends Controller
{
    // ─── Customers ────────────────────────────────────────────────────────────

    public function customers(Request $request): JsonResponse
    {
        $rows = Customer::query()
            ->with('store:id,name')
            ->when($request->filled('search'), function ($q) use ($request) {
                $t = '%'.$request->string('search').'%';
                $q->where(fn ($w) => $w->where('name', 'like', $t)->orWhere('phone', 'like', $t)->orWhere('email', 'like', $t));
            })
            ->when($request->filled('store_id'), fn ($q) => $q->where('store_id', $request->integer('store_id')))
            ->orderByDesc('total_spent')
            ->paginate(50);

        return response()->json([
            'data' => $rows->getCollection()->map(fn (Customer $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'phone' => $c->phone,
                'email' => $c->email,
                'city' => $c->city,
                'store' => $c->store?->name,
                'orders' => (int) $c->total_orders,
                'spent' => (float) $c->total_spent,
            ]),
            'meta' => ['total' => $rows->total(), 'per_page' => $rows->perPage(), 'current_page' => $rows->currentPage()],
        ]);
    }

    public function exportCustomers(Request $request): StreamedResponse
    {
        $query = Customer::query()->with('store:id,name')
            ->when($request->filled('store_id'), fn ($q) => $q->where('store_id', $request->integer('store_id')));

        return $this->streamCsv('customers', ['store', 'name', 'phone', 'email', 'city', 'total_orders', 'total_spent', 'notes'], function ($out) use ($query) {
            $query->chunk(500, function ($chunk) use ($out) {
                foreach ($chunk as $c) {
                    fputcsv($out, [$c->store?->name, $c->name, $c->phone, $c->email, $c->city, $c->total_orders, $c->total_spent, $c->notes]);
                }
            });
        });
    }

    public function importCustomers(Request $request, CustomerImportService $service): JsonResponse
    {
        $request->validate([
            'store_id' => 'required|integer|exists:stores,id',
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $store = Store::findOrFail($request->integer('store_id'));
        $rows = $this->parseCsv($request->file('file')->getRealPath());
        $result = $service->import($store, $rows);

        PlatformAuditLog::record($request->user(), 'customers.import', $store, $store->name, ['rows' => count($rows)], $request->ip());

        return response()->json(['data' => $result]);
    }

    // ─── Products ─────────────────────────────────────────────────────────────

    public function products(Request $request): JsonResponse
    {
        $rows = Product::query()
            ->with(['store:id,name', 'category:id,name'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $t = '%'.$request->string('search').'%';
                $q->where(fn ($w) => $w->where('name', 'like', $t)->orWhere('sku', 'like', $t));
            })
            ->when($request->filled('store_id'), fn ($q) => $q->where('store_id', $request->integer('store_id')))
            ->orderBy('store_id')->orderBy('name')
            ->paginate(50);

        return response()->json([
            'data' => $rows->getCollection()->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'store' => $p->store?->name,
                'category' => $p->category?->name,
                'price' => (float) $p->price,
                'stock' => $p->track_stock ? (int) $p->stock : null,
                'sku' => $p->sku,
                'active' => (bool) $p->is_active,
            ]),
            'meta' => ['total' => $rows->total(), 'per_page' => $rows->perPage(), 'current_page' => $rows->currentPage()],
        ]);
    }

    public function exportProducts(Request $request): StreamedResponse
    {
        $query = Product::query()->with(['store:id,name', 'category:id,name'])
            ->when($request->filled('store_id'), fn ($q) => $q->where('store_id', $request->integer('store_id')));

        return $this->streamCsv('products', ['store', 'name', 'category', 'sku', 'price', 'cost', 'stock', 'active'], function ($out) use ($query) {
            $query->chunk(500, function ($chunk) use ($out) {
                foreach ($chunk as $p) {
                    fputcsv($out, [$p->store?->name, $p->name, $p->category?->name, $p->sku, $p->price, $p->cost, $p->track_stock ? $p->stock : '', $p->is_active ? 'yes' : 'no']);
                }
            });
        });
    }

    // ─── Stores & orders ──────────────────────────────────────────────────────

    public function exportStores(): StreamedResponse
    {
        $query = Store::query()->with('subscription.plan');

        return $this->streamCsv('stores', ['id', 'name', 'email', 'country', 'plan', 'status', 'created_at'], function ($out) use ($query) {
            $query->chunk(500, function ($chunk) use ($out) {
                foreach ($chunk as $s) {
                    fputcsv($out, [$s->id, $s->name, $s->email, $s->country, $s->subscription?->plan?->code, $s->suspended_at ? 'suspended' : 'active', $s->created_at?->toDateString()]);
                }
            });
        });
    }

    public function exportOrders(Request $request): StreamedResponse
    {
        $query = Order::query()->with(['store:id,name'])
            ->when($request->filled('store_id'), fn ($q) => $q->where('store_id', $request->integer('store_id')));

        return $this->streamCsv('orders', ['store', 'order_number', 'status', 'payment_method', 'total', 'customer', 'date'], function ($out) use ($query) {
            $query->chunk(500, function ($chunk) use ($out) {
                foreach ($chunk as $o) {
                    fputcsv($out, [$o->store?->name, $o->order_number, $o->status->value, $o->payment_method, $o->total, $o->customer_name, $o->created_at?->toDateString()]);
                }
            });
        });
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function streamCsv(string $name, array $header, callable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($header, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $header);
            $rows($out);
            fclose($out);
        }, "{$name}-".now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * @return list<array<string, string|null>>
     */
    private function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        $header = null;
        $rows = [];

        while (($row = fgetcsv($handle)) !== false) {
            if ($header === null) {
                $header = array_map(fn ($h) => strtolower(trim((string) $h)), $row);

                continue;
            }
            $rows[] = array_combine($header, array_pad($row, count($header), null));
        }

        fclose($handle);

        return $rows;
    }
}
