<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Product create/update with variant sync and the parent-stock invariant
 * (when a product has variants, its stock == sum of variant stock). All stock
 * changes flow through StockService so the ledger stays consistent. See docs 02 (Page 3).
 */
class ProductService
{
    public function __construct(private readonly StockService $stock) {}

    public function create(array $data, ?User $user = null): Product
    {
        return DB::transaction(function () use ($data, $user): Product {
            $variants = $data['variants'] ?? [];
            $attrs = $this->attributes($data);

            // Auto-generate a SKU when none supplied (mirrors the frontend default).
            if (empty($attrs['sku'])) {
                $attrs['sku'] = 'PROD-'.strtoupper(Str::random(6));
            }

            // Parent stock is derived from variants when present.
            if ($variants !== []) {
                $attrs['stock'] = array_sum(array_map(fn ($v) => (int) ($v['stock'] ?? 0), $variants));
            } elseif (! ($attrs['track_stock'] ?? true)) {
                $attrs['stock'] = 0;
            }

            $product = Product::create($attrs);

            foreach ($variants as $i => $variant) {
                $product->variants()->create([
                    'name' => $variant['name'],
                    'option_values' => $variant['option_values'] ?? null,
                    'price' => $variant['price'] ?? $product->price,
                    'stock' => (int) ($variant['stock'] ?? 0),
                    'sku' => $variant['sku'] ?? null,
                    'sort_order' => $i,
                    'is_active' => $variant['is_active'] ?? true,
                ]);
            }

            $product->load('variants');
            $this->stock->initializeProduct($product, $user);

            return $product->load('category');
        });
    }

    public function update(Product $product, array $data, ?User $user = null): Product
    {
        return DB::transaction(function () use ($product, $data, $user): Product {
            $hasVariantsKey = array_key_exists('variants', $data);
            $variants = $data['variants'] ?? [];

            $attrs = $this->attributes($data);
            $newStock = $attrs['stock'] ?? null;
            unset($attrs['stock']); // stock only moves through StockService

            $product->fill($attrs)->save();

            if ($hasVariantsKey) {
                $this->syncVariants($product, $variants, $user);
            }

            $product->load('variants');

            if ($product->variants->isNotEmpty()) {
                // Parent stock mirrors the variant sum.
                $product->stock = $product->variants->sum('stock');
                $product->save();
            } elseif ($product->track_stock && $newStock !== null && (int) $newStock !== $product->stock) {
                $this->stock->adjustProductStock($product, (int) $newStock, $user, 'Manual stock edit');
            } elseif (! $product->track_stock && $newStock !== null) {
                $product->stock = (int) $newStock;
                $product->save();
            }

            return $product->fresh(['variants', 'category']);
        });
    }

    private function syncVariants(Product $product, array $incoming, ?User $user): void
    {
        $keepIds = array_filter(array_map(fn ($v) => $v['id'] ?? null, $incoming));

        // Delete variants no longer present.
        $product->variants()->whereNotIn('id', $keepIds ?: [0])->delete();

        foreach ($incoming as $i => $data) {
            if (! empty($data['id'])) {
                $variant = $product->variants()->find($data['id']);
                if (! $variant) {
                    continue;
                }
                $variant->fill([
                    'name' => $data['name'],
                    'option_values' => $data['option_values'] ?? null,
                    'price' => $data['price'] ?? $variant->price,
                    'sku' => $data['sku'] ?? $variant->sku,
                    'is_active' => $data['is_active'] ?? true,
                    'sort_order' => $i,
                ])->save();

                if (array_key_exists('stock', $data) && (int) $data['stock'] !== $variant->stock) {
                    $this->stock->adjustVariantStock($product, $variant, (int) $data['stock'], $user);
                }
            } else {
                $variant = $product->variants()->create([
                    'name' => $data['name'],
                    'option_values' => $data['option_values'] ?? null,
                    'price' => $data['price'] ?? $product->price,
                    'stock' => (int) ($data['stock'] ?? 0),
                    'sku' => $data['sku'] ?? null,
                    'sort_order' => $i,
                    'is_active' => $data['is_active'] ?? true,
                ]);

                if ($variant->stock !== 0) {
                    $this->stock->writeMovement(
                        $product, StockMovementType::INITIAL, $variant->stock, $variant->stock, $variant->id, $user,
                    );
                }
            }
        }
    }

    /** Map validated API fields to product columns. */
    private function attributes(array $data): array
    {
        $map = [
            'name', 'name_ar', 'description', 'description_ar', 'price', 'reduced_price', 'cost', 'category_id',
            'sku', 'unit', 'label_name', 'label_color', 'taxable', 'track_stock',
            'stock', 'min_stock', 'show_in_online_store', 'is_featured', 'is_active', 'variant_groups',
            'additional_images', 'image_focus',
        ];

        $attrs = array_intersect_key($data, array_flip($map));

        if (array_key_exists('image_url', $data)) {
            $attrs['image_path'] = $data['image_url'];
        }

        return $attrs;
    }
}
