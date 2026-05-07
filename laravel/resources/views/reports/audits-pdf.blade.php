<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Auditoría</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        h1 { color: #1e518f; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #1e518f; color: white; padding: 8px; text-align: left; font-size: 10px; }
        td { padding: 6px; border-bottom: 1px solid #eee; font-size: 10px; }
        .header { display: flex; justify-content: space-between; align-items: center; }
        .date { color: #666; font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Auditoría</h1>
        <p class="date">Generado: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Evento</th>
                <th>Modelo</th>
                <th>ID Modelo</th>
                <th>Datos Anterior</th>
                <th>Datos Nuevo</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach($audits as $audit)
                <tr>
                    <td>{{ $audit->id }}</td>
                    <td>{{ $audit->user->name ?? 'Sistema' }}</td>
                    <td>{{ $audit->event }}</td>
                    <td>{{ class_basename($audit->auditable_type) }}</td>
                    <td>{{ $audit->auditable_id }}</td>
                    <td>{{ json_encode($audit->old_values) }}</td>
                    <td>{{ json_encode($audit->new_values) }}</td>
                    <td>{{ $audit->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 30px; font-size: 10px; color: #666;">
        <p>Total de registros: {{ $audits->count() }}</p>
    </div>
</body>
</html>
