<?php

namespace App\Policies;

use App\Enums\GuarantorStatus;
use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Models\User;

class LoanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view-loans');
    }

    public function view(User $user, Loan $loan): bool
    {
        return $user->hasPermissionTo('view-loans');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create-loans');
    }

    /**
     * Loans still awaiting guarantor acceptance cannot be approved.
     */
    public function approve(User $user, Loan $loan): bool
    {
        if (! $user->hasPermissionTo('approve-loans')) {
            return false;
        }

        if ($loan->status === LoanStatus::GuarantorPending->value) {
            $guarantors = $loan->guarantors()->get();

            if ($guarantors->isNotEmpty()) {
                $accepted = $guarantors->filter(fn ($g) => $g->status === GuarantorStatus::Accepted);

                return $accepted->count() === $guarantors->count();
            }
        }

        return true;
    }

    public function reject(User $user, Loan $loan): bool
    {
        return $user->hasPermissionTo('approve-loans');
    }

    public function disburse(User $user, Loan $loan): bool
    {
        return $user->hasPermissionTo('disburse-loans');
    }

    public function repay(User $user, Loan $loan): bool
    {
        return $user->hasPermissionTo('repay-loans');
    }

    public function delete(User $user, Loan $loan): bool
    {
        return $user->hasPermissionTo('delete-loans');
    }
}
