<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
            $table->string('period', 7)->nullable()->after('entry_date');
            $table->string('prev_hash', 64)->nullable()->after('period');
            $table->string('hash', 64)->nullable()->unique()->after('prev_hash');
            $table->foreignId('reversal_of_id')->nullable()->after('status')->constrained('journal_entries')->nullOnDelete();
            $table->text('reversal_reason')->nullable()->after('reversal_of_id');
            $table->ipAddress('ip_address')->nullable()->after('reversal_reason');
            $table->string('user_agent')->nullable()->after('ip_address');
        });

        // Backfill hashes + periods for pre-existing entries in chronological order.
        $entries = DB::table('journal_entries')->orderBy('id')->get();
        $prevHash = 'GENESIS';

        foreach ($entries as $entry) {
            $period = substr((string) $entry->entry_date, 0, 7);
            $uuid = $entry->uuid ?? (string) Str::uuid();
            $hash = self::computeHash($uuid, $entry->entry_number, $period, $prevHash, $entry->id);

            DB::table('journal_entries')->where('id', $entry->id)->update([
                'uuid' => $uuid,
                'period' => $period,
                'prev_hash' => $prevHash,
                'hash' => $hash,
            ]);

            $prevHash = $hash;
        }

        // Prevent mutation/deletion of posted entries at the database level (MySQL only;
        // sqlite test environments enforce immutability at the application layer).
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::unprepared(
                'CREATE TRIGGER prevent_journal_entry_update '
                .'BEFORE UPDATE ON journal_entries '
                .'FOR EACH ROW '
                ."IF OLD.status = 'posted' THEN SIGNAL SQLSTATE '45000' "
                ."SET MESSAGE_TEXT = 'Posted journal entries are immutable'; "
                .'END IF'
            );

            DB::unprepared(
                'CREATE TRIGGER prevent_journal_entry_delete '
                .'BEFORE DELETE ON journal_entries '
                .'FOR EACH ROW '
                ."IF OLD.status = 'posted' THEN SIGNAL SQLSTATE '45000' "
                ."SET MESSAGE_TEXT = 'Posted journal entries are immutable'; "
                .'END IF'
            );
        }
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS prevent_journal_entry_update');
        DB::unprepared('DROP TRIGGER IF EXISTS prevent_journal_entry_delete');

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropUnique(['hash']);
            $table->dropForeign(['reversal_of_id']);
            $table->dropColumn([
                'uuid', 'period', 'prev_hash', 'hash', 'reversal_of_id', 'reversal_reason', 'ip_address', 'user_agent',
            ]);
        });
    }

    private static function computeHash(string $uuid, string $entryNumber, string $period, string $prevHash, int $id): string
    {
        return hash('sha256', implode('|', [$uuid, $entryNumber, $period, $prevHash, $id]));
    }
};
