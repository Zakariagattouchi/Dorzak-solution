<?php

namespace App\Models;

use App\Enums\StaffRole;
use Database\Factories\StoreFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Tenant root. See docs 03 (domain model) and 04 (schema). Domain hasMany
 * relations (categories, products, ...) are added by their respective packets.
 */
class Store extends Model
{
    /** @use HasFactory<StoreFactory> */
    use HasFactory;

    protected $fillable = [
        'name', 'tagline', 'owner_name', 'email', 'phone', 'whatsapp',
        'address', 'city', 'state', 'zip_code', 'country', 'timezone',
        'language', 'currency', 'symbol_placement', 'charge_sales_tax',
        'tax_rate', 'tax_id', 'tax_included_in_price', 'accepted_payment_methods',
        'payment_details',
    ];

    protected function casts(): array
    {
        return [
            'charge_sales_tax' => 'boolean',
            'tax_included_in_price' => 'boolean',
            'tax_rate' => 'decimal:2',
            'accepted_payment_methods' => 'array',
            'payment_details' => 'array',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->using(StoreUser::class)
            ->withPivot(['role', 'is_active', 'joined_at'])
            ->withTimestamps();
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(StoreUser::class);
    }

    public function storefrontSetting(): HasOne
    {
        return $this->hasOne(StorefrontSetting::class);
    }

    public function receiptSetting(): HasOne
    {
        return $this->hasOne(ReceiptSetting::class);
    }

    public function integrationSetting(): HasOne
    {
        return $this->hasOne(IntegrationSetting::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(SettingsAuditLog::class);
    }

    public function staffInvitations(): HasMany
    {
        return $this->hasMany(StaffInvitation::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    /**
     * The plan currently in force: the store's subscription plan, falling back to
     * the default (forever-free) plan when there is none. Used by PlanGate.
     */
    public function currentPlan(): ?Plan
    {
        return $this->subscription?->plan ?? Plan::default();
    }

    /**
     * Ensure the three 1:1 settings rows + a default (FREE) subscription exist.
     * Called on store creation (RegisterStoreAction / factory) and idempotent so
     * it can backfill legacy stores. Returns $this with the relations loaded.
     */
    public function initializeSettings(): static
    {
        $this->storefrontSetting()->firstOrCreate([]);
        $this->receiptSetting()->firstOrCreate([]);
        $this->integrationSetting()->firstOrCreate([]);
        $this->subscription()->firstOrCreate([], ['plan_id' => Plan::default()?->id]);

        return $this->load(['storefrontSetting', 'receiptSetting', 'integrationSetting', 'subscription']);
    }

    public function activeOwnerCount(): int
    {
        return $this->memberships()
            ->where('role', StaffRole::OWNER->value)
            ->where('is_active', true)
            ->count();
    }
}
