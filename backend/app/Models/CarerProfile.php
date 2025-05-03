<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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