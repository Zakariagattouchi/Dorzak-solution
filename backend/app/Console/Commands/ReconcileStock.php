<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Console\Command;

/**
 * Recomputes cached stock from the append-only stock_movements ledger — the ledger
 * is the source of truth. Variant stock = sum of its movements; product stock = sum
 * of all its movements (which equals the variant sum when a product has variants).
 * See docs 03 §4.
 */
class ReconcileStock extends Command
{
    protected $signature = 'stock:reconcile';

    protected $description = 'Recompute product/variant stock from the stock_movements ledger';

    public function handle(): int
    {
        $products = 0;

        Product::query()->withoutStoreScope()->withTrashed()->chunkById(200, function ($chunk) use (&$products) {
            foreach ($chunk as $product) {
                foreach ($product->variants as $variant) {
                    $variant->forceFill([
                        'stock' => (int) StockMovement::where('variant_id', $variant->id)->sum('quantity_change'),
                    ])->saveQuietly();
                }

                $product->forceFill([
                    'stock' => (int) StockMovement::where('product_id', $product->id)->sum('quantity_change'),
                ])->saveQuietly();
                $products++;
            }
        });

        $this->info("Reconciled stock for {$products} products.");

        return self::SUCCESS;
    }
}
