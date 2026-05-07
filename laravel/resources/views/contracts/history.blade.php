@extends('layouts.app')

@section('title', 'Historial de Contrato')

@section('content')

@php
    $statusConfig = [
        'venta'   => ['label' => 'Venta',   'class' => 'bg-green-100  text-green-700',  'bar' => 'bg-green-500',  'icon' => 'payments'],
        'limpio'  => ['label' => 'Limpio',  'class' => 'bg-blue-100   text-blue-700',   'bar' => 'bg-blue-500',   'icon' => 'check_circle'],
        'lleno'   => ['label' => 'Lleno',   'class' => 'bg-yellow-100 text-yellow-700', 'bar' => 'bg-yellow-400', 'icon' => 'contract'],
        'anulado' => ['label' => 'Anulado', 'class' => 'bg-red-100    text-red-700',    'bar' => 'bg-red-500',    'icon' => 'cancel'],
    ];

    $current = $statusConfig[$contract->current_status]
        ?? ['label' => ucfirst($contract->current_status), 'class' => 'bg-slate-100 text-slate-600', 'bar' => 'bg-slate-400', 'icon' => 'info'];
@endphp

<div class="max-w-2xl mx-auto space-y-6">

    {{-- ── Encabezado ── --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('contracts.index') }}"
           class="p-2 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
            <span class="material-symbols-outlined" style="font-size:20px;">arrow_back</span>
        </a>
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">
                Historial de contrato
            </h2>
            <p class="text-slate-500 text-sm mt-0.5 flex items-center gap-2 flex-wrap">
                <span class="font-mono font-bold text-primary">
                    {{ $contract->code ?? 'CL-'.$contract->number }}
                </span>
                <span class="text-slate-300">·</span>
                <span>{{ $contract->advisor->name ?? 'Sin asesor' }}</span>
                <span class="text-slate-300">·</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black {{ $current['class'] }}">
                    {{ strtoupper($current['label']) }}
                </span>
            </p>
        </div>
    </div>

    {{-- ── Timeline ── --}}
    @if ($histories->isEmpty())
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm py-16 text-center">
            <span class="material-symbols-outlined text-6xl text-slate-300 block mb-3">history</span>
            <p class="text-slate-500 font-semibold">Sin historial registrado</p>
            <p class="text-slate-400 text-xs mt-1">Los cambios de estado aparecerán aquí.</p>
        </div>
    @else
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">

            {{-- Header --}}
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-700">
                    {{ $histories->count() }} cambio{{ $histories->count() !== 1 ? 's' : '' }} registrado{{ $histories->count() !== 1 ? 's' : '' }}
                </h3>
                <span class="text-xs text-slate-400">Más reciente primero</span>
            </div>

            {{-- Timeline --}}
            <div class="px-6 py-4">
                <div class="relative">

                    {{-- Línea vertical --}}
                    <div class="absolute left-4 top-0 bottom-0 w-px bg-slate-100"></div>

                    <div class="space-y-6">
                        @foreach ($histories as $index => $history)
                            @php
                                $prev = $statusConfig[$history->previous_status]
                                    ?? ['label' => ucfirst($history->previous_status ?? '—'), 'class' => 'bg-slate-100 text-slate-500', 'icon' => 'radio_button_unchecked'];
                                $next = $statusConfig[$history->new_status]
                                    ?? ['label' => ucfirst($history->new_status), 'class' => 'bg-slate-100 text-slate-600', 'icon' => 'info'];

                                $changedBy = $history->changedBy->name ?? 'Sistema';
                                $isFirst   = $index === 0;
                            @endphp

                            <div class="relative flex gap-4">

                                {{-- Ícono del estado nuevo --}}
                                <div class="relative z-10 shrink-0">
                                    <div class="size-8 rounded-full flex items-center justify-center border-2 border-white shadow-sm
                                        @if($history->new_status === 'venta')   bg-green-500
                                        @elseif($history->new_status === 'lleno')   bg-yellow-400
                                        @elseif($history->new_status === 'limpio')  bg-blue-500
                                        @elseif($history->new_status === 'anulado') bg-red-500
                                        @else bg-slate-400
                                        @endif">
                                        <span class="material-symbols-outlined text-white" style="font-size:14px;">
                                            {{ $next['icon'] }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Contenido --}}
                                <div class="flex-1 min-w-0 pb-2">

                                    {{-- Cambio de estado --}}
                                    <div class="flex flex-wrap items-center gap-2 mb-1">
                                        @if ($history->previous_status)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black {{ $prev['class'] }}">
                                                {{ strtoupper($prev['label']) }}
                                            </span>
                                            <span class="material-symbols-outlined text-slate-300" style="font-size:14px;">arrow_forward</span>
                                        @endif
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black {{ $next['class'] }}">
                                            {{ strtoupper($next['label']) }}
                                        </span>
                                        @if ($isFirst)
                                            <span class="text-[10px] font-bold text-primary bg-primary/10 px-2 py-0.5 rounded-md">
                                                Actual
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Observación --}}
                                    @if ($history->observation)
                                        <p class="text-sm text-slate-600 mb-1.5 leading-relaxed">
                                            "{{ $history->observation }}"
                                        </p>
                                    @endif

                                    {{-- Meta: quién y cuándo --}}
                                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-400">
                                        <span class="flex items-center gap-1">
                                            <span class="material-symbols-outlined" style="font-size:13px;">person</span>
                                            {{ $changedBy }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <span class="material-symbols-outlined" style="font-size:13px;">schedule</span>
                                            {{ \Carbon\Carbon::parse($history->changed_at)->format('d M Y, H:i') }}
                                        </span>
                                        <span class="text-slate-300">
                                            {{ \Carbon\Carbon::parse($history->changed_at)->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    @endif

    {{-- ── Info del contrato ── --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-4">Información del contrato</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-xs text-slate-400 mb-0.5">Código</p>
                <p class="font-mono font-bold text-primary">{{ $contract->code ?? 'CL-'.$contract->number }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400 mb-0.5">Estado actual</p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-black {{ $current['class'] }}">
                    {{ strtoupper($current['label']) }}
                </span>
            </div>
            <div>
                <p class="text-xs text-slate-400 mb-0.5">Asesor</p>
                <p class="font-semibold text-slate-700">{{ $contract->advisor->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400 mb-0.5">Fecha de creación</p>
                <p class="text-slate-700">{{ $contract->created_at->format('d M Y') }}</p>
            </div>
            @if ($contract->delivered_at)
            <div>
                <p class="text-xs text-slate-400 mb-0.5">Fecha de entrega</p>
                <p class="text-slate-700">{{ \Carbon\Carbon::parse($contract->delivered_at)->format('d M Y') }}</p>
            </div>
            @endif
        </div>
    </div>

</div>

@endsection
