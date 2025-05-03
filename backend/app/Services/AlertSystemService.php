<?php

namespace App\Services;

use App\Models\AlertSystemConfig;
use App\Models\FallEvent;
use Illuminate\Support\Facades\Log;

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
            Log::info('Alert system is currently disabled');
            return;
        }

        // Check if the fall event has been in the same state for longer than the threshold
        $timeInState = now()->diffInSeconds($fallEvent->updated_at);
        
        if ($timeInState >= $this->config->settings['alert_threshold']) {
            $this->sendAlerts($fallEvent);
        }
    }

    /**
     * Send alerts through configured channels
     */
    protected function sendAlerts(FallEvent $fallEvent): void
    {
        $channels = $this->config->settings['notification_channels'];
        $elderly = $fallEvent->elderlyProfile;

        foreach ($channels as $channel) {
            try {
                match ($channel) {
                    'email' => $this->sendEmailAlert($fallEvent, $elderly),
                    'sms' => $this->sendSmsAlert($fallEvent, $elderly),
                    'push' => $this->sendPushAlert($fallEvent, $elderly),
                    default => Log::warning("Unknown notification channel: {$channel}"),
                };
            } catch (\Exception $e) {
                Log::error("Failed to send {$channel} alert: " . $e->getMessage());
            }
        }
    }

    /**
     * Send email alerts to configured contacts
     */
    protected function sendEmailAlert(FallEvent $fallEvent, $elderly): void
    {
        // TODO: Implement email notification logic
        Log::info('Email alert would be sent for fall event: ' . $fallEvent->id);
    }

    /**
     * Send SMS alerts to configured contacts
     */
    protected function sendSmsAlert(FallEvent $fallEvent, $elderly): void
    {
        // TODO: Implement SMS notification logic
        Log::info('SMS alert would be sent for fall event: ' . $fallEvent->id);
    }

    /**
     * Send push notifications to configured devices
     */
    protected function sendPushAlert(FallEvent $fallEvent, $elderly): void
    {
        // TODO: Implement push notification logic
        Log::info('Push notification would be sent for fall event: ' . $fallEvent->id);
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