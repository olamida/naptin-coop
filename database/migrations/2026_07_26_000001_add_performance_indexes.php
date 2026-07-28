<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('savings_transactions', function (Blueprint $table) {
            $table->index(['type', 'status']);
            $table->index(['savings_account_id', 'transaction_date']);
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->index('status');
            $table->index(['member_id', 'status']);
        });

        Schema::table('loan_repayments', function (Blueprint $table) {
            $table->index(['loan_id', 'payment_date']);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->index(['member_id', 'status']);
            $table->index('order_group');
        });

        Schema::table('payroll_deductions', function (Blueprint $table) {
            $table->index(['monthly_payroll_id', 'member_id']);
        });

        Schema::table('monthly_payrolls', function (Blueprint $table) {
            $table->index(['month_number', 'year']);
        });

        Schema::table('dividend_distributions', function (Blueprint $table) {
            $table->index(['dividend_id', 'member_id']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('savings_transactions', function (Blueprint $table) {
            $table->dropIndex(['type', 'status']);
            $table->dropIndex(['savings_account_id', 'transaction_date']);
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->dropIndex('status');
            $table->dropIndex(['member_id', 'status']);
        });

        Schema::table('loan_repayments', function (Blueprint $table) {
            $table->dropIndex(['loan_id', 'payment_date']);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropIndex(['member_id', 'status']);
            $table->dropIndex('order_group');
        });

        Schema::table('payroll_deductions', function (Blueprint $table) {
            $table->dropIndex(['monthly_payroll_id', 'member_id']);
        });

        Schema::table('monthly_payrolls', function (Blueprint $table) {
            $table->dropIndex(['month_number', 'year']);
        });

        Schema::table('dividend_distributions', function (Blueprint $table) {
            $table->dropIndex(['dividend_id', 'member_id']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex('user_id');
        });
    }
};
