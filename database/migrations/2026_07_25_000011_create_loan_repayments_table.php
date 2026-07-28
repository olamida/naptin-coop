<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->decimal('amount', 15, 2);
            $table->decimal('principal_portion', 15, 2);
            $table->decimal('interest_portion', 15, 2);
            $table->decimal('outstanding_after', 15, 2);
            $table->string('payment_method')->default('cash');
            $table->string('source')->default('manual');
            $table->date('payment_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_repayments');
    }
};
