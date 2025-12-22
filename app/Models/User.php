<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * User Model
 *
 * Represents a system user with role-based access control and avatar management.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $role
 * @property \Carbon\Carbon|null $last_seen
 * @property \Carbon\Carbon|null $last_login_at
 * @property \Carbon\Carbon|null $last_logout_at
 * @property string|null $last_login_ip
 * @property \Carbon\Carbon|null $email_verified_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class User extends Authenticatable implements HasMedia
{
    use HasFactory, Notifiable, HasPushSubscriptions, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'last_seen',
        'last_login_at',
        'last_logout_at',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_seen' => 'datetime',
            'last_login_at' => 'datetime',
            'last_logout_at' => 'datetime',
        ];
    }

    /**
     * Check if the user has developer role.
     *
     * @return bool
     */
    public function isDeveloper(): bool
    {
        return $this->role === 'developer';
    }

    /**
     * Check if the user has system admin role.
     *
     * @return bool
     */
    public function isSystemAdmin(): bool
    {
        return $this->role === 'system_admin';
    }

    /**
     * Check if the user has standard user role.
     *
     * @return bool
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Check if the user has a specific role.
     *
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if the user has a specific permission based on their role.
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        // System admins and developers have all permissions
        if ($this->isDeveloper() || $this->isSystemAdmin()) {
            return true;
        }

        // Standard users have limited permissions
        // This can be extended with more granular permission system
        return match ($permission) {
            'view_expenses' => false,
            'release_inventory' => true,
            'view_masterlist' => true,
            default => false,
        };
    }

    /**
     * Store avatar as base64 blob.
     *
     * @param string $imagePath
     * @return bool
     */
    public function storeAvatarAsBlob(string $imagePath): bool
    {
        if (!file_exists($imagePath)) {
            return false;
        }

        $imageData = file_get_contents($imagePath);
        if ($imageData === false) {
            return false;
        }

        $this->avatar_blob = base64_encode($imageData);
        $this->avatar_mime_type = mime_content_type($imagePath);
        $this->avatar_filename = basename($imagePath);

        return $this->save();
    }

    /**
     * Update the user's last seen timestamp.
     *
     * @return void
     */
    public function updateLastSeen(): void
    {
        $this->update(['last_seen' => now()]);
    }

    /**
     * Record user login.
     *
     * @param string|null $ip
     * @return void
     */
    public function recordLogin(?string $ip = null): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip ?? request()->ip(),
        ]);
    }

    /**
     * Record user logout.
     *
     * @return void
     */
    public function recordLogout(): void
    {
        $this->update(['last_logout_at' => now()]);
    }

    /**
     * Check if the user is currently online (active within last 5 minutes).
     *
     * @return bool
     */
    public function isOnline(): bool
    {
        return $this->last_seen && $this->last_seen->diffInMinutes(now()) < 5;
    }

    /**
     * Get the user's online status.
     *
     * @return string
     */
    public function getStatusAttribute(): string
    {
        return $this->isOnline() ? 'online' : 'offline';
    }

    /**
     * Get the avatar blob as data URL.
     *
     * @param string|null $value
     * @return string|null
     */
    public function getAvatarBlobAttribute(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $mimeType = $this->avatar_mime_type ?: 'image/jpeg';
        return 'data:' . $mimeType . ';base64,' . $value;
    }

    /**
     * Get the user's avatar URL (blob or fallback).
     *
     * @return string
     */
    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar_blob ?: 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Check if the user has an avatar blob.
     *
     * @return bool
     */
    public function hasAvatarBlob(): bool
    {
        return !empty($this->avatar_blob);
    }

    /**
     * Delete the avatar blob.
     *
     * @return bool
     */
    public function deleteAvatarBlob(): bool
    {
        $this->avatar_blob = null;
        $this->avatar_mime_type = null;
        $this->avatar_filename = null;
        return $this->save();
    }
}
