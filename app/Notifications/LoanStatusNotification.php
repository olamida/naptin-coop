<?php

namespace App\Notifications;

use App\Models\Loan;
use App\Notifications\Channels\TermiiSmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Loan $loan,
        public string $oldStatus,
        public string $newStatus,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database', TermiiSmsChannel::class];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $greeting = match ($this->newStatus) {
            'approved' => 'Congratulations!',
            'rejected' => 'Loan Application Update',
            'disbursed' => 'Loan Disbursed!',
            'completed' => 'Loan Completed!',
            'defaulted' => 'Loan Notice',
            default => 'Loan Status Update',
        };

        $message = match ($this->newStatus) {
            'approved' => "Your loan application {$this->loan->loan_number} has been approved. Please visit the cooperative office to complete disbursement.",
            'rejected' => "We regret to inform you that your loan application {$this->loan->loan_number} has been rejected." . ($this->loan->rejection_reason ? " Reason: {$this->loan->rejection_reason}" : ''),
            'disbursed' => "Your loan {$this->loan->loan_number} of ₦" . number_format($this->loan->amount, 2) . " has been disbursed. Monthly repayment: ₦" . number_format($this->loan->monthly_repayment, 2) . ".",
            'completed' => "Congratulations! Your loan {$this->loan->loan_number} has been fully repaid. Thank you for fulfilling your obligation.",
            'defaulted' => "Your loan {$this->loan->loan_number} has exceeded its maturity date and remains unpaid. Outstanding: ₦" . number_format($this->loan->outstanding, 2) . ". Please contact the cooperative.",
            default => "Your loan {$this->loan->loan_number} status has been updated to {$this->newStatus}.",
        };

        return (new MailMessage)
            ->subject('Loan ' . ucfirst($this->newStatus) . ' - ' . $this->loan->loan_number)
            ->greeting($greeting)
            ->line($message);
    }

    public function toTermii(object $notifiable): string
    {
        return match ($this->newStatus) {
            'approved' => "NAPTIN Coop: Your loan {$this->loan->loan_number} has been APPROVED. Visit the office to complete disbursement.",
            'rejected' => "NAPTIN Coop: Your loan {$this->loan->loan_number} was rejected." . ($this->loan->rejection_reason ? " Reason: " . $this->loan->rejection_reason : ''),
            'disbursed' => "NAPTIN Coop: Loan {$this->loan->loan_number} of ₦" . number_format($this->loan->amount) . " disbursed. Monthly repayment ₦" . number_format($this->loan->monthly_repayment) . ".",
            'completed' => "NAPTIN Coop: Loan {$this->loan->loan_number} fully repaid. Thank you!",
            'defaulted' => "NAPTIN Coop: Loan {$this->loan->loan_number} is overdue. Outstanding ₦" . number_format($this->loan->outstanding) . ". Contact the office.",
            default => "NAPTIN Coop: Loan {$this->loan->loan_number} status: {$this->newStatus}.",
        };
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'loan_status',
            'loan_id' => $this->loan->id,
            'loan_number' => $this->loan->loan_number,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'amount' => $this->loan->amount,
            'message' => "Your loan {$this->loan->loan_number} status changed from {$this->oldStatus} to {$this->newStatus}.",
        ];
    }
}
