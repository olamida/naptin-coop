<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportLog extends Model
{
    protected $fillable = [
        'batch_id',
        'type',
        'file_name',
        'total_rows',
        'success',
        'failed',
        'errors',
        'status',
        'created_by',
    ];

    protected $casts = [
        'errors' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function record(
        string $batchId,
        string $type,
        ?string $fileName,
        array $stats,
        string $status = 'completed',
        ?string $errorMessage = null,
    ): self {
        $errors = $stats['errors'] ?? [];

        if ($errorMessage) {
            $errors[] = ['file' => $errorMessage];
        }

        return static::create([
            'batch_id' => $batchId,
            'type' => $type,
            'file_name' => $fileName,
            'total_rows' => $stats['total_rows'] ?? 0,
            'success' => $stats['success'] ?? 0,
            'failed' => $stats['failed'] ?? 0,
            'errors' => $errors ?: null,
            'status' => $status,
            'created_by' => auth()->id(),
        ]);
    }
}
