# FallEvent Module

## Purpose
Logs each detected or reported fall event for an elderly individual. Used to trigger alerts and for historical analysis.

## Fields
- `id` (int): Primary key
- `elderly_id` (int): Foreign key to ElderlyProfile
- `location` (string): Location of the fall (e.g., Living Room)
- `status` (enum): Status of the event (detected, confirmed, false_alarm, alerted)
- `details` (string): Additional details (e.g., detected by sensor at 14:32)
- `created_at`, `updated_at`: Timestamps

## Example
```php
FallEvent::create([
    'elderly_id' => 1, // Jane Smith's ID
    'location' => 'Bathroom',
    'status' => 'detected',
    'details' => 'Fall detected by sensor at 09:15',
]);
``` 