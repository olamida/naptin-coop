<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ControlVarianceNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly array $variances) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $summary = collect($this->variances)->map(
            fn (array $row) => "{$row['code']} {$row['name']}: ledger {$row['ledger_balance']} vs sub-ledger {$row['sub_ledger_balance']} (variance {$row['variance']})"
        )->implode("\n");

        return (new MailMessage)
            ->error()
            ->subject('Ledger control-account reconciliation variance detected')
            ->greeting('Control account variance detected')
            ->line('The scheduled reconciliation found one or more control accounts that no longer match their sub-ledger totals:')
            ->line($summary)
            ->action('Open Control Reconciliation', url('/finance/control-reconciliation'))
            ->line('Run Finance → Sync Opening Balances or review recent postings.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Control account variance detected',
            'body' => 'The scheduled reconciliation found '.count($this->variances).' control account(s) out of balance with their sub-ledgers.',
            'variances' => $this->variances,
            'action_url' => '/finance/control-reconciliation',
        ];
    }
}
