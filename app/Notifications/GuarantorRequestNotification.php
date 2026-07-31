<?php

namespace App\Notifications;

use App\Models\LoanGuarantor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GuarantorRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public LoanGuarantor $guarantor,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $loan = $this->guarantor->loan;
        $borrower = $loan->member;

        $acceptUrl = $this->guarantor->accept_token
            ? route('guarantee.show', $this->guarantor->accept_token)
            : route('portal.guarantors');

        return (new MailMessage)
            ->subject('Guarantor Request - Loan ' . $loan->loan_number)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line("You have been requested to serve as a guarantor for a loan application.")
            ->line("Borrower: {$borrower->first_name} {$borrower->last_name} ({$borrower->staff_id_display})")
            ->line("Loan Number: {$loan->loan_number}")
            ->line("Loan Amount: Ã¢â€šÂ¦" . number_format($loan->amount, 2))
            ->line("Monthly Repayment: Ã¢â€šÂ¦" . number_format($loan->monthly_repayment, 2))
            ->line("Click the button below to accept or decline Ã¢â‚¬â€ no login required.")
            ->action('Respond to Request', $acceptUrl);
    }

    public function toArray(object $notifiable): array
    {
        $loan = $this->guarantor->loan;
        $borrower = $loan->member;

        return [
            'type' => 'guarantor_request',
            'guarantor_id' => $this->guarantor->id,
            'loan_number' => $loan->loan_number,
            'loan_amount' => $loan->amount,
            'borrower_name' => $borrower->first_name . ' ' . $borrower->last_name,
            'message' => "{$borrower->first_name} {$borrower->last_name} has requested you to be a guarantor for loan {$loan->loan_number}.",
            'accept_url' => $this->guarantor->accept_token
                ? route('guarantee.show', $this->guarantor->accept_token)
                : route('portal.guarantors'),
        ];
    }
}
