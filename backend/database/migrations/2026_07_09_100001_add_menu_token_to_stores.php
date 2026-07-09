<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Add nullable first so existing rows don't violate the not-null constraint.
        Schema::table('stores', function (Blueprint $table): void {
            $table->string('menu_token', 32)->nullable()->unique()->after('id');
        });

        // Backfill existing stores with a unique random token.
        DB::table('stores')->whereNull('menu_token')->orderBy('id')->each(function ($row): void {
            do {
                $token = Str::random(32);
            } while (DB::table('stores')->where('menu_token', $token)->exists());

            DB::table('stores')->where('id', $row->id)->update(['menu_token' => $token]);
        });

        // Now tighten to NOT NULL.
        Schema::table('stores', function (Blueprint $table): void {
            $table->string('menu_token', 32)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table): void {
            $table->dropColumn('menu_token');
        });
    }
};
