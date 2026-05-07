<?php

namespace App\Observers;

use App\Models\Audit;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class UserObserver
{
    private function createAudit(User $user, string $action, ?array $oldValues = null, ?array $newValues = null): void
    {
        Audit::create([
            'action' => $action,
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'user_id' => Auth::id(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Don't audit the password field
        $attributes = $user->getAttributes();
        unset($attributes['password']);

        $this->createAudit(
            $user,
            'created',
            null,
            $attributes
        );

        event(new \App\Events\UserCreated($user));
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // Don't audit password
        $attributes = $user->getAttributes();
        unset($attributes['password']);

        $this->createAudit(
            $user,
            'deleted',
            $attributes
        );
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        $attributes = $user->getAttributes();
        unset($attributes['password']);

        $this->createAudit(
            $user,
            'restored',
            null,
            $attributes
        );
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        $attributes = $user->getAttributes();
        unset($attributes['password']);

        $this->createAudit(
            $user,
            'force_deleted',
            $attributes
        );
    }
}
