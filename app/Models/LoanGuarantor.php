<?php

namespace App\Models;

use App\Enums\GuarantorStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanGuarantor extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'member_id',
        'status',
        'notes',
        'responded_at',
        'accept_token',
        'token_expires_at',
        'accepted_ip',
        'accepted_user_agent',
    ];

    protected $casts = [
        'status' => GuarantorStatus::class,
        'responded_at' => 'datetime',
        'token_expires_at' => 'datetime',
    ];

    public function isValidToken(): bool
    {
        return $this->accept_token
            && $this->status === GuarantorStatus::Pending
            && $this->token_expires_at
            && $this->token_expires_at->isFuture();
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
