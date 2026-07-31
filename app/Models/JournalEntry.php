<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    protected $fillable = [
        'entry_number', 'entry_date', 'description', 'reference_type', 'reference_id',
        'status', 'posted_by', 'posted_at',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'posted_at' => 'datetime',
        ];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopePosted(Builder $query): Builder
    {
        return $query->where('status', 'posted');
    }

    public function isBalanced(): bool
    {
        $this->loadMissing('lines');

        return $this->lines->sum('debit') === $this->lines->sum('credit');
    }

    public function post(): void
    {
        if ($this->status === 'posted') {
            throw new \RuntimeException('Journal entry already posted.');
        }

        if (!$this->isBalanced()) {
            throw new \RuntimeException('Journal entry is not balanced.');
        }

        $this->update([
            'status' => 'posted',
            'posted_by' => auth()->id(),
            'posted_at' => now(),
        ]);
    }

    public static function generateEntryNumber(): string
    {
        $prefix = 'JE-' . now()->format('Ymd') . '-';
        $last = static::where('entry_number', 'like', $prefix . '%')
            ->orderBy('entry_number', 'desc')
            ->value('entry_number');

        $next = $last ? (int) substr($last, -3) + 1 : 1;

        return $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}
