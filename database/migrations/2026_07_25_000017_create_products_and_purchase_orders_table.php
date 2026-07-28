<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->integer('stock_quantity')->default(0);
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_amount', 15, 2);
            $table->string('payment_type')->default('cash');
            $table->decimal('monthly_repayment', 15, 2)->default(0);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->string('status')->default('pending');
            $table->date('collected_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('products');
    }
};
