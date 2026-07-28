<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'is_executive',
        'enabled',
    ];

    protected $casts = [
        'is_executive' => 'boolean',
        'enabled' => 'boolean',
    ];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'member_positions')
            ->withPivot('start_date', 'end_date', 'is_current');
    }
}
