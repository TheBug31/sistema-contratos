<?php

namespace App\Providers;


use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\User;
use App\Models\Contract;
use App\Policies\UserPolicy;
use App\Policies\ContractPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }
    protected $policies = [
        User::class => UserPolicy::class,
        Contract::class => ContractPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
