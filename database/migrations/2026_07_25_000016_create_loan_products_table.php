<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('min_amount', 15, 2)->default(0);
            $table->decimal('max_amount', 15, 2)->default(0);
            $table->decimal('interest_rate', 5, 2)->default(0);
            $table->string('repayment_method')->default('flat');
            $table->integer('max_term_months')->default(12);
            $table->decimal('processing_fee_pct', 5, 2)->default(0);
            $table->boolean('requires_guarantors')->default(false);
            $table->boolean('requires_collateral')->default(false);
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_products');
    }
};
