<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Pago #{{ $pago->id }}</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            color: #333; 
            background-color: #f8f9fa; 
            margin: 0; 
            padding: 40px 20px;
        }
        .container { 
            max-width: 650px; 
            margin: auto; 
            background: #ffffff; 
            border-radius: 8px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.05); 
            padding: 40px; 
            border: 1px solid #eef2f5;
        }
        .header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border-bottom: 2px solid #f1f3f5; 
            padding-bottom: 20px; 
            margin-bottom: 30px; 
        }
        .header-title { margin: 0; }
        .header-title h1 { 
            font-size: 24px; 
            font-weight: 700; 
            color: #0d6efd; 
            margin: 0 0 5px 0; 
        }
        .header-title p { 
            font-size: 13px; 
            color: #6c757d; 
            margin: 0; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
        }
        .receipt-badge { 
            background-color: #e8f5e9; 
            color: #2e7d32; 
            font-weight: 600; 
            font-size: 12px; 
            padding: 6px 12px; 
            border-radius: 50px; 
            border: 1px solid #c8e6c9;
            text-transform: uppercase;
        }
        .receipt-badge.pendiente {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        .grid-info { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 20px; 
            margin-bottom: 30px; 
        }
        .info-block h3 { 
            font-size: 12px; 
            text-transform: uppercase; 
            color: #6c757d; 
            margin: 0 0 8px 0; 
            letter-spacing: 0.5px;
        }
        .info-block p { 
            font-size: 15px; 
            margin: 0; 
            color: #212529; 
            font-weight: 500; 
        }
        .detail-box { 
            background-color: #f8f9fa; 
            border-radius: 6px; 
            padding: 20px; 
            margin-bottom: 30px; 
            border-left: 4px solid #0d6efd;
        }
        .detail-box table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        .detail-box td { 
            padding: 6px 0; 
            font-size: 14px; 
            vertical-align: top;
        }
        .detail-box td.label { 
            color: #6c757d; 
            width: 30%; 
        }
        .detail-box td.value { 
            color: #212529; 
            font-weight: 600; 
        }
        .amount-section { 
            text-align: right; 
            border-top: 1px solid #f1f3f5; 
            padding-top: 20px; 
            margin-bottom: 20px;
        }
        .amount-label { 
            font-size: 13px; 
            color: #6c757d; 
            text-transform: uppercase; 
        }
        .amount-value { 
            font-size: 28px; 
            font-weight: 700; 
            color: #212529; 
            margin-top: 5px; 
        }
        .footer-note { 
            text-align: center; 
            color: #adb5bd; 
            font-size: 11px; 
            margin-top: 40px; 
            border-top: 1px dashed #dee2e6; 
            padding-top: 15px; 
        }
        .actions-panel { 
            text-align: center; 
            margin-top: 30px; 
        }
        .btn-print { 
            background-color: #212529; 
            color: #fff; 
            border: none; 
            padding: 10px 24px; 
            font-size: 14px; 
            font-weight: 600; 
            border-radius: 6px; 
            cursor: pointer; 
            display: inline-flex; 
            align-items: center; 
            gap: 8px;
            transition: background 0.2s ease;
        }
        .btn-print:hover { background-color: #343a40; }

        /* Estilos de Impresión Electrónica */
        @media print {
            body { background-color: #fff; padding: 0; }
            .container { box-shadow: none; border: none; padding: 0; max-width: 100%; }
            .actions-panel { display: none; }
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- Encabezado de Recibo -->
        <div class="header">
            <div class="header-title">
                <h1>Comprobante de Pago</h1>
                <p>Ref: Transacción Electrónica ##{{ $pago->id }}</p>
            </div>
            <div>
                <span class="receipt-badge {{ strtolower($pago->estado) === 'pendiente' ? 'pendiente' : '' }}">
                    {{ $pago->estado ? ucfirst($pago->estado) : 'Validado' }}
                </span>
            </div>
        </div>

        <!-- Bloques de Metadata Básica -->
        <div class="grid-info">
            <div class="info-block">
                <h3>Fecha y Hora de Gestión</h3>
                <p>{{ \Carbon\Carbon::parse($pago->fecha_pago)->translatedFormat('d \d\e F, Y - H:i') }}</p>
            </div>
            <div class="info-block">
                <h3>Vía / Canal de Pago</h3>
                <p>{{ $pago->metodo ? ucfirst($pago->metodo) : 'No especificado' }}</p>
            </div>
        </div>

        <!-- Desglose de Concepto y Beneficiario -->
        <div class="detail-box">
            <table>
                @if($pago->cuota)
                    <tr>
                        <td class="label">Concepto:</td>
                        <td class="value">Cuota — {{ $pago->cuota->titulo ?? 'Obligación Ordinaria' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Copropietario:</td>
                        <td class="value">{{ optional($pago->cuota->residente)->nombre_completo ?? 'N/D' }}</td>
                    </tr>
                @elseif($pago->multa)
                    <tr>
                        <td class="label">Concepto:</td>
                        <td class="value">Sanción — {{ $pago->multa->motivo }}</td>
                    </tr>
                    <tr>
                        <td class="label">Asignado a:</td>
                        <td class="value">
                            {{ optional($pago->multa->residente)->nombre_completo
                               ?? optional($pago->multa->empleado)->nombre_completo ?? 'N/D' }}
                        </td>
                    </tr>
                @endif

                @if($pago->observacion)
                    <tr>
                        <td class="label" style="padding-top: 12px;">Glosa/Nota:</td>
                        <td class="value" style="padding-top: 12px; font-weight: normal; font-style: italic; color: #495057;">
                            "{{ $pago->observacion }}"
                        </td>
                    </tr>
                @endif
            </table>
        </div>

        <!-- Sección de Liquidación Monetaria -->
        <div class="amount-section">
            <div class="amount-label">Total Recaudado</div>
            <div class="amount-value">Bs {{ number_format($pago->monto_pagado, 2) }}</div>
        </div>

        <!-- Nota legal interna -->
        <div class="footer-note">
            Este es un documento electrónico oficial emitido por el sistema de administración del condominio. No requiere firma física para su validez legal.
        </div>

        <!-- Panel de Acciones Interactivas -->
        <div class="actions-panel">
            <button class="btn-print" onclick="window.print()">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="margin-right:4px; vertical-align: middle;">
                    <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4zm1 5a1 1 0 1 1 0-2 1 1 0 0 1 0 2m7 0a1 1 0 1 1 0-2 1 1 0 0 1 0 2M5 10a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v3H5z"/>
                </svg>
                Imprimir o Guardar PDF
            </button>
        </div>
    </div>

</body>
</html>