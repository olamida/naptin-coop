<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Member extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'region_id',
        'user_id',
        'staff_id',
        'first_name',
        'last_name',
        'middle_name',
        'email',
        'phone',
        'gender',
        'date_of_birth',
        'employment_date',
        'retirement_date',
        'address',
        'state_of_origin',
        'nin',
        'grade_level',
        'monthly_salary',
        'monthly_savings',
        'status',
        'is_exco',
        'photo_path',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'employment_date' => 'date',
        'retirement_date' => 'date',
        'monthly_salary' => 'float',
        'monthly_savings' => 'float',
        'is_exco' => 'boolean',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function positions(): BelongsToMany
    {
        return $this->belongsToMany(Position::class, 'member_positions')
            ->withPivot('start_date', 'end_date', 'is_current');
    }

    public function nextOfKins(): HasMany
    {
        return $this->hasMany(NextOfKin::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function savingsAccount(): HasOne
    {
        return $this->hasOne(SavingsAccount::class);
    }

    public function shareAccount(): HasOne
    {
        return $this->hasOne(ShareAccount::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function loanRepayments(): HasMany
    {
        return $this->hasMany(LoanRepayment::class);
    }

    public function guarantorRequests(): HasMany
    {
        return $this->hasMany(LoanGuarantor::class);
    }

    public function guaranteedLoans(): BelongsToMany
    {
        return $this->belongsToMany(Loan::class, 'loan_guarantors')
            ->withPivot('status', 'notes', 'responded_at')
            ->withTimestamps();
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name);
    }

    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo_path) {
            return asset('storage/' . $this->photo_path);
        }

        return '';
    }

    public function getInitialsAttribute(): string
    {
        return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name ?? '', 0, 1));
    }
}
