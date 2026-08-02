<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BrandingAsset extends Model
{
    public const KEYS = [
        'favicon',
        'hero_savings',
        'hero_unity',
        'hero_fintech',
        'logo_primary',
        'icon_round',
    ];

    public const FAVICON_SIZES = [16, 32, 48, 180, 192, 512];

    protected $fillable = [
        'key',
        'label',
        'description',
        'recommended_size',
        'file_path',
        'file_type',
        'usage_locations',
        'is_active',
        'uploaded_by',
    ];

    protected $casts = [
        'usage_locations' => 'array',
        'is_active' => 'boolean',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        return Storage::disk('public')->url($this->file_path);
    }

    public function variantUrl(string $filename): ?string
    {
        $path = 'branding/'.$this->key.'/variants/'.$filename;

        return Storage::disk('public')->exists($path)
            ? Storage::disk('public')->url($path)
            : null;
    }

    public function variantPath(string $filename): string
    {
        return 'branding/'.$this->key.'/variants/'.$filename;
    }
}
