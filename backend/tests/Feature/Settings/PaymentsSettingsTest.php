<?php

namespace Tests\Feature\Settings;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentsSettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $this->owner, 'store' => $this->store] = $this->createStoreWithOwner();
    }

    public function test_payments_persist_as_method_array(): void
    {
        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/payments', [
                'cash' => true, 'card' => true, 'transfer' => false, 'whatsapp' => true,
            ])
            ->assertOk()
            ->assertJsonPath('data.payments.cash', true)
            ->assertJsonPath('data.payments.transfer', false)
            ->assertJsonPath('data.payments.whatsapp', true);

        $this->assertEqualsCanonicalizing(
            ['CASH', 'CARD', 'WHATSAPP'],
            $this->store->fresh()->accepted_payment_methods,
        );
    }

    public function test_payments_requires_at_least_one_pos_method(): void
    {
        // Only WhatsApp (online) enabled -> no in-person POS method.
        $this->actingAsMember($this->owner)
            ->putJson('/api/v1/settings/payments', [
                'cash' => false, 'card' => false, 'transfer' => false, 'whatsapp' => true,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('cash');
    }
}
