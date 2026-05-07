<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;

class ContractPolicy
{
    public function viewAny(User $authUser): bool
    {
        return $authUser->hasAnyRole([
            'Administrador',
            'Secretario',
            'Gerente'
        ]);
    }

    public function view(User $authUser, Contract $contract): bool
    {
        if ($authUser->hasRole('Administrador')) return true;

        if ($authUser->hasRole('Secretario')) return true;

        $advisor = optional($contract->advisor);

        if ($authUser->hasRole('Gerente')) {
            return $advisor
                && $authUser->operation_id === $advisor->operation_id;
        }

        if ($authUser->hasRole('Asesor')) {
            return $authUser->id === $contract->advisor_id;
        }

        return false;
    }

    public function create(User $authUser, ?User $advisor = null): bool
    {
        if ($authUser->hasRole('Administrador')) return true;

        if ($authUser->hasRole('Secretario')) return true;

        if ($authUser->hasRole('Gerente')) {
            return $advisor
                && $advisor->hasRole('Asesor')
                && $authUser->operation_id === $advisor->operation_id;
        }

        if ($authUser->hasRole('Asesor')) {
            return $advisor
                && $authUser->id === $advisor->id;
        }

        return false;
    }

    public function update(User $authUser, Contract $contract): bool
    {
        if ($authUser->hasRole('Administrador')) return true;

        if ($authUser->hasRole('Secretario')) return true;

        $advisor = optional($contract->advisor);

        if ($authUser->hasRole('Gerente')) {
            return $advisor
                && $advisor->hasRole('Asesor')
                && $authUser->operation_id === $advisor->operation_id;
        }

        if ($authUser->hasRole('Asesor')) {
            return $authUser->id === $contract->advisor_id;
        }

        return false;
    }

    public function delete(User $authUser, Contract $contract): bool
    {
        return $authUser->hasRole('Administrador');
    }

    public function assign(User $authUser): bool
    {
        if ($authUser->hasRole('Administrador')) return true;

        if ($authUser->hasRole('Secretario')) return true;

        if ($authUser->hasRole('Gerente')) return true;

        return false;
    }
    public function changeStatus(User $authUser, Contract $contract): bool
    {
        if ($authUser->hasRole('Administrador')) return true;

        if ($authUser->hasRole('Secretario')) return true;

        $advisor = optional($contract->advisor);

        if ($authUser->hasRole('Gerente')) {
            return $advisor
                && $advisor->hasRole('Asesor')
                && $authUser->operation_id === $advisor->operation_id;
        }

        if ($authUser->hasRole('Asesor')) {
            return $authUser->id === $contract->advisor_id;
        }

        return false;
    }

    public function reassign(User $authUser, Contract $contract): bool
    {
        return $authUser->hasAnyRole(['Administrador', 'Secretario']);
    }

    public function accept(User $authUser, Contract $contract): bool
    {
        if (!$authUser->hasRole('Asesor')) {
            return false;
        }

        return $authUser->id === $contract->advisor_id;
    }

    public function reject(User $authUser, Contract $contract): bool
    {
        if (!$authUser->hasRole('Asesor')) {
            return false;
        }

        return $authUser->id === $contract->advisor_id;
    }
}
