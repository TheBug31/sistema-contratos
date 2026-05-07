<?php

namespace App\Observers;

use App\Models\Audit;
use App\Models\Contract;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ContractObserver
{
    private function createAudit(Contract $contract, string $action, ?array $oldValues = null, ?array $newValues = null): void
    {
        Audit::create([
            'action' => $action,
            'auditable_type' => Contract::class,
            'auditable_id' => $contract->id,
            'user_id' => Auth::id(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Handle the Contract "created" event.
     */
    public function created(Contract $contract): void
    {
        $this->createAudit(
            $contract,
            'assigned',
            null,
            $contract->getAttributes()
        );

        // fire event
        event(new \App\Events\ContractAssigned($contract, Auth::user()));
    }

    /**
     * Handle the Contract "updated" event.
     */
    public function updated(Contract $contract): void
    {
        $changes = $contract->getChanges();

        if (empty($changes)) {
            return;
        }

        // Determine the action type based on what changed
        $action = 'updated';
        if (isset($changes['current_status'])) {
            $action = 'status_changed';
        }
        if (isset($changes['advisor_id'])) {
            $action = 'reassigned';
        }

        $oldValues = [];
        foreach ($changes as $key => $newValue) {
            $oldValues[$key] = $contract->getOriginal($key);
        }

        $this->createAudit(
            $contract,
            $action,
            $oldValues,
            $changes
        );

        // dispatch related event
        if (isset($changes['current_status'])) {
            event(new \App\Events\ContractStatusChanged(
                $contract,
                $oldValues['current_status'] ?? null,
                $changes['current_status'],
                Auth::user()
            ));
        }
        if (isset($changes['advisor_id'])) {
            event(new \App\Events\ContractReassigned(
                $contract,
                $oldValues['advisor_id'] ?? null,
                $changes['advisor_id'],
                Auth::user()
            ));
        }
    }

    /**
     * Handle the Contract "deleted" event.
     */
    public function deleted(Contract $contract): void
    {
        $this->createAudit(
            $contract,
            'deleted',
            $contract->getAttributes()
        );
    }

    /**
     * Handle the Contract "restored" event.
     */
    public function restored(Contract $contract): void
    {
        $this->createAudit(
            $contract,
            'restored',
            null,
            $contract->getAttributes()
        );
    }

    /**
     * Handle the Contract "force deleted" event.
     */
    public function forceDeleted(Contract $contract): void
    {
        $this->createAudit(
            $contract,
            'force_deleted',
            $contract->getAttributes()
        );
    }
}
