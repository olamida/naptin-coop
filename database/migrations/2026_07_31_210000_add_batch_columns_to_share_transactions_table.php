<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('share_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('share_transactions', 'import_batch_id')) {
                $table->uuid('import_batch_id')->nullable()->after('notes');
            }
            if (! Schema::hasColumn('share_transactions', 'external_reference')) {
                $table->string('external_reference', 100)->nullable()->unique()->after('import_batch_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('share_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('share_transactions', 'external_reference')) {
                $table->dropColumn('external_reference');
            }
            if (Schema::hasColumn('share_transactions', 'import_batch_id')) {
                $table->dropColumn('import_batch_id');
            }
        });
    }
};
