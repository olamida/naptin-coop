<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\ExecutableFinder;

/**
 * Database dump + optional AES-256-CBC encryption + remote mirroring.
 *
 * Shared by the manual admin backup download (BackupController) and the
 * scheduled encrypted backup command (app:backup-encrypted).
 */
class DatabaseBackupService
{
    public function dumpToTempFile(): string
    {
        $host = config('database.connections.mysql.host', '127.0.0.1');
        $port = config('database.connections.mysql.port', '3306');
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $command = [
            $this->resolveBinary(),
            '--host='.$host,
            '--port='.$port,
            '--user='.$username,
            '--password='.$password,
            '--single-transaction',
            '--routines',
            '--triggers',
            $database,
        ];

        $temp = tempnam(sys_get_temp_dir(), 'naptin_bak_');
        if ($temp === false) {
            abort(500, 'Unable to allocate a temporary file for the backup.');
        }

        $process = proc_open($command, [1 => ['file', $temp, 'w'], 2 => ['pipe', 'w']], $pipes);
        if (! is_resource($process)) {
            @unlink($temp);
            abort(500, 'Unable to start the database dump process.');
        }

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            @unlink($temp);
            abort(500, 'Database dump failed: '.trim((string) $stderr));
        }

        return $temp;
    }

    /**
     * Encrypt a plaintext file in place (AES-256-CBC), returning the path of
     * the new .enc sibling. The random IV is prepended to the ciphertext.
     */
    public function encryptToTempFile(string $plainPath): string
    {
        $key = hash('sha256', (string) config('backup.encryption_key', config('app.key')), true);
        $iv = random_bytes(16);

        $plain = file_get_contents($plainPath);
        if ($plain === false) {
            abort(500, 'Unable to read the backup dump for encryption.');
        }

        $ciphertext = openssl_encrypt($plain, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        if ($ciphertext === false) {
            abort(500, 'Unable to encrypt the backup dump.');
        }

        $encPath = $plainPath.'.enc';
        file_put_contents($encPath, base64_encode($iv.$ciphertext));

        return $encPath;
    }

    /**
     * Store an encrypted backup on S3 (when configured) or on the local backups disk.
     */
    public function storeEncrypted(string $encPath, string $filename): void
    {
        if (config('filesystems.disks.s3.key') && config('filesystems.disks.s3.bucket')) {
            try {
                Storage::disk('s3')->put('backups/encrypted/'.$filename, fopen($encPath, 'rb'));

                return;
            } catch (\Throwable $e) {
                Log::warning('S3 encrypted backup upload failed: '.$e->getMessage());
            }
        }

        Storage::disk('local')->put('backups/encrypted/'.$filename, fopen($encPath, 'rb'));
    }

    /**
     * Mirror an unencrypted dump to S3 when configured (kept for the manual download flow).
     */
    public function mirrorToS3IfConfigured(string $dumpPath, string $filename): void
    {
        if (! config('filesystems.disks.s3.key') || ! config('filesystems.disks.s3.bucket')) {
            return;
        }

        try {
            Storage::disk('s3')->put('backups/'.$filename, fopen($dumpPath, 'rb'));
        } catch (\Throwable $e) {
            Log::warning('S3 backup upload failed: '.$e->getMessage());
        }
    }

    public function deleteTemp(string ...$paths): void
    {
        foreach ($paths as $path) {
            @unlink($path);
        }
    }

    private function resolveBinary(): string
    {
        $configured = env('MYSQLDUMP_PATH');
        if ($configured && is_file($configured)) {
            return $configured;
        }

        $onPath = (new ExecutableFinder)->find('mysqldump');
        if ($onPath) {
            return $onPath;
        }

        foreach (['/usr/bin/mysqldump', '/usr/local/bin/mysqldump', '/opt/homebrew/bin/mysqldump'] as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return 'mysqldump';
    }
}
