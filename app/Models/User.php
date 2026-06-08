<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password', 'role', 'status'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    public const ROLE_OWNER = 'owner';

    public const ROLE_ADMIN = 'admin';

    public const ROLE_STAFF = 'staff';

    public const ROLE_CUSTOMER = 'customer';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_SUSPENDED = 'suspended';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function customerProfile(): HasOne
    {
        return $this->hasOne(CustomerProfile::class);
    }

    public function preAlerts(): HasMany
    {
        return $this->hasMany(PreAlert::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, self::adminRoles(), true);
    }

    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

    public function isStaff(): bool
    {
        return $this->role === self::ROLE_STAFF;
    }

    public function isCustomer(): bool
    {
        return $this->role === self::ROLE_CUSTOMER;
    }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    /**
     * @return list<string>
     */
    public static function adminRoles(): array
    {
        return [self::ROLE_OWNER, self::ROLE_ADMIN, self::ROLE_STAFF];
    }

    /**
     * @return array<string, string>
     */
    public static function adminRoleLabels(): array
    {
        return [
            self::ROLE_OWNER => 'Owner / Super Admin',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_STAFF => 'Staff',
        ];
    }

    public function hasAdminPermission(string $permission): bool
    {
        if (! $this->isAdmin()) {
            return false;
        }

        return match ($permission) {
            'manage_admins' => $this->isOwner(),
            'view_activity_logs' => $this->isOwner(),
            'manage_system_settings' => $this->isOwner(),
            'manage_customers' => in_array($this->role, [self::ROLE_OWNER, self::ROLE_ADMIN], true),
            'view_customers' => true,
            'manage_contact_messages' => in_array($this->role, [self::ROLE_OWNER, self::ROLE_ADMIN], true),
            'delete_records' => in_array($this->role, [self::ROLE_OWNER, self::ROLE_ADMIN], true),
            'manage_packages' => true,
            'manage_pre_alerts' => true,
            'manage_billing' => in_array($this->role, [self::ROLE_OWNER, self::ROLE_ADMIN], true),
            default => false,
        };
    }

    public function canManageAdminUser(self $target): bool
    {
        if (! $this->isOwner()) {
            return false;
        }

        return $target->role !== self::ROLE_OWNER || $target->is($this);
    }

    public function customerReference(): ?string
    {
        return $this->customerProfile?->customer_reference;
    }
}
