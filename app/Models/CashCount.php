<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashCount extends Model
{
    use HasFactory;

    public const STATUS_BALANCED = 'balanced';

    public const STATUS_SHORTAGE = 'shortage';

    public const STATUS_EXCESS = 'excess';

    protected $fillable = [
        'count_date',
        'system_balance',
        'physical_count',
        'variance',
        'status',
        'counted_by',
        'verified_by',
        'notes',
    ];

    protected $casts = [
        'count_date' => 'date',
        'system_balance' => 'decimal:2',
        'physical_count' => 'decimal:2',
        'variance' => 'decimal:2',
    ];

    public function countedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counted_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
