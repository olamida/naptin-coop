<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonthlyPayroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_number',
        'month',
        'year',
        'month_number',
        'total_savings',
        'total_loan_repayments',
        'total_share_contributions',
        'total_purchases',
        'grand_total',
        'member_count',
        'status',
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'month_number' => 'integer',
        'total_savings' => 'decimal:2',
        'total_loan_repayments' => 'decimal:2',
        'total_share_contributions' => 'decimal:2',
        'total_purchases' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'member_count' => 'integer',
    ];

    public function deductions(): HasMany
    {
        return $this->hasMany(PayrollDeduction::class);
    }
}
