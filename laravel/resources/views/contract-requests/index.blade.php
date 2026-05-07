@extends('layouts.app')

@section('title', 'Solicitudes de cambio')

@section('content')

<div
    x-data="{
        rejectModal: false,
        requestId: null,
        contractNumber: '',
        requestedStatus: '',
        rejectionReason: '',
        openReject(id, number, status) {
            this.requestId       = id;
            this.contractNumber  = number;
            this.requestedStatus = status;
            this.rejectionReason = '';
            this.rejectModal     = true;
        },
        close() { this.rejectModal = false; }
    }"
    class="space-y-6"
>

    {{-- ── Título ── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Solicitudes de cambio de estado</h2>
            <p class="text-slate-500 text-sm mt-1">Revisa y gestiona las solicitudes enviadas por los asesores.</p>
        </div>
        @if ($pendingCount > 0)
            <span class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-100 text-yellow-800 rounded-xl text-sm font-bold">
                <span class="material-symbols-outlined" style="font-size:18px;">hourglass_top</span>
                {{ $pendingCount }} pendiente{{ $pendingCount > 1 ? 's' : '' }}
            </span>
        @endif
    </div>

    {{-- Alertas --}}
    @if (session('success'))
        <div class="flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm font-medium">
            <span class="material-symbols-outlined text-green-500" style="font-size:20px;">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    {{-- ── Filtro de estado ── --}}
    <div class="flex gap-2">
        @foreach (['pendiente' => 'Pendientes', 'aprobado' => 'Aprobadas', 'rechazado' => 'Rechazadas', '' => 'Todas'] as $val => $label)
            <a href="{{ route('contract-requests.index', ['status' => $val]) }}"
               class="px-4 py-2 rounded-lg text-xs font-bold transition-colors
                   {{ request('status', 'pendiente') === $val
                       ? 'bg-primary text-white shadow-sm'
                       : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- ── Tabla de solicitudes ── --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Contrato</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Asesor</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Solicita cambio</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Motivo</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Estado</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Fecha</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">

                    @forelse ($requests as $req)
                        @php
                            $fromConfig = [
                                'venta'   => 'bg-green-100  text-green-700',
                                'limpio'  => 'bg-blue-100   text-blue-700',
                                'lleno'   => 'bg-yellow-100 text-yellow-700',
                                'anulado' => 'bg-red-100    text-red-700',
                            ];
                            $currentClass  = $fromConfig[$req->contract->current_status]  ?? 'bg-slate-100 text-slate-600';
                            $requestedClass = $fromConfig[$req->requested_status] ?? 'bg-slate-100 text-slate-600';
                        @endphp

                        <tr class="hover:bg-slate-50/70 transition-colors {{ $req->status === 'pendiente' ? 'bg-yellow-50/30' : '' }}">

                            {{-- Contrato --}}
                            <td class="px-6 py-4">
                                <a href="{{ route('contracts.show', $req->contract) }}"
                                   class="font-mono font-bold text-primary text-xs hover:underline">
                                    {{ $req->contract->code ?? 'CL-'.$req->contract->number }}
                                </a>
                                <p class="text-[10px] text-slate-400 mt-0.5">
                                    Actual:
                                    <span class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-black {{ $currentClass }}">
                                        {{ strtoupper($req->contract->current_status) }}
                                    </span>
                                </p>
                            </td>

                            {{-- Asesor --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="size-7 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                                        <span class="text-[10px] font-black text-primary">
                                            {{ strtoupper(substr($req->requester->name ?? 'NA', 0, 2)) }}
                                        </span>
                                    </div>
                                    <span class="font-medium text-slate-700">{{ $req->requester->name ?? '—' }}</span>
                                </div>
                            </td>

                            {{-- Cambio solicitado --}}
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="inline-flex px-2 py-0.5 rounded-md text-[10px] font-black {{ $currentClass }}">
                                        {{ strtoupper($req->contract->current_status) }}
                                    </span>
                                    <span class="material-symbols-outlined text-slate-400" style="font-size:14px;">arrow_forward</span>
                                    <span class="inline-flex px-2 py-0.5 rounded-md text-[10px] font-black {{ $requestedClass }}">
                                        {{ strtoupper($req->requested_status) }}
                                    </span>
                                </div>
                            </td>

                            {{-- Motivo --}}
                            <td class="px-6 py-4">
                                <p class="text-xs text-slate-600 max-w-[200px] truncate" title="{{ $req->reason }}">
                                    {{ $req->reason }}
                                </p>
                                @if ($req->rejection_reason)
                                    <p class="text-[10px] text-red-500 mt-0.5 max-w-[200px] truncate"
                                       title="{{ $req->rejection_reason }}">
                                        Rechazo: {{ $req->rejection_reason }}
                                    </p>
                                @endif
                            </td>

                            {{-- Estado de la solicitud --}}
                            <td class="px-6 py-4 text-center">
                                @if ($req->status === 'pendiente')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-black bg-yellow-100 text-yellow-700">
                                        <span class="material-symbols-outlined" style="font-size:12px;">hourglass_top</span>
                                        Pendiente
                                    </span>
                                @elseif ($req->status === 'aprobado')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-black bg-green-100 text-green-700">
                                        <span class="material-symbols-outlined" style="font-size:12px;">check_circle</span>
                                        Aprobada
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-black bg-red-100 text-red-700">
                                        <span class="material-symbols-outlined" style="font-size:12px;">cancel</span>
                                        Rechazada
                                    </span>
                                @endif
                            </td>

                            {{-- Fecha --}}
                            <td class="px-6 py-4 text-xs text-slate-400">
                                {{ $req->created_at->diffForHumans() }}
                                @if ($req->resolved_at)
                                    <br>
                                    <span class="text-slate-300">Resuelta {{ $req->resolved_at->diffForHumans() }}</span>
                                @endif
                            </td>

                            {{-- Acciones --}}
                            <td class="px-6 py-4 text-right">
                                @if ($req->status === 'pendiente')
                                    <div class="flex items-center justify-end gap-2">

                                        {{-- Aprobar --}}
                                        <form method="POST"
                                              action="{{ route('contract-requests.approve', $req) }}"
                                              onsubmit="return confirm('¿Aprobar esta solicitud? El estado del contrato cambiará inmediatamente.')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold
                                                           bg-green-100 text-green-700 hover:bg-green-200 transition-colors">
                                                <span class="material-symbols-outlined" style="font-size:14px;">check</span>
                                                Aprobar
                                            </button>
                                        </form>

                                        {{-- Rechazar --}}
                                        <button
                                            @click="openReject({{ $req->id }}, '{{ $req->contract->number }}', '{{ $req->requested_status }}')"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold
                                                   bg-red-100 text-red-700 hover:bg-red-200 transition-colors">
                                            <span class="material-symbols-outlined" style="font-size:14px;">close</span>
                                            Rechazar
                                        </button>

                                    </div>
                                @else
                                    <span class="text-xs text-slate-300">—</span>
                                @endif
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center">
                                <span class="material-symbols-outlined text-6xl text-slate-300 block mb-3">task_alt</span>
                                <p class="text-slate-500 font-semibold">No hay solicitudes</p>
                                <p class="text-slate-400 text-xs mt-1">Todo al día por aquí.</p>
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-xs text-slate-400 font-medium">
                Mostrando <strong class="text-slate-600">{{ $requests->firstItem() ?? 0 }}</strong>
                a <strong class="text-slate-600">{{ $requests->lastItem() ?? 0 }}</strong>
                de <strong class="text-slate-600">{{ $requests->total() }}</strong> solicitudes
            </p>
            {{ $requests->links() }}
        </div>
    </div>

    {{-- ════════════════════════════
         MODAL — Rechazar solicitud
    ════════════════════════════ --}}
    <div
        x-show="rejectModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display:none;"
    >
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="close()"></div>

        <div
            x-show="rejectModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative z-10 w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden"
            @click.stop
        >
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-black text-slate-800">Rechazar solicitud</h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Contrato <span class="font-mono font-bold text-primary" x-text="'#' + contractNumber"></span>
                        — solicitó
                        <span class="font-bold capitalize" x-text="requestedStatus"></span>
                    </p>
                </div>
                <button @click="close()"
                        class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 transition-colors">
                    <span class="material-symbols-outlined" style="font-size:20px;">close</span>
                </button>
            </div>

            <form method="POST" :action="`/contract-requests/${requestId}/reject`">
                @csrf
                @method('PATCH')

                <div class="px-6 py-5">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                        Motivo del rechazo <span class="text-red-400">*</span>
                    </label>
                    <textarea
                        name="rejection_reason"
                        x-model="rejectionReason"
                        rows="4"
                        placeholder="Explica al asesor por qué se rechaza la solicitud..."
                        class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                               focus:outline-none focus:ring-2 focus:ring-red-300 focus:border-red-400
                               placeholder-slate-400 resize-none transition-colors"
                    ></textarea>
                    <p class="text-[10px] text-slate-400 mt-1">El asesor verá este motivo en su listado.</p>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="close()"
                            class="px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-200 rounded-lg transition-colors">
                        Cancelar
                    </button>
                    <button type="submit"
                            :disabled="rejectionReason.length < 5"
                            class="inline-flex items-center gap-2 px-5 py-2 bg-red-600 text-white text-sm font-bold rounded-lg
                                   hover:opacity-90 transition-opacity disabled:opacity-40 disabled:cursor-not-allowed shadow-sm">
                        <span class="material-symbols-outlined" style="font-size:16px;">cancel</span>
                        Confirmar rechazo
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection