<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Contratos</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h1 { color: #1e518f; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #1e518f; color: white; padding: 8px; text-align: left; font-size: 11px; }
        td { padding: 6px; border-bottom: 1px solid #eee; font-size: 11px; }
        .status { padding: 2px 8px; border-radius: 3px; font-size: 10px; }
        .limpio { background: #dbeafe; color: #1e40af; }
        .lleno { background: #fef3c7; color: #92400e; }
        .venta { background: #d1fae5; color: #065f46; }
        .anulado { background: #fee2e2; color: #991b1b; }
        .header { display: flex; justify-content: space-between; align-items: center; }
        .date { color: #666; font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Contratos</h1>
        <p class="date">Generado: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nº</th>
                <th>Código</th>
                <th>Cliente</th>
                <th>Documento</th>
                <th>Valor</th>
                <th>Estado</th>
                <th>Asesor</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach($contracts as $contract)
                @php
                    $statusLabels = [
                        'limpio' => 'Limpio',
                        'lleno' => 'Lleno',
                        'venta' => 'Venta',
                        'anulado' => 'Anulado',
                    ];
                    $status = $statusLabels[$contract->current_status] ?? $contract->current_status;
                @endphp
                <tr>
                    <td>{{ $contract->number }}</td>
                    <td>{{ $contract->code ?? 'CL-'.$contract->number }}</td>
                    <td>{{ $contract->client_name ?? 'N/A' }}</td>
                    <td>{{ $contract->client_document ?? 'N/A' }}</td>
                    <td>${{ number_format($contract->amount, 0, ',', '.') }}</td>
                    <td><span class="status {{ $contract->current_status }}">{{ $status }}</span></td>
                    <td>{{ $contract->advisor->name ?? 'Sin asesor' }}</td>
                    <td>{{ $contract->created_at->format('d/m/Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 30px; font-size: 10px; color: #666;">
        <p>Total de contratos: {{ $contracts->count() }}</p>
    </div>
</body>
</html>
