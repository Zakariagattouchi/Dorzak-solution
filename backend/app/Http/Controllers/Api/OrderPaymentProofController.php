<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OrderPaymentProofController extends Controller
{
    public function __invoke(Request $request, int $order): BinaryFileResponse
    {
        abort_unless($request->user()->can('orders.view'), 403);
        $model = Order::findOrFail($order);
        abort_if(! $model->payment_proof_path || ! Storage::disk('local')->exists($model->payment_proof_path), 404);

        return response()->file(Storage::disk('local')->path($model->payment_proof_path));
    }
}
