<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Contrato #{{ $contract->number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #1e518f; padding-bottom: 20px; }
        .header h1 { color: #1e518f; margin: 0; font-size: 24px; }
        .header p { color: #666; margin: 5px 0; }
        .section { margin-bottom: 25px; }
        .section-title { background: #1e518f; color: white; padding: 8px 15px; font-weight: bold; margin-bottom: 15px; }
        .field { margin-bottom: 10px; }
        .field-label { font-weight: bold; color: #333; display: inline-block; width: 150px; }
        .field-value { color: #555; }
        .status { display: inline-block; padding: 4px 12px; border-radius: 4px; font-weight: bold; font-size: 11px; }
        .status-limpio { background: #dbeafe; color: #1e40af; }
        .status-lleno { background: #fef3c7; color: #92400e; }
        .status-venta { background: #d1fae5; color: #065f46; }
        .status-anulado { background: #fee2e2; color: #991b1b; }
        .footer { margin-top: 50px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #eee; padding-top: 20px; }
        .info-box { background: #f9fafb; border: 1px solid #e5e7eb; padding: 15px; border-radius: 8px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>SISTEMA DE CONTRATOS</h1>
        <p>Contrato #{{ $contract->code ?? 'CL-'.$contract->number }}</p>
    </div>

    <div class="section">
        <div class="section-title">INFORMACIÓN DEL CONTRATO</div>
        <div class="info-box">
            <div class="field">
                <span class="field-label">Número:</span>
                <span class="field-value">{{ $contract->number }}</span>
            </div>
            <div class="field">
                <span class="field-label">Código:</span>
                <span class="field-value">{{ $contract->code ?? 'CL-'.$contract->number }}</span>
            </div>
            <div class="field">
                <span class="field-label">Estado:</span>
                <span class="status status-{{ $contract->current_status }}">
                    @switch($contract->current_status)
                        @case('limpio') LIMPIO @break
                        @case('lleno') LLENO @break
                        @case('venta') VENTA @break
                        @case('anulado') ANULADO @break
                    @endswitch
                </span>
            </div>
            <div class="field">
                <span class="field-label">Fecha Creación:</span>
                <span class="field-value">{{ $contract->created_at->format('d/m/Y H:i') }}</span>
            </div>
            @if($contract->signed_at)
            <div class="field">
                <span class="field-label">Fecha Firma:</span>
                <span class="field-value">{{ $contract->signed_at->format('d/m/Y') }}</span>
            </div>
            @endif
            @if($contract->expires_at)
            <div class="field">
                <span class="field-label">Fecha Vencimiento:</span>
                <span class="field-value">{{ $contract->expires_at->format('d/m/Y') }}</span>
            </div>
            @endif
            @if($contract->delivered_at)
            <div class="field">
                <span class="field-label">Fecha Entrega:</span>
                <span class="field-value">{{ $contract->delivered_at->format('d/m/Y') }}</span>
            </div>
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">DATOS DEL CLIENTE</div>
        <div class="info-box">
            @if($contract->client_name)
                <div class="field">
                    <span class="field-label">Nombre:</span>
                    <span class="field-value">{{ $contract->client_name }}</span>
                </div>
            @endif
            @if($contract->client_document)
                <div class="field">
                    <span class="field-label">Documento:</span>
                    <span class="field-value">{{ $contract->client_document }}</span>
                </div>
            @endif
            @if($contract->client_phone)
                <div class="field">
                    <span class="field-label">Teléfono:</span>
                    <span class="field-value">{{ $contract->client_phone }}</span>
                </div>
            @endif
            @if(!$contract->client_name && !$contract->client_document)
                <p style="color: #999; font-style: italic;">No hay información del cliente registrada.</p>
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">DATOS FINANCIEROS</div>
        <div class="info-box">
            @if($contract->amount)
                <div class="field">
                    <span class="field-label">Valor del Contrato:</span>
                    <span class="field-value" style="font-size: 16px; font-weight: bold; color: #1e518f;">
                        ${{ number_format($contract->amount, 0, ',', '.') }}
                    </span>
                </div>
            @else
                <p style="color: #999; font-style: italic;">No hay valor registrado.</p>
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">ASESOR ASIGNADO</div>
        <div class="info-box">
            <div class="field">
                <span class="field-label">Nombre:</span>
                <span class="field-value">{{ $contract->advisor->name ?? 'Sin asesor' }}</span>
            </div>
            @if($contract->advisor && $contract->advisor->operation)
                <div class="field">
                    <span class="field-label">Operación:</span>
                    <span class="field-value">{{ $contract->advisor->operation->name }}</span>
                </div>
            @endif
        </div>
    </div>

    <div class="footer">
        <p>Documento generado el {{ now()->format('d/m/Y H:i:s') }} por el Sistema de Contratos</p>
        <p>Este documento es una representación digital de la información del contrato en el sistema.</p>
    </div>
</body>
</html>
