<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShareTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'share_account_id',
        'reference',
        'type',
        'shares',
        'amount',
        'balance_after',
        'status',
        'notes',
        'transaction_date',
        'import_batch_id',
        'external_reference',
    ];

    protected $casts = [
        'shares' => 'integer',
        'amount' => 'decimal:2',
        'balance_after' => 'integer',
        'transaction_date' => 'datetime',
    ];

    public function shareAccount(): BelongsTo
    {
        return $this->belongsTo(ShareAccount::class);
    }
}
