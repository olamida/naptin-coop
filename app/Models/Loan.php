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
        'processing_fee',
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
        'import_batch_id',
        'external_reference',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processing_fee' => 'decimal:2',
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

    /**
     * Number of paid vs total instalments for the repayment schedule progress.
     *
     * @return array{paid: int, total: int, percent: float, next_due: ?LoanRepaymentSchedule}
     */
    public function scheduleProgress(): array
    {
        $paid = $this->schedules->where('status', 'paid')->count();
        $total = $this->schedules->count();
        $percent = $total > 0 ? round(($paid / $total) * 100, 1) : 0;
        $nextDue = $total > 0 && $paid < $total
            ? $this->schedules->where('status', '!=', 'paid')->sortBy('installment_number')->first()
            : null;

        return [
            'paid' => $paid,
            'total' => $total,
            'percent' => $percent,
            'next_due' => $nextDue,
        ];
    }

    /**
     * Loan lifecycle events for the avatar timeline (oldest first).
     *
     * Each event carries: title, date, actor_name, actor_avatar, actor_initials,
     * icon, color and an optional description/notes.
     *
     * @return array<int, array<string, mixed>>
     */
    public function lifecycleTimeline(): array
    {
        $events = [];

        // 1. Application
        $events[] = [
            'title' => 'Application submitted',
            'date' => $this->application_date?->copy() ?? $this->created_at,
            'actor_name' => $this->member?->full_name ?? 'System',
            'actor_avatar' => $this->member?->photo_url ?? '',
            'actor_initials' => $this->member?->initials ?? 'SY',
            'icon' => 'description',
            'color' => 'bg-blue-500',
            'description' => 'Applied for a '.ucfirst($this->type).' loan of ₦'.number_format((float) $this->amount, 2),
        ];

        // 2. Guarantor responses
        foreach ($this->guarantors->sortBy('responded_at') as $guarantor) {
            if (! $guarantor->responded_at) {
                continue;
            }
            $accepted = $guarantor->status->value === 'accepted';
            $events[] = [
                'title' => $accepted ? 'Guarantor accepted' : 'Guarantor declined',
                'date' => $guarantor->responded_at,
                'actor_name' => $guarantor->member?->full_name ?? 'Guarantor',
                'actor_avatar' => $guarantor->member?->photo_url ?? '',
                'actor_initials' => $guarantor->member?->initials ?? 'G',
                'icon' => $accepted ? 'thumb_up' : 'thumb_down',
                'color' => $accepted ? 'bg-emerald-500' : 'bg-red-500',
                'description' => $accepted
                    ? 'Guaranteed this loan for ₦'.number_format((float) $this->amount, 2)
                    : 'Declined to guarantee this loan',
            ];
        }

        // 3. Approval
        $approvalLog = $this->approvalLogs->firstWhere('action', 'approved');
        $approver = $this->approvedBy ?? $approvalLog?->user;
        if ($this->approval_date || $approvalLog) {
            $events[] = [
                'title' => 'Loan approved',
                'date' => $this->approval_date?->copy() ?? $approvalLog->created_at,
                'actor_name' => $approver?->name ?? 'System',
                'actor_avatar' => $approver?->avatar_url ?? '',
                'actor_initials' => $approver?->initials ?? 'AP',
                'icon' => 'check_circle',
                'color' => 'bg-green-600',
                'description' => $approvalLog?->notes,
            ];
        }

        // 4. Disbursement
        $disbursedLog = $this->approvalLogs->firstWhere('action', 'disbursed');
        if ($this->disbursement_date || $disbursedLog) {
            $events[] = [
                'title' => 'Loan disbursed',
                'date' => $this->disbursement_date?->copy() ?? $disbursedLog->created_at,
                'actor_name' => $disbursedLog?->user?->name ?? 'System',
                'actor_avatar' => $disbursedLog?->user?->avatar_url ?? '',
                'actor_initials' => $disbursedLog?->user?->initials ?? 'DI',
                'icon' => 'account_balance',
                'color' => 'bg-purple-600',
                'description' => $disbursedLog?->notes,
            ];
        }

        // 5. Rejection
        $rejectedLog = $this->approvalLogs->firstWhere('action', 'rejected');
        if ($this->status === 'rejected' && $rejectedLog) {
            $events[] = [
                'title' => 'Loan rejected',
                'date' => $rejectedLog->created_at,
                'actor_name' => $rejectedLog->user?->name ?? 'System',
                'actor_avatar' => $rejectedLog->user?->avatar_url ?? '',
                'actor_initials' => $rejectedLog->user?->initials ?? 'RJ',
                'icon' => 'cancel',
                'color' => 'bg-red-600',
                'description' => $rejectedLog->notes ?? $this->rejection_reason,
            ];
        }

        // 6. Repaying progress (only once disbursed)
        if (in_array($this->status, ['disbursed', 'repaying'])) {
            $progress = $this->scheduleProgress();
            $events[] = [
                'title' => 'Repayment in progress',
                'date' => $this->disbursement_date?->copy() ?? $this->created_at,
                'actor_name' => $this->member?->full_name ?? 'Member',
                'actor_avatar' => $this->member?->photo_url ?? '',
                'actor_initials' => $this->member?->initials ?? 'MB',
                'icon' => 'payments',
                'color' => 'bg-indigo-500',
                'description' => $progress['total'] > 0
                    ? $progress['paid'].' of '.$progress['total'].' instalments paid'.(
                        $progress['next_due']
                            ? ' · Next due '.$progress['next_due']->due_date->format('d M Y').' (₦'.number_format((float) $progress['next_due']->total_amount, 2).')'
                            : ''
                    )
                    : 'Repayments recorded against this loan',
                'progress' => $progress,
            ];
        }

        // 7. Completion
        $completedLog = $this->approvalLogs->firstWhere('new_status', 'completed');
        if ($this->status === 'completed') {
            $events[] = [
                'title' => 'Loan completed',
                'date' => $completedLog?->created_at ?? $this->updated_at,
                'actor_name' => $completedLog?->user?->name ?? 'System',
                'actor_avatar' => $completedLog?->user?->avatar_url ?? '',
                'actor_initials' => $completedLog?->user?->initials ?? 'CO',
                'icon' => 'task_alt',
                'color' => 'bg-emerald-600',
                'description' => 'Fully repaid — ₦'.number_format((float) $this->total_repaid, 2).' collected',
            ];
        }

        // 8. Other approval-log events (notes, status touches) not already shown
        foreach ($this->approvalLogs as $log) {
            if (in_array($log->action, ['approved', 'disbursed', 'rejected'])) {
                continue;
            }
            if ($log->action === 'submitted') {
                continue;
            }
            $events[] = [
                'title' => ucfirst(str_replace('_', ' ', $log->action)),
                'date' => $log->created_at,
                'actor_name' => $log->user?->name ?? 'System',
                'actor_avatar' => $log->user?->avatar_url ?? '',
                'actor_initials' => $log->user?->initials ?? 'SY',
                'icon' => 'edit_note',
                'color' => 'bg-gray-400',
                'description' => $log->notes ?? (($log->old_status && $log->new_status && $log->old_status !== $log->new_status)
                    ? ucfirst($log->old_status).' → '.ucfirst($log->new_status)
                    : null),
            ];
        }

        usort($events, fn ($a, $b) => $a['date'] <=> $b['date']);

        return $events;
    }
}
