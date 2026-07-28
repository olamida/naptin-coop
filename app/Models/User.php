<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_photo_path',
        'member_id',
        'active_session_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'active_session_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->profile_photo_path) {
            return asset('storage/' . $this->profile_photo_path);
        }

        return '';
    }

    public function getInitialsAttribute(): string
    {
        return strtoupper(substr($this->name, 0, 2));
    }

    public function member(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function isMember(): bool
    {
        return $this->member_id !== null;
    }

    public function savingsAccounts()
    {
        return $this->member ? $this->member->savingsAccounts() : collect();
    }

    public function loans()
    {
        return $this->member ? $this->member->loans() : collect();
    }

    public function shareAccounts()
    {
        return $this->member ? $this->member->shareAccounts() : collect();
    }
}
