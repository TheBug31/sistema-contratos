<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ContractStatusRequest;
use App\Models\ContractStatusHistory;
use App\Notifications\ContractRequestApproved;
use App\Notifications\ContractRequestRejected;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContractStatusRequestController extends Controller
{
    /**
     * Listado de solicitudes pendientes.
     * Gerente: solo contratos de su operación.
     * Secretario/Admin: todas.
     */
    public function index(Request $request)
    {
        $user  = $request->user();
        $query = ContractStatusRequest::with(['contract.advisor', 'requester'])
            ->latest();

        if ($user->hasRole('Gerente')) {
            $query->whereHas('contract.advisor', function ($q) use ($user) {
                $q->where('operation_id', $user->operation_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'pendiente');
        }

        $requests = $query->paginate(20)->withQueryString();

        $pendingCount = ContractStatusRequest::pendiente()
            ->when($user->hasRole('Gerente'), function ($q) use ($user) {
                $q->whereHas('contract.advisor', fn($a) => $a->where('operation_id', $user->operation_id));
            })
            ->count();

        return view('contract-requests.index', compact('requests', 'pendingCount'));
    }

    /**
     * El Asesor crea una solicitud de cambio de estado.
     */
    public function store(Request $request, Contract $contract)
    {
        $request->validate([
            'requested_status' => 'required|in:limpio,lleno,venta,anulado',
            'reason'           => 'required|string|min:5|max:500',
        ]);

        // No permitir si el contrato está anulado
        if ($contract->current_status === 'anulado') {
            return back()->withErrors(['error' => 'Un contrato anulado no puede cambiar de estado.']);
        }

        // No permitir si ya hay una solicitud pendiente para este contrato
        $exists = ContractStatusRequest::where('contract_id', $contract->id)
            ->where('status', 'pendiente')
            ->exists();

        if ($exists) {
            return back()->withErrors(['error' => 'Ya existe una solicitud pendiente para este contrato.']);
        }

        // No tiene sentido pedir el mismo estado
        if ($contract->current_status === $request->requested_status) {
            return back()->withErrors(['error' => 'El estado solicitado es igual al estado actual.']);
        }

        ContractStatusRequest::create([
            'contract_id'      => $contract->id,
            'requested_by'     => $request->user()->id,
            'requested_status' => $request->requested_status,
            'reason'           => $request->reason,
            'status'           => 'pendiente',
        ]);

        return back()->with('success', 'Solicitud enviada correctamente. Queda pendiente de aprobación.');
    }

    /**
     * Aprobar una solicitud — cambia el estado real del contrato.
     */
    public function approve(Request $request, ContractStatusRequest $statusRequest)
    {
        abort_if(!$statusRequest->isPendiente(), 422, 'Esta solicitud ya fue procesada.');

        DB::transaction(function () use ($request, $statusRequest) {

            $contract = $statusRequest->contract;

            // Historial
            ContractStatusHistory::create([
                'contract_id'     => $contract->id,
                'previous_status' => $contract->current_status,
                'new_status'      => $statusRequest->requested_status,
                'observation'     => "Aprobado. Motivo del asesor: {$statusRequest->reason}",
                'changed_by'      => $request->user()->id,
                'changed_at'      => now(),
            ]);

            // Actualizar contrato
            $contract->update(['current_status' => $statusRequest->requested_status]);

            // Cerrar solicitud
            $statusRequest->update([
                'status'      => 'aprobado',
                'resolved_by' => $request->user()->id,
                'resolved_at' => now(),
            ]);
        });

        // Notificar al asesor (solicitante)
        $statusRequest->load('contract');
        $statusRequest->contract->advisor?->notify(
            new ContractRequestApproved($statusRequest, $statusRequest->contract)
        );

        return back()->with('success', 'Solicitud aprobada y estado actualizado. Se notificó al asesor.');
    }

    /**
     * Rechazar una solicitud.
     */
    public function reject(Request $request, ContractStatusRequest $statusRequest)
    {
        abort_if(!$statusRequest->isPendiente(), 422, 'Esta solicitud ya fue procesada.');

        $request->validate([
            'rejection_reason' => 'required|string|min:5|max:500',
        ]);

        $statusRequest->update([
            'status'           => 'rechazado',
            'resolved_by'      => $request->user()->id,
            'resolved_at'      => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        // Notificar al asesor (solicitante)
        $statusRequest->load('contract');
        $statusRequest->contract->advisor?->notify(
            new ContractRequestRejected($statusRequest, $statusRequest->contract)
        );

        return back()->with('success', 'Solicitud rechazada. Se notificó al asesor.');
    }
}
