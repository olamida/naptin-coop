<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('shares_enabled')->default(true)->after('membership_fee')->comment('Whether the Shares module is enabled');
            $table->boolean('dividends_enabled')->default(true)->after('shares_enabled')->comment('Whether the Dividends module is enabled');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['shares_enabled', 'dividends_enabled']);
        });
    }
};
