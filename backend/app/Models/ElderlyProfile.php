<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *   schema="ElderlyProfile",
 *   required={"first_name", "last_name", "date_of_birth", "gender", "primary_phone", "current_address", "emergency_contact_name", "emergency_contact_phone", "emergency_contact_relationship", "mobility_status", "vision_status", "hearing_status", "primary_carer_id", "care_level", "preferred_language", "device_status", "living_situation", "activity_level"},
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="first_name", type="string", example="John"),
 *   @OA\Property(property="last_name", type="string", example="Doe"),
 *   @OA\Property(property="date_of_birth", type="string", format="date", example="1940-01-01"),
 *   @OA\Property(property="gender", type="string", enum={"male", "female", "other"}, example="male"),
 *   @OA\Property(property="profile_photo", type="string", nullable=true, example="profile-photos/john.jpg"),
 *   @OA\Property(property="height", type="number", format="float", nullable=true, example=170.5),
 *   @OA\Property(property="weight", type="number", format="float", nullable=true, example=65.2),
 *   @OA\Property(property="blood_type", type="string", nullable=true, example="O+"),
 *   @OA\Property(property="national_id", type="string", nullable=true, example="A123456789"),
 *   @OA\Property(property="primary_phone", type="string", example="+1234567890"),
 *   @OA\Property(property="secondary_phone", type="string", nullable=true, example="+0987654321"),
 *   @OA\Property(property="email", type="string", nullable=true, example="john.doe@example.com"),
 *   @OA\Property(property="current_address", type="string", example="123 Main St, City, Country"),
 *   @OA\Property(property="emergency_contact_name", type="string", example="Jane Doe"),
 *   @OA\Property(property="emergency_contact_phone", type="string", example="+1122334455"),
 *   @OA\Property(property="emergency_contact_relationship", type="string", example="Daughter"),
 *   @OA\Property(property="medical_conditions", type="string", nullable=true, example="Diabetes"),
 *   @OA\Property(property="allergies", type="string", nullable=true, example="Peanuts"),
 *   @OA\Property(property="current_medications", type="string", nullable=true, example="Metformin"),
 *   @OA\Property(property="disabilities", type="string", nullable=true, example="Hearing loss"),
 *   @OA\Property(property="mobility_status", type="string", enum={"independent", "needs_assistance", "wheelchair_bound"}, example="independent"),
 *   @OA\Property(property="vision_status", type="string", enum={"normal", "glasses", "impaired"}, example="normal"),
 *   @OA\Property(property="hearing_status", type="string", enum={"normal", "hearing_aid", "impaired"}, example="normal"),
 *   @OA\Property(property="last_medical_checkup", type="string", format="date", nullable=true, example="2023-12-01"),
 *   @OA\Property(property="primary_carer_id", type="integer", example=2),
 *   @OA\Property(property="secondary_carer_id", type="integer", nullable=true, example=3),
 *   @OA\Property(property="care_level", type="string", enum={"basic", "moderate", "intensive"}, example="basic"),
 *   @OA\Property(property="special_care_instructions", type="string", nullable=true, example="Needs help with medication"),
 *   @OA\Property(property="daily_routine_notes", type="string", nullable=true, example="Walks every morning"),
 *   @OA\Property(property="dietary_restrictions", type="string", nullable=true, example="No sugar"),
 *   @OA\Property(property="preferred_language", type="string", example="English"),
 *   @OA\Property(property="device_id", type="string", nullable=true, example="DEV123456"),
 *   @OA\Property(property="device_status", type="string", enum={"active", "inactive"}, example="active"),
 *   @OA\Property(property="last_device_check", type="string", format="date", nullable=true, example="2024-05-01"),
 *   @OA\Property(property="device_battery_level", type="integer", nullable=true, example=85),
 *   @OA\Property(property="device_location", type="string", nullable=true, example="Home"),
 *   @OA\Property(property="preferred_hospital", type="string", nullable=true, example="City Hospital"),
 *   @OA\Property(property="insurance_information", type="string", nullable=true, example="Provider: ABC Insurance, Policy: 12345"),
 *   @OA\Property(property="living_situation", type="string", enum={"lives_alone", "with_family", "assisted_living"}, example="with_family"),
 *   @OA\Property(property="activity_level", type="string", enum={"active", "moderate", "sedentary"}, example="active"),
 *   @OA\Property(property="notes", type="string", nullable=true, example="Prefers vegetarian meals"),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time"),
 *   @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
 * )
 */
class ElderlyProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'profile_photo',
        'height',
        'weight',
        'blood_type',
        'national_id',
        'primary_phone',
        'secondary_phone',
        'email',
        'current_address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'medical_conditions',
        'allergies',
        'current_medications',
        'disabilities',
        'mobility_status',
        'vision_status',
        'hearing_status',
        'last_medical_checkup',
        'primary_carer_id',
        'secondary_carer_id',
        'care_level',
        'special_care_instructions',
        'daily_routine_notes',
        'dietary_restrictions',
        'preferred_language',
        'device_id',
        'device_status',
        'last_device_check',
        'device_battery_level',
        'device_location',
        'preferred_hospital',
        'insurance_information',
        'living_situation',
        'activity_level',
        'notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'last_medical_checkup' => 'date',
        'last_device_check' => 'date',
        'height' => 'decimal:2',
        'weight' => 'decimal:2',
        'device_battery_level' => 'integer',
    ];

    /**
     * Get the primary carer for the elderly profile.
     */
    public function primaryCarer()
    {
        return $this->belongsTo(User::class, 'primary_carer_id');
    }

    /**
     * Get the secondary carer for the elderly profile.
     */
    public function secondaryCarer()
    {
        return $this->belongsTo(User::class, 'secondary_carer_id');
    }

    /**
     * Get the full name of the elderly person.
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the age of the elderly person.
     */
    public function getAgeAttribute()
    {
        return $this->date_of_birth->age;
    }
} 