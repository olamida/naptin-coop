<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollDeduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'monthly_payroll_id',
        'member_id',
        'expected_savings',
        'expected_loan_repayment',
        'expected_share_contribution',
        'expected_purchase',
        'expected_arrears',
        'total_expected',
        'actual_savings',
        'actual_loan_repayment',
        'actual_share_contribution',
        'actual_purchase',
        'actual_arrears',
        'total_actual',
        'status',
    ];

    protected $casts = [
        'expected_savings' => 'decimal:2',
        'expected_loan_repayment' => 'decimal:2',
        'expected_share_contribution' => 'decimal:2',
        'expected_purchase' => 'decimal:2',
        'expected_arrears' => 'decimal:2',
        'total_expected' => 'decimal:2',
        'actual_savings' => 'decimal:2',
        'actual_loan_repayment' => 'decimal:2',
        'actual_share_contribution' => 'decimal:2',
        'actual_purchase' => 'decimal:2',
        'actual_arrears' => 'decimal:2',
        'total_actual' => 'decimal:2',
    ];

    public function monthlyPayroll(): BelongsTo
    {
        return $this->belongsTo(MonthlyPayroll::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
