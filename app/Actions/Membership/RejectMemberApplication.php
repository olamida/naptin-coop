<?php

namespace App\Actions\Membership;

use App\Actions\Action;
use App\Models\Member;

class RejectMemberApplication extends Action
{
    public function handle(Member $member): Member
    {
        if ($member->status !== 'pending') {
            throw new \RuntimeException('Only pending members can be rejected.');
        }

        $member->update(['status' => 'inactive']);

        return $member->fresh();
    }
}
