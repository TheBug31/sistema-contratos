<?php

namespace App\Listeners;

use App\Events\ContractAssigned;

class LogContractAssigned
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
    public function handle(ContractAssigned $event): void
    {
        \Log::info('Contract assigned', [
            'contract_id' => $event->contract()->id,
            'assigned_by' => optional($event->user())->id,
        ]);
    }
}
