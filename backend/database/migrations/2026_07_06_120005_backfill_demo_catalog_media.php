<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $productImages = [
            'Dorzak Signature Cotton Hoodie' => 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=800',
            'Wireless Noise-Canceling Earbuds' => 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=800',
            'Artisan Cold Brew Coffee (750ml)' => 'https://images.unsplash.com/photo-1517701604599-bb29b565090c?w=800',
            'Minimalist Leather Cardholder' => 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=800',
            'Ergonomic Desk Mat' => 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=800',
            'Stainless Steel Water Bottle (1L)' => 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=800',
        ];

        foreach ($productImages as $name => $url) {
            DB::table('products')->where('name', $name)->whereNull('image_path')->update(['image_path' => $url]);
        }

        $categoryImages = [
            'Apparel & Fashion' => 'https://images.unsplash.com/photo-1445205170230-053b83016050?w=600',
            'Electronics & Tech' => 'https://images.unsplash.com/photo-1498049794561-7780e7231661?w=600',
            'Coffee & Beverages' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=600',
            'Accessories' => 'https://images.unsplash.com/photo-1523779917675-b6ed3a42a561?w=600',
            'Home & Office' => 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?w=600',
        ];

        foreach ($categoryImages as $name => $url) {
            DB::table('categories')->where('name', $name)->whereNull('image_path')->update(['image_path' => $url]);
        }
    }

    public function down(): void
    {
        // Preserve media references; this migration only fills previously empty demo rows.
    }
};
