<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BroadcastNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public string $category = 'general',
        public string $senderName = 'Administration'
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title)
            ->line($this->message)
            ->line('— ' . $this->senderName);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'broadcast',
            'title' => $this->title,
            'message' => $this->message,
            'category' => $this->category,
            'sender' => $this->senderName,
        ];
    }
}
