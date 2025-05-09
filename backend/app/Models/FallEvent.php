<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\AlertSystemService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @OA\Schema(
 *   schema="FallEvent",
 *   required={"elderly_id", "detected_at", "status"},
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="elderly_id", type="integer", example=1, description="ID of the elderly profile"),
 *   @OA\Property(property="detected_at", type="string", format="date-time", example="2024-05-03T12:00:00Z"),
 *   @OA\Property(property="confidence_score", type="number", format="float", example=85.5),
 *   @OA\Property(property="detection_method", type="string", example="sensor"),
 *   @OA\Property(property="location", type="object", nullable=true),
 *   @OA\Property(property="location_description", type="string", example="Living Room"),
 *   @OA\Property(property="sensor_data", type="object", nullable=true),
 *   @OA\Property(property="status", type="string", enum={"detected", "confirmed", "false_alarm", "resolved"}),
 *   @OA\Property(property="severity_level", type="string", enum={"low", "medium", "high"}),
 *   @OA\Property(property="notes", type="string", nullable=true),
 *   @OA\Property(property="medical_notes", type="object", nullable=true),
 *   @OA\Property(property="required_medical_attention", type="boolean"),
 *   @OA\Property(property="resolved_by", type="integer", nullable=true),
 *   @OA\Property(property="resolved_at", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="response_time_seconds", type="integer", nullable=true),
 *   @OA\Property(property="response_actions", type="object", nullable=true),
 *   @OA\Property(property="notification_channels", type="array", @OA\Items(type="string")),
 *   @OA\Property(property="context", type="object", nullable=true),
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
        'confidence_score',
        'detection_method',
        'location',
        'location_description',
        'sensor_data',
        'status',
        'severity_level',
        'notes',
        'medical_notes',
        'required_medical_attention',
        'resolved_by',
        'resolved_at',
        'response_time_seconds',
        'response_actions',
        'notification_channels',
        'context',
    ];

    protected $casts = [
        'detected_at' => 'datetime',
        'resolved_at' => 'datetime',
        'confidence_score' => 'decimal:2',
        'location' => 'array',
        'sensor_data' => 'array',
        'medical_notes' => 'array',
        'response_actions' => 'array',
        'notification_channels' => 'array',
        'context' => 'array',
        'required_medical_attention' => 'boolean',
    ];

    /**
     * Get the elderly user associated with the fall event.
     */
    public function elderly(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ElderlyProfile::class, 'elderly_id');
    }

    /**
     * Get the user who resolved the fall event.
     */
    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Get the notifications for this fall event.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(FallEventNotification::class);
    }

    /**
     * Scope a query to only include unresolved events.
     */
    public function scopeUnresolved($query)
    {
        return $query->whereNotIn('status', ['resolved', 'false_alarm']);
    }

    /**
     * Scope a query to only include events for a specific elderly user.
     */
    public function scopeForElderly($query, $elderlyId)
    {
        return $query->where('elderly_id', $elderlyId);
    }

    /**
     * Scope a query to only include events that required medical attention.
     */
    public function scopeRequiredMedicalAttention($query)
    {
        return $query->where('required_medical_attention', true);
    }

    /**
     * Scope a query to only include events of a specific severity level.
     */
    public function scopeOfSeverity($query, $severity)
    {
        return $query->where('severity_level', $severity);
    }

    /**
     * Get the notification channels based on severity level.
     */
    public function getNotificationChannels(): array
    {
        return match ($this->severity_level) {
            'high' => ['email', 'sms', 'push'],
            'medium' => ['email', 'push'],
            'low' => ['push'],
            default => ['email']
        };
    }

    /**
     * Resolve the fall event.
     */
    public function resolve(): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'response_time_seconds' => now()->diffInSeconds($this->detected_at)
        ]);
    }

    /**
     * Mark the fall event as a false alarm.
     */
    public function markAsFalseAlarm(?string $notes = null): void
    {
        $this->update([
            'status' => 'false_alarm',
            'notes' => $notes ?? $this->notes,
            'resolved_at' => now()
        ]);
    }

    protected static function booted()
    {
        static::updated(function (FallEvent $fallEvent) {
            if (in_array($fallEvent->status, ['detected', 'confirmed'])) {
                app(AlertSystemService::class)->processFallEvent($fallEvent);
            }
        });
    }
}
