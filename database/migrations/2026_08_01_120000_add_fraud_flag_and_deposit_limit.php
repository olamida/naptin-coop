<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->boolean('is_fraud_flagged')->default(false)->after('is_exco');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->decimal('auto_approve_deposit_limit', 15, 2)
                ->nullable()
                ->default(200000)
                ->after('max_loan_multiplier');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('is_fraud_flagged');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('auto_approve_deposit_limit');
        });
    }
};
