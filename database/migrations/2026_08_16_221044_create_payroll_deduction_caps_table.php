<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payroll_deduction_caps', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Default Cooperative Deduction Cap');
            $table->decimal('default_max_percent', 5, 2)->default(33.33)->comment('Default max total deductions from net salary, e.g. 33.33% = 1/3');
            $table->decimal('hard_max_percent', 5, 2)->default(66.67)->comment('Absolute hard max even with override, e.g. 66.67% = 2/3 - cannot exceed');
            $table->text('description')->nullable()->comment('Based on employer agreement, typically 1/3 of basic salary');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_deduction_caps');
    }
};
