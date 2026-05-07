<?php

namespace App\Listeners;

use App\Events\ContractReassigned;

class LogContractReassigned
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ContractReassigned $event): void
    {
        \Log::info('Contract reassigned', [
            'contract_id' => $event->contract()->id,
            'old_advisor' => $event->oldAdvisorId,
            'new_advisor' => $event->newAdvisorId,
            'changed_by' => optional($event->user())->id,
        ]);
    }
}
