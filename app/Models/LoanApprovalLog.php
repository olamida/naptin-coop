<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanApprovalLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'user_id',
        'action',
        'old_status',
        'new_status',
        'notes',
        'ip_address',
        'user_agent',
    ];

    public static function record(int $loanId, string $action, ?string $oldStatus, string $newStatus, ?string $notes = null): self
    {
        return static::create([
            'loan_id' => $loanId,
            'user_id' => auth()->id(),
            'action' => $action,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'notes' => $notes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
