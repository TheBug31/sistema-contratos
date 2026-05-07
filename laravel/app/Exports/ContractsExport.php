<?php

namespace App\Exports;

use App\Models\Contract;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ContractsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $user = auth()->user();
        $query = Contract::with('advisor.operation');

        if ($user->hasRole('Gerente')) {
            $query->whereHas('advisor', fn($q) => $q->where('operation_id', $user->operation_id));
        }

        if ($user->hasRole('Asesor')) {
            $query->where('advisor_id', $user->id);
        }

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', '%' . $search . '%')
                    ->orWhere('client_name', 'like', '%' . $search . '%')
                    ->orWhereHas('advisor', fn($a) => $a->where('name', 'like', '%' . $search . '%'));
            });
        }

        if (!empty($this->filters['status'])) {
            $query->where('current_status', $this->filters['status']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        return $query->latest();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Número',
            'Código',
            'Cliente',
            'Documento',
            'Teléfono',
            'Valor',
            'Estado',
            'Asesor',
            'Operación',
            'Fecha Creación',
            'Fecha Firma',
            'Fecha Vencimiento',
        ];
    }

    public function map($contract): array
    {
        $statusLabels = [
            'limpio' => 'Limpio',
            'lleno' => 'Lleno',
            'venta' => 'Venta',
            'anulado' => 'Anulado',
        ];

        return [
            $contract->id,
            $contract->number,
            $contract->code,
            $contract->client_name,
            $contract->client_document,
            $contract->client_phone,
            $contract->amount,
            $statusLabels[$contract->current_status] ?? $contract->current_status,
            $contract->advisor->name ?? 'Sin asesor',
            $contract->advisor->operation->name ?? 'N/A',
            $contract->created_at->format('d/m/Y'),
            $contract->signed_at?->format('d/m/Y'),
            $contract->expires_at?->format('d/m/Y'),
        ];
    }
}
