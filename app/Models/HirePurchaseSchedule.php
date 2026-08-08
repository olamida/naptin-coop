<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HirePurchaseSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'installment_number',
        'due_date',
        'principal_amount',
        'total_amount',
        'balance_after',
        'status',
        'amount_paid',
        'paid_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'principal_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
