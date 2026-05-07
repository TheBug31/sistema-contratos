<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function before(User $user)
    {
        if ($user->hasRole('Administrador')) {
            return true;
        }
    }
    public function viewAny(User $authUser)
    {
        return $authUser->hasAnyRole(['Administrador', 'Secretario', 'Gerente']);
    }

    public function view(User $authUser, User $user)
    {
        // non-admins shouldn't be able to even retrieve the administrator's record
        if ($user->hasRole('Administrador') && !$authUser->hasRole('Administrador')) {
            return false;
        }

        if ($authUser->hasRole('Administrador')) return true;

        if ($authUser->hasRole('Secretario')) return true;

        if ($authUser->hasRole('Gerente')) {
            return $authUser->operation_id === $user->operation_id;
        }

        if ($authUser->hasRole('Asesor')) {
            return $authUser->id === $user->id;
        }

        return false;
    }

    public function create(User $authUser)
    {
        return $authUser->hasAnyRole(['Administrador', 'Secretario', 'Gerente']);
    }

    public function update(User $authUser, User $user)
    {
        // never allow a non‑admin to touch an administrator record
        if ($user->hasRole('Administrador') && !$authUser->hasRole('Administrador')) {
            return false;
        }

        if ($authUser->hasRole('Administrador')) {
            return true;
        }

        if ($authUser->hasRole('Secretario')) {
            // after the check above we already know target is not an admin
            return true;
        }

        if ($authUser->hasRole('Gerente')) {
            return $authUser->operation_id === $user->operation_id
                && $user->hasRole('Asesor');
        }

        if ($authUser->hasRole('Asesor')) {
            return $authUser->id === $user->id;
        }

        return false;
    }

    public function delete(User $authUser, User $user)
    {
        // only administrators delete users, and an admin can of course delete another admin
        return $authUser->hasRole('Administrador');
    }
}
