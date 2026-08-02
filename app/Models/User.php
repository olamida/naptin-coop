<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
        'phone',
        'whatsapp_enabled',
        'active_session_token',
        'must_change_password',
        'totp_secret',
        'totp_enabled',
        'totp_recovery_codes',
        'totp_confirmed_at',
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
            'must_change_password' => 'boolean',
            'whatsapp_enabled' => 'boolean',
            'totp_enabled' => 'boolean',
            'totp_recovery_codes' => 'array',
            'totp_confirmed_at' => 'datetime',
        ];
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->profile_photo_path) {
            return asset('storage/'.$this->profile_photo_path);
        }

        return '';
    }

    public function getInitialsAttribute(): string
    {
        return strtoupper(substr($this->name, 0, 2));
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function routeNotificationForTermii($notification = null): ?string
    {
        if ($this->phone) {
            return $this->phone;
        }

        return $this->member?->phone;
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
