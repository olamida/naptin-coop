<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Process\ExecutableFinder;

class BackupController extends Controller
{
    public function download(): StreamedResponse
    {
        $filename = 'naptin_coop_backup_'.date('Y-m-d_His').'.sql';

        $dumpPath = $this->dumpToTempFile($filename);

        $this->mirrorToS3IfConfigured($dumpPath, $filename);

        return response()->stream(function () use ($dumpPath) {
            $handle = fopen($dumpPath, 'rb');
            while (! feof($handle)) {
                echo fread($handle, 8192);
            }
            fclose($handle);
            @unlink($dumpPath);
        }, 200, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    private function dumpToTempFile(string $filename): string
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

    private function mirrorToS3IfConfigured(string $dumpPath, string $filename): void
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
}
