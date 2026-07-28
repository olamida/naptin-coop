<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NextOfKin extends Model
{
    use HasFactory;

    protected $table = 'next_of_kins';

    protected $fillable = [
        'member_id',
        'name',
        'relationship',
        'phone',
        'email',
        'address',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
