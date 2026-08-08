<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChartOfAccount extends Model
{
    protected $fillable = [
        'code', 'name', 'type', 'subtype', 'normal_side', 'is_control_account', 'control_module',
        'is_active', 'allow_manual_entry', 'parent_id', 'description',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_control_account' => 'boolean',
            'allow_manual_entry' => 'boolean',
        ];
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function getBalanceAttribute()
    {
        $debit = $this->journalLines()->sum('debit');
        $credit = $this->journalLines()->sum('credit');

        if ($this->normal_side === 'debit') {
            return $debit - $credit;
        }

        return $credit - $debit;
    }
}
