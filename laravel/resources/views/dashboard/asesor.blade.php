@extends('layouts.app')

@section('title', 'Dashboard Asesor')

@section('content')

@php
    $total      = $summary->sum();
    $ventas     = $summary->get('venta',     0);
    $llenos     = $summary->get('lleno',     0);
    $limpios    = $summary->get('limpio',    0);
    $anulados   = $summary->get('anulado',   0);
    $asignados  = $summary->get('asignado',  0);

    $ventasPct   = $total > 0 ? round($ventas   / $total * 100) : 0;
    $llenosPct   = $total > 0 ? round($llenos   / $total * 100) : 0;
    $limpiosPct  = $total > 0 ? round($limpios  / $total * 100) : 0;
    $asignadosPct = $total > 0 ? round($asignados / $total * 100) : 0;
@endphp

<div class="space-y-8">

    {{-- ── Bienvenida ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight">
                ¡Hola, {{ explode(' ', auth()->user()->name)[0] }}! 👋
            </h2>
            <p class="text-sm text-slate-500 mt-0.5">
                Aquí tienes un resumen de tu desempeño.
            </p>
        </div>
        <a href="{{ route('contracts.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg text-sm font-semibold hover:opacity-90 transition-opacity shadow-sm">
            <span class="material-symbols-outlined" style="font-size:18px;">description</span>
            Mis contratos
        </a>
    </div>

    {{-- ── 4 Métricas ── --}}
    <section class="grid grid-cols-1 md:grid-cols-4 gap-6">

        {{-- Total contratos --}}
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <span class="text-slate-500 font-semibold text-xs uppercase tracking-wider">Mis contratos</span>
                <span class="material-symbols-outlined text-primary bg-primary/10 p-2 rounded-lg">description</span>
            </div>
            <p class="text-3xl font-black text-slate-800">{{ number_format($total) }}</p>
            <div class="mt-3 flex justify-between text-xs text-slate-500">
                <span>Hoy: <strong class="text-slate-700">{{ $todayCount }}</strong></span>
                <span>Esta semana: <strong class="text-slate-700">{{ $weekCount }}</strong></span>
            </div>
        </div>

        {{-- Pendientes aceptar --}}
        <div class="bg-white p-6 rounded-xl border border-purple-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <span class="text-slate-500 font-semibold text-xs uppercase tracking-wider">Por aceptar</span>
                <span class="material-symbols-outlined text-purple-600 bg-purple-50 p-2 rounded-lg">mark_email_unread</span>
            </div>
            <p class="text-3xl font-black text-purple-700">{{ number_format($asignados) }}</p>
            <div class="mt-3 w-full bg-slate-100 h-1.5 rounded-full">
                <div class="bg-purple-500 h-full rounded-full"
                     style="width: {{ max($asignadosPct, $asignados > 0 ? 4 : 0) }}%"></div>
            </div>
            <p class="text-xs text-slate-400 mt-1.5">{{ $asignadosPct }}% del total de contratos</p>
        </div>

        {{-- Ventas --}}
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <span class="text-slate-500 font-semibold text-xs uppercase tracking-wider">Mis ventas</span>
                <span class="material-symbols-outlined text-green-600 bg-green-50 p-2 rounded-lg">payments</span>
            </div>
            <p class="text-3xl font-black text-slate-800">{{ number_format($ventas) }}</p>
            <div class="mt-3 w-full bg-slate-100 h-1.5 rounded-full">
                <div class="bg-green-500 h-full rounded-full"
                     style="width: {{ max($ventasPct, $ventas > 0 ? 4 : 0) }}%"></div>
            </div>
            <p class="text-xs text-slate-400 mt-1.5">{{ $ventasPct }}% del total de contratos</p>
        </div>

        {{-- Llenos --}}
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <span class="text-slate-500 font-semibold text-xs uppercase tracking-wider">Llenos</span>
                <span class="material-symbols-outlined text-yellow-600 bg-yellow-50 p-2 rounded-lg">contract</span>
            </div>
            <p class="text-3xl font-black text-slate-800">{{ number_format($llenos) }}</p>
            <div class="mt-3 w-full bg-slate-100 h-1.5 rounded-full">
                <div class="bg-yellow-400 h-full rounded-full"
                     style="width: {{ max($llenosPct, $llenos > 0 ? 4 : 0) }}%"></div>
            </div>
            <p class="text-xs text-slate-400 mt-1.5">{{ $llenosPct }}% del total de contratos</p>
        </div>

    </section>

    {{-- ── Tabla + Panel lateral ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- ── Mis contratos recientes (2/3) ── --}}
        <section class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">

            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-800">Mis contratos recientes</h3>
                <a href="{{ route('contracts.index') }}"
                   class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined" style="font-size:15px;">open_in_new</span>
                    Ver todos
                </a>
            </div>

            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-5 py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Contrato</th>
                            <th class="px-5 py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Estado</th>
                            <th class="px-5 py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Fecha</th>
                            <th class="px-5 py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">

                        @forelse ($recentContracts as $contract)
                            @php
                                $statusConfig = [
                                    'asignado' => ['label' => 'ASIGNADO', 'class' => 'bg-purple-100 text-purple-700'],
                                    'venta'   => ['label' => 'VENTA',   'class' => 'bg-green-100  text-green-700'],
                                    'limpio'  => ['label' => 'LIMPIO',  'class' => 'bg-blue-100   text-blue-700'],
                                    'lleno'   => ['label' => 'LLENO',   'class' => 'bg-yellow-100 text-yellow-700'],
                                    'anulado' => ['label' => 'ANULADO', 'class' => 'bg-red-100    text-red-700'],
                                ];
                                $st = $statusConfig[$contract->current_status]
                                    ?? ['label' => strtoupper($contract->current_status), 'class' => 'bg-slate-100 text-slate-600'];
                            @endphp

                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-5 py-3.5">
                                    <span class="font-mono font-semibold text-xs text-primary">
                                        {{ $contract->code ?? 'CL-'.$contract->number }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-black tracking-wide {{ $st['class'] }}">
                                        {{ $st['label'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-xs text-slate-400">
                                    {{ $contract->created_at->format('d M Y') }}
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('contracts.show', $contract) }}"
                                       class="p-1.5 rounded-lg hover:bg-primary/10 text-slate-400 hover:text-primary transition-colors inline-flex"
                                       title="Ver detalle">
                                        <span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
                                    </a>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="4" class="py-12 text-center">
                                    <span class="material-symbols-outlined text-5xl text-slate-300 block mb-2">inbox</span>
                                    <span class="text-sm text-slate-400">Aún no tienes contratos asignados.</span>
                                </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>

            @if ($recentContracts->hasPages())
                <div class="px-5 py-4 border-t border-slate-100">
                    {{ $recentContracts->links() }}
                </div>
            @endif

        </section>

        {{-- ── Panel lateral (1/3) ── --}}
        <div class="space-y-6">

            {{-- Resumen de estados --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <h3 class="text-base font-bold text-slate-800 mb-5">Mis estados</h3>

                @php
                    $stList = [
                        ['label' => 'Por aceptar','val' => $asignados, 'pct' => $asignadosPct, 'bar' => 'bg-purple-500', 'text' => 'text-purple-600'],
                        ['label' => 'Limpios',    'val' => $limpios,  'pct' => $limpiosPct,  'bar' => 'bg-blue-500',   'text' => 'text-blue-600'],
                        ['label' => 'Llenos',     'val' => $llenos,   'pct' => $llenosPct,   'bar' => 'bg-yellow-400', 'text' => 'text-yellow-600'],
                        ['label' => 'Ventas',     'val' => $ventas,   'pct' => $ventasPct,   'bar' => 'bg-green-500',  'text' => 'text-green-600'],
                        ['label' => 'Anulados',   'val' => $anulados, 'pct' => $total > 0 ? round($anulados/$total*100) : 0, 'bar' => 'bg-red-500', 'text' => 'text-red-500'],
                    ];
                @endphp

                <div class="space-y-4">
                    @foreach ($stList as $s)
                        <div>
                            <div class="flex justify-between text-xs mb-1.5">
                                <span class="font-semibold {{ $s['text'] }}">{{ $s['label'] }}</span>
                                <span class="font-black text-slate-700">
                                    {{ $s['val'] }}
                                    <span class="font-normal text-slate-400">({{ $s['pct'] }}%)</span>
                                </span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-1.5">
                                <div class="{{ $s['bar'] }} h-1.5 rounded-full"
                                     style="width: {{ max($s['pct'], $s['val'] > 0 ? 2 : 0) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Mini stats hoy / semana --}}
                <div class="mt-5 pt-4 border-t border-slate-100 grid grid-cols-2 gap-3">
                    <div class="text-center p-3 bg-slate-50 rounded-lg">
                        <p class="text-2xl font-black text-slate-800">{{ $todayCount }}</p>
                        <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wide mt-0.5">Hoy</p>
                    </div>
                    <div class="text-center p-3 bg-slate-50 rounded-lg">
                        <p class="text-2xl font-black text-slate-800">{{ $weekCount }}</p>
                        <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wide mt-0.5">Esta semana</p>
                    </div>
                </div>
            </div>

            {{-- CTA — ir a contratos --}}
            <div class="bg-primary rounded-xl shadow-lg p-5 relative overflow-hidden">
                <div class="relative z-10">
                    <h3 class="text-base font-bold text-white mb-1">¿Necesitas ver un contrato?</h3>
                    <p class="text-blue-200 text-xs mb-4 leading-relaxed">
                        Busca rápidamente por número de contrato.
                    </p>
                    <form method="GET" action="{{ route('contracts.index') }}" class="flex gap-2">
                        <input
                            type="text"
                            name="search"
                            placeholder="Nº de contrato..."
                            class="flex-1 rounded-lg border-none text-slate-800 text-sm py-2 px-3
                                   focus:ring-2 focus:ring-white/50 focus:outline-none placeholder-slate-400"
                        >
                        <button type="submit"
                                class="bg-white text-primary p-2 rounded-lg hover:bg-blue-50 transition-colors">
                            <span class="material-symbols-outlined" style="font-size:20px;">arrow_forward</span>
                        </button>
                    </form>
                </div>
                <div class="absolute -right-6 -bottom-6 opacity-10 pointer-events-none">
                    <span class="material-symbols-outlined" style="font-size:110px;">manage_search</span>
                </div>
            </div>

        </div>

    </div>
</div>

@endsection