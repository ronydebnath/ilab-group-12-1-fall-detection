# ElderlyProfile Module

## Purpose
Represents an elderly individual being monitored for fall detection. Stores personal and contact information required for notifications.

## Fields
- `id` (int): Primary key
- `name` (string): Full name (e.g., John Doe)
- `age` (int): Age (e.g., 78)
- `gender` (string): Gender (e.g., male, female, other)
- `address` (string): Home address (e.g., 123 Main St, Springfield)
- `phone` (string): Phone number in E.164 format (e.g., +15551234567)
- `email` (string): Email address (e.g., johndoe@example.com)
- `device_token` (string): FCM device token for push notifications
- `created_at`, `updated_at`: Timestamps

## Example
```php
ElderlyProfile::create([
    'name' => 'Jane Smith',
    'age' => 82,
    'gender' => 'female',
    'address' => '456 Oak Ave, Metropolis',
    'phone' => '+15559876543',
    'email' => 'janesmith@example.com',
    'device_token' => 'fcm_token_456',
]);
``` 