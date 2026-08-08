<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'slogan',
        'description',
        'short_history',
        'registration_number',
        'email',
        'phone',
        'address',
        'website',
        'logo_path',
        'banner_path',
        'theme_color',
        'secondary_color',
        'thrift_amount',
        'membership_fee',
        'shares_enabled',
        'dividends_enabled',
        'savings_interest_rate',
        'loan_interest_rate',
        'max_loan_multiplier',
        'auto_approve_deposit_limit',
        'footer_note',
        'facebook',
        'twitter',
        'instagram',
        'linkedin',
        'opening_hours',
    ];

    protected $casts = [
        'thrift_amount' => 'decimal:2',
        'membership_fee' => 'decimal:2',
        'shares_enabled' => 'boolean',
        'dividends_enabled' => 'boolean',
        'savings_interest_rate' => 'decimal:2',
        'loan_interest_rate' => 'decimal:2',
        'max_loan_multiplier' => 'integer',
        'auto_approve_deposit_limit' => 'decimal:2',
        'branding_json' => 'array',
    ];

    public static function instance(): static
    {
        $company = static::query()->firstOrCreate([], [
            'name' => 'NAPTIN Staff Thrift Cooperative',
        ]);

        // firstOrCreate() with empty attributes inserts only the explicitly set
        // columns, so DB-defaulted flags (shares_enabled, dividends_enabled, etc.)
        // are absent from the in-memory model and read back as null. Reload the
        // freshly created row so module gating and company settings work on the
        // very first request that bootstraps the singleton company row.
        if ($company->wasRecentlyCreated) {
            $company->refresh();
        }

        return $company;
    }

    public function moduleEnabled(string $module): bool
    {
        return (bool) $this->{$module.'_enabled'};
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        return asset('storage/'.$this->logo_path);
    }

    public function getBannerUrlAttribute(): ?string
    {
        if (! $this->banner_path) {
            return null;
        }

        return asset('storage/'.$this->banner_path);
    }

    public function branding(string $key, mixed $default = null): mixed
    {
        return data_get($this->branding_json, $key, $default);
    }
}
