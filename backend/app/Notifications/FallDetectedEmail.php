<?php

namespace App\Notifications;

use App\Models\FallEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FallDetectedEmail extends Notification implements ShouldQueue
{
    use Queueable;

    protected FallEvent $fallEvent;

    public function __construct(FallEvent $fallEvent)
    {
        $this->fallEvent = $fallEvent;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $elderly = $this->fallEvent->elderlyProfile;
        
        return (new MailMessage)
            ->subject('Fall Detection Alert')
            ->greeting('Fall Detection Alert')
            ->line("A fall has been detected for {$elderly->name}.")
            ->line("Time: {$this->fallEvent->created_at->format('Y-m-d H:i:s')}")
            ->line("Location: {$this->fallEvent->location}")
            ->line("Status: {$this->fallEvent->status}")
            ->action('View Details', url("/admin/fall-events/{$this->fallEvent->id}"))
            ->line('Please check on the person immediately if possible.');
    }
} 