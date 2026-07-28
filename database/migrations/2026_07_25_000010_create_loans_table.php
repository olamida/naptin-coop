<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->string('loan_number')->unique();
            $table->string('type')->default('regular');
            $table->decimal('amount', 15, 2);
            $table->decimal('interest_rate', 5, 2)->default(0);
            $table->integer('tenure_months');
            $table->decimal('monthly_repayment', 15, 2);
            $table->decimal('total_repaid', 15, 2)->default(0);
            $table->decimal('outstanding', 15, 2);
            $table->date('application_date');
            $table->date('approval_date')->nullable();
            $table->date('disbursement_date')->nullable();
            $table->date('maturity_date')->nullable();
            $table->string('status')->default('pending');
            $table->text('purpose')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
