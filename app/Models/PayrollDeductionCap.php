<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollDeductionCap extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'default_max_percent',
        'hard_max_percent',
        'description',
        'is_active',
    ];

    protected $casts = [
        'default_max_percent' => 'decimal:2',
        'hard_max_percent' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
