<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollArrear extends Model
{
    use HasFactory;

    protected $fillable = [
        'monthly_payroll_id',
        'member_id',
        'component',
        'expected_amount',
        'actual_amount',
        'shortfall',
        'reason',
        'status',
        'recorded_by',
        'settled_at',
    ];

    protected $casts = [
        'expected_amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'shortfall' => 'decimal:2',
        'settled_at' => 'datetime',
    ];

    public function monthlyPayroll(): BelongsTo
    {
        return $this->belongsTo(MonthlyPayroll::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeSettled($query)
    {
        return $query->where('status', 'settled');
    }
}
