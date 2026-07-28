<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SavingsAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'account_number',
        'balance',
        'interest_rate',
        'status',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'interest_rate' => 'decimal:2',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(SavingsTransaction::class);
    }
}
