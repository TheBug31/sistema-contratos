<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return match (true) {
            $user->hasRole('Administrador') => $this->admin($request),
            $user->hasRole('Secretario')    => $this->secretario($request),
            $user->hasRole('Gerente')       => $this->gerente($request),
            $user->hasRole('Asesor')        => $this->asesor($request),
            default                         => abort(403),
        };
    }

    // ───────────────── ADMIN ─────────────────

    private function admin(Request $request)
    {
        $contracts = Contract::with('advisor');

        $totalContracts = (clone $contracts)->count();

        $asignados = (clone $contracts)
            ->where('current_status', 'asignado')
            ->count();

        $limpios = (clone $contracts)
            ->where('current_status', 'limpio')
            ->count();

        $llenos = (clone $contracts)
            ->where('current_status', 'lleno')
            ->count();

        $ventas = (clone $contracts)
            ->where('current_status', 'venta')
            ->count();

        $anulados = (clone $contracts)
            ->where('current_status', 'anulado')
            ->count();

        $metrics = [
            'total_contracts' => $totalContracts,
            'asignados' => $asignados,
            'limpios' => $limpios,
            'llenos' => $llenos,
            'ventas' => $ventas,
            'anulados' => $anulados,
        ];

        $recentContracts = (clone $contracts)
            ->latest()
            ->take(10)
            ->get();

        $top_advisors = Contract::selectRaw('advisor_id, COUNT(*) as contracts_count')
            ->groupBy('advisor_id')
            ->with('advisor:id,name')
            ->orderByDesc('contracts_count')
            ->take(5)
            ->get()
            ->map(function ($row) {

                $name = $row->advisor->name ?? 'N/A';
                $initials = collect(explode(' ', $name))
                    ->map(fn($w) => $w[0])
                    ->join('');

                return (object)[
                    'name' => $name,
                    'initials' => $initials,
                    'contracts_count' => $row->contracts_count,
                    'performance_percentage' => rand(40, 100),
                    'bg_color' => 'bg-indigo-100',
                    'border_color' => 'border-indigo-200',
                    'text_color' => 'text-indigo-700',
                    'progress_color' => 'bg-indigo-500'
                ];
            });

        // Datos para Chart.js - Estados
        $statusChartData = [
            'labels' => ['Asignados', 'Limpios', 'Llenos', 'Ventas', 'Anulados'],
            'data' => [$asignados, $limpios, $llenos, $ventas, $anulados],
            'colors' => ['#8B5CF6', '#3B82F6', '#F59E0B', '#10B981', '#EF4444']
        ];

        // Datos para Chart.js - Contratos por mes (últimos 6 meses)
        $monthlyContracts = Contract::selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, COUNT(*) as total')
            ->whereYear('created_at', now()->year)
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $monthNames = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $monthlyData = array_fill(0, 12, 0);
        foreach ($monthlyContracts as $item) {
            $monthlyData[$item->month - 1] = $item->total;
        }

        $monthlyChartData = [
            'labels' => array_slice($monthNames, 0, now()->month),
            'data' => array_slice($monthlyData, 0, now()->month),
        ];

        return view('dashboard.admin', compact(
            'metrics',
            'recentContracts',
            'top_advisors',
            'statusChartData',
            'monthlyChartData'
        ));
    }
    // ───────────────── SECRETARIO ─────────────────

    private function secretario(Request $request)
    {
        $contracts = Contract::query();

        return $this->buildDashboard(
            $contracts,
            'dashboard.secretario'
        );
    }

    // ───────────────── GERENTE ─────────────────

    private function gerente(Request $request)
    {
        $user = $request->user();

        $contracts = Contract::whereHas(
            'advisor',
            fn($q) => $q->where('operation_id', $user->operation_id)
        );

        return $this->buildDashboard(
            $contracts,
            'dashboard.gerente',
            true
        );
    }

    // ───────────────── ASESOR ─────────────────

    private function asesor(Request $request)
    {
        $user = $request->user();

        $contracts = Contract::where('advisor_id', $user->id);

        return $this->buildDashboard(
            $contracts,
            'dashboard.asesor'
        );
    }

    // ───────────────── CONSTRUCTOR DE DASHBOARD ─────────────────

    private function buildDashboard(Builder $contracts, string $view, bool $withTopAdvisors = false)
    {
        $summary   = $this->summary($contracts);
        $today     = $this->todayCount($contracts);
        $week      = $this->weekCount($contracts);

        $recentContracts = (clone $contracts)
            ->with('advisor:id,name')
            ->latest()
            ->paginate(10);

        $data = [
            'summary'         => $summary,
            'todayCount'      => $today,
            'weekCount'       => $week,
            'recentContracts' => $recentContracts
        ];

        if ($withTopAdvisors) {
            $data['topAdvisors'] = $this->topAdvisors($contracts);
        }

        return view($view, $data);
    }

    // ───────────────── HELPERS ─────────────────

    private function summary(Builder $query)
    {
        return (clone $query)
            ->selectRaw('current_status, COUNT(*) as total')
            ->groupBy('current_status')
            ->pluck('total', 'current_status');
    }

    private function todayCount(Builder $query): int
    {
        return (clone $query)
            ->whereDate('created_at', now())
            ->count();
    }

    private function weekCount(Builder $query): int
    {
        return (clone $query)
            ->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])
            ->count();
    }

    private function topAdvisors(Builder $query)
    {
        return (clone $query)
            ->selectRaw('advisor_id, COUNT(*) as total')
            ->groupBy('advisor_id')
            ->orderByDesc('total')
            ->take(5)
            ->get()
            ->load('advisor:id,name')
            ->map(fn($row) => [
                'advisor_id'   => $row->advisor_id,
                'advisor_name' => $row->advisor->name ?? 'Sin asesor',
                'total'        => $row->total,
            ]);
    }
}
