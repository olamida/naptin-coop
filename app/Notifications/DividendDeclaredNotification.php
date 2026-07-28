<?php

namespace App\Notifications;

use App\Models\Dividend;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DividendDeclaredNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Dividend $dividend,
        public float $memberAmount,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $amount = '₦' . number_format($this->memberAmount, 2);

        return (new MailMessage)
            ->subject('Dividend Declaration - ' . $this->dividend->year)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line("The {$this->dividend->year} dividend has been declared and your share has been calculated.")
            ->line("Your dividend: {$amount}")
            ->line("Based on your share holding in the cooperative.")
            ->action('View Dividends', route('portal.shares'));
    }

    public function toArray(object $notifiable): array
    {
        $amount = '₦' . number_format($this->memberAmount, 2);

        return [
            'type' => 'dividend_declared',
            'dividend_id' => $this->dividend->id,
            'year' => $this->dividend->year,
            'amount' => $this->memberAmount,
            'message' => "The {$this->dividend->year} dividend has been declared. Your dividend: {$amount}.",
        ];
    }
}
