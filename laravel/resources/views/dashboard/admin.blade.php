@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')

<div class="space-y-8">

    {{-- ── 5 Métricas ── --}}
    <section class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6">

        {{-- Total contratos --}}
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <span class="text-slate-500 font-semibold text-xs uppercase tracking-wider">Total contratos</span>
                <span class="material-symbols-outlined text-primary bg-primary/10 p-2 rounded-lg">description</span>
            </div>
            <p class="text-3xl font-black text-slate-800">
                {{ number_format($metrics['total_contracts'] ?? 0) }}
            </p>
            <p class="text-xs text-slate-400 mt-1">contratos registrados</p>
        </div>

        {{-- Asignados --}}
        <div class="bg-white p-6 rounded-xl border border-purple-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <span class="text-slate-500 font-semibold text-xs uppercase tracking-wider">Asignados</span>
                <span class="material-symbols-outlined text-purple-600 bg-purple-50 p-2 rounded-lg">mark_email_unread</span>
            </div>
            <p class="text-3xl font-black text-purple-700">
                {{ number_format($metrics['asignados'] ?? 0) }}
            </p>
            <p class="text-xs text-slate-400 mt-1">pendientes aceptar</p>
        </div>

        {{-- Limpios --}}
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <span class="text-slate-500 font-semibold text-xs uppercase tracking-wider">Limpios</span>
                <span class="material-symbols-outlined text-blue-600 bg-blue-50 p-2 rounded-lg">check_circle</span>
            </div>
            <p class="text-3xl font-black text-slate-800">
                {{ number_format($metrics['limpios'] ?? 0) }}
            </p>
            <p class="text-xs text-slate-400 mt-1">sin gestión aún</p>
        </div>

        {{-- Llenos --}}
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <span class="text-slate-500 font-semibold text-xs uppercase tracking-wider">Llenos</span>
                <span class="material-symbols-outlined text-yellow-600 bg-yellow-50 p-2 rounded-lg">contract</span>
            </div>
            <p class="text-3xl font-black text-slate-800">
                {{ number_format($metrics['llenos'] ?? 0) }}
            </p>
            <p class="text-xs text-slate-400 mt-1">en proceso</p>
        </div>

        {{-- Ventas --}}
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <span class="text-slate-500 font-semibold text-xs uppercase tracking-wider">Ventas</span>
                <span class="material-symbols-outlined text-green-600 bg-green-50 p-2 rounded-lg">payments</span>
            </div>
            <p class="text-3xl font-black text-slate-800">
                {{ number_format($metrics['ventas'] ?? 0) }}
            </p>
            <p class="text-xs text-slate-400 mt-1">contratos cerrados</p>
        </div>

    </section>

    {{-- ── Gráficos Chart.js ── --}}
    <section class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Gráfico de Estados --}}
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <h3 class="text-base font-bold text-slate-800 mb-4">Distribución por Estados</h3>
            <canvas id="statusChart" height="200"></canvas>
        </div>

        {{-- Gráfico de Contratos por Mes --}}
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <h3 class="text-base font-bold text-slate-800 mb-4">Contratos por Mes ({{ now()->year }})</h3>
            <canvas id="monthlyChart" height="200"></canvas>
        </div>

    </section>

    {{-- ── Tabla + Estado + Top Asesores ── --}}
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-8">

        {{-- ── Últimos contratos (2/3) ── --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">

            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-800">Últimos contratos</h3>
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
                            <th class="px-5 py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Asesor</th>
                            <th class="px-5 py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Estado</th>
                            <th class="px-5 py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">

                        @forelse ($recentContracts as $contract)
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
                            @endphp

                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('contracts.show', $contract) }}"
                                       class="font-mono font-semibold text-xs text-primary hover:underline">
                                        {{ $contract->code ?? 'CL-'.$contract->number }}
                                    </a>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-2">
                                        <div class="size-7 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                                            <span class="text-[10px] font-black text-primary">
                                                {{ strtoupper(substr($contract->advisor->name ?? 'NA', 0, 2)) }}
                                            </span>
                                        </div>
                                        <span class="text-slate-700">{{ $contract->advisor->name ?? 'Sin asesor' }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-black tracking-wide {{ $st['class'] }}">
                                        {{ strtoupper($st['label']) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-xs text-slate-400">
                                    {{ $contract->created_at->format('d M Y') }}
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="4" class="py-12 text-center">
                                    <span class="material-symbols-outlined text-5xl text-slate-300 block mb-2">inbox</span>
                                    <span class="text-sm text-slate-400">No hay contratos registrados</span>
                                </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── Columna derecha (1/3): Estado + Top Asesores ── --}}
        <div class="space-y-6">

            {{-- Estados de contratos --}}
                @php
                    $total = ($metrics['asignados'] ?? 0)
                           + ($metrics['llenos']   ?? 0)
                           + ($metrics['limpios']  ?? 0)
                           + ($metrics['ventas']   ?? 0)
                           + ($metrics['anulados'] ?? 0);

                    $statuses = [
                        ['label' => 'Asignados','key' => 'asignados','color' => 'bg-purple-500', 'text' => 'text-purple-600'],
                        ['label' => 'Limpios',  'key' => 'limpios',  'color' => 'bg-blue-500',   'text' => 'text-blue-600'],
                        ['label' => 'Llenos',   'key' => 'llenos',   'color' => 'bg-yellow-400', 'text' => 'text-yellow-600'],
                        ['label' => 'Ventas',   'key' => 'ventas',   'color' => 'bg-green-500',  'text' => 'text-green-600'],
                        ['label' => 'Anulados', 'key' => 'anulados', 'color' => 'bg-red-500',    'text' => 'text-red-500'],
                    ];
                @endphp

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <h3 class="text-base font-bold text-slate-800 mb-5">Estados</h3>

                <div class="space-y-4">
                    @foreach ($statuses as $s)
                        @php
                            $val = $metrics[$s['key']] ?? 0;
                            $pct = $total > 0 ? round($val / $total * 100) : 0;
                        @endphp
                        <div>
                            <div class="flex justify-between text-xs mb-1.5">
                                <span class="font-semibold {{ $s['text'] }}">{{ $s['label'] }}</span>
                                <span class="font-black text-slate-700">{{ number_format($val) }}
                                    <span class="font-normal text-slate-400">({{ $pct }}%)</span>
                                </span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-1.5">
                                <div class="{{ $s['color'] }} h-1.5 rounded-full transition-all"
                                     style="width: {{ max($pct, $val > 0 ? 2 : 0) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Top 5 Asesores --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <div class="flex justify-between items-center mb-5">
                    <h3 class="text-base font-bold text-slate-800">Top Asesores</h3>
                    <a href="{{ route('users.index') }}"
                       class="text-xs font-bold text-primary hover:underline">Ver todos</a>
                </div>

                <div class="space-y-3">
                    @forelse ($top_advisors as $index => $advisor)
                        @php
                            $bgList  = ['bg-indigo-500','bg-purple-500','bg-pink-500','bg-teal-500','bg-orange-500'];
                            $bgClass = $bgList[$index] ?? 'bg-slate-400';
                            $isFirst = $index === 0;

                            $words    = preg_split('/\s+/', trim($advisor->name));
                            $initials = strtoupper(substr($words[0] ?? '', 0, 1) . substr($words[1] ?? '', 0, 1)) ?: 'NA';

                            $maxCount = $top_advisors->first()->contracts_count ?? 1;
                            $pct      = $maxCount > 0 ? round($advisor->contracts_count / $maxCount * 100) : 0;
                        @endphp

                        <div class="flex items-center gap-3">
                            {{-- Posición --}}
                            <span class="text-xs font-black w-4 text-center {{ $isFirst ? 'text-yellow-500' : 'text-slate-400' }}">
                                {{ $index + 1 }}
                            </span>

                            {{-- Avatar --}}
                            <div class="size-8 rounded-full flex items-center justify-center text-xs font-black text-white shrink-0 {{ $bgClass }}">
                                {{ $initials }}
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-800 truncate">{{ $advisor->name }}</p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <div class="flex-1 h-1 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="{{ $isFirst ? 'bg-primary' : 'bg-slate-300' }} h-full rounded-full"
                                             style="width: {{ max($pct, 6) }}%"></div>
                                    </div>
                                    <span class="text-[10px] text-slate-400 whitespace-nowrap">
                                        {{ $advisor->contracts_count }} ctr.
                                    </span>
                                </div>
                            </div>
                        </div>

                    @empty
                        <p class="text-sm text-slate-400 text-center py-4">Sin datos aún.</p>
                    @endforelse
                </div>
            </div>

        </div>
        {{-- fin columna derecha --}}

    </section>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Gráfico de Estados
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusChartData = @json($statusChartData ?? ['labels' => [], 'data' => [], 'colors' => []]);

    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusChartData.labels,
            datasets: [{
                data: statusChartData.data,
                backgroundColor: statusChartData.colors,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 15, usePointStyle: true, font: { size: 11 } }
                }
            }
        }
    });

    // Gráfico de Contratos por Mes
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyChartData = @json($monthlyChartData ?? ['labels' => [], 'data' => []]);

    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: monthlyChartData.labels,
            datasets: [{
                label: 'Contratos',
                data: monthlyChartData.data,
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
</script>
@endpush