<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('savings_account_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->string('type');
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->string('status')->default('completed');
            $table->string('source')->default('manual');
            $table->text('notes')->nullable();
            $table->timestamp('transaction_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_transactions');
    }
};
