<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dividends', function (Blueprint $table) {
            $table->id();
            $table->string('dividend_number')->unique();
            $table->integer('year');
            $table->decimal('total_profit', 15, 2)->default(0);
            $table->decimal('total_distributed', 15, 2)->default(0);
            $table->integer('eligible_members')->default(0);
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('dividend_distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dividend_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->decimal('share_count', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('status')->default('pending');
            $table->date('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dividend_distributions');
        Schema::dropIfExists('dividends');
    }
};
