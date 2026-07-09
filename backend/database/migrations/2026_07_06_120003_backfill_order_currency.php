<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('stores')->select(['id', 'currency'])->orderBy('id')->each(function ($store): void {
            DB::table('orders')->where('store_id', $store->id)->update([
                'currency_code' => $store->currency ?: 'QAR',
            ]);
        });
    }

    public function down(): void
    {
        // Currency snapshots intentionally remain valid when rolling this data-only migration back.
    }
};
