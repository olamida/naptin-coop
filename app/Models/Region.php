<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'state',
        'zone',
        'headquarters',
        'address',
        'phone',
        'email',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }
}
