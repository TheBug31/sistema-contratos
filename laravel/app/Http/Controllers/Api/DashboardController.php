<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $contracts = Contract::query();

        if ($user->hasRole('Gerente')) {
            $contracts->whereHas('advisor', function ($q) use ($user) {
                $q->where('operation_id', $user->operation_id);
            });
        }

        if ($user->hasRole('Asesor')) {
            $contracts->where('advisor_id', $user->id);
        }

        // Clone query for summary to avoid selectRaw modifying the original
        $summaryQuery = clone $contracts;
        $summary = $summaryQuery
            ->selectRaw('current_status, COUNT(*) as total')
            ->groupBy('current_status')
            ->pluck('total', 'current_status');

        // compute today and week counts
        $todayCount = (clone $contracts)->whereDate('created_at', now()->toDateString())->count();
        $weekCount = (clone $contracts)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();

        // top advisors by contract count (limit 5)
        $topAdvisors = (clone $contracts)
            ->selectRaw('advisor_id, COUNT(*) as total')
            ->groupBy('advisor_id')
            ->orderByDesc('total')
            ->with('advisor:id,name')
            ->take(5)
            ->get()
            ->map(fn($row) => [
                'advisor_id' => $row->advisor_id,
                'advisor_name' => $row->advisor->name ?? null,
                'total' => $row->total
            ]);

        // sales by operation (contracts with status venta)
        $salesByOp = (clone $contracts)
            ->where('current_status', 'venta')
            ->selectRaw('users.operation_id, COUNT(*) as total')
            ->join('users', 'contracts.advisor_id', '=', 'users.id')
            ->groupBy('users.operation_id')
            ->pluck('total', 'operation_id');

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $contracts->count(),
                'limpio' => $summary['limpio'] ?? 0,
                'lleno' => $summary['lleno'] ?? 0,
                'venta' => $summary['venta'] ?? 0,
                'anulado' => $summary['anulado'] ?? 0,
                'asignado' => $summary['asignado'] ?? 0,
                'today' => $todayCount,
                'week' => $weekCount,
                'top_advisors' => $topAdvisors,
                'sales_by_operation' => $salesByOp,
                'recent' => $contracts->latest()->limit(10)->get()
            ]
        ]);
    }

    public function search(Request $request)
    {
        $user = $request->user();
        $query = Contract::with('advisor');

        // Filtrado por rol
        if ($user->hasRole('Gerente')) {
            $query->whereHas(
                'advisor',
                fn($q) =>
                $q->where('operation_id', $user->operation_id)
            );
        }

        if ($user->hasRole('Asesor')) {
            $query->where('advisor_id', $user->id);
        }

        // Filtro por contrato o nombre de asesor
        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('number', $request->q)
                    ->orWhereHas(
                        'advisor',
                        fn($a) =>
                        $a->where('name', 'like', '%' . $request->q . '%')
                    );
            });
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderBy('number', 'desc')->paginate(20)
        ]);
    }
}
