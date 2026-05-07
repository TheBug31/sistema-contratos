<?php

namespace App\Exports;

use App\Models\Audit;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AuditsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Audit::with('user')->latest();

        if (!empty($this->filters['user_id'])) {
            $query->where('user_id', $this->filters['user_id']);
        }

        if (!empty($this->filters['event'])) {
            $query->where('event', $this->filters['event']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Usuario',
            'Evento',
            ' Modelo',
            'Modelo ID',
            'Datos Anterior',
            'Datos Nuevo',
            'Dirección IP',
            'Fecha',
        ];
    }

    public function map($audit): array
    {
        return [
            $audit->id,
            $audit->user->name ?? 'Sistema',
            $audit->event,
            $audit->auditable_type,
            $audit->auditable_id,
            json_encode($audit->old_values),
            json_encode($audit->new_values),
            $audit->ip_address,
            $audit->created_at->format('d/m/Y H:i:s'),
        ];
    }
}
