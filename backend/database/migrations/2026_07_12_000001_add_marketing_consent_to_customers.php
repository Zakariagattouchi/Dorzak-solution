<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Marketing consent: campaigns may only reach customers who opted in. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('marketing_consent')->default(false)->after('email');
            $table->timestamp('consented_at')->nullable()->after('marketing_consent');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['marketing_consent', 'consented_at']);
        });
    }
};
