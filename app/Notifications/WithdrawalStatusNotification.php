<?php

namespace App\Notifications;

use App\Models\SavingsTransaction;
use App\Notifications\Channels\TermiiSmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public SavingsTransaction $transaction,
        public string $oldStatus,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database', TermiiSmsChannel::class];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $amount = '₦' . number_format($this->transaction->amount, 2);
        $ref = $this->transaction->reference;

        if ($this->transaction->status === 'completed') {
            return (new MailMessage)
                ->subject('Withdrawal Approved - ' . $ref)
                ->greeting('Hello ' . $notifiable->name . '!')
                ->line("Your withdrawal request of {$amount} has been approved and processed.")
                ->line("Reference: {$ref}")
                ->line("The amount has been deducted from your savings account.")
                ->line("New balance: ₦" . number_format($this->transaction->balance_after, 2));
        }

        if ($this->transaction->status === 'rejected') {
            $msg = (new MailMessage)
                ->subject('Withdrawal Rejected - ' . $ref)
                ->greeting('Hello ' . $notifiable->name . '!')
                ->line("Your withdrawal request of {$amount} has been rejected.")
                ->line("Reference: {$ref}");

            if ($this->transaction->rejection_reason) {
                $msg->line("Reason: {$this->transaction->rejection_reason}");
            }

            return $msg->line("Please contact the cooperative for more information.");
        }

        return (new MailMessage)
            ->subject('Withdrawal Status Update - ' . $ref)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line("Your withdrawal request of {$amount} status has been updated to {$this->transaction->status}.");
    }

    public function toTermii(object $notifiable): string
    {
        $amount = '₦' . number_format($this->transaction->amount);
        $ref = $this->transaction->reference;

        return match ($this->transaction->status) {
            'completed' => "NAPTIN Coop: Withdrawal {$amount} ({$ref}) approved and processed.",
            'rejected' => "NAPTIN Coop: Withdrawal {$amount} ({$ref}) rejected." . ($this->transaction->rejection_reason ? " Reason: " . $this->transaction->rejection_reason : ''),
            default => "NAPTIN Coop: Withdrawal {$amount} ({$ref}) status: {$this->transaction->status}.",
        };
    }

    public function toArray(object $notifiable): array
    {
        $amount = '₦' . number_format($this->transaction->amount, 2);

        return [
            'type' => 'withdrawal_status',
            'transaction_id' => $this->transaction->id,
            'reference' => $this->transaction->reference,
            'amount' => $this->transaction->amount,
            'old_status' => $this->oldStatus,
            'new_status' => $this->transaction->status,
            'message' => "Your withdrawal of {$amount} was {$this->transaction->status}.",
        ];
    }
}
