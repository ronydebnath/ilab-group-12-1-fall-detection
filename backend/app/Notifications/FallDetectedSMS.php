<?php

namespace App\Notifications;

use App\Models\FallEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\TwilioMessage;
use Illuminate\Notifications\Notification;

class FallDetectedSMS extends Notification implements ShouldQueue
{
    use Queueable;

    protected FallEvent $fallEvent;

    public function __construct(FallEvent $fallEvent)
    {
        $this->fallEvent = $fallEvent;
    }

    public function via($notifiable): array
    {
        return ['twilio'];
    }

    public function toTwilio($notifiable): TwilioMessage
    {
        $elderly = $this->fallEvent->elderlyProfile;
        
        return (new TwilioMessage)
            ->content(
                "FALL ALERT: {$elderly->name} has fallen at {$this->fallEvent->location}. " .
                "Time: {$this->fallEvent->created_at->format('H:i:s')}. " .
                "Status: {$this->fallEvent->status}. " .
                "Please check immediately."
            );
    }
} 