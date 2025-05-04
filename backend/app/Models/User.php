<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

/**
 * @OA\Schema(
 *     schema="User",
 *     required={"name", "email", "role"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="role", type="string", enum={"admin", "carer", "elderly"}, example="elderly"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(
 *         property="elderly_profile",
 *         ref="#/components/schemas/ElderlyProfile",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="carer_profile",
 *         ref="#/components/schemas/CarerProfile",
 *         nullable=true
 *     )
 * )
 */
class User extends Authenticatable implements FilamentUser, CanResetPasswordContract
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Get the carer profile associated with the user.
     */
    public function carerProfile()
    {
        return $this->hasOne(CarerProfile::class);
    }

    /**
     * Get the elderly profiles where this user is the primary carer.
     */
    public function primaryElderlyProfiles()
    {
        return $this->hasMany(ElderlyProfile::class, 'primary_carer_id');
    }

    /**
     * Get the elderly profiles where this user is the secondary carer.
     */
    public function secondaryElderlyProfiles()
    {
        return $this->hasMany(ElderlyProfile::class, 'secondary_carer_id');
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the user is a carer.
     */
    public function isCarer(): bool
    {
        return $this->role === 'carer';
    }

    /**
     * Check if the user is an elderly user.
     */
    public function isElderly(): bool
    {
        return $this->role === 'elderly';
    }

    /**
     * Get all permissions for the user based on their role.
     */
    public function getPermissions(): array
    {
        return match($this->role) {
            'admin' => [
                'manage_users',
                'manage_carers',
                'manage_elderly',
                'view_all_profiles',
                'manage_settings',
                'view_analytics',
            ],
            'carer' => [
                'view_assigned_elderly',
                'update_elderly_status',
                'manage_fall_events',
                'view_own_profile',
                'update_own_profile',
            ],
            'elderly' => [
                'view_own_profile',
                'update_own_profile',
                'view_own_fall_events',
                'manage_own_device',
                'manage_own_alerts',
            ],
            default => [],
        };
    }

    /**
     * Check if the user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->getPermissions());
    }

    /**
     * Get the elderly profile associated with the user.
     */
    public function elderlyProfile()
    {
        return $this->hasOne(ElderlyProfile::class);
    }
}
