<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Encrypted backup settings
    |--------------------------------------------------------------------------
    |
    | encryption_key — the key used to AES-256-CBC encrypt scheduled backups
    | (app:backup-encrypted). Defaults to the application key when no dedicated
    | key is set. Files are uploaded to the S3 disk under backups/encrypted/
    | when S3 is configured, otherwise to the local disk.
    |
    */
    'encryption_key' => env('BACKUP_ENCRYPTION_KEY', env('APP_KEY')),
];
