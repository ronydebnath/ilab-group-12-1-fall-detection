<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *   schema="CarerProfile",
 *   required={"user_id", "phone_number", "emergency_contact_name", "emergency_contact_phone", "address", "qualification", "specialization", "years_of_experience", "availability_schedule", "max_elderly_capacity", "current_elderly_count", "status"},
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="user_id", type="integer", example=1),
 *   @OA\Property(property="phone_number", type="string", example="+1234567890"),
 *   @OA\Property(property="emergency_contact_name", type="string", example="Jane Smith"),
 *   @OA\Property(property="emergency_contact_phone", type="string", example="+0987654321"),
 *   @OA\Property(property="address", type="string", example="456 Care Street, City"),
 *   @OA\Property(property="qualification", type="string", example="Registered Nurse"),
 *   @OA\Property(property="specialization", type="string", example="Geriatric Care"),
 *   @OA\Property(property="years_of_experience", type="integer", example=5),
 *   @OA\Property(property="availability_schedule", type="object", example={"monday":"09:00-17:00","tuesday":"09:00-17:00"}),
 *   @OA\Property(property="max_elderly_capacity", type="integer", example=5),
 *   @OA\Property(property="current_elderly_count", type="integer", example=3),
 *   @OA\Property(property="status", type="string", enum={"active", "inactive", "on_leave"}, example="active"),
 *   @OA\Property(property="last_active_at", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="notes", type="string", nullable=true, example="Prefers morning shifts"),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time"),
 *   @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true),
 *   @OA\Property(
 *       property="user",
 *       ref="#/components/schemas/User",
 *       nullable=true
 *   )
 * )
 */
class CarerProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'phone_number',
        'emergency_contact_name',
        'emergency_contact_phone',
        'address',
        'qualification',
        'specialization',
        'years_of_experience',
        'availability_schedule',
        'max_elderly_capacity',
        'current_elderly_count',
        'status',
        'last_active_at',
        'notes'
    ];

    protected $casts = [
        'availability_schedule' => 'array',
        'last_active_at' => 'datetime',
    ];

    /**
     * Get the user that owns the carer profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the elderly profiles that this carer is responsible for as primary carer.
     */
    public function primaryElderlyProfiles()
    {
        return $this->hasMany(ElderlyProfile::class, 'primary_carer_id', 'user_id');
    }

    /**
     * Get the elderly profiles that this carer is responsible for as secondary carer.
     */
    public function secondaryElderlyProfiles()
    {
        return $this->hasMany(ElderlyProfile::class, 'secondary_carer_id', 'user_id');
    }
} 