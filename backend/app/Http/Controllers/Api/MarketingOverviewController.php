<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Coupon;
use App\Models\GiftCard;
use App\Models\LoyaltyAccount;
use App\Models\Order;
use App\Models\Referral;
use App\Models\Review;
use App\Models\WalletAccount;
use Illuminate\Http\JsonResponse;

/**
 * One aggregate read powering the Marketing console's overview strip: what each
 * growth surface has actually produced. Store-scoped by the global StoreScope.
 */
class MarketingOverviewController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $couponOrders = Order::query()
            ->whereNotNull('coupon_id')
            ->where('status', '!=', OrderStatus::CANCELLED);

        return response()->json([
            'coupons' => [
                'active' => Coupon::where('active', true)->count(),
                'redemptions' => (int) Coupon::sum('used_count'),
                'revenue' => (float) (clone $couponOrders)->sum('total'),
                'discount_given' => (float) (clone $couponOrders)->sum('discount'),
            ],
            'campaigns' => [
                'sent' => Campaign::where('status', 'sent')->count(),
                'scheduled' => Campaign::where('status', 'scheduled')->count(),
                'recipients' => (int) Campaign::sum('sent_count'),
            ],
            'referrals' => [
                'rewarded' => Referral::where('status', 'rewarded')->count(),
                'pending' => Referral::where('status', 'pending')->count(),
            ],
            'gift_cards' => [
                'outstanding_value' => (float) GiftCard::where('status', 'active')->sum('amount'),
                'redeemed' => GiftCard::where('status', 'redeemed')->count(),
            ],
            'loyalty' => [
                'members' => LoyaltyAccount::where('points', '>', 0)->count(),
                'points_outstanding' => (int) LoyaltyAccount::sum('points'),
            ],
            'store_credit' => [
                'outstanding' => (float) WalletAccount::sum('balance'),
            ],
            'reviews' => [
                'pending' => Review::where('approved', false)->count(),
                'approved' => Review::where('approved', true)->count(),
                'average' => round((float) Review::where('approved', true)->avg('rating'), 2),
            ],
        ]);
    }
}
