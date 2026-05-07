<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Audit;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ContractsExport;
use App\Exports\AuditsExport;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Reporte de contratos en PDF
     */
    public function contractsPdf(Request $request)
    {
        $query = $this->getContractsQuery($request);

        $contracts = $query->with('advisor.operation')->latest()->get();

        $pdf = Pdf::loadView('reports.contracts-pdf', compact('contracts'));

        return $pdf->download('contratos_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Reporte de contratos en Excel
     */
    public function contractsExcel(Request $request)
    {
        $filters = $request->only(['status', 'date_from', 'date_to', 'search']);

        return Excel::download(new ContractsExport($filters), 'contratos_' . now()->format('Y-m-d') . '.xlsx');
    }

    /**
     * Reporte de auditoría en PDF
     */
    public function auditsPdf(Request $request)
    {
        $this->authorize('viewAny', \App\Models\Audit::class);

        $query = Audit::with('user')->latest();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $audits = $query->paginate(100);

        $pdf = Pdf::loadView('reports.audits-pdf', compact('audits'));

        return $pdf->download('auditoria_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Reporte de auditoría en Excel
     */
    public function auditsExcel(Request $request)
    {
        $this->authorize('viewAny', \App\Models\Audit::class);

        $filters = $request->only(['user_id', 'event', 'date_from', 'date_to']);

        return Excel::download(new AuditsExport($filters), 'auditoria_' . now()->format('Y-m-d') . '.xlsx');
    }

    /**
     * Dashboard metrics report in PDF
     */
    public function dashboardPdf(Request $request)
    {
        $user = $request->user();

        $data = match (true) {
            $user->hasRole('Administrador') => $this->getAdminData(),
            $user->hasRole('Secretario') => $this->getSecretarioData(),
            $user->hasRole('Gerente') => $this->getGerenteData($user),
            $user->hasRole('Asesor') => $this->getAsesorData($user),
            default => abort(403),
        };

        $pdf = Pdf::loadView('reports.dashboard-pdf', $data);

        return $pdf->download('dashboard_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Helper: Get contracts query based on user role and filters
     */
    private function getContractsQuery(Request $request)
    {
        $user = $request->user();
        $query = Contract::query();

        if ($user->hasRole('Gerente')) {
            $query->whereHas('advisor', function ($q) use ($user) {
                $q->where('operation_id', $user->operation_id);
            });
        }

        if ($user->hasRole('Asesor')) {
            $query->where('advisor_id', $user->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', '%' . $search . '%')
                    ->orWhere('client_name', 'like', '%' . $search . '%')
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

        return $query;
    }

    /**
     * Get admin dashboard data
     */
    private function getAdminData()
    {
        $contracts = Contract::with('advisor');

        return [
            'total' => (clone $contracts)->count(),
            'limpios' => (clone $contracts)->where('current_status', 'limpio')->count(),
            'llenos' => (clone $contracts)->where('current_status', 'lleno')->count(),
            'ventas' => (clone $contracts)->where('current_status', 'venta')->count(),
            'anulados' => (clone $contracts)->where('current_status', 'anulado')->count(),
            'recentContracts' => (clone $contracts)->latest()->take(10)->get(),
        ];
    }

    /**
     * Get secretario dashboard data
     */
    private function getSecretarioData()
    {
        return $this->getAdminData();
    }

    /**
     * Get gerente dashboard data
     */
    private function getGerenteData($user)
    {
        $contracts = Contract::whereHas('advisor', fn($q) => $q->where('operation_id', $user->operation_id));

        return [
            'total' => (clone $contracts)->count(),
            'limpios' => (clone $contracts)->where('current_status', 'limpio')->count(),
            'llenos' => (clone $contracts)->where('current_status', 'lleno')->count(),
            'ventas' => (clone $contracts)->where('current_status', 'venta')->count(),
            'anulados' => (clone $contracts)->where('current_status', 'anulado')->count(),
            'recentContracts' => (clone $contracts)->latest()->take(10)->get(),
        ];
    }

    /**
     * Get asesor dashboard data
     */
    private function getAsesorData($user)
    {
        $contracts = Contract::where('advisor_id', $user->id);

        return [
            'total' => (clone $contracts)->count(),
            'limpios' => (clone $contracts)->where('current_status', 'limpio')->count(),
            'llenos' => (clone $contracts)->where('current_status', 'lleno')->count(),
            'ventas' => (clone $contracts)->where('current_status', 'venta')->count(),
            'anulados' => (clone $contracts)->where('current_status', 'anulado')->count(),
            'recentContracts' => (clone $contracts)->latest()->take(10)->get(),
        ];
    }
}
