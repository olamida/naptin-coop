<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class BackupEncrypted extends Command
{
    protected $signature = 'app:backup-encrypted';

    protected $description = 'Dump the database, encrypt it (AES-256-CBC) and store it on S3/local disk';

    public function handle(DatabaseBackupService $backup): int
    {
        $this->info('Dumping database…');

        $dumpPath = $backup->dumpToTempFile();
        $encPath = $backup->encryptToTempFile($dumpPath);

        $filename = 'naptin_coop_backup_'.date('Y-m-d_His').'.sql.enc';

        try {
            $backup->storeEncrypted($encPath, $filename);
        } finally {
            $backup->deleteTemp($dumpPath, $encPath);
        }

        ActivityLog::log('backup_encrypted', "Encrypted database backup stored as {$filename}");

        $this->info("Encrypted backup stored as {$filename}.");

        return self::SUCCESS;
    }
}
