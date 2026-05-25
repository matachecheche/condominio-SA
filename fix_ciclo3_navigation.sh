#!/usr/bin/env bash
# =============================================================================
# fix_ciclo3_navigation.sh
# Ejecutar desde la RAÍZ del proyecto: bash fix_ciclo3_navigation.sh
#
# Qué hace:
#  1. Corrige CU20 = Gestionar Propiedades (en routes, nav, panel y vistas)
#  2. Elimina los duplicados de CU21 que quedaron en el sidebar
#  3. Mueve CU20 al Paquete 2 (Personas y Estructura) según documentación
#  4. Activa CU12, CU13, CU14, CU15, CU16 con sus rutas reales en el panel
#  5. Reescribe navigation-menu y panel/index con navegación correcta + mejoras UI
# =============================================================================

set -e

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; CYAN='\033[0;36m'; NC='\033[0m'
ok()   { echo -e "${GREEN}✔ $*${NC}"; }
info() { echo -e "${CYAN}▸ $*${NC}"; }
warn() { echo -e "${YELLOW}⚠ $*${NC}"; }
err()  { echo -e "${RED}✘ $*${NC}"; exit 1; }

# ── Verificar que estamos en la raíz del proyecto Laravel ────────────────────
[[ -f "artisan" ]] || err "Ejecuta este script desde la raíz del proyecto Laravel"
[[ -f "routes/web.php" ]] || err "No se encontró routes/web.php"

info "Creando backup de archivos críticos..."
BACKUP_DIR=".backup_fix_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"
cp routes/web.php "$BACKUP_DIR/" 2>/dev/null || true
cp resources/views/components/navigation-menu.blade.php "$BACKUP_DIR/" 2>/dev/null || true
cp resources/views/panel/index.blade.php "$BACKUP_DIR/" 2>/dev/null || true
ok "Backup en $BACKUP_DIR"

# =============================================================================
# 1. ROUTES/WEB.PHP — Renombrar CU21 → CU20, limpiar duplicado propiedades
# =============================================================================
info "Actualizando routes/web.php..."

cat > routes/web.php << 'ROUTES_EOF'
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
    Route::get('/informes/administrativo', [InformeController::class, 'administrativo'])
         ->name('informes.administrativo');
    Route::get('/informes/pagos', [InformeController::class, 'pagos'])
         ->name('informes.pagos');
});

// ── CU15 — Empresas Externas (contratación) ───────────────────────────────────
Route::resource('empresas', EmpresaExternaController::class);

// ── CU16 — Incidencias y denuncias ────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::resource('incidencias', IncidenciaController::class);
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
ROUTES_EOF

ok "routes/web.php actualizado"

# =============================================================================
# 2. NAVIGATION MENU — Sidebar correcto con paquetes y CU20
# =============================================================================
info "Reescribiendo navigation-menu.blade.php..."

cat > resources/views/components/navigation-menu.blade.php << 'NAV_EOF'
<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion"
         style="background:#0b1120; border-right:1px solid rgba(255,255,255,0.07);">
        <div class="sb-sidenav-menu">
            <div class="nav">

                {{-- INICIO --}}
                <div class="sb-sidenav-menu-heading" style="color:#64748b;font-size:0.65rem;letter-spacing:.08em;padding:.75rem 1rem .25rem;">
                    SISTEMA
                </div>
                <a class="nav-link d-flex align-items-center gap-2" href="{{ route('panel') }}"
                   style="color:#e2e8f0;">
                    <div class="sb-nav-link-icon" style="color:#38bdf8;"><i class="fas fa-tachometer-alt"></i></div>
                    <span>Panel de Control</span>
                </a>

                {{-- ═══════════════════════════════════════════════════
                     PAQUETE 1 — Acceso y Seguridad: CU1 CU2 CU3 CU4
                ════════════════════════════════════════════════════ --}}
                <div class="sb-sidenav-menu-heading mt-2"
                     style="color:#60a5fa;font-size:0.65rem;letter-spacing:.06em;padding:.6rem 1rem .25rem;
                            border-top:1px solid rgba(96,165,250,.15);">
                    📦 PKG 1 — ACCESO Y SEGURIDAD
                </div>

                <a class="nav-link d-flex align-items-center gap-2" href="{{ route('login') }}" style="color:#cbd5e1;">
                    <div class="sb-nav-link-icon" style="color:#60a5fa;"><i class="fas fa-sign-in-alt"></i></div>
                    <span>CU1 · Iniciar sesión</span>
                </a>

                <a class="nav-link d-flex align-items-center gap-2" href="{{ route('logout') }}" style="color:#cbd5e1;">
                    <div class="sb-nav-link-icon" style="color:#60a5fa;"><i class="fas fa-sign-out-alt"></i></div>
                    <span>CU2 · Cerrar sesión</span>
                </a>

                <a class="nav-link d-flex align-items-center gap-2" href="{{ route('users.index') }}" style="color:#cbd5e1;">
                    <div class="sb-nav-link-icon" style="color:#60a5fa;"><i class="fas fa-users"></i></div>
                    <span>CU3 · Gestionar Usuarios</span>
                </a>

                <a class="nav-link d-flex align-items-center gap-2" href="{{ route('roles.index') }}" style="color:#cbd5e1;">
                    <div class="sb-nav-link-icon" style="color:#60a5fa;"><i class="fas fa-user-shield"></i></div>
                    <span>CU4 · Roles y Permisos</span>
                </a>

                {{-- ═══════════════════════════════════════════════════════════
                     PAQUETE 2 — Personas y Estructura: CU5 CU6 CU13 CU20
                ══════════════════════════════════════════════════════════ --}}
                <div class="sb-sidenav-menu-heading mt-2"
                     style="color:#34d399;font-size:0.65rem;letter-spacing:.06em;padding:.6rem 1rem .25rem;
                            border-top:1px solid rgba(52,211,153,.15);">
                    📦 PKG 2 — PERSONAS Y ESTRUCTURA
                </div>

                <a class="nav-link collapsed d-flex align-items-center gap-2" href="#"
                   data-bs-toggle="collapse" data-bs-target="#collapseEmpleados"
                   aria-expanded="false" aria-controls="collapseEmpleados" style="color:#cbd5e1;">
                    <div class="sb-nav-link-icon" style="color:#34d399;"><i class="fas fa-id-card"></i></div>
                    <span>CU5 · Empleados</span>
                    <div class="sb-sidenav-collapse-arrow ms-auto"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseEmpleados" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav" style="background:#0d1628;">
                        <a class="nav-link ps-4" href="{{ route('empleados.index') }}" style="color:#94a3b8;">
                            <i class="fas fa-list me-2"></i> Lista de Empleados
                        </a>
                        <a class="nav-link ps-4" href="{{ route('cargos.index') }}" style="color:#94a3b8;">
                            <i class="fas fa-briefcase me-2"></i> Cargos
                        </a>
                    </nav>
                </div>

                <a class="nav-link d-flex align-items-center gap-2" href="{{ route('residentes.index') }}" style="color:#cbd5e1;">
                    <div class="sb-nav-link-icon" style="color:#34d399;"><i class="fas fa-building"></i></div>
                    <span>CU6 · Gestionar Residentes</span>
                </a>

                <a class="nav-link d-flex align-items-center gap-2" href="{{ route('unidades.index') }}" style="color:#cbd5e1;">
                    <div class="sb-nav-link-icon" style="color:#34d399;"><i class="fas fa-link"></i></div>
                    <span>CU13 · Vincular Residente-Unidad</span>
                </a>

                <a class="nav-link d-flex align-items-center gap-2" href="{{ route('propiedades.index') }}" style="color:#cbd5e1;">
                    <div class="sb-nav-link-icon" style="color:#34d399;"><i class="fas fa-home"></i></div>
                    <span>CU20 · Gestionar Propiedades</span>
                </a>

                {{-- ═══════════════════════════════════════════════════════════════════
                     PAQUETE 3 — Gestión Operativa: CU7 CU8 CU9 CU10 CU15 CU16 CU17
                ══════════════════════════════════════════════════════════════════ --}}
                <div class="sb-sidenav-menu-heading mt-2"
                     style="color:#fb923c;font-size:0.65rem;letter-spacing:.06em;padding:.6rem 1rem .25rem;
                            border-top:1px solid rgba(251,146,60,.15);">
                    📦 PKG 3 — GESTIÓN OPERATIVA
                </div>

                <a class="nav-link collapsed d-flex align-items-center gap-2" href="#"
                   data-bs-toggle="collapse" data-bs-target="#collapseCuotas"
                   aria-expanded="false" aria-controls="collapseCuotas" style="color:#cbd5e1;">
                    <div class="sb-nav-link-icon" style="color:#fb923c;"><i class="fas fa-dollar-sign"></i></div>
                    <span>CU7 · Cuotas y Pagos</span>
                    <div class="sb-sidenav-collapse-arrow ms-auto"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseCuotas" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav" style="background:#0d1628;">
                        <a class="nav-link ps-4" href="{{ route('cuotas.index') }}" style="color:#94a3b8;">
                            <i class="fas fa-list me-2"></i> Cuotas
                        </a>
                        <a class="nav-link ps-4" href="{{ route('tipos-cuotas.index') }}" style="color:#94a3b8;">
                            <i class="fas fa-tags me-2"></i> Tipos de Cuota
                        </a>
                        <a class="nav-link ps-4" href="{{ route('pagos.index') }}" style="color:#94a3b8;">
                            <i class="fas fa-money-bill me-2"></i> Pagos
                        </a>
                        <a class="nav-link ps-4" href="{{ route('multas.index') }}" style="color:#94a3b8;">
                            <i class="fas fa-exclamation-triangle me-2"></i> Multas
                        </a>
                    </nav>
                </div>

                <a class="nav-link collapsed d-flex align-items-center gap-2" href="#"
                   data-bs-toggle="collapse" data-bs-target="#collapseReservas"
                   aria-expanded="false" aria-controls="collapseReservas" style="color:#cbd5e1;">
                    <div class="sb-nav-link-icon" style="color:#fb923c;"><i class="fas fa-calendar-check"></i></div>
                    <span>CU8 · Áreas y Reservas</span>
                    <div class="sb-sidenav-collapse-arrow ms-auto"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseReservas" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav" style="background:#0d1628;">
                        <a class="nav-link ps-4" href="{{ route('areas-comunes.index') }}" style="color:#94a3b8;">
                            <i class="fas fa-map-marked me-2"></i> Áreas Comunes
                        </a>
                        <a class="nav-link ps-4" href="{{ route('reservas.index') }}" style="color:#94a3b8;">
                            <i class="fas fa-calendar me-2"></i> Reservas
                        </a>
                    </nav>
                </div>

                <a class="nav-link collapsed d-flex align-items-center gap-2" href="#"
                   data-bs-toggle="collapse" data-bs-target="#collapseMantenimiento"
                   aria-expanded="false" aria-controls="collapseMantenimiento" style="color:#cbd5e1;">
                    <div class="sb-nav-link-icon" style="color:#fb923c;"><i class="fas fa-tools"></i></div>
                    <span>CU9 · Mantenimientos</span>
                    <div class="sb-sidenav-collapse-arrow ms-auto"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseMantenimiento" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav" style="background:#0d1628;">
                        <a class="nav-link ps-4" href="{{ route('mantenimientos.index') }}" style="color:#94a3b8;">
                            <i class="fas fa-list me-2"></i> Mantenimientos
                        </a>
                    </nav>
                </div>

                <a class="nav-link collapsed d-flex align-items-center gap-2" href="#"
                   data-bs-toggle="collapse" data-bs-target="#collapseVisitas"
                   aria-expanded="false" aria-controls="collapseVisitas" style="color:#cbd5e1;">
                    <div class="sb-nav-link-icon" style="color:#fb923c;"><i class="fas fa-door-open"></i></div>
                    <span>CU10 · Visitas</span>
                    <div class="sb-sidenav-collapse-arrow ms-auto"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseVisitas" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav" style="background:#0d1628;">
                        <a class="nav-link ps-4" href="{{ route('visitas.index') }}" style="color:#94a3b8;">
                            <i class="fas fa-list me-2"></i> Visitas
                        </a>
                        <a class="nav-link ps-4" href="{{ route('visitas.panel-guardia') }}" style="color:#94a3b8;">
                            <i class="fas fa-shield-alt me-2"></i> Panel Guardia
                        </a>
                        <a class="nav-link ps-4" href="{{ route('visitas.mostrar-validar-codigo') }}" style="color:#94a3b8;">
                            <i class="fas fa-key me-2"></i> Validar Código
                        </a>
                    </nav>
                </div>

                <a class="nav-link d-flex align-items-center gap-2" href="{{ route('empresas.index') }}" style="color:#cbd5e1;">
                    <div class="sb-nav-link-icon" style="color:#fb923c;"><i class="fas fa-handshake"></i></div>
                    <span>CU15 · Empresas Externas</span>
                </a>

                <a class="nav-link d-flex align-items-center gap-2" href="{{ route('incidencias.index') }}" style="color:#cbd5e1;">
                    <div class="sb-nav-link-icon" style="color:#fb923c;"><i class="fas fa-flag"></i></div>
                    <span>CU16 · Denuncias / Incidencias</span>
                </a>

                <span class="nav-link d-flex align-items-center gap-2" style="color:#334155;cursor:default;">
                    <div class="sb-nav-link-icon"><i class="fas fa-bell"></i></div>
                    <span>CU17 · Notificaciones</span>
                    <small class="ms-auto" style="font-size:.6rem;color:#334155;">Pendiente</small>
                </span>

                {{-- ════════════════════════════════════════════════════════════
                     PAQUETE 4 — Comunicación y Reportes: CU11 CU12 CU14 CU18 CU19
                ═════════════════════════════════════════════════════════════ --}}
                <div class="sb-sidenav-menu-heading mt-2"
                     style="color:#a78bfa;font-size:0.65rem;letter-spacing:.06em;padding:.6rem 1rem .25rem;
                            border-top:1px solid rgba(167,139,250,.15);">
                    📦 PKG 4 — COMUNICACIÓN Y REPORTES
                </div>

                <a class="nav-link d-flex align-items-center gap-2" href="{{ route('comunicados.index') }}" style="color:#cbd5e1;">
                    <div class="sb-nav-link-icon" style="color:#a78bfa;"><i class="fas fa-envelope"></i></div>
                    <span>CU11 · Comunicados Internos</span>
                </a>

                <a class="nav-link d-flex align-items-center gap-2" href="{{ route('informes.administrativo') }}" style="color:#cbd5e1;">
                    <div class="sb-nav-link-icon" style="color:#a78bfa;"><i class="fas fa-file-alt"></i></div>
                    <span>CU12 · Informes Administrativos</span>
                </a>

                <a class="nav-link d-flex align-items-center gap-2" href="{{ route('informes.pagos') }}" style="color:#cbd5e1;">
                    <div class="sb-nav-link-icon" style="color:#a78bfa;"><i class="fas fa-receipt"></i></div>
                    <span>CU14 · Reportes de Pagos</span>
                </a>

                <span class="nav-link d-flex align-items-center gap-2" style="color:#334155;cursor:default;">
                    <div class="sb-nav-link-icon"><i class="fas fa-exclamation-circle"></i></div>
                    <span>CU18 · Reclamos Administrativos</span>
                    <small class="ms-auto" style="font-size:.6rem;color:#334155;">Pendiente</small>
                </span>

                <span class="nav-link d-flex align-items-center gap-2" style="color:#334155;cursor:default;">
                    <div class="sb-nav-link-icon"><i class="fas fa-calendar-star"></i></div>
                    <span>CU19 · Eventos Comunitarios</span>
                    <small class="ms-auto" style="font-size:.6rem;color:#334155;">Pendiente</small>
                </span>

                {{-- OTROS --}}
                <div class="sb-sidenav-menu-heading mt-2"
                     style="color:#64748b;font-size:0.65rem;letter-spacing:.08em;padding:.6rem 1rem .25rem;
                            border-top:1px solid rgba(255,255,255,.05);">
                    SISTEMA
                </div>
                <a class="nav-link d-flex align-items-center gap-2" href="{{ route('bitacora.index') }}" style="color:#cbd5e1;">
                    <div class="sb-nav-link-icon" style="color:#64748b;"><i class="fas fa-book"></i></div>
                    <span>Bitácora</span>
                </a>
                <a class="nav-link d-flex align-items-center gap-2 mt-1" href="{{ route('logout') }}"
                   style="color:#f87171;">
                    <div class="sb-nav-link-icon" style="color:#f87171;"><i class="fa fa-sign-out"></i></div>
                    <span>Salir del sistema</span>
                </a>

            </div>
        </div>
    </nav>
</div>
NAV_EOF

ok "navigation-menu.blade.php actualizado"

# =============================================================================
# 3. PANEL/INDEX.BLADE.PHP — Dashboard con paquetes correctos y CU activos
# =============================================================================
info "Reescribiendo panel/index.blade.php..."

cat > resources/views/panel/index.blade.php << 'PANEL_EOF'
@extends('plantilla')

@section('title', 'Panel de Control')

@section('content')
<style>
    body { background:#0b1120; }
    .dark-page { background:#0b1120; color:#e2e8f0; min-height:100vh; }

    /* ── Cards de paquete ─────────────────────────────────────────── */
    .pkg-card {
        border-radius:14px;
        border:1px solid rgba(255,255,255,0.07);
        background:#111827;
        margin-bottom:1.4rem;
        overflow:hidden;
        box-shadow:0 4px 24px rgba(0,0,0,.35);
        transition:box-shadow .2s;
    }
    .pkg-card:hover { box-shadow:0 6px 32px rgba(0,0,0,.5); }

    .pkg-header {
        padding:.9rem 1.4rem;
        cursor:pointer;
        display:flex;
        align-items:center;
        justify-content:space-between;
        font-weight:700;
        font-size:.95rem;
        user-select:none;
        border-bottom:1px solid rgba(255,255,255,0.06);
    }
    .pkg-body { padding:1rem 1.4rem; }

    /* ── Items CU ─────────────────────────────────────────────────── */
    .cu-item {
        display:flex;
        align-items:center;
        gap:11px;
        padding:.6rem .85rem;
        border-radius:9px;
        margin-bottom:.35rem;
        text-decoration:none;
        font-size:.875rem;
        transition:background .15s, transform .1s;
        color:#cbd5e1;
    }
    a.cu-item:hover {
        background:rgba(255,255,255,0.06);
        transform:translateX(2px);
        color:#f1f5f9;
    }
    .cu-item.disabled {
        color:#334155;
        pointer-events:none;
        cursor:default;
    }
    .cu-badge {
        font-size:.68rem; font-weight:700;
        padding:2px 7px; border-radius:6px;
        min-width:38px; text-align:center;
        flex-shrink:0; letter-spacing:.02em;
    }
    .badge-done    { background:#22c55e1a; color:#4ade80; border:1px solid #4ade8044; }
    .badge-pending { background:#ffffff08; color:#475569; border:1px solid #33415530; }

    /* ── Headers por paquete ──────────────────────────────────────── */
    .hdr-p1 { background:linear-gradient(120deg,#1e3a8a 0%,#1d4ed8 100%); }
    .hdr-p2 { background:linear-gradient(120deg,#064e3b 0%,#059669 100%); }
    .hdr-p3 { background:linear-gradient(120deg,#7c2d12 0%,#ea580c 100%); }
    .hdr-p4 { background:linear-gradient(120deg,#4c1d95 0%,#7c3aed 100%); }

    .chevron { transition:transform .22s; }
    .pkg-header.collapsed .chevron { transform:rotate(-90deg); }

    .ciclo-tag {
        margin-left:auto; font-size:.62rem;
        color:#475569; flex-shrink:0;
    }

    /* ── Stats bar ────────────────────────────────────────────────── */
    .stat-pill {
        background:#1e293b;
        border:1px solid rgba(255,255,255,.08);
        border-radius:10px;
        padding:.55rem 1.1rem;
        font-size:.8rem;
        color:#94a3b8;
        display:inline-flex;
        align-items:center;
        gap:8px;
    }
    .stat-pill strong { color:#e2e8f0; font-size:.9rem; }
</style>

<div class="container-fluid px-4 dark-page">

    {{-- HEADER --}}
    <div class="d-flex align-items-end justify-content-between mt-4 mb-1">
        <div>
            <h2 class="fw-bold text-light mb-1" style="letter-spacing:-.01em;">
                🏢 Panel de Control
            </h2>
            <p class="text-secondary mb-0" style="font-size:.875rem;">
                Sistema de Gestión — Condominio San Diego · Módulos por paquete
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <span class="stat-pill"><i class="fas fa-circle-check" style="color:#4ade80;"></i> <strong>16</strong> CU implementados</span>
            <span class="stat-pill"><i class="fas fa-circle-dot" style="color:#60a5fa;"></i> <strong>4</strong> paquetes</span>
        </div>
    </div>
    <hr style="border-color:rgba(255,255,255,.07); margin:.75rem 0 1.5rem;">

    {{-- ══ PAQUETE 1 — Acceso y Seguridad: CU1 CU2 CU3 CU4 ══ --}}
    <div class="pkg-card">
        <div class="pkg-header hdr-p1 text-white"
             data-bs-toggle="collapse" data-bs-target="#pkg1" aria-expanded="true">
            <div class="d-flex align-items-center gap-3">
                <i class="fas fa-shield-halved fa-lg"></i>
                <span>Paquete 1 &mdash; Gestión de Acceso y Seguridad</span>
                <span style="font-size:.7rem;opacity:.7;font-weight:400;">CU1 · CU2 · CU3 · CU4</span>
            </div>
            <i class="fas fa-chevron-down chevron"></i>
        </div>
        <div id="pkg1" class="collapse show">
            <div class="pkg-body">
                <div class="row g-0">
                    <div class="col-md-6">
                        <a href="{{ route('login') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU1</span>
                            <i class="fas fa-sign-in-alt" style="color:#60a5fa;width:16px;text-align:center;"></i>
                            Iniciar sesión
                        </a>
                        <a href="{{ route('logout') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU2</span>
                            <i class="fas fa-sign-out-alt" style="color:#60a5fa;width:16px;text-align:center;"></i>
                            Cerrar sesión
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('users.index') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU3</span>
                            <i class="fas fa-users" style="color:#60a5fa;width:16px;text-align:center;"></i>
                            Gestionar usuarios
                        </a>
                        <a href="{{ route('roles.index') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU4</span>
                            <i class="fas fa-user-shield" style="color:#60a5fa;width:16px;text-align:center;"></i>
                            Gestionar roles y permisos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ PAQUETE 2 — Personas y Estructura: CU5 CU6 CU13 CU20 ══ --}}
    <div class="pkg-card">
        <div class="pkg-header hdr-p2 text-white"
             data-bs-toggle="collapse" data-bs-target="#pkg2" aria-expanded="true">
            <div class="d-flex align-items-center gap-3">
                <i class="fas fa-people-roof fa-lg"></i>
                <span>Paquete 2 &mdash; Gestión de Personas y Estructura</span>
                <span style="font-size:.7rem;opacity:.7;font-weight:400;">CU5 · CU6 · CU13 · CU20</span>
            </div>
            <i class="fas fa-chevron-down chevron"></i>
        </div>
        <div id="pkg2" class="collapse show">
            <div class="pkg-body">
                <div class="row g-0">
                    <div class="col-md-6">
                        <a href="{{ route('empleados.index') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU5</span>
                            <i class="fas fa-id-badge" style="color:#34d399;width:16px;text-align:center;"></i>
                            Gestionar empleados
                        </a>
                        <a href="{{ route('residentes.index') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU6</span>
                            <i class="fas fa-building" style="color:#34d399;width:16px;text-align:center;"></i>
                            Gestionar residentes
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('unidades.index') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU13</span>
                            <i class="fas fa-link" style="color:#34d399;width:16px;text-align:center;"></i>
                            Vincular residente con unidad habitacional
                        </a>
                        <a href="{{ route('propiedades.index') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU20</span>
                            <i class="fas fa-home" style="color:#34d399;width:16px;text-align:center;"></i>
                            Gestionar propiedades
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ PAQUETE 3 — Gestión Operativa: CU7 CU8 CU9 CU10 CU15 CU16 CU17 ══ --}}
    <div class="pkg-card">
        <div class="pkg-header hdr-p3 text-white"
             data-bs-toggle="collapse" data-bs-target="#pkg3" aria-expanded="true">
            <div class="d-flex align-items-center gap-3">
                <i class="fas fa-cogs fa-lg"></i>
                <span>Paquete 3 &mdash; Gestión Operativa del Condominio</span>
                <span style="font-size:.7rem;opacity:.7;font-weight:400;">CU7 · CU8 · CU9 · CU10 · CU15 · CU16 · CU17</span>
            </div>
            <i class="fas fa-chevron-down chevron"></i>
        </div>
        <div id="pkg3" class="collapse show">
            <div class="pkg-body">
                <div class="row g-0">
                    <div class="col-md-6">
                        <a href="{{ route('cuotas.index') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU7</span>
                            <i class="fas fa-dollar-sign" style="color:#fb923c;width:16px;text-align:center;"></i>
                            Gestionar cuotas y pagos
                        </a>
                        <a href="{{ route('reservas.index') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU8</span>
                            <i class="fas fa-calendar-check" style="color:#fb923c;width:16px;text-align:center;"></i>
                            Gestionar reservas de áreas comunes
                        </a>
                        <a href="{{ route('mantenimientos.index') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU9</span>
                            <i class="fas fa-tools" style="color:#fb923c;width:16px;text-align:center;"></i>
                            Gestionar mantenimientos
                        </a>
                        <a href="{{ route('visitas.index') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU10</span>
                            <i class="fas fa-door-open" style="color:#fb923c;width:16px;text-align:center;"></i>
                            Gestionar visitas al condominio
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('empresas.index') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU15</span>
                            <i class="fas fa-handshake" style="color:#fb923c;width:16px;text-align:center;"></i>
                            Registrar contratación de empresa externa
                        </a>
                        <a href="{{ route('incidencias.index') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU16</span>
                            <i class="fas fa-flag" style="color:#fb923c;width:16px;text-align:center;"></i>
                            Gestionar denuncias / incidencias
                        </a>
                        <span class="cu-item disabled">
                            <span class="cu-badge badge-pending">CU17</span>
                            <i class="fas fa-bell" style="width:16px;text-align:center;"></i>
                            Enviar notificaciones a residentes
                            <span class="ciclo-tag">Pendiente</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ PAQUETE 4 — Comunicación y Reportes: CU11 CU12 CU14 CU18 CU19 ══ --}}
    <div class="pkg-card">
        <div class="pkg-header hdr-p4 text-white"
             data-bs-toggle="collapse" data-bs-target="#pkg4" aria-expanded="true">
            <div class="d-flex align-items-center gap-3">
                <i class="fas fa-chart-bar fa-lg"></i>
                <span>Paquete 4 &mdash; Comunicación y Reportes</span>
                <span style="font-size:.7rem;opacity:.7;font-weight:400;">CU11 · CU12 · CU14 · CU18 · CU19</span>
            </div>
            <i class="fas fa-chevron-down chevron"></i>
        </div>
        <div id="pkg4" class="collapse show">
            <div class="pkg-body">
                <div class="row g-0">
                    <div class="col-md-6">
                        <a href="{{ route('comunicados.index') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU11</span>
                            <i class="fas fa-envelope" style="color:#a78bfa;width:16px;text-align:center;"></i>
                            Gestionar comunicados internos
                        </a>
                        <a href="{{ route('informes.administrativo') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU12</span>
                            <i class="fas fa-file-alt" style="color:#a78bfa;width:16px;text-align:center;"></i>
                            Generar informes administrativos
                        </a>
                        <a href="{{ route('informes.pagos') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU14</span>
                            <i class="fas fa-receipt" style="color:#a78bfa;width:16px;text-align:center;"></i>
                            Generar reportes de pagos
                        </a>
                    </div>
                    <div class="col-md-6">
                        <span class="cu-item disabled">
                            <span class="cu-badge badge-pending">CU18</span>
                            <i class="fas fa-exclamation-circle" style="width:16px;text-align:center;"></i>
                            Gestionar reclamos administrativos
                            <span class="ciclo-tag">Pendiente</span>
                        </span>
                        <span class="cu-item disabled">
                            <span class="cu-badge badge-pending">CU19</span>
                            <i class="fas fa-calendar-star" style="width:16px;text-align:center;"></i>
                            Gestionar eventos comunitarios
                            <span class="ciclo-tag">Pendiente</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
PANEL_EOF

ok "panel/index.blade.php actualizado"

# =============================================================================
# 4. VISTAS DE PROPIEDADES — Cambiar @extends('layouts.ap') → @extends('plantilla')
#    para que usen el mismo layout que el resto del sistema
# =============================================================================
info "Alineando layout de vistas de propiedades..."

for blade_file in resources/views/propiedades/*.blade.php; do
    if grep -q "layouts\.ap" "$blade_file" 2>/dev/null; then
        sed -i "s/@extends('layouts\.ap')/@extends('plantilla')/" "$blade_file"
        ok "  → $blade_file: layouts.ap → plantilla"
    fi
done

# =============================================================================
# 5. PLANTILLA.BLADE.PHP — Actualizar título (dice "Sistema ventas" → "Condominio San Diego")
# =============================================================================
info "Corrigiendo título en plantilla.blade.php..."

if grep -q "Sistema ventas" resources/views/plantilla.blade.php 2>/dev/null; then
    sed -i 's/Sistema ventas/Condominio San Diego/g' resources/views/plantilla.blade.php
    ok "Título de plantilla.blade.php actualizado"
fi

# =============================================================================
# 6. VERIFICACIÓN FINAL
# =============================================================================
echo ""
echo -e "${CYAN}══════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}  ✔  Script completado sin errores${NC}"
echo -e "${CYAN}══════════════════════════════════════════════════════${NC}"
echo ""
echo "  Cambios realizados:"
echo "  • routes/web.php           → CU20 = propiedades (sin duplicados)"
echo "  • navigation-menu.blade.php → sidebar limpio, paquetes correctos"
echo "  • panel/index.blade.php     → dashboard con todos los CU y paquetes"
echo "  • propiedades/*.blade.php   → layouts.ap → plantilla"
echo "  • plantilla.blade.php       → título corregido"
echo ""
echo "  Paquetes y CU según documentación:"
echo "  PKG1 · CU1  CU2  CU3  CU4"
echo "  PKG2 · CU5  CU6  CU13  CU20  ← propiedades en CU20 ✔"
echo "  PKG3 · CU7  CU8  CU9  CU10  CU15  CU16  CU17"
echo "  PKG4 · CU11  CU12  CU14  CU18  CU19"
echo ""
echo -e "${YELLOW}  Backup en: $BACKUP_DIR/${NC}"
echo ""
