<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'totp_secret')) {
                $table->text('totp_secret')->nullable()->after('must_change_password');
            }
            if (!Schema::hasColumn('users', 'totp_enabled')) {
                $table->boolean('totp_enabled')->default(false)->after('totp_secret');
            }
            if (!Schema::hasColumn('users', 'totp_recovery_codes')) {
                $table->text('totp_recovery_codes')->nullable()->after('totp_enabled');
            }
            if (!Schema::hasColumn('users', 'totp_confirmed_at')) {
                $table->timestamp('totp_confirmed_at')->nullable()->after('totp_recovery_codes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['totp_secret', 'totp_enabled', 'totp_recovery_codes', 'totp_confirmed_at']);
        });
    }
};
