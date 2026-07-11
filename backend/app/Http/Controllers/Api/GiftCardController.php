<?php

namespace App\Http\Controllers\Api;

use App\Enums\PlanFeature;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\GiftCard;
use App\Services\GiftCardService;
use App\Services\PlanGate;
use App\Services\WalletService;
use App\Support\StoreContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Gift card management (premium — PlanFeature::GIFT_CARDS). Issue cards, list
 * them, and redeem a card into a customer's store-credit wallet.
 */
class GiftCardController extends Controller
{
    public function __construct(
        private readonly PlanGate $plans,
        private readonly StoreContext $context,
        private readonly GiftCardService $giftCards,
        private readonly WalletService $wallet,
    ) {}

    public function index(): JsonResponse
    {
        $rows = GiftCard::orderByDesc('id')->limit(200)->get()->map(fn (GiftCard $g) => [
            'id' => $g->id, 'code' => $g->code, 'amount' => (float) $g->amount, 'status' => $g->status,
        ]);

        return response()->json(['gift_cards' => $rows]);
    }

    public function store(Request $request): JsonResponse
    {
        $store = $this->context->store();
        $this->plans->ensure($store, PlanFeature::GIFT_CARDS);
        abort_unless($request->user()->can('settings.manage'), 403);

        $data = $request->validate(['amount' => ['required', 'numeric', 'min:1']]);

        $card = $this->giftCards->issue($store, (float) $data['amount']);

        return response()->json(['id' => $card->id, 'code' => $card->code], 201);
    }

    public function redeem(Request $request): JsonResponse
    {
        $store = $this->context->store();
        $this->plans->ensure($store, PlanFeature::GIFT_CARDS);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:40'],
            'customer_id' => ['required', 'integer'],
        ]);

        $customer = Customer::findOrFail($data['customer_id']);
        $this->giftCards->redeem($store, $data['code'], $customer);

        return response()->json(['balance' => $this->wallet->balance($customer)]);
    }
}
