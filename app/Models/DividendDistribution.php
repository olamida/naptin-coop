<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DividendDistribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'dividend_id',
        'member_id',
        'share_count',
        'amount',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'share_count' => 'integer',
        'amount' => 'decimal:2',
        'paid_at' => 'date',
    ];

    public function dividend(): BelongsTo
    {
        return $this->belongsTo(Dividend::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
