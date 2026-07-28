<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('share_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('share_account_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->string('type');
            $table->decimal('shares', 15, 2);
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->string('status')->default('completed');
            $table->text('notes')->nullable();
            $table->timestamp('transaction_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('share_transactions');
    }
};
