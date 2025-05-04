# Fall Detection Backend Application

## Overview
This backend application is built with Laravel and provides APIs and admin management for fall detection, elderly profiles, event logging, and a configurable alert system supporting email, SMS, and push notifications.

---

## Table of Contents
- [Setup](#setup)
- [Environment Configuration](#environment-configuration)
- [Database Migrations](#database-migrations)
- [Modules & Usage](#modules--usage)
  - [Elderly Profiles](#elderly-profiles)
  - [Fall Events](#fall-events)
  - [Alert System Config](#alert-system-config)
  - [Notifications](#notifications)
- [Testing Notifications](#testing-notifications)
- [Admin Panel (Filament)](#admin-panel-filament)
- [API Documentation (Swagger)](#api-documentation-swagger)

---

## Setup

1. **Clone the repository:**
   ```bash
   git clone https://github.com/your-org/fall-detection-backend.git
   cd fall-detection-backend/backend
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Copy and edit environment file:**
   ```bash
   cp .env.example .env
   # Edit .env with your database and mail/SMS/FCM credentials
   ```

4. **Generate application key:**
   ```bash
   php artisan key:generate
   ```

---

## Environment Configuration

Example `.env` values for notifications:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_user
MAIL_PASSWORD=your_mailtrap_pass
MAIL_FROM_ADDRESS=no-reply@fallapp.com
MAIL_FROM_NAME="Fall Detection System"

TWILIO_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_TOKEN=your_twilio_token
TWILIO_FROM=+15551234567

FCM_SERVER_KEY=AAAA...your_fcm_server_key
```

---

## Database Migrations

Run all migrations:
```bash
php artisan migrate
```

---

## Modules & Usage

### Elderly Profiles
Represents monitored individuals.

**Example:**
```php
ElderlyProfile::create([
    'name' => 'John Doe',
    'age' => 78,
    'gender' => 'male',
    'address' => '123 Main St, Springfield',
    'phone' => '+15551234567',
    'email' => 'johndoe@example.com',
    'device_token' => 'fcm_device_token_123',
]);
```

---

### Fall Events
Logs detected or reported falls.

**Example:**
```php
FallEvent::create([
    'elderly_id' => 1, // John Doe's ID
    'location' => 'Living Room',
    'status' => 'detected', // detected | confirmed | false_alarm | alerted
    'details' => 'Fall detected by sensor at 14:32',
]);
```

---

### Alert System Config
Configures how and when alerts are sent.

**Example:**
```php
AlertSystemConfig::create([
    'name' => 'default',
    'description' => 'Default alert system configuration',
    'settings' => [
        'notification_channels' => ['email', 'sms', 'push'],
        'alert_threshold' => 30, // seconds
        'escalation_delay' => 300, // seconds
        'max_escalation_level' => 2,
        'contact_priority' => [
            'primary' => true,
            'secondary' => true,
            'emergency' => false,
        ],
    ],
    'is_active' => true,
]);
```

---

### Notifications
Notifications are sent automatically when a fall event is detected and meets the alert threshold.

**Channels:**
- **Email:** Sent to the elderly's `email` using Mailtrap (for testing)
- **SMS:** Sent to the elderly's `phone` using Twilio (sandbox or live)
- **Push:** Sent to the elderly's `device_token` using FCM

**Example Notification Payloads:**
- **Email:**
  > Subject: Fall Detection Alert
  >
  > A fall has been detected for John Doe.
  > Time: 2024-05-03 14:32:00
  > Location: Living Room
  > Status: detected
  > [View Details](https://your-app.com/admin/fall-events/1)

- **SMS:**
  > FALL ALERT: John Doe has fallen at Living Room. Time: 14:32:00. Status: detected. Please check immediately.

- **Push:**
  > Title: Fall Detection Alert
  > Body: John Doe has fallen at Living Room
  > Data: { event_id: 1, elderly_id: 1, status: 'detected', location: 'Living Room', timestamp: '2024-05-03T14:32:00Z' }

---

## Testing Notifications

You can test notifications without a mobile app:

1. **Email:** Check your Mailtrap inbox for messages.
2. **SMS:** Use a Twilio-verified phone number and check Twilio's dashboard for sent messages.
3. **Push:** Use FCM's dry-run mode or inspect the HTTP response in logs.

**Manual Test via Tinker:**
```php
php artisan tinker

$p = App\Models\ElderlyProfile::first();
$e = App\Models\FallEvent::create([
    'elderly_id' => $p->id,
    'location' => 'Kitchen',
    'status' => 'detected',
    'details' => 'Fall detected by sensor at 15:00',
]);
$e->updated_at = now()->subSeconds(31); $e->save();
app(App\Services\AlertSystemService::class)->processFallEvent($e);
```

---

## Admin Panel (Filament)

- Access the admin panel at `/admin`.
- Manage Elderly Profiles, Fall Events, and Alert System Configs.
- Use the UI to create, edit, and delete records.

---

## API Documentation (Swagger)

- The API is documented using OpenAPI/Swagger annotations.
- Access the docs at `/api/documentation` (if enabled).

---

## Support
For questions or issues, please open an issue on the repository or contact the maintainers.
