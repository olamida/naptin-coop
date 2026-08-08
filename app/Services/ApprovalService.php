<?php

namespace App\Services;

use App\Models\ApprovalWorkflow;
use App\Models\PendingApproval;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ApprovalService
{
    /**
     * The workflow definition for a given key, or null.
     */
    public function workflow(string $key): ?ApprovalWorkflow
    {
        return ApprovalWorkflow::where('key', $key)->first();
    }

    /**
     * Whether the given action/amount requires the maker-checker workflow.
     */
    public function requiresApproval(string $key, ?float $amount = null): bool
    {
        $workflow = $this->workflow($key);

        if (! $workflow || ! $workflow->enabled) {
            return false;
        }

        if ($workflow->threshold_amount !== null) {
            return $amount !== null && $amount > (float) $workflow->threshold_amount;
        }

        return true;
    }

    /**
     * Create pending approval slots for the workflow. Idempotent: re-requesting
     * does not duplicate open slots.
     */
    public function request(string $key, Model $approvable, ?int $requestedBy = null, ?string $reason = null): Collection
    {
        $workflow = $this->workflow($key);
        if (! $workflow || ! $workflow->enabled) {
            throw new \RuntimeException("Approval workflow [{$key}] is not enabled.");
        }

        $created = collect();

        foreach (($workflow->required_roles ?? ['reviewer']) as $role) {
            $created->push(PendingApproval::firstOrCreate(
                [
                    'workflow' => $key,
                    'approvable_type' => $approvable::class,
                    'approvable_id' => $approvable->getKey(),
                    'required_role' => $role,
                ],
                [
                    'status' => PendingApproval::STATUS_PENDING,
                    'requested_by' => $requestedBy,
                    'reason' => $reason,
                ]
            ));
        }

        return $created;
    }

    /**
     * Approve a single approval slot.
     */
    public function approve(PendingApproval $approval, int $approverId): void
    {
        if ($approval->status !== PendingApproval::STATUS_PENDING) {
            throw new \RuntimeException('This approval was already resolved.');
        }

        $approval->update([
            'status' => PendingApproval::STATUS_APPROVED,
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);
    }

    /**
     * All approval slots for the workflow+approvable pair are approved by
     * distinct users.
     */
    public function isFullyApproved(Model $approvable, string $workflow): bool
    {
        $slots = $this->slotsFor($approvable, $workflow);

        if ($slots->isEmpty()) {
            return false;
        }

        $approved = $slots->where('status', PendingApproval::STATUS_APPROVED);

        return $approved->count() === $slots->count()
            && $approved->pluck('approved_by')->filter()->unique()->count() === $slots->count();
    }

    /**
     * Outstanding (still pending) approval slots count.
     */
    public function outstanding(Model $approvable, string $workflow): int
    {
        return $this->slotsFor($approvable, $workflow)
            ->where('status', PendingApproval::STATUS_PENDING)
            ->count();
    }

    /**
     * All slots for the workflow+approvable pair.
     */
    public function slotsFor(Model $approvable, string $workflow): Collection
    {
        return PendingApproval::where('workflow', $workflow)
            ->where('approvable_type', $approvable::class)
            ->where('approvable_id', $approvable->getKey())
            ->orderBy('id')
            ->get();
    }

    /**
     * Whether a user may fill one of the pending slots for the workflow+approvable pair.
     * A checker must hold the workflow's gate permission, must not be the requester,
     * and must not already have approved another slot in the same workflow (dual control).
     */
    public function approverEligible(Model $approvable, string $workflow, User $user): bool
    {
        $workflowDef = $this->workflow($workflow);
        $gate = $workflowDef?->required_permission;

        if ($gate && ! $user->can($gate)) {
            return false;
        }

        $slots = $this->slotsFor($approvable, $workflow);

        if ($slots->where('status', PendingApproval::STATUS_PENDING)->isEmpty()) {
            return false;
        }

        if ($slots->where('requested_by', $user->id)->isNotEmpty()) {
            return false;
        }

        if ($slots->where('status', PendingApproval::STATUS_APPROVED)
            ->where('approved_by', $user->id)->isNotEmpty()) {
            return false;
        }

        return true;
    }

    /**
     * The first pending slot the given user is eligible to approve.
     */
    public function nextApprovableSlot(Model $approvable, string $workflow, User $user): ?PendingApproval
    {
        return $this->slotsFor($approvable, $workflow)
            ->where('status', PendingApproval::STATUS_PENDING)
            ->first(fn (PendingApproval $slot) => $this->approverEligible($approvable, $workflow, $user));
    }
}
