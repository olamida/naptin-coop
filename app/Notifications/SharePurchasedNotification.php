<?php

namespace App\Notifications;

use App\Models\ShareTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SharePurchasedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ShareTransaction $transaction,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $shares = $this->transaction->shares;
        $amount = '₦' . number_format($this->transaction->amount, 2);

        return (new MailMessage)
            ->subject('Share Purchase Confirmed')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line("Your share purchase has been confirmed.")
            ->line("Shares purchased: {$shares}")
            ->line("Amount: {$amount}")
            ->line("Total shares: " . $this->transaction->shareAccount->total_shares)
            ->action('View Shares', route('portal.shares'));
    }

    public function toArray(object $notifiable): array
    {
        $shares = $this->transaction->shares;
        $amount = '₦' . number_format($this->transaction->amount, 2);

        return [
            'type' => 'share_purchased',
            'transaction_id' => $this->transaction->id,
            'shares' => $shares,
            'amount' => $this->transaction->amount,
            'message' => "You purchased {$shares} share(s) for {$amount}.",
        ];
    }
}
