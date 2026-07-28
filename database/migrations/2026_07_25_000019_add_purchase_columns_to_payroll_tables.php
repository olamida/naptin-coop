<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_deductions', function (Blueprint $table) {
            $table->decimal('expected_purchase', 15, 2)->default(0)->after('expected_share_contribution');
            $table->decimal('actual_purchase', 15, 2)->default(0)->after('actual_share_contribution');
        });

        Schema::table('monthly_payrolls', function (Blueprint $table) {
            $table->decimal('total_purchases', 15, 2)->default(0)->after('total_share_contributions');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_deductions', function (Blueprint $table) {
            $table->dropColumn(['expected_purchase', 'actual_purchase']);
        });

        Schema::table('monthly_payrolls', function (Blueprint $table) {
            $table->dropColumn('total_purchases');
        });
    }
};
