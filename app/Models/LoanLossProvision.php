<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanLossProvision extends Model
{
    protected $fillable = [
        'loan_id', 'period', 'outstanding', 'days_past_due', 'classification', 'rate', 'provision_amount', 'journal_entry_id',
    ];

    protected function casts(): array
    {
        return [
            'outstanding' => 'decimal:2',
            'days_past_due' => 'integer',
            'rate' => 'decimal:2',
            'provision_amount' => 'decimal:2',
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }
}
