# AlertSystemConfig Module

## Purpose
Defines how the alert system behaves: which channels to use, thresholds, escalation, and contact priorities. Allows for flexible, named configurations.

## Fields
- `id` (int): Primary key
- `name` (string): Unique config name (e.g., default, night_mode)
- `description` (string): Description of the config
- `settings` (json):
  - `notification_channels` (array): ["email", "sms", "push"]
  - `alert_threshold` (int): Seconds before sending first alert (e.g., 30)
  - `escalation_delay` (int): Seconds between escalation levels (e.g., 300)
  - `max_escalation_level` (int): Max escalation rounds (e.g., 2)
  - `contact_priority` (object): {"primary": true, "secondary": true, "emergency": false}
- `is_active` (bool): Whether this config is active
- `created_at`, `updated_at`: Timestamps

## Example
```php
AlertSystemConfig::create([
    'name' => 'night_mode',
    'description' => 'Reduced alerts during night hours',
    'settings' => [
        'notification_channels' => ['email', 'push'],
        'alert_threshold' => 60, // seconds
        'escalation_delay' => 600, // seconds
        'max_escalation_level' => 1,
        'contact_priority' => [
            'primary' => true,
            'secondary' => false,
            'emergency' => false,
        ],
    ],
    'is_active' => false,
]);
``` 