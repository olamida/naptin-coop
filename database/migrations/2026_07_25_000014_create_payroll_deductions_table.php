<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_payroll_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->decimal('expected_savings', 15, 2)->default(0);
            $table->decimal('expected_loan_repayment', 15, 2)->default(0);
            $table->decimal('expected_share_contribution', 15, 2)->default(0);
            $table->decimal('total_expected', 15, 2)->default(0);
            $table->decimal('actual_savings', 15, 2)->default(0);
            $table->decimal('actual_loan_repayment', 15, 2)->default(0);
            $table->decimal('actual_share_contribution', 15, 2)->default(0);
            $table->decimal('total_actual', 15, 2)->default(0);
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_deductions');
    }
};
