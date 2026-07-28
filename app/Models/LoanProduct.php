<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'min_amount',
        'max_amount',
        'interest_rate',
        'repayment_method',
        'max_term_months',
        'max_loans_per_member',
        'max_total_amount_per_member',
        'processing_fee_pct',
        'requires_guarantors',
        'requires_collateral',
        'enabled',
    ];

    protected $casts = [
        'min_amount' => 'float',
        'max_amount' => 'float',
        'interest_rate' => 'float',
        'max_total_amount_per_member' => 'float',
        'processing_fee_pct' => 'float',
        'requires_guarantors' => 'boolean',
        'requires_collateral' => 'boolean',
        'enabled' => 'boolean',
    ];

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }
}
