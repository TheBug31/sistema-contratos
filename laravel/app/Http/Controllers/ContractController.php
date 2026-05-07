<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\User;
use App\Models\ContractStatusHistory;
use Illuminate\Http\Request;


class ContractController extends Controller
{
    public function index(Request $request)
    {
        $user  = $request->user();
        $query = Contract::with(['advisor', 'statusRequests']);

        // Scope por rol
        if ($user->hasRole('Gerente')) {
            $query->whereHas('advisor', function ($q) use ($user) {
                $q->where('operation_id', $user->operation_id);
            });
        }

        if ($user->hasRole('Asesor')) {
            $query->where('advisor_id', $user->id);
        }

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', '%' . $search . '%')
                    ->orWhereHas('advisor', fn($a) => $a->where('name', 'like', '%' . $search . '%'));
            });
        }

        if ($request->filled('status')) {
            $query->where('current_status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $contracts = $query->latest()->paginate(20)->withQueryString();

        // Asesores para modal de reasignación (gestores únicamente)
        $advisors = $user->hasRole('Asesor')
            ? collect()
            : User::role('Asesor')
            ->when(
                $user->hasRole('Gerente'),
                fn($q) => $q->where('operation_id', $user->operation_id)
            )
            ->orderBy('name')
            ->get();

        return view('contracts.index', compact('contracts', 'advisors'));
    }

    public function show(Contract $contract)
    {
        $contract->load('advisor');

        return view('contracts.show', compact('contract'));
    }

    public function create()
    {
        $advisors = User::role('Asesor')->orderBy('name')->get();

        return view('contracts.create', compact('advisors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'start_number' => 'required|integer|min:1',
            'quantity'     => 'required|integer|min:1|max:500',
            'advisor_id'   => 'required|exists:users,id',
            'delivered_at' => 'nullable|date',
            'observation'  => 'nullable|string|max:1000',
            'client_name' => 'nullable|string|max:255',
            'client_document' => 'nullable|string|max:50',
            'client_phone' => 'nullable|string|max:20',
            'amount' => 'nullable|numeric|min:0',
            'signed_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:signed_at',
        ]);

        $advisor = User::findOrFail($request->advisor_id);
        if (!$advisor->hasRole('Asesor')) {
            return back()->withErrors(['advisor_id' => 'El usuario seleccionado no tiene rol de Asesor.'])->withInput();
        }

        $start    = (int) $request->start_number;
        $quantity = (int) $request->quantity;
        $created  = 0;
        $skipped  = [];

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $start, $quantity, &$created, &$skipped) {
            for ($i = 0; $i < $quantity; $i++) {
                $number = $start + $i;

                if (Contract::where('number', $number)->exists()) {
                    $skipped[] = $number;
                    continue;
                }

                Contract::create([
                    'number'         => $number,
                    'code'           => 'CL-' . $number,
                    'advisor_id'     => $request->advisor_id,
                    'current_status' => Contract::STATUS_ASIGNADO,
                    'delivered_at'   => $request->delivered_at ?? now(),
                    'client_name'    => $request->client_name,
                    'client_document' => $request->client_document,
                    'client_phone'   => $request->client_phone,
                    'amount'         => $request->amount,
                    'signed_at'      => $request->signed_at,
                    'expires_at'     => $request->expires_at,
                ]);

                // Historial inicial si hay observación
                if ($request->filled('observation')) {
                    $contract = Contract::where('number', $number)->first();
                    ContractStatusHistory::create([
                        'contract_id'     => $contract->id,
                        'changed_by'      => $request->user()->id,
                        'previous_status' => null,
                        'new_status'      => Contract::STATUS_ASIGNADO,
                        'observation'     => $request->observation,
                        'changed_at'      => now(),
                    ]);
                }

                $created++;
            }
        });

        $message = "Se asignaron {$created} contrato(s) correctamente.";
        if (!empty($skipped)) {
            $message .= ' Los siguientes números ya existían y se omitieron: ' . implode(', ', $skipped) . '.';
        }

        return redirect()
            ->route('contracts.index')
            ->with('success', $message);
    }

    public function history(Contract $contract)
    {
        $histories = ContractStatusHistory::with('changedBy')
            ->where('contract_id', $contract->id)
            ->orderBy('changed_at', 'desc')
            ->get();

        return view('contracts.history', compact('contract', 'histories'));
    }

    public function changeStatus(Contract $contract)
    {
        return view('contracts.change-status', compact('contract'));
    }

    /**
     * Cambio directo de estado (Admin / Secretario / Gerente).
     * Ruta: PUT /contracts/{contract}/status  → name: contracts.update-status
     */
    public function updateStatus(Request $request, Contract $contract)
    {
        $request->validate([
            'status'      => 'required|in:asignado,limpio,lleno,venta,anulado',
            'observation' => 'nullable|string|max:1000',
        ]);

        if ($contract->current_status === 'anulado') {
            return back()->withErrors(['error' => 'Un contrato anulado no puede cambiar de estado.']);
        }

        $previousStatus = $contract->current_status;

        $contract->update(['current_status' => $request->status]);

        ContractStatusHistory::create([
            'contract_id'     => $contract->id,
            'changed_by'      => $request->user()->id,
            'previous_status' => $previousStatus,
            'new_status'      => $request->status,
            'observation'     => $request->observation ?? 'Sin observación.',
            'changed_at'      => now(),
        ]);

        // Si había solicitud pendiente para este contrato, cerrarla automáticamente
        $contract->statusRequests()
            ->where('status', 'pendiente')
            ->update([
                'status'           => 'aprobado',
                'resolved_by'      => $request->user()->id,
                'resolved_at'      => now(),
                'rejection_reason' => null,
            ]);

        return back()->with('success', 'Estado actualizado correctamente.');
    }

    /**
     * Reasignación de asesor (Admin / Secretario / Gerente).
     * Ruta: PUT /contracts/{contract}/reassign  → name: contracts.reassign
     */
    public function updateReassign(Request $request, Contract $contract)
    {
        $request->validate([
            'advisor_id' => [
                'required',
                'exists:users,id',
                'different:' . $contract->advisor_id,
            ],
        ]);

        // Verificar que el nuevo asesor sea efectivamente Asesor
        $newAdvisor = User::findOrFail($request->advisor_id);
        if (!$newAdvisor->hasRole('Asesor')) {
            return back()->withErrors(['advisor_id' => 'El usuario seleccionado no tiene rol de Asesor.']);
        }

        $previousAdvisorId = $contract->advisor_id;

        $contract->update(['advisor_id' => $request->advisor_id]);

        ContractStatusHistory::create([
            'contract_id'     => $contract->id,
            'changed_by'      => $request->user()->id,
            'previous_status' => $contract->current_status,
            'new_status'      => $contract->current_status,
            'observation'     => "Reasignado del asesor #{$previousAdvisorId} al asesor #{$request->advisor_id}.",
            'changed_at'      => now(),
        ]);

        return back()->with('success', 'Contrato reasignado correctamente.');
    }

    /**
     * Generar PDF del contrato.
     * Ruta: GET /contracts/{contract}/pdf → name: contracts.pdf
     */
    public function generatePdf(Contract $contract)
    {
        $this->authorize('view', $contract);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.contract-pdf', compact('contract'));

        return $pdf->download('contrato_' . ($contract->code ?? $contract->number) . '.pdf');
    }

    /**
     * Aceptar contrato asignado (Solo Asesor).
     * Ruta: POST /contracts/{contract}/accept → name: contracts.accept
     */
    public function accept(Contract $contract)
    {
        $this->authorize('accept', $contract);

        if ($contract->current_status !== Contract::STATUS_ASIGNADO) {
            return back()->withErrors(['error' => 'El contrato no está en estado asignado.']);
        }

        $previousStatus = $contract->current_status;
        $contract->update(['current_status' => Contract::STATUS_LIMPIO]);

        ContractStatusHistory::create([
            'contract_id'     => $contract->id,
            'changed_by'      => request()->user()->id,
            'previous_status' => $previousStatus,
            'new_status'      => Contract::STATUS_LIMPIO,
            'observation'     => 'Contrato aceptado por el asesor.',
            'changed_at'      => now(),
        ]);

        return back()->with('success', 'Contrato aceptado correctamente.');
    }

    /**
     * Rechazar contrato asignado (Solo Asesor).
     * Ruta: POST /contracts/{contract}/reject → name: contracts.reject
     */
    public function reject(Request $request, Contract $contract)
    {
        $this->authorize('reject', $contract);

        if ($contract->current_status !== Contract::STATUS_ASIGNADO) {
            return back()->withErrors(['error' => 'El contrato no está en estado asignado.']);
        }

        $request->validate([
            'rejection_reason' => 'required|string|min:5|max:1000',
        ]);

        $previousStatus = $contract->current_status;

        // El contrato vuelve a estar disponible (se desasigna)
        $contract->update([
            'current_status' => Contract::STATUS_LIMPIO,
            'advisor_id'     => null,
        ]);

        ContractStatusHistory::create([
            'contract_id'     => $contract->id,
            'changed_by'      => $request->user()->id,
            'previous_status' => $previousStatus,
            'new_status'      => Contract::STATUS_LIMPIO,
            'observation'     => 'Contrato rechazado por el asesor. Motivo: ' . $request->rejection_reason,
            'changed_at'      => now(),
        ]);

        // Notificar a administradores sobre el rechazo
        $admins = \App\Models\User::role(['Admin', 'Secretario'])->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\ContractRejectedByAdvisor($contract, $request->user(), $request->rejection_reason));
        }

        return back()->with('success', 'Contrato rechazado y devuelto al pool de contratos.');
    }

    /**
     * Cambio de estado masivo (Admin / Secretario / Gerente).
     * Ruta: POST /contracts/bulk/status  → name: contracts.bulk-status
     */
    public function bulkStatusUpdate(Request $request)
    {
        $request->validate([
            'contract_ids' => 'required|array|min:1',
            'contract_ids.*' => 'exists:contracts,id',
            'new_status' => 'required|in:asignado,limpio,lleno,venta,anulado',
            'observation' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();
        $contracts = Contract::whereIn('id', $request->contract_ids)->get();

        if ($user->hasRole('Gerente')) {
            $contracts = $contracts->filter(function ($contract) use ($user) {
                return $contract->advisor && $contract->advisor->operation_id === $user->operation_id;
            });
        }

        $updated = 0;
        $errors = [];

        foreach ($contracts as $contract) {
            if ($contract->current_status === 'anulado') {
                $errors[] = "Contrato #{$contract->number}: no se puede cambiar estado anulado";
                continue;
            }

            $previousStatus = $contract->current_status;
            $contract->update(['current_status' => $request->new_status]);

            ContractStatusHistory::create([
                'contract_id'     => $contract->id,
                'changed_by'      => $user->id,
                'previous_status' => $previousStatus,
                'new_status'      => $request->new_status,
                'observation'     => $request->observation ?? 'Cambio masivo',
                'changed_at'      => now(),
            ]);

            // Cerrar solicitudes pendientes
            $contract->statusRequests()
                ->where('status', 'pendiente')
                ->update([
                    'status'           => 'aprobado',
                    'resolved_by'      => $user->id,
                    'resolved_at'      => now(),
                    'rejection_reason' => null,
                ]);

            $updated++;
        }

        $message = "Se actualizaron {$updated} contratos.";
        if (!empty($errors)) {
            $message .= ' Errores: ' . implode(', ', $errors);
        }

        return back()->with('success', $message);
    }

    /**
     * Reasignación masiva (Admin / Secretario / Gerente).
     * Ruta: POST /contracts/bulk/reassign  → name: contracts.bulk-reassign
     */
    public function bulkReassign(Request $request)
    {
        $request->validate([
            'contract_ids' => 'required|array|min:1',
            'contract_ids.*' => 'exists:contracts,id',
            'advisor_id' => 'required|exists:users,id',
        ]);

        $user = $request->user();

        // Verificar que el nuevo asesor sea efectivamente Asesor
        $newAdvisor = User::findOrFail($request->advisor_id);
        if (!$newAdvisor->hasRole('Asesor')) {
            return back()->withErrors(['advisor_id' => 'El usuario seleccionado no tiene rol de Asesor.']);
        }

        $contracts = Contract::whereIn('id', $request->contract_ids)->get();

        if ($user->hasRole('Gerente')) {
            $contracts = $contracts->filter(function ($contract) use ($user) {
                return $contract->advisor && $contract->advisor->operation_id === $user->operation_id;
            });
        }

        $updated = 0;

        foreach ($contracts as $contract) {
            $previousAdvisorId = $contract->advisor_id;
            $contract->update(['advisor_id' => $request->advisor_id]);

            ContractStatusHistory::create([
                'contract_id'     => $contract->id,
                'changed_by'      => $user->id,
                'previous_status' => $contract->current_status,
                'new_status'      => $contract->current_status,
                'observation'     => "Reasignación masiva del asesor #{$previousAdvisorId} al asesor #{$request->advisor_id}.",
                'changed_at'      => now(),
            ]);

            $updated++;
        }

        return back()->with('success', "Se reasignaron {$updated} contratos al asesor {$newAdvisor->name}.");
    }
}
