<?php

namespace Tests\Feature\Settings;

use App\Enums\StaffRole;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StorefrontMediaTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        ['user' => $this->owner, 'store' => $this->store] = $this->createStoreWithOwner();
    }

    public function test_banner_upload_stores_file_and_returns_url(): void
    {
        $response = $this->actingAsMember($this->owner)
            ->postJson('/api/v1/settings/storefront/banner', [
                'file' => UploadedFile::fake()->image('banner.jpg', 1200, 400),
            ]);

        $response->assertOk()->assertJsonStructure(['data' => ['path', 'url']]);

        $path = $response->json('data.path');
        Storage::disk('public')->assertExists($path);
        $this->assertSame($path, $this->store->storefrontSetting->fresh()->banner_path);
    }

    public function test_logo_upload_targets_logo_column(): void
    {
        $response = $this->actingAsMember($this->owner)
            ->postJson('/api/v1/settings/storefront/logo', [
                'file' => UploadedFile::fake()->image('logo.png'),
            ])->assertOk();

        $this->assertSame($response->json('data.path'), $this->store->storefrontSetting->fresh()->logo_path);
    }

    public function test_upload_rejects_non_image_and_oversize(): void
    {
        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/settings/storefront/banner', [
                'file' => UploadedFile::fake()->create('malware.pdf', 100, 'application/pdf'),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('file');

        $this->actingAsMember($this->owner)
            ->postJson('/api/v1/settings/storefront/banner', [
                'file' => UploadedFile::fake()->image('huge.jpg')->size(5000),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('file');
    }

    public function test_cashier_cannot_upload(): void
    {
        $cashier = $this->createMember(StaffRole::CASHIER, $this->store);

        $this->actingAsMember($cashier)
            ->postJson('/api/v1/settings/storefront/banner', [
                'file' => UploadedFile::fake()->image('banner.jpg'),
            ])
            ->assertForbidden();
    }
}
