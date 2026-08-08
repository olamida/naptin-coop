<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'order_group',
        'member_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_amount',
        'payment_type',
        'is_society_expense',
        'monthly_repayment',
        'amount_paid',
        'status',
        'collected_at',
        'approved_by',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'monthly_repayment' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'quantity' => 'integer',
        'is_society_expense' => 'boolean',
        'collected_at' => 'date',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(HirePurchaseSchedule::class, 'purchase_order_id');
    }
}
