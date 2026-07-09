<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')->orderBy('id')->each(function ($product): void {
            if ($product->variant_groups !== null) {
                return;
            }

            $variants = DB::table('product_variants')->where('product_id', $product->id)->orderBy('sort_order')->get();
            if ($variants->isEmpty()) {
                return;
            }

            $options = $variants->map(fn ($variant) => [
                'id' => "legacy-{$variant->id}",
                'name' => $variant->name,
            ])->values()->all();

            DB::table('products')->where('id', $product->id)->update([
                'variant_groups' => json_encode([[
                    'id' => 'options',
                    'name' => 'Options',
                    'required' => true,
                    'options' => $options,
                ]]),
            ]);

            foreach ($variants as $variant) {
                DB::table('product_variants')->where('id', $variant->id)->update([
                    'option_values' => json_encode(['options' => "legacy-{$variant->id}"]),
                ]);
            }
        });
    }

    public function down(): void
    {
        DB::table('products')->whereNotNull('variant_groups')->orderBy('id')->each(function ($product): void {
            $groups = json_decode($product->variant_groups, true);
            if (($groups[0]['id'] ?? null) !== 'options') {
                return;
            }
            DB::table('products')->where('id', $product->id)->update(['variant_groups' => null]);
            DB::table('product_variants')->where('product_id', $product->id)->update(['option_values' => null]);
        });
    }
};
