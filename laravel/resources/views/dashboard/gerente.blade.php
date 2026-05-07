@extends('layouts.app')

@section('title', 'Dashboard Gerente')

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

    {{-- ── Banner de Operación ── --}}
    <section class="relative h-32 rounded-xl overflow-hidden flex items-center px-8 shadow-lg bg-primary">
        <div class="absolute inset-0 bg-gradient-to-r from-black/20 via-transparent to-black/20 pointer-events-none"></div>
        <div class="relative z-10">
            <h2 class="text-3xl font-black text-white">
                {{ auth()->user()->operation->name ?? 'Mi Operación' }}
            </h2>
            <p class="text-white/70 font-medium mt-1">Resumen de desempeño en tiempo real</p>
        </div>
    </section>

    {{-- ── 5 Tarjetas de métricas ── --}}
    <section class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6">

        {{-- Total Contratos --}}
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-slate-500 font-semibold text-xs uppercase tracking-wider">Total Contratos</span>
                <span class="material-symbols-outlined text-primary bg-primary/10 p-2 rounded-lg">description</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-800">{{ number_format($total) }}</span>
                <span class="text-xs text-slate-400">acumulados</span>
            </div>
            <div class="mt-3 flex justify-between text-xs text-slate-500 mb-2">
                <span>Hoy: <strong class="text-slate-700">{{ $todayCount }}</strong></span>
                <span>Esta semana: <strong class="text-slate-700">{{ $weekCount }}</strong></span>
            </div>
            <div class="w-full bg-slate-100 h-1.5 rounded-full">
                <div class="bg-primary h-full rounded-full" style="width: {{ max($ventasPct, 4) }}%"></div>
            </div>
        </div>

        {{-- Asignados --}}
        <div class="bg-white p-6 rounded-xl border border-purple-200 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-slate-500 font-semibold text-xs uppercase tracking-wider">Asignados</span>
                <span class="material-symbols-outlined text-purple-600 bg-purple-50 p-2 rounded-lg">mark_email_unread</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black text-purple-700">{{ number_format($asignados) }}</span>
                <span class="text-xs font-bold text-purple-500">{{ $asignadosPct }}% del total</span>
            </div>
            <p class="text-xs text-slate-400 mt-3 mb-2">Pendientes aceptar</p>
            <div class="w-full bg-slate-100 h-1.5 rounded-full">
                <div class="bg-purple-500 h-full rounded-full" style="width: {{ max($asignadosPct, 4) }}%"></div>
            </div>
        </div>

        {{-- Ventas --}}
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-slate-500 font-semibold text-xs uppercase tracking-wider">Ventas</span>
                <span class="material-symbols-outlined text-green-600 bg-green-50 p-2 rounded-lg">payments</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-800">{{ number_format($ventas) }}</span>
                <span class="text-xs font-bold text-green-500">{{ $ventasPct }}% del total</span>
            </div>
            <p class="text-xs text-slate-400 mt-3 mb-2">Contratos cerrados como venta</p>
            <div class="w-full bg-slate-100 h-1.5 rounded-full">
                <div class="bg-green-500 h-full rounded-full" style="width: {{ max($ventasPct, 4) }}%"></div>
            </div>
        </div>

        {{-- Llenos --}}
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-slate-500 font-semibold text-xs uppercase tracking-wider">Llenos</span>
                <span class="material-symbols-outlined text-yellow-600 bg-yellow-50 p-2 rounded-lg">contract</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-800">{{ number_format($llenos) }}</span>
                <span class="text-xs font-bold text-yellow-500">{{ $llenosPct }}% del total</span>
            </div>
            <p class="text-xs text-slate-400 mt-3 mb-2">Contratos en estado lleno</p>
            <div class="w-full bg-slate-100 h-1.5 rounded-full">
                <div class="bg-yellow-400 h-full rounded-full" style="width: {{ max($llenosPct, 4) }}%"></div>
            </div>
        </div>

        {{-- Limpios --}}
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-slate-500 font-semibold text-xs uppercase tracking-wider">Limpios</span>
                <span class="material-symbols-outlined text-slate-500 bg-slate-100 p-2 rounded-lg">speed</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-800">{{ number_format($limpios) }}</span>
                <span class="text-xs text-slate-400">/ {{ number_format($anulados) }} anulados</span>
            </div>
            <div class="mt-3 flex items-center gap-2 mb-2">
                <span class="text-xs text-slate-500">Limpios {{ $limpiosPct }}%</span>
            </div>
            <div class="w-full bg-slate-100 h-1.5 rounded-full">
                <div class="bg-slate-400 h-full rounded-full" style="width: {{ max($limpiosPct, 4) }}%"></div>
            </div>
        </div>

    </section>

    {{-- ── Top Asesores + Tabla ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- ── Top 5 Asesores ── --}}
        <section class="lg:col-span-1 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-bold text-base text-slate-800">Top 5 Asesores</h3>
                <a href="{{ route('users.index') }}"
                   class="text-xs font-bold text-primary hover:underline">Ver todos</a>
            </div>

            <div class="flex-1 p-4 space-y-3">
                @forelse ($topAdvisors as $index => $advisor)
                    @php
                        $name     = $advisor['advisor_name'] ?? 'Sin nombre';
                        $words    = preg_split('/\s+/', trim($name));
                        $initials = strtoupper(
                            substr($words[0] ?? '', 0, 1) . substr($words[1] ?? '', 0, 1)
                        ) ?: 'NA';

                        $maxTotal = $topAdvisors->first()['total'] ?? 1;
                        $pct      = $maxTotal > 0 ? round($advisor['total'] / $maxTotal * 100) : 0;
                        $isFirst  = $index === 0;

                        $bgList  = ['bg-indigo-500','bg-purple-500','bg-pink-500','bg-teal-500','bg-orange-500'];
                        $bgClass = $bgList[$index] ?? 'bg-slate-400';
                    @endphp

                    <div class="flex items-center gap-3 p-3 rounded-lg transition-colors
                        {{ $isFirst
                            ? 'bg-primary/5 border border-primary/10'
                            : 'hover:bg-slate-50' }}">

                        {{-- Avatar con iniciales --}}
                        <div class="relative flex-shrink-0">
                            <div class="size-10 rounded-full flex items-center justify-center text-sm font-black text-white {{ $bgClass }}">
                                {{ $initials }}
                            </div>
                            <div class="absolute -top-1 -left-1 size-5 rounded-full flex items-center justify-center border-2 border-white
                                {{ $isFirst ? 'bg-yellow-400' : 'bg-slate-300' }}">
                                <span class="text-[9px] font-black {{ $isFirst ? 'text-white' : 'text-slate-600' }}">
                                    {{ $index + 1 }}
                                </span>
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold truncate text-slate-800">{{ $name }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full {{ $isFirst ? 'bg-primary' : 'bg-slate-300' }}"
                                         style="width: {{ max($pct, 6) }}%"></div>
                                </div>
                                <span class="text-[10px] text-slate-400 whitespace-nowrap">{{ $advisor['total'] }} ctr.</span>
                            </div>
                        </div>

                        <span class="text-xs font-black flex-shrink-0 {{ $isFirst ? 'text-primary' : 'text-slate-400' }}">
                            {{ $pct }}%
                        </span>
                    </div>

                @empty
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <span class="material-symbols-outlined text-5xl text-slate-300 mb-2">person_off</span>
                        <p class="text-sm text-slate-400">Sin datos de asesores aún.</p>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- ── Contratos Recientes ── --}}
        <section class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm flex flex-col overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center">
                <h3 class="font-bold text-base text-slate-800">Contratos Recientes</h3>
                <a href="{{ route('contracts.index') }}"
                   class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined" style="font-size:15px;">open_in_new</span>
                    Ver todos
                </a>
            </div>

            <div class="flex-1 overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-5 py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Nº Contrato</th>
                            <th class="px-5 py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Asesor</th>
                            <th class="px-5 py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Estado</th>
                            <th class="px-5 py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">

                        @forelse ($recentContracts as $contract)
                            @php
                                $statusConfig = [
                                    'asignado' => ['label' => 'ASIGNADO', 'class' => 'bg-purple-100 text-purple-700'],
                                    'venta'   => ['label' => 'VENTA',   'class' => 'bg-green-100  text-green-700'],
                                    'lleno'   => ['label' => 'LLENO',   'class' => 'bg-yellow-100 text-yellow-700'],
                                    'limpio'  => ['label' => 'LIMPIO',  'class' => 'bg-blue-100   text-blue-700'],
                                    'anulado' => ['label' => 'ANULADO', 'class' => 'bg-red-100    text-red-700'],
                                ];
                                $st = $statusConfig[$contract->current_status]
                                    ?? ['label' => strtoupper($contract->current_status), 'class' => 'bg-slate-100 text-slate-600'];
                            @endphp

                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('contracts.show', $contract) }}"
                                       class="text-xs font-mono font-semibold text-primary hover:underline">
                                        {{ $contract->code ?? 'CL-'.$contract->number }}
                                    </a>
                                </td>
                                <td class="px-5 py-3.5 text-sm text-slate-700">
                                    {{ $contract->advisor->name ?? '—' }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-black tracking-wide {{ $st['class'] }}">
                                        {{ $st['label'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-xs text-slate-400">
                                    {{ $contract->created_at->diffForHumans() }}
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-12 text-center">
                                    <span class="material-symbols-outlined text-5xl text-slate-300 block mb-2">inbox</span>
                                    <span class="text-sm text-slate-400">No hay contratos registrados aún.</span>
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

    </div>
</div>

@endsection