<?php

namespace App\Http\Controllers\Api;

use App\Enums\PlanFeature;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateBusinessRequest;
use App\Http\Requests\Settings\UpdateCurrencyRequest;
use App\Http\Requests\Settings\UpdateGeneralRequest;
use App\Http\Requests\Settings\UpdateIntegrationsRequest;
use App\Http\Requests\Settings\UpdatePaymentsRequest;
use App\Http\Requests\Settings\UpdateReceiptsRequest;
use App\Http\Requests\Settings\UpdateStorefrontRequest;
use App\Http\Requests\Settings\UpdateTaxesRequest;
use App\Http\Resources\SettingsResource;
use App\Models\Store;
use App\Services\PlanGate;
use App\Services\SettingsService;
use App\Support\StoreContext;
use Illuminate\Foundation\Http\FormRequest;

class SettingsController extends Controller
{
    public function __construct(
        private readonly SettingsService $settings,
        private readonly StoreContext $context,
        private readonly PlanGate $plans,
    ) {}

    /** GET /settings — grouped envelope; readable by any active member. */
    public function show(): SettingsResource
    {
        $store = $this->store()->load(['storefrontSetting', 'receiptSetting', 'integrationSetting']);

        return new SettingsResource($store);
    }

    public function updateGeneral(UpdateGeneralRequest $request): SettingsResource
    {
        return $this->apply('general', $request);
    }

    public function updateBusiness(UpdateBusinessRequest $request): SettingsResource
    {
        return $this->apply('business', $request);
    }

    public function updateCurrency(UpdateCurrencyRequest $request): SettingsResource
    {
        return $this->apply('currency', $request);
    }

    public function updateTaxes(UpdateTaxesRequest $request): SettingsResource
    {
        return $this->apply('taxes', $request);
    }

    public function updateReceipts(UpdateReceiptsRequest $request): SettingsResource
    {
        return $this->apply('receipts', $request);
    }

    public function updatePayments(UpdatePaymentsRequest $request): SettingsResource
    {
        return $this->apply('payments', $request);
    }

    public function updateIntegrations(UpdateIntegrationsRequest $request): SettingsResource
    {
        return $this->apply('integrations', $request);
    }

    public function updateStorefront(UpdateStorefrontRequest $request): SettingsResource
    {
        // A branded slug (the basis of the subdomain storefront) is a paid feature.
        // Free stores may still edit menu appearance — only CLAIMING OR CHANGING a
        // slug is gated. Resubmitting the slug already on file (the form always
        // sends the whole payload) must not block unrelated edits.
        $currentSlug = $this->store()->storefrontSetting?->slug;

        if ($request->filled('store_slug') && $request->string('store_slug')->toString() !== $currentSlug) {
            $this->plans->ensure($this->store(), PlanFeature::BRANDED_STOREFRONT);
        }

        return $this->apply('storefront', $request);
    }

    private function apply(string $group, FormRequest $request): SettingsResource
    {
        $store = $this->settings->update($this->store(), $group, $request->validated(), $request->user());

        return new SettingsResource($store);
    }

    private function store(): Store
    {
        return $this->context->store();
    }
}
