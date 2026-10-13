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
        'username',
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

    public function isSuperadmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOperator(): bool
    {
        return $this->role === 'operator';
    }

    /**
     * Operator & Superadmin can create shipment.
     * Admin is view-only for shipment creation.
     */
    public function canCreateShipment(): bool
    {
        return in_array($this->role, ['superadmin', 'operator']);
    }

    /**
     * Only Superadmin can access Settings (Role, Users, Master SOP, Parameter).
     */
    public function canAccessSettings(): bool
    {
        return $this->role === 'superadmin';
    }

    /**
     * Superadmin & Admin can download reports (PDF, Excel, ZIP).
     */
    public function canDownloadReports(): bool
    {
        return in_array($this->role, ['superadmin', 'admin']);
    }

    /**
     * Check if user has permission to edit a specific shipment:
     * - Superadmin & Admin: can edit draft and submitted shipments
     * - Operator: can only edit draft shipments
     */
    public function canEditShipment(?Shipment $shipment = null): bool
    {
        if ($this->isSuperadmin() || $this->isAdmin()) {
            return true;
        }

        if ($this->isOperator()) {
            return ! $shipment || $shipment->status === 'draft';
        }

        return false;
    }

    /**
     * Only Superadmin has permission to permanently delete shipments.
     */
    public function canDeleteShipment(): bool
    {
        return $this->isSuperadmin();
    }
}
