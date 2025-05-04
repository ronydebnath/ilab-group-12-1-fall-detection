<?php

namespace App\Notifications;

use App\Models\FallEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\FCM\FCMMessage;
use NotificationChannels\FCM\FCMMessage as FCM;
use NotificationChannels\FCM\FCMChannel;

class FallDetectedPush extends Notification implements ShouldQueue
{
    use Queueable;

    protected FallEvent $fallEvent;

    public function __construct(FallEvent $fallEvent)
    {
        $this->fallEvent = $fallEvent;
    }

    public function via($notifiable): array
    {
        return [FCMChannel::class];
    }

    public function toFcm($notifiable): FCMMessage
    {
        $elderly = $this->fallEvent->elderlyProfile;
        
        return FCM::create()
            ->setData([
                'event_id' => (string) $this->fallEvent->id,
                'elderly_id' => (string) $elderly->id,
                'status' => $this->fallEvent->status,
                'location' => $this->fallEvent->location,
                'timestamp' => $this->fallEvent->created_at->toIso8601String(),
            ])
            ->setNotification(\NotificationChannels\FCM\Resources\Notification::create()
                ->setTitle('Fall Detection Alert')
                ->setBody("{$elderly->name} has fallen at {$this->fallEvent->location}")
                ->setImage('https://your-app.com/fall-alert-icon.png'))
            ->setAndroid(\NotificationChannels\FCM\Resources\AndroidConfig::create()
                ->setPriority(\NotificationChannels\FCM\Resources\AndroidConfig::PRIORITY_HIGH)
                ->setSound('default')
                ->setChannelId('fall_alerts'))
            ->setApns(\NotificationChannels\FCM\Resources\ApnsConfig::create()
                ->setSound('default')
                ->setBadge(1)
                ->setCategory('fall_alert'));
    }
} 