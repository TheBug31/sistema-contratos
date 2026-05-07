<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\User;
use App\Models\ContractStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContractController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Contract::with('advisor');

        if ($user->hasRole('Gerente')) {
            $query->whereHas('advisor', function($q) use ($user) {
                $q->where('operation_id', $user->operation_id);
            });
        }

        if ($user->hasRole('Asesor')) {
            $query->where('advisor_id', $user->id);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('number', $request->search)
                  ->orWhereHas('advisor', function($a) use ($request) {
                      $a->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->status) {
            $query->where('current_status', $request->status);
        }

        return response()->json([
            'success' => true,
            'data' => $query->latest()->paginate(20)
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Contract::class);

        $request->validate([
            'number'         => 'required|integer|unique:contracts,number',
            'advisor_id'     => 'required|exists:users,id',
            'delivered_at'   => 'nullable|date',
            'client_name'    => 'nullable|string|max:255',
            'client_document' => 'nullable|string|max:50',
            'client_phone'   => 'nullable|string|max:20',
            'amount'         => 'nullable|numeric|min:0',
            'signed_at'      => 'nullable|date',
            'expires_at'     => 'nullable|date|after_or_equal:signed_at',
        ]);

        $advisor = User::findOrFail($request->advisor_id);
        if (!$advisor->hasRole('Asesor')) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario no es Asesor'
            ], 422);
        }

        $contract = Contract::create([
            'number'         => $request->number,
            'code'           => 'CL-' . $request->number,
            'advisor_id'     => $request->advisor_id,
            'current_status' => 'asignado',
            'delivered_at'   => $request->delivered_at ?? now(),
            'client_name'    => $request->client_name,
            'client_document' => $request->client_document,
            'client_phone'   => $request->client_phone,
            'amount'         => $request->amount,
            'signed_at'      => $request->signed_at,
            'expires_at'     => $request->expires_at,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contrato creado correctamente',
            'data' => $contract->load('advisor')
        ], 201);
    }

    public function show(Contract $contract)
    {
        $this->authorize('view', $contract);
        return response()->json([
            'success' => true,
            'data' => $contract->load('advisor', 'histories.changedBy')
        ]);
    }

    public function update(Request $request, Contract $contract)
    {
        $this->authorize('update', $contract);

        $request->validate([
            'client_name'    => 'sometimes|nullable|string|max:255',
            'client_document' => 'sometimes|nullable|string|max:50',
            'client_phone'   => 'sometimes|nullable|string|max:20',
            'amount'         => 'sometimes|nullable|numeric|min:0',
            'signed_at'      => 'sometimes|nullable|date',
            'expires_at'     => 'sometimes|nullable|date|after_or_equal:signed_at',
        ]);

        $contract->update($request->only([
            'client_name', 'client_document', 'client_phone',
            'amount', 'signed_at', 'expires_at'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Contrato actualizado correctamente',
            'data' => $contract->fresh()->load('advisor')
        ]);
    }

    public function destroy(Contract $contract)
    {
        $this->authorize('delete', $contract);
        $contract->delete();
        return response()->json([
            'success' => true,
            'message' => 'Contrato eliminado correctamente'
        ]);
    }

    public function history(Contract $contract)
    {
        $this->authorize('view', $contract);
        return response()->json([
            'success' => true,
            'data' => $contract->histories()->with('changedBy')->latest()->get()
        ]);
    }

    public function assign(Request $request)
    {
        $this->authorize('assign', Contract::class);

        $request->validate([
            'advisor_id'   => 'required|exists:users,id',
            'start_number' => 'required|integer|min:1',
            'quantity'     => 'required|integer|min:1|max:500'
        ]);

        $advisor = User::findOrFail($request->advisor_id);
        if (!$advisor->hasRole('Asesor')) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario no es Asesor'
            ], 422);
        }

        DB::transaction(function () use ($request) {
            for ($i = 0; $i < $request->quantity; $i++) {
                $number = $request->start_number + $i;
                if (Contract::where('number', $number)->exists()) {
                    throw new \Exception("El contrato {$number} ya existe");
                }
                Contract::create([
                    'number'         => $number,
                    'code'           => 'CL-' . $number,
                    'advisor_id'     => $request->advisor_id,
                    'current_status' => 'asignado',
                    'delivered_at'   => now()
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Contratos asignados correctamente'
        ], 201);
    }

    public function changeStatus(Request $request, Contract $contract)
    {
        $this->authorize('changeStatus', $contract);

        $request->validate([
            'new_status'  => 'required|in:asignado,limpio,lleno,venta,anulado',
            'observation' => 'required|string|min:5'
        ]);

        DB::transaction(function () use ($request, $contract) {
            $previousStatus = $contract->current_status;

            if ($previousStatus === 'anulado') {
                throw new \Exception('Un contrato anulado no puede cambiar de estado');
            }

            ContractStatusHistory::create([
                'contract_id'    => $contract->id,
                'previous_status' => $previousStatus,
                'new_status'     => $request->new_status,
                'observation'    => $request->observation,
                'changed_by'     => $request->user()->id,
                'changed_at'     => now()
            ]);

            $contract->update(['current_status' => $request->new_status]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente'
        ]);
    }

    public function reassign(Request $request, Contract $contract)
    {
        $this->authorize('reassign', $contract);

        $request->validate(['advisor_id' => 'required|exists:users,id']);

        $newAdvisor = User::findOrFail($request->advisor_id);
        if (!$newAdvisor->hasRole('Asesor')) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario no es Asesor'
            ], 422);
        }

        if ($contract->current_status === 'venta') {
            return response()->json([
                'success' => false,
                'message' => 'Un contrato en venta no puede reasignarse'
            ], 422);
        }

        $contract->update(['advisor_id' => $newAdvisor->id]);

        return response()->json([
            'success' => true,
            'message' => 'Contrato reasignado correctamente'
        ]);
    }

    public function accept(Request $request, Contract $contract)
    {
        $this->authorize('accept', $contract);

        if ($contract->current_status !== 'asignado') {
            return response()->json([
                'success' => false,
                'message' => 'El contrato no está en estado asignado'
            ], 422);
        }

        $previousStatus = $contract->current_status;
        $contract->update(['current_status' => 'limpio']);

        ContractStatusHistory::create([
            'contract_id'     => $contract->id,
            'changed_by'      => $request->user()->id,
            'previous_status' => $previousStatus,
            'new_status'      => 'limpio',
            'observation'     => 'Contrato aceptado por el asesor.',
            'changed_at'      => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contrato aceptado correctamente'
        ]);
    }

    public function reject(Request $request, Contract $contract)
    {
        $this->authorize('reject', $contract);

        if ($contract->current_status !== 'asignado') {
            return response()->json([
                'success' => false,
                'message' => 'El contrato no está en estado asignado'
            ], 422);
        }

        $request->validate(['rejection_reason' => 'required|string|min:5|max:1000']);

        $previousStatus = $contract->current_status;

        $contract->update([
            'current_status' => 'limpio',
            'advisor_id'     => null,
        ]);

        ContractStatusHistory::create([
            'contract_id'     => $contract->id,
            'changed_by'      => $request->user()->id,
            'previous_status' => $previousStatus,
            'new_status'      => 'limpio',
            'observation'     => 'Contrato rechazado por el asesor. Motivo: ' . $request->rejection_reason,
            'changed_at'      => now(),
        ]);

        $admins = \App\Models\User::role(['Administrador', 'Secretario'])->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\ContractRejectedByAdvisor(
                $contract,
                $request->user(),
                $request->rejection_reason
            ));
        }

        return response()->json([
            'success' => true,
            'message' => 'Contrato rechazado y devuelto al pool de contratos'
        ]);
    }
}
