<?php

namespace App\Notifications;

use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MemberRegisteredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Member $member,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'member_registered',
            'member_id' => $this->member->id,
            'staff_id' => $this->member->staff_id,
            'message' => "New member registered: {$this->member->first_name} {$this->member->last_name} ({$this->member->staff_id_display}).",
        ];
    }
}
