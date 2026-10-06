<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'nama_warehouse',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    /**
     * Get the role model associated with this user.
     */
    public function roleModel()
    {
        return $this->belongsTo(Role::class, 'role', 'name');
    }

    /**
     * Get human-readable role name.
     */
    public function getRoleDisplayNameAttribute(): string
    {
        return match ($this->role) {
            'superadmin' => 'Super Administrator',
            'admin' => 'Admin Warehouse',
            'operator' => 'Operator Lapangan',
            default => ucfirst((string) $this->role),
        };
    }
}
