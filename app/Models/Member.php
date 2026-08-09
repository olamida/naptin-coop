<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'is_fraud_flagged',
        'photo_path',
        'import_batch_id',
        'external_reference',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'employment_date' => 'date',
        'retirement_date' => 'date',
        'monthly_salary' => 'float',
        'monthly_savings' => 'float',
        'is_exco' => 'boolean',
        'is_fraud_flagged' => 'boolean',
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
        return trim(preg_replace('/\s+/', ' ', trim($this->first_name.' '.$this->middle_name.' '.$this->last_name)));
    }

    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo_path) {
            return asset('storage/'.$this->photo_path);
        }

        return '';
    }

    public function getInitialsAttribute(): string
    {
        return strtoupper(substr($this->first_name, 0, 1).substr($this->last_name ?? '', 0, 1));
    }

    public function getStaffIdDisplayAttribute(): string
    {
        return 'NAP/'.($this->attributes['staff_id'] ?? '');
    }

    public function healthScore(): float
    {
        $savings = $this->savingsAccount?->balance ?? 0;
        $outstanding = $this->loans()
            ->whereIn('status', ['disbursed', 'repaying', 'arrears'])
            ->sum('outstanding');

        return round($savings / max($outstanding, 1) * 100, 1);
    }

    public function healthLabel(): string
    {
        $score = $this->healthScore();

        return match (true) {
            $score > 100 => 'Excellent',
            $score > 50 => 'Good',
            $score > 25 => 'Fair',
            default => 'At Risk',
        };
    }

    public function healthColor(): string
    {
        $score = $this->healthScore();

        return match (true) {
            $score > 100 => 'emerald',
            $score > 50 => 'blue',
            $score > 25 => 'amber',
            default => 'rose',
        };
    }
}
