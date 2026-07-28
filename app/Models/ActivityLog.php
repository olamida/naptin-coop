<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'event',
        'description',
        'ip_address',
        'user_agent',
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log(string $event, ?string $description = null, ?array $properties = null): static
    {
        $request = request();

        return static::create([
            'user_id' => auth()->id(),
            'event' => $event,
            'description' => $description,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'properties' => $properties,
        ]);
    }
}
