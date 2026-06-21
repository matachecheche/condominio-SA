<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo }}</title>
    <style>
        :root { --tinta:#0b1120; --suave:#64748b; --linea:#e2e8f0; --fondo:#f8fafc; }
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #1f2937; margin: 0; padding: 32px; background: #fff;
        }
        .hoja { max-width: 1100px; margin: 0 auto; }
        header { border-bottom: 3px solid var(--tinta); padding-bottom: 12px; margin-bottom: 20px;
                 display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 8px; }
        .marca { font-size: 20px; font-weight: 700; color: var(--tinta); }
        .marca small { display:block; font-size: 11px; font-weight: 400; color: var(--suave); margin-top: 2px; }
        .meta-doc { font-size: 11px; color: var(--suave); text-align: right; }
        h1 { font-size: 22px; color: var(--tinta); margin: 0 0 4px; }
        .subtitulo { color: var(--suave); font-size: 13px; margin-bottom: 18px; }
        .resumen { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
        .chip { background: var(--fondo); border: 1px solid var(--linea); border-radius: 8px;
                padding: 8px 14px; font-size: 13px; }
        .chip b { color: var(--tinta); }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        thead th { background: var(--tinta); color: #fff; text-align: left; padding: 10px 12px; }
        tbody td { padding: 9px 12px; border-bottom: 1px solid var(--linea); vertical-align: top; }
        tbody tr:nth-child(even) { background: var(--fondo); }
        .vacio { text-align: center; color: var(--suave); padding: 40px; background: var(--fondo);
                 border-radius: 8px; }
        footer { margin-top: 24px; padding-top: 12px; border-top: 1px solid var(--linea);
                 color: #94a3b8; font-size: 11px; text-align: center; }
        @media print { body { padding: 0; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="hoja">
        <header>
            <div class="marca">
                Condominio San Diego
                <small>Sistema de Administración · Informe administrativo</small>
            </div>
            <div class="meta-doc">
                Generado el {{ \Illuminate\Support\Carbon::now()->format('d/m/Y H:i') }}<br>
                Usuario: {{ auth()->user()->name ?? '—' }}
            </div>
        </header>

        <h1>{{ $titulo }}</h1>
        @if(!empty($subtitulo))
            <div class="subtitulo">{{ $subtitulo }}</div>
        @endif

        @if(!empty($meta))
            <div class="resumen">
                @foreach($meta as $k => $v)
                    <div class="chip"><span>{{ $k }}:</span> <b>{{ $v }}</b></div>
                @endforeach
            </div>
        @endif

        @if(empty($rows))
            <div class="vacio">No se encontraron registros para los filtros seleccionados.</div>
        @else
            <table>
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
            <p style="margin-top:10px; color:var(--suave); font-size:12px;">
                Total de registros: {{ count($rows) }}
            </p>
        @endif

        <footer>Documento generado automáticamente por el sistema de Condominio San Diego.</footer>
    </div>
</body>
</html>
