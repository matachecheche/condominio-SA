<?php

use App\Http\Controllers\PropiedadController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ResidenteController;
use App\Http\Controllers\BitacoraController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\CargoEmpleadoController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CuotaController;
use App\Http\Controllers\TipoCuotaController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\MultaController;
use App\Http\Controllers\EmpresaExternaController;
use App\Http\Controllers\MantenimientoController;
use App\Http\Controllers\AreaComunController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\VisitaController;
use App\Http\Controllers\ComunicadoController;
use App\Http\Controllers\UnidadController;
use App\Http\Controllers\InformeController;
use App\Http\Controllers\IncidenciaController;
use App\Http\Controllers\ReclamoController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\VoiceCommandController;
use App\Models\Bitacora;

// ── Recuperación de contraseña ────────────────────────────────────────────────
Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

// ── Cargos de empleados ───────────────────────────────────────────────────────
Route::prefix('empleados/cargo')->group(function () {
    Route::get('/', [CargoEmpleadoController::class, 'index'])->name('cargos.index');
    Route::get('/crear', [CargoEmpleadoController::class, 'create'])->name('cargos.create');
    Route::post('/', [CargoEmpleadoController::class, 'store'])->name('cargos.store');
    Route::get('/{id}/editar', [CargoEmpleadoController::class, 'edit'])->name('cargos.edit');
    Route::put('/{id}', [CargoEmpleadoController::class, 'update'])->name('cargos.update');
    Route::delete('/{id}', [CargoEmpleadoController::class, 'destroy'])->name('cargos.destroy');
});

// ── Panel y navegación ────────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('panel');
Route::get('/panel', [HomeController::class, 'index']);

// ── CICLO 1 — Acceso y Seguridad ─────────────────────────────────────────────
Route::resource('bitacora', BitacoraController::class);
Route::resource('roles', RoleController::class)->middleware('auth');
Route::resources([
    'users'      => UsuarioController::class,
    'residentes' => ResidenteController::class,
]);
Route::resource('empleados', EmpleadoController::class);

// ── Autenticación ─────────────────────────────────────────────────────────────
Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/logout', [LogoutController::class, 'logout'])->name('logout');

// ── Páginas de error ──────────────────────────────────────────────────────────
Route::get('/401', fn() => view('pages.401'));
Route::get('/404', fn() => view('pages.404'));
Route::get('/500', fn() => view('pages.500'));
Route::get('/admin', fn() => null)->middleware('role:ADMINISTRADOR');
Route::get('/prueba-permiso', fn() => 'Tienes permiso')->middleware(['auth', 'permission:ver-role']);

// ── CU7 — Cuotas, Multas y Pagos ─────────────────────────────────────────────
Route::resource('tipos-cuotas', TipoCuotaController::class);
Route::resource('cuotas', CuotaController::class);
Route::middleware(['auth'])->group(function () {
    Route::resource('pagos', PagoController::class)->only(['index', 'store']);
    Route::get('/pagos/create', [PagoController::class, 'create'])->name('pagos.create');
    Route::get('/mis-cuotas', [PagoController::class, 'misCuotas'])->name('pagos.mis_cuotas');
    Route::post('/pagos/qr', [PagoController::class, 'pagoQR'])->name('pagos.qr');
    Route::get('/pagos/create/cuota/{cuota}', [PagoController::class, 'createCuota'])->name('pagos.create.cuota');
    Route::get('/pagos/comprobante/{pago}', [PagoController::class, 'comprobante'])->name('pagos.comprobante');
    Route::resource('multas', MultaController::class)->parameters(['multas' => 'multa']);
    Route::get('/pagos/create/multa/{multa}', [PagoController::class, 'createMulta'])->name('pagos.create.multa');
    Route::post('/pagos/qr-multa', [PagoController::class, 'pagoQRMulta'])->name('pagos.qr.multa');
    Route::post('/pagos/stripe/multa', [PagoController::class, 'pagoStripeMulta'])->name('pagos.stripe.multa');
    Route::get('/stripe/success/multa/{multa}', [PagoController::class, 'stripeSuccessMulta'])->name('pagos.stripe.success.multa');
    Route::post('/pagos/stripe', [PagoController::class, 'pagoStripe'])->name('pagos.stripe');
    Route::get('/stripe/success/{cuota}', [PagoController::class, 'stripeSuccess'])->name('pagos.stripe.success');
    Route::get('/stripe/cancel', fn() => redirect()->route('pagos.mis_cuotas')->with('error', 'Pago cancelado.'))->name('pagos.stripe.cancel');
});

// Webhook de Stripe (sin auth ni CSRF — Stripe llama directo a esta URL).
Route::post('/stripe/webhook', [PagoController::class, 'webhook'])->name('stripe.webhook');

// ── CU8 — Áreas comunes y Reservas ───────────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::resource('areas-comunes', AreaComunController::class)->parameters(['areas-comunes' => 'areaComun']);
    Route::resource('reservas', ReservaController::class)->parameters(['reservas' => 'reserva']);
    Route::get('reservas/{reserva}/verificar-inventario', [ReservaController::class, 'verificarInventario'])->name('reservas.verificar-inventario');
    Route::post('/reservas/{reserva}/verificar-inventario', [ReservaController::class, 'guardarVerificacion'])->name('reservas.guardar-verificacion');
});
Route::get('/api/horas-libres', [ReservaController::class, 'horasLibres']);

// ── CU9 — Mantenimientos ─────────────────────────────────────────────────────
Route::resource('mantenimientos', MantenimientoController::class);

// ── CU10 — Visitas ────────────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::resource('visitas', VisitaController::class);
    Route::get('/validar-codigo', [VisitaController::class, 'mostrarValidarCodigo'])->name('visitas.mostrar-validar-codigo');
    Route::post('/visitas/validar-codigo', [VisitaController::class, 'validarCodigo'])->name('visitas.validar-codigo');
    Route::post('/visitas/{visita}/entrada', [VisitaController::class, 'registrarEntrada'])->name('visitas.entrada');
    Route::post('/visitas/{visita}/salida', [VisitaController::class, 'registrarSalida'])->name('visitas.salida');
    Route::get('/panel-guardia', [VisitaController::class, 'panelGuardia'])->name('visitas.panel-guardia');
    Route::get('/buscar-codigo', [VisitaController::class, 'buscarPorCodigo'])->name('visitas.buscar-codigo');
});

// ── CU11 — Comunicados ────────────────────────────────────────────────────────
Route::resource('comunicados', ComunicadoController::class);

// ── CU13 — Unidades Habitacionales (vincular residente con unidad) ────────────
Route::middleware(['auth'])->group(function () {
    Route::resource('unidades', UnidadController::class);
});

// ── CU12 + CU14 — Informes administrativos y reportes de pagos ───────────────
Route::middleware(['auth'])->group(function () {
    // CU12 — Generar Informe Administrativo
    Route::get('/informes/administrativo', [InformeController::class, 'administrativo'])
         ->name('informes.administrativo');

    // Modo dinámico (el usuario elige tabla y columnas)
    Route::get('/informes/administrativo/columnas', [InformeController::class, 'columnas'])
         ->name('informes.columnas');
    Route::post('/informes/administrativo/dinamico', [InformeController::class, 'dinamico'])
         ->name('informes.dinamico');

    // Modo por voz / texto (Whisper + OpenAI)
    Route::post('/informes/administrativo/voz/transcribir', [InformeController::class, 'transcribir'])
         ->name('informes.transcribir');
    Route::post('/informes/administrativo/voz/interpretar', [InformeController::class, 'interpretar'])
         ->name('informes.interpretar');
    Route::post('/informes/administrativo/voz/exportar', [InformeController::class, 'exportarIa'])
         ->name('informes.exportar-ia');

    // CU14 — Reportes de pagos
    Route::get('/informes/pagos', [InformeController::class, 'pagos'])
         ->name('informes.pagos');
});

// ── CU15 — Empresas Externas (contratación) ───────────────────────────────────
Route::resource('empresas', EmpresaExternaController::class);

// ── CU16 — Incidencias y denuncias ────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::resource('incidencias', IncidenciaController::class);
});

// ── CU17 — Notificaciones a residentes ────────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::resource('notificaciones', NotificacionController::class)
         ->parameters(['notificaciones' => 'notificacion'])
         ->except(['edit', 'update']);
    Route::post('/notificaciones/{notificacion}/marcar-leida', [NotificacionController::class, 'marcarLeida'])
         ->name('notificaciones.marcar-leida');
});

// ── CU18 — Reclamos administrativos ──────────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::resource('reclamos', ReclamoController::class);
});

// ── CU19 — Eventos comunitarios ──────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::resource('eventos', EventoController::class);
});

// ── CU20 — Gestión de Propiedades ────────────────────────────────────────────
Route::resource('propiedades', PropiedadController::class);

// ── Bitácora: cierre de página ────────────────────────────────────────────────
Route::post('/bitacora/page-close', function () {
    if (Auth::check()) {
        Bitacora::create([
            'user_id'    => Auth::id(),
            'usuario'    => Auth::user()->name,
            'accion'     => 'Cerró o abandonó la página del sistema',
            'fecha_hora' => now(),
            'ip'         => request()->ip(),
        ]);
    }
    return response()->noContent();
})->middleware('web')->name('bitacora.page-close');

// ── Asistente de voz (navegación por comandos hablados) ───────────────────────
Route::middleware(['auth'])->post('/voice-command', [VoiceCommandController::class, 'handle'])->name('voice-command');
