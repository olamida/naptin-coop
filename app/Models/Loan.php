<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'member_id',
        'loan_product_id',
        'parent_loan_id',
        'loan_number',
        'type',
        'amount',
        'interest_rate',
        'tenure_months',
        'monthly_repayment',
        'total_repaid',
        'outstanding',
        'application_date',
        'approval_date',
        'disbursement_date',
        'maturity_date',
        'status',
        'purpose',
        'rejection_reason',
        'admin_notes',
        'approved_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'monthly_repayment' => 'decimal:2',
        'total_repaid' => 'decimal:2',
        'outstanding' => 'decimal:2',
        'application_date' => 'date',
        'approval_date' => 'date',
        'disbursement_date' => 'date',
        'maturity_date' => 'date',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function parentLoan(): BelongsTo
    {
        return $this->belongsTo(Loan::class, 'parent_loan_id');
    }

    public function topupLoans(): HasMany
    {
        return $this->hasMany(Loan::class, 'parent_loan_id');
    }

    public function loanProduct(): BelongsTo
    {
        return $this->belongsTo(LoanProduct::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function repayments(): HasMany
    {
        return $this->hasMany(LoanRepayment::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(LoanRepaymentSchedule::class);
    }

    public function guarantors(): HasMany
    {
        return $this->hasMany(LoanGuarantor::class);
    }

    public function acceptedGuarantors(): HasMany
    {
        return $this->guarantors()->where('status', 'accepted');
    }

    public function approvalLogs(): HasMany
    {
        return $this->hasMany(LoanApprovalLog::class)->latest();
    }

    public function requiresGuarantors(): bool
    {
        return $this->loanProduct?->requires_guarantors ?? false;
    }

    public function scopeDisbursed(Builder $query): Builder
    {
        return $query->whereIn('status', ['disbursed', 'repaying']);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->disbursed()
            ->where('maturity_date', '<', now()->toDateString())
            ->where('outstanding', '>', 0);
    }

    public function scopeDefaulted(Builder $query): Builder
    {
        return $query->where('status', 'defaulted');
    }

    public function scopeInArrears(Builder $query): Builder
    {
        return $query->where('status', 'defaulted')
            ->orWhere(function ($q) {
                $q->disbursed()
                    ->where('maturity_date', '<', now()->toDateString())
                    ->where('outstanding', '>', 0);
            });
    }

    public function isOverdue(): bool
    {
        return in_array($this->status, ['disbursed', 'repaying'])
            && $this->maturity_date
            && $this->maturity_date->isPast()
            && $this->outstanding > 0;
    }

    public function daysOverdue(): int
    {
        if (! $this->maturity_date || ! $this->isOverdue()) {
            return 0;
        }

        return (int) $this->maturity_date->diffInDays(now());
    }

    public function canTopup(): bool
    {
        return in_array($this->status, ['disbursed', 'repaying']) && $this->outstanding > 0 && ! $this->parent_loan_id;
    }

    public function isTopup(): bool
    {
        return $this->parent_loan_id !== null;
    }
}
