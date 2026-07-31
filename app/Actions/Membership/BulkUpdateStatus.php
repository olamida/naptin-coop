<?php

namespace App\Actions\Membership;

use App\Actions\Action;
use App\Models\Member;

class BulkUpdateStatus extends Action
{
    public function handle(array $memberIds, string $status): int
    {
        return Member::whereIn('id', $memberIds)
            ->update(['status' => $status]);
    }
}
