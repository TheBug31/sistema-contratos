<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h1 { color: #1e518f; margin-bottom: 20px; }
        .metrics { display: flex; gap: 15px; margin-bottom: 30px; }
        .metric-card { flex: 1; padding: 15px; border: 1px solid #eee; border-radius: 8px; }
        .metric-label { font-size: 10px; color: #666; text-transform: uppercase; }
        .metric-value { font-size: 24px; font-weight: bold; color: #1e518f; margin: 5px 0; }
        .metric-desc { font-size: 10px; color: #999; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #1e518f; color: white; padding: 8px; text-align: left; font-size: 11px; }
        td { padding: 6px; border-bottom: 1px solid #eee; font-size: 11px; }
        .header { display: flex; justify-content: space-between; align-items: center; }
        .date { color: #666; font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Dashboard</h1>
        <p class="date">Generado: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="metrics">
        <div class="metric-card">
            <div class="metric-label">Total Contratos</div>
            <div class="metric-value">{{ number_format($total ?? 0) }}</div>
            <div class="metric-desc">contratos registrados</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Limpios</div>
            <div class="metric-value">{{ number_format($limpios ?? 0) }}</div>
            <div class="metric-desc">sin gestión</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Llenos</div>
            <div class="metric-value">{{ number_format($llenos ?? 0) }}</div>
            <div class="metric-desc">en proceso</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Ventas</div>
            <div class="metric-value">{{ number_format($ventas ?? 0) }}</div>
            <div class="metric-desc">cerrados</div>
        </div>
    </div>

    <h2 style="color: #1e518f; font-size: 16px; margin-top: 30px;">Últimos Contratos</h2>
    <table>
        <thead>
            <tr>
                <th>Nº</th>
                <th>Cliente</th>
                <th>Estado</th>
                <th>Asesor</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach(($recentContracts ?? []) as $contract)
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
                    <td>{{ $contract->client_name ?? 'N/A' }}</td>
                    <td>{{ $status }}</td>
                    <td>{{ $contract->advisor->name ?? 'Sin asesor' }}</td>
                    <td>{{ $contract->created_at->format('d/m/Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
