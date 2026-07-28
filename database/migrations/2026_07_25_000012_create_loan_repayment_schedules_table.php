<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_repayment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->integer('installment_number');
            $table->date('due_date');
            $table->decimal('principal_amount', 15, 2);
            $table->decimal('interest_amount', 15, 2);
            $table->decimal('total_amount', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->string('status')->default('pending');
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_repayment_schedules');
    }
};
