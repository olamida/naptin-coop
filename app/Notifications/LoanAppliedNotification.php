<?php

namespace App\Notifications;

use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanAppliedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Loan $loan,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $amount = '₦' . number_format($this->loan->amount, 2);
        $member = $this->loan->member;
        $memberName = "{$member->first_name} {$member->last_name}";
        $purpose = $this->loan->purpose ?: 'Not specified';

        return (new MailMessage)
            ->subject('New Loan Application - ' . $this->loan->loan_number)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line("A new loan application has been submitted and requires your review.")
            ->line("Applicant: {$memberName} ({$member->staff_id})")
            ->line("Loan Number: {$this->loan->loan_number}")
            ->line("Amount: {$amount}")
            ->line("Purpose: {$purpose}")
            ->action('Review Application', route('loans.show', $this->loan));
    }

    public function toArray(object $notifiable): array
    {
        $amount = '₦' . number_format($this->loan->amount, 2);
        $member = $this->loan->member;

        return [
            'type' => 'loan_applied',
            'loan_id' => $this->loan->id,
            'loan_number' => $this->loan->loan_number,
            'amount' => $this->loan->amount,
            'message' => "{$member->first_name} {$member->last_name} applied for a loan of {$amount} ({$this->loan->loan_number}).",
        ];
    }
}
