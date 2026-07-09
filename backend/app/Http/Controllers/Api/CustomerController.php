<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerController extends Controller
{
    /** GET /customers — search + sort + pagination, with a whole-store summary. */
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('customers.view'), 403);

        $query = Customer::query()->search($request->string('search')->toString() ?: null);
        $this->applySort($query, $request->string('sort')->toString());

        $perPage = min(max($request->integer('per_page', 25), 1), 200);
        $payload = CustomerResource::collection($query->paginate($perPage)->withQueryString())
            ->response()->getData(true);

        // Summary over the whole store (not just the current page / filter).
        $total = Customer::count();
        $spent = (float) Customer::sum('total_spent');
        $payload['meta']['summary'] = [
            'count' => $total,
            'total_spent' => number_format($spent, 2, '.', ''),
            'avg_ltv' => number_format($total > 0 ? $spent / $total : 0, 2, '.', ''),
        ];

        return response()->json($payload);
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        if ($existing = $this->duplicateByPhone($request->string('phone')->toString())) {
            return response()->json([
                'message' => 'A customer with this phone number already exists.',
                'errors' => ['phone' => ['A customer with this phone number already exists.']],
                'duplicate_customer_id' => $existing->id,
            ], 422);
        }

        $customer = Customer::create($request->validated());

        return (new CustomerResource($customer))->response()->setStatusCode(201);
    }

    public function show(int $customer): CustomerResource
    {
        $model = Customer::findOrFail($customer);
        // recent_orders is populated once the orders table exists (TP-06).
        $model->recent_orders = [];

        return new CustomerResource($model);
    }

    public function update(UpdateCustomerRequest $request, int $customer): CustomerResource
    {
        $model = Customer::findOrFail($customer);

        if ($existing = $this->duplicateByPhone($request->string('phone')->toString(), $model->id)) {
            abort(response()->json([
                'message' => 'A customer with this phone number already exists.',
                'errors' => ['phone' => ['A customer with this phone number already exists.']],
                'duplicate_customer_id' => $existing->id,
            ], 422));
        }

        $model->update($request->validated());

        return new CustomerResource($model);
    }

    public function destroy(int $customer): JsonResponse
    {
        abort_unless(request()->user()->can('customers.delete'), 403);
        Customer::findOrFail($customer)->delete();

        return response()->json(status: 204);
    }

    /** GET /customers/export — CSV stream. */
    public function export(Request $request): StreamedResponse
    {
        abort_unless($request->user()->can('customers.export'), 403);

        $query = Customer::query()->search($request->string('search')->toString() ?: null)->orderBy('name');

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['name', 'phone', 'email', 'address', 'city', 'total_orders', 'total_spent', 'notes']);
            $query->chunk(500, function ($customers) use ($out) {
                foreach ($customers as $c) {
                    fputcsv($out, $this->escapeCsv([
                        $c->name, $c->phone, $c->email, $c->address, $c->city,
                        $c->total_orders, $c->total_spent, $c->notes,
                    ]));
                }
            });
            fclose($out);
        }, 'customers-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    private function duplicateByPhone(string $phone, ?int $ignoreId = null): ?Customer
    {
        $normalized = preg_replace('/\D/', '', $phone);
        if ($normalized === '') {
            return null;
        }

        return Customer::where('phone_normalized', $normalized)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->first();
    }

    private function applySort($query, string $sort): void
    {
        match ($sort) {
            'name' => $query->orderBy('name'),
            '-name' => $query->orderByDesc('name'),
            'total_spent' => $query->orderBy('total_spent'),
            '-total_spent' => $query->orderByDesc('total_spent'),
            default => $query->orderBy('name'),
        };
    }

    /** Prevent CSV formula injection when the file is opened in a spreadsheet (docs 12 §10). */
    private function escapeCsv(array $row): array
    {
        return array_map(function ($value) {
            $value = (string) $value;

            return preg_match('/^[=+\-@]/', $value) ? "'".$value : $value;
        }, $row);
    }
}
