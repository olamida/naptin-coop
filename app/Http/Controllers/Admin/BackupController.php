<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DatabaseBackupService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function __construct(private readonly DatabaseBackupService $backup) {}

    public function download(): StreamedResponse
    {
        $filename = 'naptin_coop_backup_'.date('Y-m-d_His').'.sql';

        $dumpPath = $this->backup->dumpToTempFile();

        $this->backup->mirrorToS3IfConfigured($dumpPath, $filename);

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
}
