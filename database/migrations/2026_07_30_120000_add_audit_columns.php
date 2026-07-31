<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('savings_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('savings_transactions', 'import_batch_id')) {
                $table->uuid('import_batch_id')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('savings_transactions', 'external_reference')) {
                $table->string('external_reference', 100)->nullable()->unique()->after('import_batch_id');
            }
        });

        Schema::table('loan_repayments', function (Blueprint $table) {
            if (!Schema::hasColumn('loan_repayments', 'import_batch_id')) {
                $table->uuid('import_batch_id')->nullable()->after('payment_date');
            }
            if (!Schema::hasColumn('loan_repayments', 'external_reference')) {
                $table->string('external_reference', 100)->nullable()->unique()->after('import_batch_id');
            }
        });

        Schema::table('members', function (Blueprint $table) {
            if (!Schema::hasColumn('members', 'import_batch_id')) {
                $table->uuid('import_batch_id')->nullable()->after('nin');
            }
            if (!Schema::hasColumn('members', 'external_reference')) {
                $table->string('external_reference', 100)->nullable()->unique()->after('import_batch_id');
            }
        });

        Schema::table('loan_guarantors', function (Blueprint $table) {
            if (!Schema::hasColumn('loan_guarantors', 'accept_token')) {
                $table->string('accept_token', 64)->nullable()->unique()->after('notes');
            }
            if (!Schema::hasColumn('loan_guarantors', 'token_expires_at')) {
                $table->timestamp('token_expires_at')->nullable()->after('accept_token');
            }
            if (!Schema::hasColumn('loan_guarantors', 'accepted_ip')) {
                $table->string('accepted_ip', 45)->nullable()->after('token_expires_at');
            }
            if (!Schema::hasColumn('loan_guarantors', 'accepted_user_agent')) {
                $table->text('accepted_user_agent')->nullable()->after('accepted_ip');
            }
        });

        Schema::table('loan_approval_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('loan_approval_logs', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('notes');
            }
            if (!Schema::hasColumn('loan_approval_logs', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip_address');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'must_change_password')) {
                $table->boolean('must_change_password')->default(false)->after('member_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('savings_transactions', function (Blueprint $table) {
            $table->dropColumn(['import_batch_id', 'external_reference']);
        });
        Schema::table('loan_repayments', function (Blueprint $table) {
            $table->dropColumn(['import_batch_id', 'external_reference']);
        });
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['import_batch_id', 'external_reference']);
        });
        Schema::table('loan_guarantors', function (Blueprint $table) {
            $table->dropColumn(['accept_token', 'token_expires_at', 'accepted_ip', 'accepted_user_agent']);
        });
        Schema::table('loan_approval_logs', function (Blueprint $table) {
            $table->dropColumn(['ip_address', 'user_agent']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('must_change_password');
        });
    }
};
