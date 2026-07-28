<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('staff_id')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->date('employment_date')->nullable();
            $table->date('retirement_date')->nullable();
            $table->text('address')->nullable();
            $table->string('state_of_origin')->nullable();
            $table->string('nin')->nullable();
            $table->string('grade_level')->nullable();
            $table->decimal('monthly_salary', 15, 2)->default(0);
            $table->string('status')->default('active');
            $table->boolean('is_exco')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
