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
        Schema::table('members', function (Blueprint $table) {
            $table->decimal('monthly_net_salary', 15, 2)->nullable()->comment('Net take-home after tax, from IPPIS');
            $table->date('expected_retirement_date')->nullable()->comment('For retirement recovery logic');
            $table->boolean('is_defaulter')->default(false)->comment('Flag for defaulters needing catch-up');
            $table->decimal('defaulter_outstanding_arrears', 15, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn([
                'monthly_net_salary',
                'expected_retirement_date',
                'is_defaulter',
                'defaulter_outstanding_arrears',
            ]);
        });
    }
};
