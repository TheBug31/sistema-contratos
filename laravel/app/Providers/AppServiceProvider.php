<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Observers
        \App\Models\Contract::observe(\App\Observers\ContractObserver::class);
        \App\Models\User::observe(\App\Observers\UserObserver::class);

        // Events
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\ContractAssigned::class,
            [\App\Listeners\LogContractAssigned::class, 'handle']
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\ContractReassigned::class,
            [\App\Listeners\LogContractReassigned::class, 'handle']
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\ContractStatusChanged::class,
            [\App\Listeners\LogContractStatusChanged::class, 'handle']
        );
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\UserCreated::class,
            [\App\Listeners\LogUserCreated::class, 'handle']
        );
    }
}
