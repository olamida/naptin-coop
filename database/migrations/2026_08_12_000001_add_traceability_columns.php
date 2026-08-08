<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('savings_transactions', function (Blueprint $table) {
            $table->foreignId('journal_entry_id')->nullable()->after('external_reference')->constrained('journal_entries')->nullOnDelete();
        });

        Schema::table('loan_repayments', function (Blueprint $table) {
            $table->decimal('fees_portion', 15, 2)->default(0)->after('interest_portion');
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->string('import_batch_id')->nullable()->after('admin_notes')->index();
            $table->string('external_reference')->nullable()->after('import_batch_id')->index();
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('import_batch_id')->nullable()->after('approved_by')->index();
            $table->string('external_reference')->nullable()->after('import_batch_id')->index();
        });

        Schema::table('dividends', function (Blueprint $table) {
            $table->string('import_batch_id')->nullable()->after('approved_by')->index();
            $table->string('external_reference')->nullable()->after('import_batch_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('dividends', function (Blueprint $table) {
            $table->dropIndex(['import_batch_id']);
            $table->dropIndex(['external_reference']);
            $table->dropColumn(['import_batch_id', 'external_reference']);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropIndex(['import_batch_id']);
            $table->dropIndex(['external_reference']);
            $table->dropColumn(['import_batch_id', 'external_reference']);
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->dropIndex(['import_batch_id']);
            $table->dropIndex(['external_reference']);
            $table->dropColumn(['import_batch_id', 'external_reference']);
        });

        Schema::table('loan_repayments', function (Blueprint $table) {
            $table->dropColumn('fees_portion');
        });

        Schema::table('savings_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('journal_entry_id');
        });
    }
};
