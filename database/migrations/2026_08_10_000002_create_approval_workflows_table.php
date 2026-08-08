<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_workflows', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('required_permission')->nullable();   // Spatie permission gate for checkers
            $table->json('required_roles')->nullable();          // semantic role slots (president, auditor, treasurer, ...)
            $table->decimal('threshold_amount', 15, 2)->nullable(); // only required when amount > threshold
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_workflows');
    }
};
