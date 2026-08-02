<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TermiiSmsChannel
{
    /**
     * Send the given notification as an SMS via Termii.
     *
     * @param  mixed  $notifiable
     * @return array|void
     */
    public function send($notifiable, Notification $notification)
    {
        $phone = method_exists($notifiable, 'routeNotificationForTermii')
            ? $notifiable->routeNotificationForTermii($notification)
            : $notifiable->routeNotificationFor('sms');

        if (! $phone || ! method_exists($notification, 'toTermii')) {
            return;
        }

        $message = $notification->toTermii($notifiable);
        if (! is_string($message) || trim($message) === '') {
            return;
        }

        $apiKey = config('termii.api_key');
        if (empty($apiKey)) {
            return;
        }

        try {
            $response = Http::timeout(10)
                ->acceptJson()
                ->withHeaders([
                    'Authorization' => 'Bearer '.$apiKey,
                ])
                ->post(config('termii.base_url').'/sms/send', [
                    'to' => preg_replace('/[^0-9+]/', '', $phone),
                    'from' => config('termii.sender_id', 'NAPTIN-COOP'),
                    'sms' => $message,
                    'type' => 'plain',
                    'channel' => config('termii.channel', 'generic'),
                ]);

            if ($response->failed()) {
                Log::warning('Termii SMS send failed', [
                    'phone' => substr(preg_replace('/[^0-9]/', '', $phone), 0, -3).'***',
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }

            return $response->json();
        } catch (\Throwable $e) {
            Log::error('Termii SMS exception: '.$e->getMessage());
        }
    }
}
