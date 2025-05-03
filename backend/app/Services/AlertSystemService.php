<?php

namespace App\Services;

use App\Models\AlertSystemConfig;
use App\Models\FallEvent;
use App\Notifications\FallDetectedEmail;
use App\Notifications\FallDetectedPush;
use App\Notifications\FallDetectedSMS;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class AlertSystemService
{
    protected AlertSystemConfig $config;

    public function __construct()
    {
        $this->config = AlertSystemConfig::getDefault();
    }

    /**
     * Process a fall event and determine if alerts should be sent
     */
    public function processFallEvent(FallEvent $fallEvent): void
    {
        if (!$this->config->is_active) {
            Log::info('Alert system is disabled');
            return;
        }

        // Check if the event has been in the same state longer than the threshold
        $threshold = $this->config->settings['alert_threshold'] ?? 30;
        $timeInState = now()->diffInSeconds($fallEvent->updated_at);

        if ($timeInState >= $threshold) {
            $this->sendAlerts($fallEvent);
        }
    }

    /**
     * Send alerts through configured channels
     */
    protected function sendAlerts(FallEvent $fallEvent): void
    {
        $elderly = $fallEvent->elderlyProfile;
        $channels = $this->config->settings['notification_channels'] ?? ['email'];

        foreach ($channels as $channel) {
            try {
                switch ($channel) {
                    case 'email':
                        if ($elderly->email) {
                            Notification::route('mail', $elderly->email)
                                ->notify(new FallDetectedEmail($fallEvent));
                        }
                        break;

                    case 'sms':
                        if ($elderly->phone) {
                            Notification::route('twilio', $elderly->phone)
                                ->notify(new FallDetectedSMS($fallEvent));
                        }
                        break;

                    case 'push':
                        if ($elderly->device_token) {
                            Notification::route('fcm', $elderly->device_token)
                                ->notify(new FallDetectedPush($fallEvent));
                        }
                        break;
                }
            } catch (\Exception $e) {
                Log::error("Failed to send {$channel} notification: " . $e->getMessage());
            }
        }
    }

    /**
     * Get the current alert system configuration
     */
    public function getConfig(): AlertSystemConfig
    {
        return $this->config;
    }

    /**
     * Update the alert system configuration
     */
    public function updateConfig(AlertSystemConfig $config): void
    {
        $this->config = $config;
    }
} 