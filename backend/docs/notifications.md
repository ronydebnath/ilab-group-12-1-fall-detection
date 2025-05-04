# Notifications Module

## Purpose
Sends alerts to contacts when a fall event is detected and meets the configured threshold. Supports multiple channels: email, SMS, and push notifications.

## Supported Channels
- **Email:** Sent via Mailtrap (for testing) or any configured SMTP provider
- **SMS:** Sent via Twilio (sandbox or live)
- **Push:** Sent via Firebase Cloud Messaging (FCM)

## Configuration
Set the following in your `.env`:
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

## Usage Example
When a `FallEvent` is created or updated and meets the alert threshold, notifications are sent automatically to the elderly's contacts based on the active `AlertSystemConfig`.

**Example:**
```php
$p = App\Models\ElderlyProfile::create([
    'name' => 'Test User',
    'email' => 'test@mailtrap.io',
    'phone' => '+15551230000',
    'device_token' => 'fcm_token_test',
]);
$e = App\Models\FallEvent::create([
    'elderly_id' => $p->id,
    'location' => 'Bedroom',
    'status' => 'detected',
    'details' => 'Fall detected by sensor at 10:00',
]);
$e->updated_at = now()->subSeconds(31); $e->save();
app(App\Services\AlertSystemService::class)->processFallEvent($e);
```

## Testing
- **Email:** Check your Mailtrap inbox for the alert message.
- **SMS:** Use a Twilio-verified phone number and check Twilio's dashboard for sent messages.
- **Push:** Use FCM's dry-run mode or inspect the HTTP response in logs.

## Example Notification Payloads
- **Email:**
  > Subject: Fall Detection Alert
  >
  > A fall has been detected for Test User.
  > Time: 2024-05-03 10:00:00
  > Location: Bedroom
  > Status: detected
  > [View Details](https://your-app.com/admin/fall-events/1)

- **SMS:**
  > FALL ALERT: Test User has fallen at Bedroom. Time: 10:00:00. Status: detected. Please check immediately.

- **Push:**
  > Title: Fall Detection Alert
  > Body: Test User has fallen at Bedroom
  > Data: { event_id: 1, elderly_id: 1, status: 'detected', location: 'Bedroom', timestamp: '2024-05-03T10:00:00Z' } 