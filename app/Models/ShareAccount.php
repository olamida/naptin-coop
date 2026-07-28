<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShareAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'total_shares',
        'total_value',
        'share_price',
        'status',
    ];

    protected $casts = [
        'total_shares' => 'integer',
        'total_value' => 'decimal:2',
        'share_price' => 'decimal:2',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(ShareTransaction::class);
    }
}
