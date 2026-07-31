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
        Schema::table('loan_guarantors', function (Blueprint $table) {
            if (! Schema::hasColumn('loan_guarantors', 'accept_token')) {
                $table->string('accept_token', 64)->unique()->nullable()->after('notes');
            }
            if (! Schema::hasColumn('loan_guarantors', 'accepted_ip')) {
                $table->string('accepted_ip', 45)->nullable()->after('accept_token');
            }
            if (! Schema::hasColumn('loan_guarantors', 'accepted_user_agent')) {
                $table->text('accepted_user_agent')->nullable()->after('accepted_ip');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_guarantors', function (Blueprint $table) {
            $table->dropColumn(['accept_token', 'accepted_ip', 'accepted_user_agent']);
        });
    }
};
