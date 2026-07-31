<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_deductions', function (Blueprint $table) {
            $table->decimal('expected_arrears', 15, 2)->default(0)->after('expected_purchase');
            $table->decimal('actual_arrears', 15, 2)->default(0)->after('actual_purchase');
        });

        Schema::table('monthly_payrolls', function (Blueprint $table) {
            $table->decimal('total_arrears', 15, 2)->default(0)->after('total_purchases');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_deductions', function (Blueprint $table) {
            $table->dropColumn(['expected_arrears', 'actual_arrears']);
        });

        Schema::table('monthly_payrolls', function (Blueprint $table) {
            $table->dropColumn('total_arrears');
        });
    }
};
