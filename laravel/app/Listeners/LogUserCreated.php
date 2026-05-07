<?php

namespace App\Listeners;

use App\Events\UserCreated;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class LogUserCreated
{
    public function handle(UserCreated $event): void
    {
        Log::info('User created', [
            'user_id' => $event->user->id,
            'name' => $event->user->name,
            // Use Auth::id() or auth()->id() here
            'created_by' => Auth::id(),
        ]);
    }
}
