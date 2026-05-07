@extends('layouts.app')

@section('title', 'Contratos')

@section('content')

@php $isAsesor = auth()->user()->hasRole('Asesor'); @endphp

<div
    x-data="{
        {{-- Modal solicitud (Asesor) --}}
        solicitudModal: false,
        {{-- Modal cambio directo (Admin/Secretario/Gerente) --}}
        cambioModal: false,
        {{-- Modal reasignación --}}
        reasignarModal: false,
        {{-- Modal rechazar contrato (Asesor) --}}
        rejectModal: false,

        contractId: null,
        contractNumber: '',
        currentStatus: '',
        currentAdvisorId: null,

        {{-- Asesor --}}
        requestedStatus: '',
        reason: '',
        rejectionReason: '',

        {{-- Admin/Sec/Gerente --}}
        newStatus: '',
        observation: '',

        {{-- Reasignación --}}
        newAdvisorId: '',

        openSolicitud(id, number, status) {
            this.contractId      = id;
            this.contractNumber  = number;
            this.currentStatus   = status;
            this.requestedStatus = status;
            this.reason          = '';
            this.solicitudModal  = true;
        },

        openCambio(id, number, status) {
            this.contractId     = id;
            this.contractNumber = number;
            this.currentStatus  = status;
            this.newStatus      = status;
            this.observation    = '';
            this.cambioModal    = true;
        },

        openReasignar(id, number, advisorId) {
            this.contractId       = id;
            this.contractNumber   = number;
            this.currentAdvisorId = advisorId;
            this.newAdvisorId     = '';
            this.reasignarModal   = true;
        },

        openReject(id, number) {
            this.contractId      = id;
            this.contractNumber  = number;
            this.rejectionReason = '';
            this.rejectModal     = true;
        },

        close() {
            this.solicitudModal = false;
            this.cambioModal    = false;
            this.reasignarModal = false;
            this.rejectModal    = false;
        }
    }"
    class="space-y-6"
>

    {{-- ── Título + acción ── --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">
                @role('Asesor') Mis Contratos @else Gestión de Contratos @endrole
            </h2>
            <p class="text-slate-500 text-sm mt-1">
                @role('Asesor')
                    Gestiona tus contratos y solicita cambios de estado cuando lo necesites.
                @else
                    Administra y supervisa todos los contratos centralizados en un solo lugar.
                @endrole
            </p>
        </div>

        @role('Administrador|Secretario')
        <a href="{{ route('contracts.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg text-sm font-bold hover:opacity-90 transition-opacity shadow-sm">
            <span class="material-symbols-outlined" style="font-size:18px;">add</span>
            Nuevo contrato
        </a>
        @endrole
    </div>

    {{-- ── Alertas ── --}}
    @if (session('success'))
        <div class="flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm font-medium">
            <span class="material-symbols-outlined text-green-500" style="font-size:20px;">check_circle</span>
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="flex items-center gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm font-medium">
            <span class="material-symbols-outlined text-red-500" style="font-size:20px;">error</span>
            {{ $errors->first() }}
        </div>
    @endif

    {{-- ── Filtros ── --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <form method="GET" action="{{ route('contracts.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Búsqueda</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" style="font-size:18px;">search</span>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="{{ $isAsesor ? 'Nº contrato...' : 'Nº contrato o asesor...' }}"
                               class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                      focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                      placeholder-slate-400 transition-colors">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Estado</label>
                    <select name="status"
                            class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                   focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors">
                        <option value="">Todos los estados</option>
                        <option value="asignado" {{ request('status') === 'asignado' ? 'selected' : '' }}>Asignado</option>
                        <option value="limpio"  {{ request('status') === 'limpio'  ? 'selected' : '' }}>Limpio</option>
                        <option value="lleno"   {{ request('status') === 'lleno'   ? 'selected' : '' }}>Lleno</option>
                        <option value="venta"   {{ request('status') === 'venta'   ? 'selected' : '' }}>Venta</option>
                        <option value="anulado" {{ request('status') === 'anulado' ? 'selected' : '' }}>Anulado</option>
                    </select>
                </div>

                @if (!$isAsesor)
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Desde</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" style="font-size:18px;">calendar_today</span>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                               class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                      focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors">
                    </div>
                </div>
                @endif

                <div class="flex gap-2 {{ $isAsesor ? 'md:col-span-1' : '' }}">
                    @if (!$isAsesor)
                    <div class="flex-1">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Hasta</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                               class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                      focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors">
                    </div>
                    @endif
                    <div class="flex items-end gap-2 {{ $isAsesor ? 'flex-1' : '' }}">
                        <button type="submit"
                                class="{{ $isAsesor ? 'flex-1' : 'px-4' }} py-2.5 bg-primary text-white rounded-lg text-sm font-bold hover:opacity-90 transition-opacity">
                            @if($isAsesor) Filtrar @else <span class="material-symbols-outlined" style="font-size:18px;">search</span> @endif
                        </button>
                        @if (request()->hasAny(['search','status','date_from','date_to']))
                            <a href="{{ route('contracts.index') }}"
                               class="px-3 py-2.5 bg-slate-100 text-slate-500 rounded-lg hover:bg-slate-200 transition-colors"
                               title="Limpiar filtros">
                                <span class="material-symbols-outlined" style="font-size:18px;">close</span>
                            </a>
                        @endif
                    </div>
                </div>

            </div>
        </form>
    </div>

    {{-- ── Tabla ── --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Nº Contrato</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Cliente</th>
                        @if (!$isAsesor)
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Asesor</th>
                        @endif
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Estado</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Valor</th>
                        @if ($isAsesor)
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Solicitud</th>
                        @endif
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Fecha</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">

                    @forelse ($contracts as $contract)
                        @php
                            $statusConfig = [
                                'asignado' => ['label' => 'Asignado', 'class' => 'bg-purple-100 text-purple-700'],
                                'venta'   => ['label' => 'Venta',   'class' => 'bg-green-100  text-green-700'],
                                'limpio'  => ['label' => 'Limpio',  'class' => 'bg-blue-100   text-blue-700'],
                                'lleno'   => ['label' => 'Lleno',   'class' => 'bg-yellow-100 text-yellow-700'],
                                'anulado' => ['label' => 'Anulado', 'class' => 'bg-red-100    text-red-700'],
                            ];
                            $st = $statusConfig[$contract->current_status]
                                ?? ['label' => ucfirst($contract->current_status), 'class' => 'bg-slate-100 text-slate-600'];

                            $advisorName = $contract->advisor->name ?? 'Sin asesor';
                            $initials    = strtoupper(substr($advisorName, 0, 2));

                            $pendingRequest = $contract->statusRequests
                                ->where('status', 'pendiente')->first();

                            $lastRequest = $contract->statusRequests
                                ->whereIn('status', ['aprobado','rechazado'])
                                ->sortByDesc('resolved_at')->first();
                        @endphp

                        <tr class="hover:bg-slate-50/70 transition-colors">

                            {{-- Nº Contrato --}}
                            <td class="px-6 py-4">
                                <a href="{{ route('contracts.show', $contract) }}"
                                   class="font-mono font-bold text-primary hover:underline text-xs">
                                    {{ $contract->code ?? 'CL-'.$contract->number }}
                                </a>
                                {{-- Badge solicitud pendiente visible para gestores --}}
                                @if (!$isAsesor && $pendingRequest)
                                    <span class="ml-1.5 inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[9px] font-black bg-yellow-100 text-yellow-700">
                                        <span class="material-symbols-outlined" style="font-size:10px;">hourglass_top</span>
                                        Solicitud
                                    </span>
                                @endif
                            </td>

                            {{-- Cliente --}}
                            <td class="px-6 py-4">
                                @if($contract->client_name)
                                    <div>
                                        <p class="text-sm font-medium text-slate-700">{{ $contract->client_name }}</p>
                                        <p class="text-[10px] text-slate-400">{{ $contract->client_document }}</p>
                                    </div>
                                @else
                                    <span class="text-slate-300 text-xs">—</span>
                                @endif
                            </td>

                            {{-- Asesor (solo gestores) --}}
                            @if (!$isAsesor)
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="size-8 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                                        <span class="text-[10px] font-black text-primary">{{ $initials }}</span>
                                    </div>
                                    <span class="text-slate-700 font-medium">{{ $advisorName }}</span>
                                </div>
                            </td>
                            @endif

                            {{-- Estado actual --}}
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-black tracking-wide {{ $st['class'] }}">
                                    {{ strtoupper($st['label']) }}
                                </span>
                            </td>

                            {{-- Valor --}}
                            <td class="px-6 py-4 text-sm text-slate-600">
                                @if($contract->amount)
                                    ${{ number_format($contract->amount, 0, ',', '.') }}
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>

                            {{-- Columna Solicitud (solo Asesor) --}}
                            @if ($isAsesor)
                            <td class="px-6 py-4 text-center">
                                @if ($pendingRequest)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-black bg-yellow-100 text-yellow-700">
                                        <span class="material-symbols-outlined" style="font-size:13px;">hourglass_top</span>
                                        Pendiente → {{ strtoupper($pendingRequest->requested_status) }}
                                    </span>
                                @elseif ($lastRequest)
                                    @if ($lastRequest->status === 'aprobado')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-black bg-green-100 text-green-700">
                                            <span class="material-symbols-outlined" style="font-size:13px;">check_circle</span>
                                            Aprobada
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-black bg-red-100 text-red-700"
                                              title="{{ $lastRequest->rejection_reason }}">
                                            <span class="material-symbols-outlined" style="font-size:13px;">cancel</span>
                                            Rechazada
                                        </span>
                                    @endif
                                @else
                                    <span class="text-slate-300 text-xs">—</span>
                                @endif
                            </td>
                            @endif

                            {{-- Fecha --}}
                            <td class="px-6 py-4 text-xs text-slate-400">
                                {{ $contract->created_at->format('d M Y') }}
                            </td>

                            {{-- Acciones --}}
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">

                                    {{-- ASESOR: ver detalle + solicitar cambio --}}
                                    @if ($isAsesor)
                                        <a href="{{ route('contracts.show', $contract) }}"
                                           class="p-1.5 rounded-lg text-slate-400 hover:text-primary hover:bg-primary/5 transition-colors"
                                           title="Ver detalle">
                                            <span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
                                        </a>

                                        {{-- Botones aceptar/rechazar para contratos asignados --}}
                                        @if ($contract->current_status === 'asignado')
                                            <form method="POST" action="{{ route('contracts.accept', $contract) }}" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        class="p-1.5 rounded-lg text-green-500 hover:bg-green-50 transition-colors"
                                                        title="Aceptar contrato">
                                                    <span class="material-symbols-outlined" style="font-size:18px;">check_circle</span>
                                                </button>
                                            </form>
                                            <button
                                                @click="openReject({{ $contract->id }}, '{{ $contract->number }}')"
                                                class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 transition-colors"
                                                title="Rechazar contrato">
                                                <span class="material-symbols-outlined" style="font-size:18px;">cancel</span>
                                            </button>
                                        @elseif ($contract->current_status !== 'anulado' && !$pendingRequest)
                                            <button
                                                @click="openSolicitud({{ $contract->id }}, '{{ $contract->number }}', '{{ $contract->current_status }}')"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-primary hover:bg-primary/5 transition-colors"
                                                title="Solicitar cambio de estado">
                                                <span class="material-symbols-outlined" style="font-size:18px;">sync</span>
                                            </button>
                                        @elseif ($pendingRequest)
                                            <span class="p-1.5 text-yellow-400" title="Solicitud en revisión">
                                                <span class="material-symbols-outlined" style="font-size:18px;">hourglass_top</span>
                                            </span>
                                        @endif

                                    {{-- GESTORES: reasignar + cambio de estado --}}
                                    @else
                                        @role('Administrador|Secretario|Gerente')

                                            {{-- Reasignar --}}
                                            <button
                                                @click="openReasignar({{ $contract->id }}, '{{ $contract->number }}', {{ $contract->advisor_id ?? 'null' }})"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-primary hover:bg-primary/5 transition-colors"
                                                title="Reasignar asesor">
                                                <span class="material-symbols-outlined" style="font-size:18px;">person_edit</span>
                                            </button>

                                            {{-- Cambiar estado --}}
                                            @if ($contract->current_status !== 'anulado')
                                                <button
                                                    @click="openCambio({{ $contract->id }}, '{{ $contract->number }}', '{{ $contract->current_status }}')"
                                                    class="p-1.5 rounded-lg transition-colors
                                                        {{ $pendingRequest
                                                            ? 'text-yellow-500 hover:bg-yellow-50'
                                                            : 'text-slate-400 hover:text-primary hover:bg-primary/5' }}"
                                                    title="{{ $pendingRequest ? 'Hay solicitud pendiente — cambiar estado' : 'Cambiar estado' }}">
                                                    <span class="material-symbols-outlined" style="font-size:18px;">sync</span>
                                                </button>
                                            @else
                                                <span class="p-1.5 text-slate-200 cursor-not-allowed" title="Estado final">
                                                    <span class="material-symbols-outlined" style="font-size:18px;">block</span>
                                                </span>
                                            @endif

                                            {{-- Historial --}}
                                            <a href="{{ route('contracts.history', $contract) }}"
                                               class="p-1.5 rounded-lg text-slate-400 hover:text-primary hover:bg-primary/5 transition-colors"
                                               title="Ver historial">
                                                <span class="material-symbols-outlined" style="font-size:18px;">history</span>
                                            </a>

                                        @endrole
                                    @endif

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isAsesor ? 5 : 5 }}" class="py-16 text-center">
                                <span class="material-symbols-outlined text-6xl text-slate-300 block mb-3">inbox</span>
                                <p class="text-slate-500 font-semibold">No se encontraron contratos</p>
                                <p class="text-slate-400 text-xs mt-1">
                                    @if (request()->hasAny(['search','status','date_from','date_to']))
                                        Intenta con otros filtros o
                                        <a href="{{ route('contracts.index') }}" class="text-primary hover:underline">limpia la búsqueda</a>.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-xs text-slate-400 font-medium">
                Mostrando <strong class="text-slate-600">{{ $contracts->firstItem() ?? 0 }}</strong>
                a <strong class="text-slate-600">{{ $contracts->lastItem() ?? 0 }}</strong>
                de <strong class="text-slate-600">{{ $contracts->total() }}</strong> contratos
            </p>
            {{ $contracts->links() }}
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         MODAL 1 — ASESOR: Solicitar cambio
    ══════════════════════════════════════════ --}}
    <div x-show="solicitudModal"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="close()"></div>

        <div x-show="solicitudModal"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative z-10 w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden" @click.stop>

            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-black text-slate-800">Solicitar cambio de estado</h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Contrato <span class="font-mono font-bold text-primary" x-text="'#' + contractNumber"></span>
                    </p>
                </div>
                <button @click="close()" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 transition-colors">
                    <span class="material-symbols-outlined" style="font-size:20px;">close</span>
                </button>
            </div>

            <form method="POST" :action="'/contracts/' + contractId + '/requests'">
                @csrf
                <div class="px-6 py-5 space-y-5">

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Estado actual</label>
                        <div class="flex items-center gap-2 px-3 py-2.5 bg-slate-50 rounded-lg border border-slate-200">
                            <span class="material-symbols-outlined text-slate-400" style="font-size:16px;">radio_button_checked</span>
                            <span class="text-sm font-semibold text-slate-600 capitalize" x-text="currentStatus"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                            Estado que solicitas <span class="text-red-400">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach (['asignado' => ['Asignado','mark_email_unread','text-purple-500'], 'limpio' => ['Limpio','check_circle','text-blue-500'], 'lleno' => ['Lleno','contract','text-yellow-500'], 'venta' => ['Venta','payments','text-green-500'], 'anulado' => ['Anulado','cancel','text-red-500']] as $value => [$label, $icon, $color])
                            <label class="relative flex items-center gap-2.5 p-3 rounded-xl border-2 cursor-pointer transition-all"
                                   :class="requestedStatus === '{{ $value }}' ? 'border-primary bg-primary/5' : 'border-slate-200 hover:border-slate-300'">
                                <input type="radio" name="requested_status" value="{{ $value }}" x-model="requestedStatus" class="sr-only">
                                <span class="material-symbols-outlined {{ $color }}" style="font-size:20px;">{{ $icon }}</span>
                                <span class="text-sm font-semibold text-slate-700">{{ $label }}</span>
                                <span class="ml-auto size-4 rounded-full border-2 flex items-center justify-center transition-all"
                                      :class="requestedStatus === '{{ $value }}' ? 'border-primary bg-primary' : 'border-slate-300'">
                                    <span class="material-symbols-outlined text-white" style="font-size:11px;" x-show="requestedStatus === '{{ $value }}'">check</span>
                                </span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                            Motivo <span class="text-red-400">*</span>
                        </label>
                        <textarea name="reason" x-model="reason" rows="3"
                                  placeholder="Explica brevemente por qué necesitas este cambio..."
                                  class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                         focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                         placeholder-slate-400 resize-none transition-colors"></textarea>
                        <p class="text-[10px] text-slate-400 mt-1">Mínimo 5 caracteres. Tu gerente verá este motivo.</p>
                    </div>

                </div>
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="close()"
                            class="px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-200 rounded-lg transition-colors">
                        Cancelar
                    </button>
                    <button type="submit"
                            :disabled="requestedStatus === currentStatus || reason.length < 5"
                            class="inline-flex items-center gap-2 px-5 py-2 bg-primary text-white text-sm font-bold rounded-lg
                                   hover:opacity-90 transition-opacity disabled:opacity-40 disabled:cursor-not-allowed shadow-sm">
                        <span class="material-symbols-outlined" style="font-size:16px;">send</span>
                        Enviar solicitud
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         MODAL 2 — GESTORES: Cambio directo
    ══════════════════════════════════════════ --}}
    @role('Administrador|Secretario|Gerente')
    <div x-show="cambioModal"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="close()"></div>

        <div x-show="cambioModal"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative z-10 w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden" @click.stop>

            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-black text-slate-800">Cambiar estado</h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Contrato <span class="font-mono font-bold text-primary" x-text="'#' + contractNumber"></span>
                    </p>
                </div>
                <button @click="close()" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 transition-colors">
                    <span class="material-symbols-outlined" style="font-size:20px;">close</span>
                </button>
            </div>

            <form method="POST" :action="'/contracts/' + contractId + '/status'">
                @csrf
                @method('PUT')
                <div class="px-6 py-5 space-y-5">

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Estado actual</label>
                        <div class="flex items-center gap-2 px-3 py-2.5 bg-slate-50 rounded-lg border border-slate-200">
                            <span class="material-symbols-outlined text-slate-400" style="font-size:16px;">radio_button_checked</span>
                            <span class="text-sm font-semibold text-slate-600 capitalize" x-text="currentStatus"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                            Nuevo estado <span class="text-red-400">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach (['asignado' => ['Asignado','mark_email_unread','text-purple-500'], 'limpio' => ['Limpio','check_circle','text-blue-500'], 'lleno' => ['Lleno','contract','text-yellow-500'], 'venta' => ['Venta','payments','text-green-500'], 'anulado' => ['Anulado','cancel','text-red-500']] as $value => [$label, $icon, $color])
                            <label class="relative flex items-center gap-2.5 p-3 rounded-xl border-2 cursor-pointer transition-all"
                                   :class="newStatus === '{{ $value }}' ? 'border-primary bg-primary/5' : 'border-slate-200 hover:border-slate-300'">
                                <input type="radio" name="status" value="{{ $value }}" x-model="newStatus" class="sr-only">
                                <span class="material-symbols-outlined {{ $color }}" style="font-size:20px;">{{ $icon }}</span>
                                <span class="text-sm font-semibold text-slate-700">{{ $label }}</span>
                                <span class="ml-auto size-4 rounded-full border-2 flex items-center justify-center transition-all"
                                      :class="newStatus === '{{ $value }}' ? 'border-primary bg-primary' : 'border-slate-300'">
                                    <span class="material-symbols-outlined text-white" style="font-size:11px;" x-show="newStatus === '{{ $value }}'">check</span>
                                </span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Observación</label>
                        <textarea name="observation" x-model="observation" rows="3"
                                  placeholder="Motivo del cambio (opcional)..."
                                  class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                         focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                         placeholder-slate-400 resize-none transition-colors"></textarea>
                    </div>

                </div>
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="close()"
                            class="px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-200 rounded-lg transition-colors">
                        Cancelar
                    </button>
                    <button type="submit"
                            :disabled="newStatus === currentStatus"
                            class="inline-flex items-center gap-2 px-5 py-2 bg-primary text-white text-sm font-bold rounded-lg
                                   hover:opacity-90 transition-opacity disabled:opacity-40 disabled:cursor-not-allowed shadow-sm">
                        <span class="material-symbols-outlined" style="font-size:16px;">sync</span>
                        Actualizar estado
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endrole

    {{-- ══════════════════════════════════════════
         MODAL 3 — GESTORES: Reasignar asesor
    ══════════════════════════════════════════ --}}
    @role('Administrador|Secretario|Gerente')
    <div x-show="reasignarModal"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="close()"></div>

        <div x-show="reasignarModal"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative z-10 w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden" @click.stop>

            {{-- Header --}}
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-black text-slate-800">Reasignar asesor</h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Contrato <span class="font-mono font-bold text-primary" x-text="'#' + contractNumber"></span>
                    </p>
                </div>
                <button @click="close()" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 transition-colors">
                    <span class="material-symbols-outlined" style="font-size:20px;">close</span>
                </button>
            </div>

            <form method="POST" :action="'/contracts/' + contractId + '/reassign'">
                @csrf
                @method('PUT')

                <div class="px-6 py-5 space-y-4">

                    <p class="text-xs text-slate-500">
                        Selecciona el nuevo asesor al que se asignará este contrato.
                        El asesor actual quedará sin este contrato.
                    </p>

                    {{-- Lista de asesores --}}
                    <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                        @forelse ($advisors as $advisor)
                            @php
                                $words    = preg_split('/\s+/', trim($advisor->name));
                                $initials = strtoupper(substr($words[0] ?? '', 0, 1) . substr($words[1] ?? '', 0, 1)) ?: 'NA';
                                $colors   = ['bg-indigo-500','bg-purple-500','bg-teal-500','bg-pink-500','bg-orange-500'];
                                $bg       = $colors[$advisor->id % count($colors)];
                            @endphp
                            <label
                                class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all"
                                :class="newAdvisorId == '{{ $advisor->id }}'
                                    ? 'border-primary bg-primary/5'
                                    : 'border-slate-200 hover:border-slate-300'"
                            >
                                <input type="radio" name="advisor_id" value="{{ $advisor->id }}"
                                       x-model="newAdvisorId" class="sr-only">

                                <div class="size-9 rounded-full flex items-center justify-center text-xs font-black text-white shrink-0 {{ $bg }}">
                                    {{ $initials }}
                                </div>

                                <span class="text-sm font-semibold text-slate-700 flex-1">{{ $advisor->name }}</span>

                                {{-- Indicator --}}
                                <span class="size-4 rounded-full border-2 flex items-center justify-center transition-all shrink-0"
                                      :class="newAdvisorId == '{{ $advisor->id }}' ? 'border-primary bg-primary' : 'border-slate-300'">
                                    <span class="material-symbols-outlined text-white" style="font-size:11px;"
                                          x-show="newAdvisorId == '{{ $advisor->id }}'">check</span>
                                </span>
                            </label>
                        @empty
                            <p class="text-sm text-slate-400 text-center py-4">No hay asesores disponibles.</p>
                        @endforelse
                    </div>

                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="close()"
                            class="px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-200 rounded-lg transition-colors">
                        Cancelar
                    </button>
                    <button type="submit"
                            :disabled="!newAdvisorId || newAdvisorId == currentAdvisorId"
                            class="inline-flex items-center gap-2 px-5 py-2 bg-primary text-white text-sm font-bold rounded-lg
                                   hover:opacity-90 transition-opacity disabled:opacity-40 disabled:cursor-not-allowed shadow-sm">
                        <span class="material-symbols-outlined" style="font-size:16px;">person_edit</span>
                        Reasignar
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endrole

    {{-- ═════════════════════════════════════════
         MODAL 4 — ASESOR: Rechazar contrato asignado
         ═════════════════════════════════════════ --}}
    <div x-show="rejectModal"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="close()"></div>

        <div x-show="rejectModal"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative z-10 w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden" @click.stop>

            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-black text-slate-800">Rechazar contrato</h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Contrato <span class="font-mono font-bold text-primary" x-text="'#' + contractNumber"></span>
                    </p>
                </div>
                <button @click="close()" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 transition-colors">
                    <span class="material-symbols-outlined" style="font-size:20px;">close</span>
                </button>
            </div>

            <form method="POST" :action="'/contracts/' + contractId + '/reject'">
                @csrf
                <div class="px-6 py-5 space-y-5">

                    <div class="flex items-start gap-3 p-3 bg-red-50 rounded-lg border border-red-200">
                        <span class="material-symbols-outlined text-red-500 mt-0.5" style="font-size:20px;">warning</span>
                        <p class="text-xs text-red-700">
                            Al rechazar este contrato, volverá al pool general y se notificará a los administradores.
                            Debes proporcionar un motivo obligatorio.
                        </p>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                            Motivo del rechazo <span class="text-red-400">*</span>
                        </label>
                        <textarea name="rejection_reason" x-model="rejectionReason" rows="3"
                                  placeholder="Explica por qué rechazas este contrato..."
                                  class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                         focus:outline-none focus:ring-2 focus:ring-red-500/30 focus:border-red-500
                                         placeholder-slate-400 resize-none transition-colors"></textarea>
                        <p class="text-[10px] text-slate-400 mt-1">Mínimo 5 caracteres. Se notificará a administración.</p>
                    </div>

                </div>
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="close()"
                            class="px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-200 rounded-lg transition-colors">
                        Cancelar
                    </button>
                    <button type="submit"
                            :disabled="rejectionReason.length < 5"
                            class="inline-flex items-center gap-2 px-5 py-2 bg-red-500 text-white text-sm font-bold rounded-lg
                                   hover:bg-red-600 transition-colors disabled:opacity-40 disabled:cursor-not-allowed shadow-sm">
                        <span class="material-symbols-outlined" style="font-size:16px;">cancel</span>
                        Rechazar contrato
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>{{-- fin x-data --}}

{{-- ══════════════════════════════════════════
     OPERACIONES MASIVAS (Admin/Secretario/Gerente)
     ══════════════════════════════════════════ --}}
@role('Administrador|Secretario|Gerente')
<div class="mt-8 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100">
        <h3 class="text-base font-bold text-slate-800">Operaciones Masivas</h3>
        <p class="text-xs text-slate-400 mt-1">Selecciona múltiples contratos usando los checkboxes en la tabla (proximamente) o ingresa los IDs manualmente.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">

        {{-- Cambio de Estado Masivo --}}
        <div class="border border-slate-200 rounded-xl p-5">
            <h4 class="text-sm font-bold text-slate-700 mb-4">Cambio de Estado Masivo</h4>
            <form method="POST" action="{{ route('contracts.bulk-status') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">IDs de Contratos (separados por coma)</label>
                        <textarea name="contract_ids" rows="3" placeholder="Ej: 1, 2, 3, 4, 5"
                                  class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                         focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                         placeholder-slate-400 resize-none transition-colors"
                                  required></textarea>
                        <p class="text-[10px] text-slate-400 mt-1">IDs de los contratos a actualizar</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">Nuevo Estado</label>
                        <select name="new_status" required
                                class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                       focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors">
                            <option value="">Seleccionar estado...</option>
                            <option value="asignado">Asignado</option>
                            <option value="limpio">Limpio</option>
                            <option value="lleno">Lleno</option>
                            <option value="venta">Venta</option>
                            <option value="anulado">Anulado</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">Observación</label>
                        <textarea name="observation" rows="2" placeholder="Motivo del cambio masivo..."
                                  class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                         focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                         placeholder-slate-400 resize-none transition-colors"></textarea>
                    </div>

                    <button type="submit"
                            class="w-full px-4 py-2.5 bg-primary text-white text-sm font-bold rounded-lg hover:opacity-90 transition-opacity">
                        Aplicar Cambio de Estado
                    </button>
                </div>
            </form>
        </div>

        {{-- Reasignación Masiva --}}
        <div class="border border-slate-200 rounded-xl p-5">
            <h4 class="text-sm font-bold text-slate-700 mb-4">Reasignación Masiva</h4>
            <form method="POST" action="{{ route('contracts.bulk-reassign') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">IDs de Contratos (separados por coma)</label>
                        <textarea name="contract_ids" rows="3" placeholder="Ej: 1, 2, 3, 4, 5"
                                  class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                         focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary
                                         placeholder-slate-400 resize-none transition-colors"
                                  required></textarea>
                        <p class="text-[10px] text-slate-400 mt-1">IDs de los contratos a reasignar</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5">Nuevo Asesor</label>
                        <select name="advisor_id" required
                                class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm
                                       focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors">
                            <option value="">Seleccionar asesor...</option>
                            @foreach($advisors as $advisor)
                                <option value="{{ $advisor->id }}">{{ $advisor->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit"
                            class="w-full px-4 py-2.5 bg-primary text-white text-sm font-bold rounded-lg hover:opacity-90 transition-opacity">
                        Aplicar Reasignación
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endrole

@endsection