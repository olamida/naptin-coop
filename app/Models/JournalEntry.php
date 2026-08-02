<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class JournalEntry extends Model
{
    protected $fillable = [
        'entry_number', 'entry_date', 'period', 'description', 'reference_type', 'reference_id',
        'status', 'posted_by', 'posted_at', 'uuid', 'prev_hash', 'hash',
        'reversal_of_id', 'reversal_reason', 'ip_address', 'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'posted_at' => 'datetime',
        ];
    }

    public function reversalOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversal_of_id');
    }

    public function reversals(): HasMany
    {
        return $this->hasMany(self::class, 'reversal_of_id');
    }

    public function isReversal(): bool
    {
        return $this->reversal_of_id !== null;
    }

    public static function computeHash(string $uuid, string $entryNumber, string $period, string $prevHash, int $id): string
    {
        return hash('sha256', implode('|', [$uuid, $entryNumber, $period, $prevHash, $id]));
    }

    /**
     * Recompute this entry's hash from its own fields and verify against the stored value.
     */
    public function verifyHash(?string $prevHash = null): bool
    {
        if ($this->hash === null) {
            return true;
        }

        $expected = self::computeHash(
            (string) $this->uuid,
            (string) $this->entry_number,
            (string) $this->period,
            (string) ($prevHash ?? $this->prev_hash),
            (int) $this->id
        );

        return hash_equals($this->hash, $expected);
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

        if (! $this->isBalanced()) {
            throw new \RuntimeException('Journal entry is not balanced.');
        }

        $period = substr((string) $this->entry_date, 0, 7);

        if (PeriodClose::isClosed($period)) {
            throw new \RuntimeException("Financial period {$period} is closed. New postings are not allowed.");
        }

        if (! $this->uuid) {
            $this->uuid = (string) Str::uuid();
        }

        if (! $this->period) {
            $this->period = $period;
        }

        $this->prev_hash = JournalEntry::whereNotNull('hash')->orderByDesc('id')->value('hash') ?? 'GENESIS';
        $this->hash = self::computeHash((string) $this->uuid, $this->entry_number, (string) $this->period, (string) $this->prev_hash, (int) $this->id);

        $this->update([
            'uuid' => $this->uuid,
            'period' => $this->period,
            'prev_hash' => $this->prev_hash,
            'hash' => $this->hash,
            'status' => 'posted',
            'posted_by' => auth()->id(),
            'posted_at' => now(),
        ]);
    }

    public static function generateEntryNumber(): string
    {
        $prefix = 'JE-'.now()->format('Ymd').'-';
        $last = static::where('entry_number', 'like', $prefix.'%')
            ->orderBy('entry_number', 'desc')
            ->value('entry_number');

        $next = $last ? (int) substr($last, -3) + 1 : 1;

        return $prefix.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}
