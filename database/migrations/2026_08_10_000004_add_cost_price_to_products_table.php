<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('cost_price', 15, 2)->nullable()->after('unit_price');
        });

        // Existing stock is carried at selling price by default so the migration
        // is data-safe; the sales-margin split only takes effect once a real cost
        // is recorded.
        DB::table('products')->update(['cost_price' => DB::raw('unit_price')]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });
    }
};
