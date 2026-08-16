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
        Schema::create('member_loan_eligibility_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members');
            $table->foreignId('loan_product_id')->constrained('loan_products');
            $table->decimal('custom_multiplier', 5, 2)->nullable()->comment('Override multiplier for this member+product, e.g. 4.5');
            $table->decimal('custom_max_deduction_percent', 5, 2)->nullable()->comment('Override max deduction from salary, e.g. 60% for retiring member');
            $table->decimal('custom_max_amount', 15, 2)->nullable()->comment('Override absolute max amount');
            $table->enum('reason_category', ['retirement_recovery', 'defaulter_catchup', 'long_service_goodwill', 'emergency_medical', 'exco_discretion', 'agm_approval', 'other']);
            $table->text('reason_details')->comment('Detailed justification');
            $table->foreignId('approved_by')->constrained('users')->comment('EXCO who approved override');
            $table->foreignId('second_approved_by')->nullable()->constrained('users')->comment('Second approval for >50% deduction');
            $table->date('valid_from');
            $table->date('valid_until')->nullable()->comment('Temporary override e.g. for 6 months to recover loan before retirement');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_loan_eligibility_overrides');
    }
};
