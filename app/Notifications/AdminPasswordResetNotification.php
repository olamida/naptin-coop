<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminPasswordResetNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $temporaryPassword,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Password Has Been Reset')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('An administrator has reset your password for the NAPTIN Cooperative portal.')
            ->line('Your new temporary password is:')
            ->line("**{$this->temporaryPassword}**")
            ->line('Please log in and change your password immediately for security.')
            ->action('Login Now', route('login'))
            ->line('If you did not expect this change, please contact the administrator immediately.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'admin_password_reset',
            'message' => 'Your password has been reset by an administrator. Check your email for the new temporary password.',
        ];
    }
}
