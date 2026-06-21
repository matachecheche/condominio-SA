<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }}</title>
    <style>
        @page { margin: 110px 36px 70px 36px; }
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 10px; color: #1f2937; }

        header {
            position: fixed; top: -90px; left: 0; right: 0; height: 80px;
            border-bottom: 2px solid #0b1120;
        }
        header .marca { font-size: 16px; font-weight: bold; color: #0b1120; }
        header .sub   { font-size: 9px; color: #64748b; }
        header .fecha { font-size: 9px; color: #64748b; text-align: right; }

        footer {
            position: fixed; bottom: -50px; left: 0; right: 0; height: 40px;
            border-top: 1px solid #cbd5e1; color: #94a3b8; font-size: 8px;
            padding-top: 6px;
        }
        .pagenum:before { content: counter(page) " / " counter(pages); }

        h1 { font-size: 15px; color: #0b1120; margin: 0 0 2px; }
        .subtitulo { font-size: 9px; color: #64748b; margin-bottom: 10px; }

        .resumen { width: 100%; margin-bottom: 12px; }
        .resumen td {
            background: #f1f5f9; border: 1px solid #e2e8f0; padding: 6px 8px;
            font-size: 9px;
        }
        .resumen .k { color: #475569; font-weight: bold; }
        .resumen .v { color: #0b1120; }

        table.datos { width: 100%; border-collapse: collapse; }
        table.datos th {
            background: #0b1120; color: #fff; text-align: left;
            padding: 6px 6px; font-size: 9px; border: 1px solid #0b1120;
        }
        table.datos td {
            padding: 5px 6px; font-size: 9px; border: 1px solid #e2e8f0;
            vertical-align: top;
        }
        table.datos tr:nth-child(even) td { background: #f8fafc; }
        .vacio { text-align: center; color: #94a3b8; padding: 20px; }
    </style>
</head>
<body>
    <header>
        <table style="width:100%;">
            <tr>
                <td>
                    <div class="marca">Condominio San Diego</div>
                    <div class="sub">Sistema de Administración · Informe administrativo</div>
                </td>
                <td class="fecha">
                    Generado el {{ \Illuminate\Support\Carbon::now()->format('d/m/Y H:i') }}<br>
                    Usuario: {{ auth()->user()->name ?? '—' }}
                </td>
            </tr>
        </table>
    </header>

    <footer>
        Documento generado automáticamente por el sistema · Página <span class="pagenum"></span>
    </footer>

    <main>
        <h1>{{ $titulo }}</h1>
        @if(!empty($subtitulo))
            <div class="subtitulo">{{ $subtitulo }}</div>
        @endif

        @if(!empty($meta))
            <table class="resumen">
                <tr>
                    @foreach($meta as $k => $v)
                        <td><span class="k">{{ $k }}:</span> <span class="v">{{ $v }}</span></td>
                    @endforeach
                </tr>
            </table>
        @endif

        @if(empty($rows))
            <div class="vacio">No se encontraron registros para los filtros seleccionados.</div>
        @else
            <table class="datos">
                <thead>
                    <tr>
                        @foreach($headers as $h)
                            <th>{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $fila)
                        <tr>
                            @foreach($fila as $celda)
                                <td>{{ is_bool($celda) ? ($celda ? 'Sí' : 'No') : $celda }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p style="margin-top:8px; font-size:9px; color:#64748b;">
                Total de registros: {{ count($rows) }}
            </p>
        @endif
    </main>
</body>
</html>
