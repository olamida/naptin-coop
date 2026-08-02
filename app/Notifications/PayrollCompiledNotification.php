<?php

namespace App\Notifications;

use App\Models\MonthlyPayroll;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PayrollCompiledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public MonthlyPayroll $payroll,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payroll Compiled - '.$this->payroll->month.' '.$this->payroll->year)
            ->greeting('Hello '.$notifiable->name.'!')
            ->line("The {$this->payroll->month} {$this->payroll->year} payroll has been compiled.")
            ->line('Total deductions: ₦'.number_format($this->payroll->grand_total, 2))
            ->line("Members included: {$this->payroll->member_count}")
            ->line('Please review and process the payroll deductions.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payroll_compiled',
            'payroll_id' => $this->payroll->id,
            'month' => $this->payroll->month,
            'year' => $this->payroll->year,
            'message' => "The {$this->payroll->month} {$this->payroll->year} payroll has been compiled (₦".number_format($this->payroll->grand_total, 2).').',
        ];
    }
}
