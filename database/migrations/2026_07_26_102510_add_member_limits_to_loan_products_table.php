<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_products', function (Blueprint $table) {
            $table->integer('max_loans_per_member')->nullable()->after('max_term_months');
            $table->decimal('max_total_amount_per_member', 15, 2)->nullable()->after('max_loans_per_member');
        });
    }

    public function down(): void
    {
        Schema::table('loan_products', function (Blueprint $table) {
            $table->dropColumn(['max_loans_per_member', 'max_total_amount_per_member']);
        });
    }
};
