<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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