# CU12 · Generar Informe Administrativo

Módulo de informes administrativos del Condominio San Diego. Ruta principal:
`/informes/administrativo`.

Ofrece tres modos de generación y cuatro formatos de salida.

## Modos de generación

### 1. Informes predefinidos (estáticos)
Consultas ya definidas sobre las tablas del sistema, con columnas seleccionadas,
filtros frecuentes (rango de fechas, estado, tipo de residente, método de pago) y
totales cuando aplica. Informes disponibles: residentes, residentes morosos,
unidades, propiedades, cuotas, pagos, multas, mantenimientos, incidencias,
reclamos, visitas, reservas, eventos, comunicados, empleados, áreas comunes,
empresas externas y un resumen financiero.

### 2. Reporte dinámico
El usuario elige una **tabla principal** y marca las **columnas** que quiere ver
(campos propios y datos relacionados, p. ej. el residente de una unidad). Permite
añadir filtros (igual, distinto, contiene, mayor/menor, entre), ordenar y limitar.
Las columnas se obtienen en tiempo real del esquema de la base de datos
(`Schema::getColumnListing`) restringido a una lista blanca de tablas.

### 3. Por voz o texto (IA)
El usuario habla o escribe una instrucción en lenguaje natural
(p. ej. *"pagos aprobados de este mes"*). El audio se transcribe con **Whisper** y
la instrucción se interpreta con **OpenAI**, que devuelve una *especificación de
reporte* en JSON (tabla, columnas, filtros, orden). Esa especificación se valida
contra la lista blanca y se ejecuta con el mismo constructor seguro del modo
dinámico.

> **Seguridad:** el modelo de IA nunca genera SQL. Solo produce una especificación
> JSON restringida al catálogo de tablas/columnas permitidas, que se valida en el
> servidor antes de ejecutarse. Esto evita inyección SQL y el acceso a datos
> sensibles (p. ej. contraseñas, que están excluidas explícitamente).

## Formatos de salida
Todos los modos pueden **visualizar** en pantalla y **descargar** en:

- **HTML** — documento autocontenido y estilizado.
- **Excel (XLSX)** — generado de forma nativa con `ZipArchive` (sin dependencias
  externas adicionales).
- **CSV** — con BOM UTF-8 y separador `;` (se abre correctamente en Excel con
  tildes y ñ).
- **PDF** — generado con DomPDF (`barryvdh/laravel-dompdf`).

## Configuración (sólo para el modo por voz/texto)
Añade tu clave de OpenAI al archivo `.env`:

```env
OPENAI_API_KEY=sk-...
# Opcionales:
OPENAI_MODEL=gpt-4o-mini
OPENAI_WHISPER_MODEL=whisper-1
OPENAI_BASE_URL=https://api.openai.com/v1
OPENAI_TIMEOUT=60
```

Los modos estático y dinámico funcionan sin clave de OpenAI.

## Endpoints
| Método | Ruta | Nombre | Descripción |
|-------|------|--------|-------------|
| GET  | `/informes/administrativo` | `informes.administrativo` | Página principal + informes estáticos y su descarga |
| GET  | `/informes/administrativo/columnas` | `informes.columnas` | (AJAX) columnas de una tabla |
| POST | `/informes/administrativo/dinamico` | `informes.dinamico` | Genera/exporta un reporte dinámico |
| POST | `/informes/administrativo/voz/transcribir` | `informes.transcribir` | Audio → texto (Whisper) |
| POST | `/informes/administrativo/voz/interpretar` | `informes.interpretar` | Texto → reporte (OpenAI) |
| POST | `/informes/administrativo/voz/exportar` | `informes.exportar-ia` | Exporta el reporte generado por IA |

## Archivos principales
- `app/Http/Controllers/InformeController.php` — orquesta los tres modos.
- `app/Services/Reportes/InformeEstatico.php` — definiciones de informes predefinidos.
- `app/Services/Reportes/ReporteSchema.php` — lista blanca de tablas y constructor seguro de consultas.
- `app/Services/Reportes/OpenAiReportService.php` — integración con Whisper y OpenAI.
- `app/Services/Reportes/ReporteExporter.php` — exportación a HTML/CSV/XLSX/PDF.
- `app/Services/Reportes/SimpleXlsxWriter.php` — escritor XLSX sin dependencias externas.
- `resources/views/informes/administrativo.blade.php` — interfaz con las tres pestañas.
- `resources/views/informes/pdf.blade.php` — plantilla del PDF.
- `resources/views/informes/export_html.blade.php` — plantilla del HTML descargable.
