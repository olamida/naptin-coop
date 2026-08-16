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
        Schema::table('loan_products', function (Blueprint $table) {
            // requires_guarantors already exists - rename to requires_guarantor for consistency
            // But we'll just add the new columns
            $table->integer('min_guarantors')->default(1)->after('requires_guarantors');
            $table->integer('max_guarantors')->default(3)->after('min_guarantors');
            $table->boolean('allow_multiplier_override')->default(true)->comment('Can EXCO override multiplier for special cases')->after('max_guarantors');
            $table->boolean('allow_deduction_cap_override')->default(true)->comment('Can EXCO override 33% cap')->after('allow_multiplier_override');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_products', function (Blueprint $table) {
            $table->dropColumn([
                'min_guarantors',
                'max_guarantors',
                'allow_multiplier_override',
                'allow_deduction_cap_override',
            ]);
        });
    }
};
