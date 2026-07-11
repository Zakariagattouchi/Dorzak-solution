<?php

namespace App\Http\Controllers\Api;

use App\Enums\PlanFeature;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Services\CampaignService;
use App\Services\PlanGate;
use App\Support\StoreContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Marketing campaigns (premium — PlanFeature::CAMPAIGNS). */
class CampaignController extends Controller
{
    public function __construct(
        private readonly PlanGate $plans,
        private readonly StoreContext $context,
        private readonly CampaignService $campaigns,
    ) {}

    public function index(): JsonResponse
    {
        $rows = Campaign::orderByDesc('id')->get()->map(fn (Campaign $c) => [
            'id' => $c->id, 'subject' => $c->subject, 'status' => $c->status,
            'audience' => $c->audience, 'scheduled_at' => $c->scheduled_at?->toIso8601String(),
            'sent_count' => $c->sent_count,
        ]);

        return response()->json(['campaigns' => $rows]);
    }

    public function store(Request $request): JsonResponse
    {
        $store = $this->context->store();
        $this->plans->ensure($store, PlanFeature::CAMPAIGNS);
        abort_unless($request->user()->can('settings.manage'), 403);

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string', 'max:5000'],
            'channel' => ['nullable', 'in:email,whatsapp'],
            'audience' => ['required', 'array'],
            'audience.type' => ['required', 'in:all,segment'],
            'audience.segment_id' => ['nullable', 'integer'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        $campaign = $this->campaigns->create($store, $data);

        return response()->json(['id' => $campaign->id, 'status' => $campaign->status], 201);
    }

    /** Send a draft/scheduled campaign immediately. */
    public function send(Request $request, Campaign $campaign): JsonResponse
    {
        $store = $this->context->store();
        abort_unless($campaign->store_id === $store->id, 404);
        $this->plans->ensure($store, PlanFeature::CAMPAIGNS);
        abort_unless($request->user()->can('settings.manage'), 403);
        abort_if($campaign->status === 'sent', 422, 'This campaign has already been sent.');

        $this->campaigns->send($campaign);

        return response()->json(['status' => $campaign->refresh()->status, 'sent_count' => $campaign->sent_count]);
    }

    public function destroy(Request $request, Campaign $campaign): JsonResponse
    {
        abort_unless($campaign->store_id === $this->context->store()->id, 404);
        abort_unless($request->user()->can('settings.manage'), 403);
        abort_if($campaign->status === 'sent', 422, 'A sent campaign cannot be deleted.');

        $campaign->delete();

        return response()->json(['ok' => true]);
    }
}
