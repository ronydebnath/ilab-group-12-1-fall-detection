<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\AlertSystemService;

/**
 * @OA\Schema(
 *   schema="FallEvent",
 *   required={"elderly_id", "detected_at", "status"},
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="elderly_id", type="integer", example=1, description="ID of the elderly profile"),
 *   @OA\Property(property="detected_at", type="string", format="date-time", example="2024-05-03T12:00:00Z"),
 *   @OA\Property(property="resolved_at", type="string", format="date-time", nullable=true, example="2024-05-03T13:00:00Z"),
 *   @OA\Property(property="status", type="string", enum={"detected", "safe", "alerted", "resolved", "false_alarm"}, example="detected"),
 *   @OA\Property(property="sensor_data", type="object", nullable=true, example={"acc_x":0.1,"acc_y":0.2}),
 *   @OA\Property(property="notes", type="string", nullable=true, example="Fall detected in the living room."),
 *   @OA\Property(property="false_alarm", type="boolean", example=false),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time"),
 *   @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
 * )
 */
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
        'false_alarm',
    ];

    protected $casts = [
        'detected_at' => 'datetime',
        'resolved_at' => 'datetime',
        'sensor_data' => 'array',
        'false_alarm' => 'boolean',
    ];

    public function elderly()
    {
        return $this->belongsTo(ElderlyProfile::class, 'elderly_id');
    }

    protected static function booted()
    {
        static::updated(function (FallEvent $fallEvent) {
            if (in_array($fallEvent->status, ['detected', 'alerted'])) {
                app(AlertSystemService::class)->processFallEvent($fallEvent);
            }
        });
    }
}
