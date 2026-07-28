<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Company extends Model
{
    protected $fillable = [
        'name',
        'slogan',
        'registration_number',
        'email',
        'phone',
        'address',
        'website',
        'logo_path',
        'thrift_amount',
        'membership_fee',
        'savings_interest_rate',
        'loan_interest_rate',
        'max_loan_multiplier',
        'footer_note',
    ];

    protected $casts = [
        'thrift_amount' => 'decimal:2',
        'membership_fee' => 'decimal:2',
        'savings_interest_rate' => 'decimal:2',
        'loan_interest_rate' => 'decimal:2',
        'max_loan_multiplier' => 'integer',
    ];

    public static function instance(): static
    {
        return static::firstOrCreate([], [
            'name' => 'NAPTIN Staff Thrift Cooperative',
        ]);
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        return asset('storage/' . $this->logo_path);
    }
}
