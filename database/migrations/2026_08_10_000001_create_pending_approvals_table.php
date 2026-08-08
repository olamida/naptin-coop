<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_approvals', function (Blueprint $table) {
            $table->id();
            $table->string('workflow');                 // period_reopen, loan_disbursement, dividend, withdrawal, ...
            $table->string('approvable_type');          // morph target
            $table->unsignedBigInteger('approvable_id');
            $table->string('required_role');            // semantic role slot (president, auditor, treasurer, ...)
            $table->string('status')->default('pending'); // pending | approved | rejected
            $table->foreignId('requested_by')->nullable()->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->unique(['workflow', 'approvable_type', 'approvable_id', 'required_role'], 'uq_approval_slot');
            $table->index(['workflow', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_approvals');
    }
};
