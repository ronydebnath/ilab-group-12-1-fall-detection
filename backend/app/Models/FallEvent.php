<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FallEvent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'elderly_id',
        'detected_at',
        'resolved_at',
        'status',
        'sensor_data',
        'notes',
    ];

    protected $casts = [
        'detected_at' => 'datetime',
        'resolved_at' => 'datetime',
        'sensor_data' => 'array',
    ];

    public function elderly()
    {
        return $this->belongsTo(ElderlyProfile::class, 'elderly_id');
    }
}
