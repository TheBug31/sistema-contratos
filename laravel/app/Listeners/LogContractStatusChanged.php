<?php

namespace App\Listeners;

use App\Events\ContractStatusChanged;

class LogContractStatusChanged
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
    public function handle(ContractStatusChanged $event): void
    {
        \Log::info('Contract status changed', [
            'contract_id' => $event->contract()->id,
            'previous_status' => $event->previousStatus,
            'new_status' => $event->newStatus,
            'changed_by' => optional($event->user())->id,
        ]);
    }
}
