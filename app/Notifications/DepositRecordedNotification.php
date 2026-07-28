<?php

namespace App\Notifications;

use App\Models\SavingsTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DepositRecordedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public SavingsTransaction $transaction,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $amount = '₦' . number_format($this->transaction->amount, 2);
        $ref = $this->transaction->reference;

        return (new MailMessage)
            ->subject('Savings Deposit Recorded - ' . $ref)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line("A savings deposit of {$amount} has been recorded to your account.")
            ->line("Reference: {$ref}")
            ->line("New balance: ₦" . number_format($this->transaction->balance_after, 2))
            ->action('View Savings', route('portal.savings'));
    }

    public function toArray(object $notifiable): array
    {
        $amount = '₦' . number_format($this->transaction->amount, 2);

        return [
            'type' => 'deposit_recorded',
            'transaction_id' => $this->transaction->id,
            'reference' => $this->transaction->reference,
            'amount' => $this->transaction->amount,
            'message' => "A savings deposit of {$amount} has been recorded to your account.",
        ];
    }
}
