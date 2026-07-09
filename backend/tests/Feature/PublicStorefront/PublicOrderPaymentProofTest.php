<?php

namespace Tests\Feature\PublicStorefront;

use App\Models\Order;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicOrderPaymentProofTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        ['store' => $this->store] = $this->createStoreWithOwner();
        $this->store->storefrontSetting->update([
            'online_store_enabled' => true, 'slug' => 'proof-shop', 'fawran_enabled' => true,
        ]);
        $this->assignPlan($this->store, 'PRO');
    }

    private function order(array $attrs = []): Order
    {
        return Order::factory()->create(array_merge([
            'store_id' => $this->store->id,
            'order_number' => 'ORD-2001',
            'fulfillment' => 'DELIVERY',
            'status' => 'CONFIRMING',
            'payment_status' => 'UNPAID',
            'customer_name' => 'Jane',
            'subtotal' => 40, 'delivery_fee' => 12, 'total' => 52,
            'delivery_fee_status' => 'SET',
        ], $attrs));
    }

    private function upload(): array
    {
        return ['payment_proof' => UploadedFile::fake()->image('receipt.jpg')];
    }

    public function test_customer_can_pay_after_the_fee_is_set(): void
    {
        $this->order();

        $this->post('/api/public/stores/proof-shop/orders/ORD-2001/payment-proof', $this->upload())
            ->assertOk()
            ->assertJsonPath('data.payment_status', 'PENDING_VERIFICATION');

        $order = Order::withoutGlobalScopes()->where('order_number', 'ORD-2001')->first();
        $this->assertSame('FAWRAN', $order->payment_method->value);
        $this->assertNotNull($order->payment_proof_path);
        $this->assertSame('Jane', $order->payment_reference); // defaults to customer name
    }

    public function test_upload_is_blocked_while_the_fee_is_pending(): void
    {
        $this->order(['delivery_fee_status' => 'PENDING', 'delivery_fee' => 0, 'total' => 40]);

        $this->post('/api/public/stores/proof-shop/orders/ORD-2001/payment-proof', $this->upload())
            ->assertStatus(422);
    }

    public function test_upload_is_blocked_when_already_processing_or_cancelled(): void
    {
        $this->order(['payment_status' => 'PENDING_VERIFICATION']);
        $this->post('/api/public/stores/proof-shop/orders/ORD-2001/payment-proof', $this->upload())
            ->assertStatus(422);

        Order::withoutGlobalScopes()->where('order_number', 'ORD-2001')
            ->update(['payment_status' => 'UNPAID', 'status' => 'CANCELLED']);
        $this->post('/api/public/stores/proof-shop/orders/ORD-2001/payment-proof', $this->upload())
            ->assertStatus(422);
    }

    public function test_upload_is_blocked_when_fawran_disabled_and_404_on_unknown_order(): void
    {
        $this->store->storefrontSetting->update(['fawran_enabled' => false]);
        $this->order();

        $this->post('/api/public/stores/proof-shop/orders/ORD-2001/payment-proof', $this->upload())
            ->assertStatus(422);

        $this->store->storefrontSetting->update(['fawran_enabled' => true]);
        $this->post('/api/public/stores/proof-shop/orders/ORD-9999/payment-proof', $this->upload())
            ->assertNotFound();
    }
}
