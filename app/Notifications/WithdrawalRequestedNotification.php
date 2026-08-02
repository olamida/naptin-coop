<?php

namespace App\Notifications;

use App\Models\SavingsTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalRequestedNotification extends Notification implements ShouldQueue
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
        $amount = '₦'.number_format($this->transaction->amount, 2);
        $member = $this->transaction->savingsAccount->member ?? null;
        $memberName = $member ? "{$member->first_name} {$member->last_name}" : 'A member';
        $ref = $this->transaction->reference;

        return (new MailMessage)
            ->subject('Withdrawal Request Pending Approval - '.$ref)
            ->greeting('Hello '.$notifiable->name.'!')
            ->line("A savings withdrawal request of {$amount} has been submitted and requires your approval.")
            ->line("Member: {$memberName}".($member ? " ({$member->staff_id_display})" : ''))
            ->line("Reference: {$ref}")
            ->line('Current savings balance: ₦'.number_format($this->transaction->balance_before, 2))
            ->action('Review Request', route('savings.pending-withdrawals'));
    }

    public function toArray(object $notifiable): array
    {
        $amount = '₦'.number_format($this->transaction->amount, 2);
        $member = $this->transaction->savingsAccount->member ?? null;

        return [
            'type' => 'withdrawal_requested',
            'transaction_id' => $this->transaction->id,
            'reference' => $this->transaction->reference,
            'amount' => $this->transaction->amount,
            'message' => "A withdrawal request of {$amount} from {$member->first_name} {$member->last_name} is pending approval.",
        ];
    }
}
