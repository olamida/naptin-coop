<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_counts', function (Blueprint $table) {
            $table->id();
            $table->date('count_date')->unique();
            $table->decimal('system_balance', 15, 2);
            $table->decimal('physical_count', 15, 2);
            $table->decimal('variance', 15, 2);
            $table->string('status'); // balanced, shortage, excess
            $table->foreignId('counted_by')->constrained('users');
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_counts');
    }
};
