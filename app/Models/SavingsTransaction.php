<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingsTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'savings_account_id',
        'reference',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'status',
        'source',
        'notes',
        'payment_evidence_path',
        'transaction_date',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'transaction_date' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function savingsAccount(): BelongsTo
    {
        return $this->belongsTo(SavingsAccount::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
