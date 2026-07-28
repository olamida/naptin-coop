<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_payrolls', function (Blueprint $table) {
            $table->id();
            $table->string('payroll_number')->unique();
            $table->string('month');
            $table->integer('year');
            $table->integer('month_number');
            $table->decimal('total_savings', 15, 2)->default(0);
            $table->decimal('total_loan_repayments', 15, 2)->default(0);
            $table->decimal('total_share_contributions', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->integer('member_count')->default(0);
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_payrolls');
    }
};
