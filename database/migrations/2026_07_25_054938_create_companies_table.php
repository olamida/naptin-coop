<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('NAPTIN Staff Thrift Cooperative');
            $table->string('slogan')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('website')->nullable();
            $table->string('logo_path')->nullable();
            $table->decimal('thrift_amount', 12, 2)->default(0)->comment('Default monthly thrift amount');
            $table->decimal('membership_fee', 12, 2)->default(0);
            $table->decimal('savings_interest_rate', 5, 2)->default(0)->comment('Annual interest rate %');
            $table->decimal('loan_interest_rate', 5, 2)->default(0)->comment('Default annual interest rate %');
            $table->integer('max_loan_multiplier')->default(3)->comment('Max loan as multiplier of savings');
            $table->text('footer_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
