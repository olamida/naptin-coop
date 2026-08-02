<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeriodClose extends Model
{
    protected $fillable = [
        'period', 'closed_at', 'closed_by', 'reopened_at', 'reopened_by', 'reopen_reason', 'is_closed', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'closed_at' => 'date',
            'reopened_at' => 'date',
            'is_closed' => 'boolean',
        ];
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function reopenedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reopened_by');
    }

    public static function isClosed(string $period): bool
    {
        return static::where('period', $period)->where('is_closed', true)->exists();
    }
}
