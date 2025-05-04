<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlertSystemConfig extends Model
{
    protected $fillable = [
        'name',
        'description',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the default alert configuration
     */
    public static function getDefault(): self
    {
        return static::firstOrCreate(
            ['name' => 'default'],
            [
                'description' => 'Default alert system configuration',
                'settings' => [
                    'notification_channels' => ['email', 'sms'],
                    'alert_threshold' => 30, // seconds
                    'escalation_delay' => 300, // 5 minutes
                    'max_escalation_level' => 3,
                    'contact_priority' => [
                        'primary' => true,
                        'secondary' => true,
                        'emergency' => true,
                    ],
                ],
                'is_active' => true,
            ]
        );
    }
}
