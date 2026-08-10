<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LedgerTamperNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly array $brokenEntries) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $count = count($this->brokenEntries);
        $ids = collect($this->brokenEntries)->pluck('entry_number')->implode(', ');

        return (new MailMessage)
            ->error()
            ->subject("Ledger integrity check failed: {$count} tampered journal entr".($count === 1 ? 'y' : 'ies'))
            ->greeting('Ledger hash-chain verification failed')
            ->line('The scheduled ledger integrity check detected journal entries whose stored hash does not match the recomputed hash chain.')
            ->line("Affected entry numbers: {$ids}")
            ->action('Open Audit Trail', url('/finance/audit-trail'))
            ->line('Investigate immediately — the ledger may have been tampered with outside the application.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Ledger integrity check failed',
            'body' => 'The scheduled hash-chain verification found '.count($this->brokenEntries).' tampered journal entries. Review the Audit Trail immediately.',
            'entries' => $this->brokenEntries,
            'action_url' => '/finance/audit-trail',
        ];
    }
}
