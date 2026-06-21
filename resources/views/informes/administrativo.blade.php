@extends('plantilla')
@section('title', 'Generar Informe Administrativo')

@push('css')
<style>
    .informe-tabs .nav-link { color:#475569; font-weight:600; }
    .informe-tabs .nav-link.active { color:#0b1120; border-color:#0b1120 #e2e8f0 #fff; }
    .col-pill { display:inline-flex; align-items:center; gap:.4rem; background:#f1f5f9;
                border:1px solid #e2e8f0; border-radius:20px; padding:.25rem .7rem; margin:.2rem;
                font-size:.85rem; cursor:pointer; user-select:none; }
    .col-pill input { margin:0; }
    .filtro-row { background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:.5rem; margin-bottom:.5rem; }
    .preview-wrap { max-height:600px; overflow:auto; }
    #grabarBtn.grabando { background:#dc2626 !important; border-color:#dc2626 !important; color:#fff !important; }
    .pulse { animation:pulse 1.2s infinite; }
    @keyframes pulse { 0%{opacity:1;} 50%{opacity:.4;} 100%{opacity:1;} }
    .spinner-sm { width:1rem; height:1rem; }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Generar Informe Administrativo</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">CU12 · Informes Administrativos</li>
    </ol>

    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white">
            <ul class="nav nav-tabs card-header-tabs informe-tabs" id="informeTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-estatico-btn" data-bs-toggle="tab"
                            data-bs-target="#tab-estatico" type="button" role="tab">
                        <i class="fas fa-table me-1"></i> Informes predefinidos
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-dinamico-btn" data-bs-toggle="tab"
                            data-bs-target="#tab-dinamico" type="button" role="tab">
                        <i class="fas fa-sliders-h me-1"></i> Reporte dinámico
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-voz-btn" data-bs-toggle="tab"
                            data-bs-target="#tab-voz" type="button" role="tab">
                        <i class="fas fa-microphone me-1"></i> Por voz o texto (IA)
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content">

                {{-- ══════════════════ MODO 1: ESTÁTICO ══════════════════ --}}
                <div class="tab-pane fade show active" id="tab-estatico" role="tabpanel">
                    <p class="text-muted small">Consultas predefinidas sobre las tablas del sistema, listas para visualizar o descargar.</p>

                    <form method="GET" action="{{ route('informes.administrativo') }}" id="formEstatico">
                        <input type="hidden" name="formato" id="formatoEstatico" value="">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Tipo de informe</label>
                                <select name="reporte" id="reporteSelect" class="form-select form-select-sm" required>
                                    <option value="">— Selecciona un informe —</option>
                                    @foreach($catalogoEstatico as $key => $def)
                                        <option value="{{ $key }}" {{ $reporteActivo === $key ? 'selected' : '' }}>
                                            {{ $def['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 filtro-est" data-filtro="rango">
                                <label class="form-label">Desde</label>
                                <input type="date" name="desde" class="form-control form-control-sm" value="{{ request('desde') }}">
                            </div>
                            <div class="col-md-2 filtro-est" data-filtro="rango">
                                <label class="form-label">Hasta</label>
                                <input type="date" name="hasta" class="form-control form-control-sm" value="{{ request('hasta') }}">
                            </div>
                            <div class="col-md-2 filtro-est" data-filtro="estado">
                                <label class="form-label">Estado</label>
                                <select name="estado" id="estadoEstatico" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                </select>
                            </div>
                            <div class="col-md-2 filtro-est" data-filtro="tipo_residente">
                                <label class="form-label">Tipo residente</label>
                                <select name="tipo_residente" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <option value="Propietario" {{ request('tipo_residente')=='Propietario'?'selected':'' }}>Propietario</option>
                                    <option value="Inquilino" {{ request('tipo_residente')=='Inquilino'?'selected':'' }}>Inquilino</option>
                                </select>
                            </div>
                            <div class="col-md-2 filtro-est" data-filtro="metodo">
                                <label class="form-label">Método</label>
                                <select name="metodo" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    @foreach(['efectivo','QR','Stripe','transferencia'] as $met)
                                        <option value="{{ $met }}" {{ request('metodo')==$met?'selected':'' }}>{{ ucfirst($met) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary" onclick="document.getElementById('formatoEstatico').value=''">
                                <i class="fas fa-eye me-1"></i> Visualizar informe
                            </button>
                            <small class="text-muted ms-2 d-none d-md-inline">
                                Elige el informe y pulsa aquí. Las opciones de descarga aparecerán junto al resultado.
                            </small>
                        </div>
                    </form>

                    <hr>

                    @if($errorReporte)
                        <div class="alert alert-danger">
                            <i class="fas fa-circle-exclamation me-1"></i>
                            No se pudo generar el informe: {{ $errorReporte }}
                        </div>
                    @elseif($reporteData)
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <div>
                                <h5 class="mb-0 d-inline-block">{{ $reporteData['titulo'] }}</h5>
                                <span class="badge bg-secondary ms-2">{{ $reporteData['count'] }} registros</span>
                            </div>
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <span class="text-muted small me-1"><i class="fas fa-download me-1"></i>Descargar:</span>
                                <button form="formEstatico" type="submit" formnovalidate class="btn btn-outline-dark btn-sm"
                                        onclick="document.getElementById('formatoEstatico').value='html'"><i class="fas fa-code me-1"></i> HTML</button>
                                <button form="formEstatico" type="submit" formnovalidate class="btn btn-outline-success btn-sm"
                                        onclick="document.getElementById('formatoEstatico').value='xlsx'"><i class="fas fa-file-excel me-1"></i> Excel</button>
                                <button form="formEstatico" type="submit" formnovalidate class="btn btn-outline-success btn-sm"
                                        onclick="document.getElementById('formatoEstatico').value='csv'"><i class="fas fa-file-csv me-1"></i> CSV</button>
                                <button form="formEstatico" type="submit" formnovalidate class="btn btn-outline-danger btn-sm"
                                        onclick="document.getElementById('formatoEstatico').value='pdf'"><i class="fas fa-file-pdf me-1"></i> PDF</button>
                            </div>
                        </div>

                        @if(!empty($reporteData['resumen']))
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                @foreach($reporteData['resumen'] as $k => $v)
                                    <span class="badge bg-light text-dark border p-2">{{ $k }}: <strong>{{ $v }}</strong></span>
                                @endforeach
                            </div>
                        @endif

                        @if($reporteData['count'] === 0)
                            <div class="alert alert-info">No se encontraron registros para los filtros seleccionados.</div>
                        @else
                            <div class="preview-wrap">
                                <table class="table table-sm table-striped table-hover">
                                    <thead class="table-dark sticky-top">
                                        <tr>
                                            @foreach($reporteData['headers'] as $h)
                                                <th>{{ $h }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($reporteData['rows'] as $fila)
                                            <tr>
                                                @foreach($fila as $celda)
                                                    <td>{{ is_bool($celda) ? ($celda ? 'Sí' : 'No') : $celda }}</td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-table fa-2x mb-2 d-block opacity-50"></i>
                            Selecciona un informe y pulsa <strong>Visualizar informe</strong>.
                        </div>
                    @endif
                </div>

                {{-- ══════════════════ MODO 2: DINÁMICO ══════════════════ --}}
                <div class="tab-pane fade" id="tab-dinamico" role="tabpanel">
                    <p class="text-muted small">Elige una tabla, marca las columnas que quieres y añade filtros: el reporte se construye a tu medida.</p>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tabla principal</label>
                            <select id="tablaDinamica" class="form-select form-select-sm">
                                <option value="">— Selecciona una tabla —</option>
                                @foreach($catalogoTablas as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ordenar por</label>
                            <select id="ordenarDinamico" class="form-select form-select-sm"><option value="">Sin orden</option></select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Dirección</label>
                            <select id="direccionDinamica" class="form-select form-select-sm">
                                <option value="asc">Ascendente</option>
                                <option value="desc">Descendente</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Límite</label>
                            <input type="number" id="limiteDinamico" class="form-control form-control-sm" min="1" placeholder="Todos">
                        </div>
                    </div>

                    <div id="columnasBox" class="mt-3 d-none">
                        <label class="form-label fw-semibold mb-1">Columnas a incluir
                            <small class="text-muted">(si no marcas ninguna, se incluyen todas)</small>
                        </label>
                        <div class="border rounded p-2 bg-white">
                            <div class="mb-1"><span class="badge bg-primary">Campos</span></div>
                            <div id="columnasBase"></div>
                            <div id="relacionesWrap" class="mt-2 d-none">
                                <div class="mb-1"><span class="badge bg-info text-dark">Relacionados</span></div>
                                <div id="columnasRel"></div>
                            </div>
                        </div>
                    </div>

                    <div id="filtrosBox" class="mt-3 d-none">
                        <label class="form-label fw-semibold mb-1">Filtros (opcional)</label>
                        <div id="filtrosLista"></div>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="addFiltro">
                            <i class="fas fa-plus me-1"></i> Añadir filtro
                        </button>
                    </div>

                    <div class="mt-3 d-flex flex-wrap gap-2" id="accionesDinamico" style="display:none!important">
                        <button type="button" class="btn btn-primary btn-sm" id="generarDinamico">
                            <i class="fas fa-eye me-1"></i> Generar
                        </button>
                        <button type="button" class="btn btn-outline-dark btn-sm btn-exp-din" data-formato="html"><i class="fas fa-code me-1"></i> HTML</button>
                        <button type="button" class="btn btn-outline-success btn-sm btn-exp-din" data-formato="xlsx"><i class="fas fa-file-excel me-1"></i> Excel</button>
                        <button type="button" class="btn btn-outline-success btn-sm btn-exp-din" data-formato="csv"><i class="fas fa-file-csv me-1"></i> CSV</button>
                        <button type="button" class="btn btn-outline-danger btn-sm btn-exp-din" data-formato="pdf"><i class="fas fa-file-pdf me-1"></i> PDF</button>
                    </div>

                    <hr>
                    <div id="previewDinamico" class="preview-wrap"></div>
                </div>

                {{-- ══════════════════ MODO 3: VOZ / TEXTO ══════════════════ --}}
                <div class="tab-pane fade" id="tab-voz" role="tabpanel">
                    <p class="text-muted small">
                        Habla o escribe lo que necesitas y la IA (Whisper + OpenAI) generará el reporte.
                        Ejemplos: <em>"pagos aprobados de este mes"</em>, <em>"residentes inquilinos"</em>,
                        <em>"multas pendientes ordenadas por monto"</em>.
                    </p>

                    <div class="row g-2 align-items-end">
                        <div class="col-md-9">
                            <label class="form-label fw-semibold">Instrucción</label>
                            <textarea id="textoVoz" class="form-control" rows="2"
                                      placeholder="Escribe tu consulta o usa el micrófono…"></textarea>
                        </div>
                        <div class="col-md-3 d-grid gap-2">
                            <button type="button" class="btn btn-outline-danger btn-sm" id="grabarBtn">
                                <i class="fas fa-microphone me-1"></i> <span id="grabarLabel">Grabar voz</span>
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" id="interpretarBtn">
                                <i class="fas fa-wand-magic-sparkles me-1"></i> Generar reporte
                            </button>
                        </div>
                    </div>

                    <div id="estadoVoz" class="small text-muted mt-2"></div>

                    <div id="explicacionVoz" class="alert alert-info mt-3 d-none"></div>

                    <div class="mt-2 d-flex flex-wrap gap-2" id="accionesVoz" style="display:none!important">
                        <span class="badge bg-dark align-self-center" id="tituloVoz"></span>
                        <button type="button" class="btn btn-outline-dark btn-sm btn-exp-voz" data-formato="html"><i class="fas fa-code me-1"></i> HTML</button>
                        <button type="button" class="btn btn-outline-success btn-sm btn-exp-voz" data-formato="xlsx"><i class="fas fa-file-excel me-1"></i> Excel</button>
                        <button type="button" class="btn btn-outline-success btn-sm btn-exp-voz" data-formato="csv"><i class="fas fa-file-csv me-1"></i> CSV</button>
                        <button type="button" class="btn btn-outline-danger btn-sm btn-exp-voz" data-formato="pdf"><i class="fas fa-file-pdf me-1"></i> PDF</button>
                    </div>

                    <hr>
                    <div id="previewVoz" class="preview-wrap"></div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
const RUTAS = {
    columnas:    "{{ route('informes.columnas') }}",
    dinamico:    "{{ route('informes.dinamico') }}",
    transcribir: "{{ route('informes.transcribir') }}",
    interpretar: "{{ route('informes.interpretar') }}",
    exportarIa:  "{{ route('informes.exportar-ia') }}",
};
const CSRF = "{{ csrf_token() }}";

const FILTROS_ESTATICO = {!! json_encode(collect($catalogoEstatico)->map(fn($d)=>$d['filtros'])) !!};

const ESTADOS = {
    unidades:    ['activa','inactiva'],
    propiedades: ['disponible','ocupada','activa','mantenimiento'],
    cuotas:      ['pendiente','activa','cancelada','pagado'],
    pagos:       ['pendiente','aprobado','rechazado'],
    multas:      ['pendiente','pagada','anulada'],
    incidencias: ['pendiente','en_revision','resuelto','cerrado'],
    reclamos:    ['pendiente','en_revision','resuelto','rechazado'],
    visitas:     ['pendiente','en_curso','finalizada','rechazada'],
    reservas:    ['pendiente','confirmada','cancelada'],
    eventos:     ['programado','en_curso','finalizado','cancelado'],
};

function esc(s){ return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

function tablaHtml(headers, rows){
    if(!rows || rows.length === 0){
        return '<div class="alert alert-info">No se encontraron registros para los filtros seleccionados.</div>';
    }
    let h = '<table class="table table-sm table-striped table-hover"><thead class="table-dark sticky-top"><tr>';
    headers.forEach(c => h += '<th>'+esc(c)+'</th>');
    h += '</tr></thead><tbody>';
    rows.forEach(f => { h += '<tr>'; f.forEach(c => h += '<td>'+esc(c)+'</td>'); h += '</tr>'; });
    h += '</tbody></table>';
    return h;
}

async function descargar(resp){
    if(!resp.ok){
        let msg = 'Error al exportar.';
        try { const j = await resp.json(); msg = j.error || msg; } catch(e){}
        throw new Error(msg);
    }
    const blob = await resp.blob();
    const cd = resp.headers.get('Content-Disposition') || '';
    const m = cd.match(/filename="?([^"]+)"?/);
    const nombre = m ? m[1] : 'informe';
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url; a.download = nombre; document.body.appendChild(a); a.click();
    a.remove(); URL.revokeObjectURL(url);
}

/* MODO 1: Estático */
const reporteSelect = document.getElementById('reporteSelect');
const estadoEstatico = document.getElementById('estadoEstatico');

function refrescarFiltrosEstatico(){
    const rep = reporteSelect.value;
    const filtros = FILTROS_ESTATICO[rep] || [];
    document.querySelectorAll('.filtro-est').forEach(el => {
        el.style.display = filtros.includes(el.dataset.filtro) ? '' : 'none';
    });
    if(estadoEstatico){
        const actual = "{{ request('estado') }}";
        const ops = ESTADOS[rep] || [];
        estadoEstatico.innerHTML = '<option value="">Todos</option>' +
            ops.map(e => '<option value="'+e+'" '+(e===actual?'selected':'')+'>'+(e.charAt(0).toUpperCase()+e.slice(1).replace(/_/g,' '))+'</option>').join('');
    }
}
reporteSelect.addEventListener('change', refrescarFiltrosEstatico);
refrescarFiltrosEstatico();

/* MODO 2: Dinámico */
const tablaDin = document.getElementById('tablaDinamica');
const columnasBox = document.getElementById('columnasBox');
const filtrosBox = document.getElementById('filtrosBox');
const accionesDin = document.getElementById('accionesDinamico');
let COLUMNAS_ACTUALES = { base:{}, relaciones:{} };

tablaDin.addEventListener('change', async () => {
    const tabla = tablaDin.value;
    if(!tabla){ columnasBox.classList.add('d-none'); filtrosBox.classList.add('d-none'); accionesDin.style.display='none'; return; }
    try {
        const resp = await fetch(RUTAS.columnas + '?tabla=' + encodeURIComponent(tabla), {headers:{'X-Requested-With':'XMLHttpRequest'}});
        const data = await resp.json();
        if(data.error){ alert(data.error); return; }
        COLUMNAS_ACTUALES = data.columnas;
        const base = data.columnas.base, rel = data.columnas.relaciones;
        document.getElementById('columnasBase').innerHTML = Object.entries(base).map(([k,v]) =>
            '<label class="col-pill"><input type="checkbox" class="col-base" value="'+k+'"> '+esc(v)+'</label>').join('');
        const relWrap = document.getElementById('relacionesWrap');
        if(Object.keys(rel).length){
            relWrap.classList.remove('d-none');
            document.getElementById('columnasRel').innerHTML = Object.entries(rel).map(([k,v]) =>
                '<label class="col-pill"><input type="checkbox" class="col-rel" value="'+k+'"> '+esc(v)+'</label>').join('');
        } else { relWrap.classList.add('d-none'); document.getElementById('columnasRel').innerHTML = ''; }
        const orden = document.getElementById('ordenarDinamico');
        orden.innerHTML = '<option value="">Sin orden</option>' +
            Object.entries(base).map(([k,v]) => '<option value="'+k+'">'+esc(v)+'</option>').join('');
        document.getElementById('filtrosLista').innerHTML = '';
        columnasBox.classList.remove('d-none');
        filtrosBox.classList.remove('d-none');
        accionesDin.style.display='flex';
    } catch(e){ alert('No se pudieron cargar las columnas.'); }
});

document.getElementById('addFiltro').addEventListener('click', () => {
    const base = COLUMNAS_ACTUALES.base || {};
    const opts = Object.entries(base).map(([k,v]) => '<option value="'+k+'">'+esc(v)+'</option>').join('');
    const row = document.createElement('div');
    row.className = 'filtro-row row g-2 align-items-center';
    row.innerHTML =
        '<div class="col-md-4"><select class="form-select form-select-sm f-col">'+opts+'</select></div>'+
        '<div class="col-md-3"><select class="form-select form-select-sm f-op">'+
            '<option value="=">igual a</option><option value="!=">distinto de</option>'+
            '<option value="like">contiene</option>'+
            '<option value=">">mayor que</option><option value=">=">mayor o igual</option>'+
            '<option value="<">menor que</option><option value="<=">menor o igual</option>'+
            '<option value="between">entre</option></select></div>'+
        '<div class="col-md-2"><input class="form-control form-control-sm f-val" placeholder="Valor"></div>'+
        '<div class="col-md-2"><input class="form-control form-control-sm f-val2" placeholder="Valor 2" style="display:none"></div>'+
        '<div class="col-md-1 text-end"><button type="button" class="btn btn-sm btn-outline-danger f-del"><i class="fas fa-times"></i></button></div>';
    document.getElementById('filtrosLista').appendChild(row);
    row.querySelector('.f-op').addEventListener('change', e => {
        row.querySelector('.f-val2').style.display = e.target.value === 'between' ? '' : 'none';
    });
    row.querySelector('.f-del').addEventListener('click', () => row.remove());
});

function payloadDinamico(){
    const columnas = [...document.querySelectorAll('.col-base:checked')].map(c => c.value);
    const relaciones = [...document.querySelectorAll('.col-rel:checked')].map(c => c.value);
    const filtros = [...document.querySelectorAll('#filtrosLista .filtro-row')].map(r => ({
        columna:  r.querySelector('.f-col').value,
        operador: r.querySelector('.f-op').value,
        valor:    r.querySelector('.f-val').value,
        valor2:   r.querySelector('.f-val2').value,
    })).filter(f => f.valor !== '' || f.operador === 'between');
    return {
        tabla: tablaDin.value, columnas, relaciones, filtros,
        ordenar_por: document.getElementById('ordenarDinamico').value || null,
        direccion: document.getElementById('direccionDinamica').value,
        limite: document.getElementById('limiteDinamico').value || null,
    };
}

document.getElementById('generarDinamico').addEventListener('click', async () => {
    const prev = document.getElementById('previewDinamico');
    prev.innerHTML = '<div class="text-muted"><span class="spinner-border spinner-sm"></span> Generando…</div>';
    try {
        const resp = await fetch(RUTAS.dinamico, {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
            body: JSON.stringify(payloadDinamico()),
        });
        const data = await resp.json();
        if(data.error){ prev.innerHTML = '<div class="alert alert-danger">'+esc(data.error)+'</div>'; return; }
        const head = '<div class="d-flex justify-content-between align-items-center mb-2"><h5 class="mb-0">'+esc(data.titulo)+'</h5><span class="badge bg-secondary">'+data.count+' registros</span></div>';
        prev.innerHTML = head + tablaHtml(data.headers, data.rows);
    } catch(e){ prev.innerHTML = '<div class="alert alert-danger">Error al generar el reporte.</div>'; }
});

document.querySelectorAll('.btn-exp-din').forEach(btn => btn.addEventListener('click', async () => {
    if(!tablaDin.value){ alert('Selecciona una tabla primero.'); return; }
    const p = payloadDinamico(); p.formato = btn.dataset.formato;
    try {
        const resp = await fetch(RUTAS.dinamico, {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF}, body: JSON.stringify(p)});
        await descargar(resp);
    } catch(e){ alert(e.message); }
}));

/* MODO 3: Voz / Texto */
let mediaRecorder = null, chunks = [], grabando = false;
const grabarBtn = document.getElementById('grabarBtn');
const grabarLabel = document.getElementById('grabarLabel');
const estadoVoz = document.getElementById('estadoVoz');
let SPEC_ACTUAL = null;

grabarBtn.addEventListener('click', async () => {
    if(grabando){ mediaRecorder.stop(); return; }
    if(!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia){
        estadoVoz.innerHTML = '<span class="text-danger">Tu navegador no permite grabar audio.</span>'; return;
    }
    try {
        const stream = await navigator.mediaDevices.getUserMedia({audio:true});
        mediaRecorder = new MediaRecorder(stream);
        chunks = [];
        mediaRecorder.ondataavailable = e => chunks.push(e.data);
        mediaRecorder.onstop = async () => {
            stream.getTracks().forEach(t => t.stop());
            grabando = false; grabarBtn.classList.remove('grabando','pulse'); grabarLabel.textContent = 'Grabar voz';
            const blob = new Blob(chunks, {type:'audio/webm'});
            estadoVoz.innerHTML = '<span class="spinner-border spinner-sm"></span> Transcribiendo audio…';
            const fd = new FormData(); fd.append('audio', blob, 'consulta.webm');
            try {
                const resp = await fetch(RUTAS.transcribir, {method:'POST', headers:{'X-CSRF-TOKEN':CSRF}, body:fd});
                const data = await resp.json();
                if(data.error){ estadoVoz.innerHTML = '<span class="text-danger">'+esc(data.error)+'</span>'; return; }
                document.getElementById('textoVoz').value = data.texto || '';
                estadoVoz.innerHTML = '<span class="text-success">Audio transcrito. Revisa el texto y genera el reporte.</span>';
            } catch(e){ estadoVoz.innerHTML = '<span class="text-danger">Error al transcribir.</span>'; }
        };
        mediaRecorder.start();
        grabando = true; grabarBtn.classList.add('grabando','pulse'); grabarLabel.textContent = 'Detener';
        estadoVoz.innerHTML = '<span class="text-danger">● Grabando… pulsa Detener para terminar.</span>';
    } catch(e){ estadoVoz.innerHTML = '<span class="text-danger">No se pudo acceder al micrófono.</span>'; }
});

document.getElementById('interpretarBtn').addEventListener('click', async () => {
    const texto = document.getElementById('textoVoz').value.trim();
    const prev = document.getElementById('previewVoz');
    const expl = document.getElementById('explicacionVoz');
    const acc = document.getElementById('accionesVoz');
    if(!texto){ estadoVoz.innerHTML = '<span class="text-danger">Escribe o graba una instrucción.</span>'; return; }
    estadoVoz.innerHTML = '<span class="spinner-border spinner-sm"></span> Interpretando con IA…';
    expl.classList.add('d-none'); acc.style.display='none'; prev.innerHTML = '';
    try {
        const resp = await fetch(RUTAS.interpretar, {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
            body: JSON.stringify({texto}),
        });
        const data = await resp.json();
        if(data.error){ estadoVoz.innerHTML='<span class="text-danger">'+esc(data.error)+'</span>'; return; }
        estadoVoz.innerHTML = '';
        SPEC_ACTUAL = data.spec;
        if(data.explicacion){ expl.textContent = data.explicacion; expl.classList.remove('d-none'); }
        document.getElementById('tituloVoz').textContent = data.titulo || 'Reporte';
        acc.style.display='flex';
        const head = '<div class="d-flex justify-content-between align-items-center mb-2"><h5 class="mb-0">'+esc(data.titulo)+'</h5><span class="badge bg-secondary">'+data.count+' registros</span></div>';
        prev.innerHTML = head + tablaHtml(data.headers, data.rows);
    } catch(e){ estadoVoz.innerHTML = '<span class="text-danger">Error al interpretar la instrucción.</span>'; }
});

document.querySelectorAll('.btn-exp-voz').forEach(btn => btn.addEventListener('click', async () => {
    if(!SPEC_ACTUAL){ alert('Genera un reporte primero.'); return; }
    const p = Object.assign({}, SPEC_ACTUAL, {formato: btn.dataset.formato});
    try {
        const resp = await fetch(RUTAS.exportarIa, {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF}, body: JSON.stringify(p)});
        await descargar(resp);
    } catch(e){ alert(e.message); }
}));
</script>
@endpush
