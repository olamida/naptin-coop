<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberLoanEligibilityOverride extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'loan_product_id',
        'custom_multiplier',
        'custom_max_deduction_percent',
        'custom_max_amount',
        'reason_category',
        'reason_details',
        'approved_by',
        'second_approved_by',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected $casts = [
        'custom_multiplier' => 'decimal:2',
        'custom_max_deduction_percent' => 'decimal:2',
        'custom_max_amount' => 'decimal:2',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_active' => 'boolean',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function loanProduct(): BelongsTo
    {
        return $this->belongsTo(LoanProduct::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function secondApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'second_approved_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('valid_from', '<=', now()->toDateString())
            ->where(function ($q) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', now()->toDateString());
            });
    }
}
