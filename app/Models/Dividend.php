<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dividend extends Model
{
    use HasFactory;

    protected $fillable = [
        'dividend_number',
        'year',
        'total_profit',
        'total_distributed',
        'eligible_members',
        'status',
        'notes',
        'approved_by',
        'import_batch_id',
        'external_reference',
    ];

    protected $casts = [
        'year' => 'integer',
        'total_profit' => 'decimal:2',
        'total_distributed' => 'decimal:2',
        'eligible_members' => 'integer',
    ];

    public function distributions(): HasMany
    {
        return $this->hasMany(DividendDistribution::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
