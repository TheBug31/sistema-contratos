<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \App\Events\ContractAssigned::class => [
            \App\Listeners\LogContractAssigned::class,
        ],
        \App\Events\ContractReassigned::class => [
            \App\Listeners\LogContractReassigned::class,
        ],
        \App\Events\ContractStatusChanged::class => [
            \App\Listeners\LogContractStatusChanged::class,
        ],
        \App\Events\UserCreated::class => [
            \App\Listeners\LogUserCreated::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();

        // you may also register closures here
    }
}
