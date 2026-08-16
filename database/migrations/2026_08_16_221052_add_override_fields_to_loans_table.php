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
        Schema::table('loans', function (Blueprint $table) {
            $table->decimal('applied_multiplier', 5, 2)->nullable()->comment('Multiplier used at application time, e.g. 2.5x');
            $table->decimal('approved_multiplier', 5, 2)->nullable()->comment('Final multiplier after EXCO review');
            $table->boolean('is_multiplier_override')->default(false);
            $table->foreignId('multiplier_override_id')->nullable()->constrained('member_loan_eligibility_overrides');
            $table->decimal('total_deduction_percent_at_approval', 5, 2)->nullable()->comment('Total deductions as % of salary at time of approval including this loan');
            $table->boolean('is_deduction_cap_override')->default(false);
            $table->text('deduction_override_reason')->nullable();
            $table->foreignId('deduction_override_approved_by')->nullable()->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn([
                'applied_multiplier',
                'approved_multiplier',
                'is_multiplier_override',
                'multiplier_override_id',
                'total_deduction_percent_at_approval',
                'is_deduction_cap_override',
                'deduction_override_reason',
                'deduction_override_approved_by',
            ]);
        });
    }
};
