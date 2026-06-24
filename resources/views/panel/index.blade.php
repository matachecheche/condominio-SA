@extends('plantilla')

@section('title', 'Panel de Control')

@section('content')
<style>
    .panel-page {
        background: #0b1120;
        min-height: 100vh;
        color: #e2e8f0;
        padding: 2rem 1.5rem;
    }

    /* ── Header ───────────────────────────────────────────────────── */
    .panel-title {
        font-size: 1.65rem;
        font-weight: 800;
        letter-spacing: -.02em;
        color: #f1f5f9;
        line-height: 1.2;
    }
    .panel-subtitle {
        font-size: .82rem;
        color: #64748b;
        margin-top: .25rem;
    }
    .stat-pill {
        background: #1e293b;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 10px;
        padding: .45rem 1rem;
        font-size: .78rem;
        color: #94a3b8;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        white-space: nowrap;
    }
    .stat-pill strong { color: #f1f5f9; font-size: .88rem; }

    /* ── Cards de paquete ─────────────────────────────────────────── */
    .pkg-card {
        border-radius: 16px;
        border: 1px solid rgba(255,255,255,.07);
        background: #111827;
        margin-bottom: 1.25rem;
        overflow: hidden;
        box-shadow: 0 2px 20px rgba(0,0,0,.3);
        transition: box-shadow .2s, border-color .2s;
    }
    .pkg-card:hover {
        box-shadow: 0 4px 32px rgba(0,0,0,.5);
        border-color: rgba(255,255,255,.12);
    }

    .pkg-header {
        padding: 1rem 1.4rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-weight: 700;
        font-size: .92rem;
        user-select: none;
        border-bottom: 1px solid rgba(255,255,255,.06);
        transition: filter .15s;
    }
    .pkg-header:hover { filter: brightness(1.08); }

    .pkg-header-left {
        display: flex;
        align-items: center;
        gap: .85rem;
        flex-wrap: wrap;
    }
    .pkg-icon-wrap {
        width: 36px; height: 36px;
        border-radius: 9px;
        background: rgba(255,255,255,.12);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .pkg-cu-tags {
        font-size: .67rem;
        font-weight: 400;
        opacity: .6;
        letter-spacing: .03em;
    }

    .pkg-body { padding: 1.1rem 1.25rem .75rem; }

    /* ── Items CU ─────────────────────────────────────────────────── */
    .cu-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: .6rem .85rem;
        border-radius: 10px;
        margin-bottom: .3rem;
        text-decoration: none;
        font-size: .84rem;
        transition: background .15s, transform .1s, color .1s;
        color: #94a3b8;
        border: 1px solid transparent;
    }
    a.cu-item:hover {
        background: rgba(255,255,255,.06);
        border-color: rgba(255,255,255,.07);
        transform: translateX(3px);
        color: #f1f5f9;
    }
    .cu-item.disabled {
        color: #2d3f55;
        pointer-events: none;
        cursor: default;
    }

    .cu-badge {
        font-size: .65rem;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 6px;
        min-width: 38px;
        text-align: center;
        flex-shrink: 0;
        letter-spacing: .02em;
    }
    .badge-done    { background: #22c55e18; color: #4ade80; border: 1px solid #4ade8033; }
    .badge-pending { background: #ffffff06; color: #3b4f65; border: 1px solid #2d3f5530; }

    .cu-icon {
        width: 18px;
        text-align: center;
        flex-shrink: 0;
        font-size: .85rem;
    }

    /* ── Separador de columnas ────────────────────────────────────── */
    .col-divider {
        border-left: 1px solid rgba(255,255,255,.05);
    }
    @media (max-width: 767px) {
        .col-divider { border-left: none; border-top: 1px solid rgba(255,255,255,.05); padding-top: .5rem; margin-top: .5rem; }
    }

    /* ── Headers por paquete ──────────────────────────────────────── */
    .hdr-p1 { background: linear-gradient(120deg, #1e3a8a 0%, #2563eb 100%); }
    .hdr-p2 { background: linear-gradient(120deg, #064e3b 0%, #059669 100%); }
    .hdr-p3 { background: linear-gradient(120deg, #7c2d12 0%, #ea580c 100%); }
    .hdr-p4 { background: linear-gradient(120deg, #4c1d95 0%, #7c3aed 100%); }

    .chevron { transition: transform .22s; flex-shrink: 0; }
    .pkg-header[aria-expanded="false"] .chevron { transform: rotate(-90deg); }

    /* ── Divisor header ───────────────────────────────────────────── */
    .panel-divider {
        border: none;
        border-top: 1px solid rgba(255,255,255,.06);
        margin: .75rem 0 1.75rem;
    }

    /* ── Responsive ───────────────────────────────────────────────── */
    @media (max-width: 576px) {
        .panel-page { padding: 1.25rem .85rem; }
        .panel-title { font-size: 1.3rem; }
        .pkg-header { font-size: .85rem; padding: .85rem 1rem; }
        .pkg-body { padding: .85rem 1rem .6rem; }
        .pkg-cu-tags { display: none; }
        .stat-pill { font-size: .73rem; padding: .4rem .8rem; }
    }
</style>

<div class="container-fluid panel-page">

    {{-- ── HEADER ──────────────────────────────────────────────────── --}}
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-end justify-content-between gap-3 mt-2 mb-1">
        <div>
            <div class="panel-title">🏡 Panel de Control</div>
            <div class="panel-subtitle">Sistema de Gestión — Condominio San Diego · Módulos por paquete</div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <span class="stat-pill">
                <i class="fas fa-circle-check" style="color:#4ade80;"></i>
                <strong>16</strong> CU implementados
            </span>
            <span class="stat-pill">
                <i class="fas fa-circle-dot" style="color:#60a5fa;"></i>
                <strong>4</strong> paquetes
            </span>
        </div>
    </div>
    <hr class="panel-divider">

    {{-- ══ PAQUETE 1 — Acceso y Seguridad ══ --}}
    <div class="pkg-card">
        <div class="pkg-header hdr-p1 text-white"
             data-bs-toggle="collapse" data-bs-target="#pkg1"
             aria-expanded="true" aria-controls="pkg1">
            <div class="pkg-header-left">
                <div class="pkg-icon-wrap"><i class="fas fa-shield-halved"></i></div>
                <div>
                    <div>Paquete 1 — Gestión de Acceso y Seguridad</div>
                    <div class="pkg-cu-tags">CU1 · CU2 · CU3 · CU4</div>
                </div>
            </div>
            <i class="fas fa-chevron-down chevron"></i>
        </div>
        <div id="pkg1" class="collapse show">
            <div class="pkg-body">
                <div class="row g-0">
                    <div class="col-md-6 pe-md-2">
                        <a href="{{ route('login') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU1</span>
                            <i class="fas fa-sign-in-alt cu-icon" style="color:#60a5fa;"></i>
                            Iniciar sesión
                        </a>
                        <a href="{{ route('logout') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU2</span>
                            <i class="fas fa-sign-out-alt cu-icon" style="color:#60a5fa;"></i>
                            Cerrar sesión
                        </a>
                    </div>
                    <div class="col-md-6 ps-md-2 col-divider">
                        <a href="{{ route('users.index') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU3</span>
                            <i class="fas fa-users cu-icon" style="color:#60a5fa;"></i>
                            Gestionar usuarios
                        </a>
                        <a href="{{ route('roles.index') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU4</span>
                            <i class="fas fa-user-shield cu-icon" style="color:#60a5fa;"></i>
                            Gestionar roles y permisos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ PAQUETE 2 — Personas y Estructura ══ --}}
    <div class="pkg-card">
        <div class="pkg-header hdr-p2 text-white"
             data-bs-toggle="collapse" data-bs-target="#pkg2"
             aria-expanded="true" aria-controls="pkg2">
            <div class="pkg-header-left">
                <div class="pkg-icon-wrap"><i class="fas fa-people-roof"></i></div>
                <div>
                    <div>Paquete 2 — Gestión de Personas y Estructura</div>
                    <div class="pkg-cu-tags">CU5 · CU6 · CU13 · CU20</div>
                </div>
            </div>
            <i class="fas fa-chevron-down chevron"></i>
        </div>
        <div id="pkg2" class="collapse show">
            <div class="pkg-body">
                <div class="row g-0">
                    <div class="col-md-6 pe-md-2">
                        <a href="{{ route('empleados.index') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU5</span>
                            <i class="fas fa-id-badge cu-icon" style="color:#34d399;"></i>
                            Gestionar empleados
                        </a>
                        <a href="{{ route('residentes.index') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU6</span>
                            <i class="fas fa-building cu-icon" style="color:#34d399;"></i>
                            Gestionar residentes
                        </a>
                    </div>
                    <div class="col-md-6 ps-md-2 col-divider">
                        <a href="{{ route('unidades.index') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU13</span>
                            <i class="fas fa-link cu-icon" style="color:#34d399;"></i>
                            Vincular residente con unidad habitacional
                        </a>
                        <a href="{{ route('propiedades.index') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU20</span>
                            <i class="fas fa-home cu-icon" style="color:#34d399;"></i>
                            Gestionar propiedades
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ PAQUETE 3 — Gestión Operativa ══ --}}
    <div class="pkg-card">
        <div class="pkg-header hdr-p3 text-white"
             data-bs-toggle="collapse" data-bs-target="#pkg3"
             aria-expanded="true" aria-controls="pkg3">
            <div class="pkg-header-left">
                <div class="pkg-icon-wrap"><i class="fas fa-cogs"></i></div>
                <div>
                    <div>Paquete 3 — Gestión Operativa del Condominio</div>
                    <div class="pkg-cu-tags">CU7 · CU8 · CU9 · CU10 · CU15 · CU16 · CU17</div>
                </div>
            </div>
            <i class="fas fa-chevron-down chevron"></i>
        </div>
        <div id="pkg3" class="collapse show">
            <div class="pkg-body">
                <div class="row g-0">
                    <div class="col-md-6 pe-md-2">
                        <a href="{{ route('cuotas.index') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU7</span>
                            <i class="fas fa-dollar-sign cu-icon" style="color:#fb923c;"></i>
                            Gestionar cuotas y pagos
                        </a>
                        <a href="{{ route('reservas.index') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU8</span>
                            <i class="fas fa-calendar-check cu-icon" style="color:#fb923c;"></i>
                            Gestionar reservas de áreas comunes
                        </a>
                        <a href="{{ route('mantenimientos.index') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU9</span>
                            <i class="fas fa-tools cu-icon" style="color:#fb923c;"></i>
                            Gestionar mantenimientos
                        </a>
                        <a href="{{ route('visitas.index') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU10</span>
                            <i class="fas fa-door-open cu-icon" style="color:#fb923c;"></i>
                            Gestionar visitas al condominio
                        </a>
                    </div>
                    <div class="col-md-6 ps-md-2 col-divider">
                        <a href="{{ route('empresas.index') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU15</span>
                            <i class="fas fa-handshake cu-icon" style="color:#fb923c;"></i>
                            Registrar contratación de empresa externa
                        </a>
                        <a href="{{ route('incidencias.index') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU16</span>
                            <i class="fas fa-flag cu-icon" style="color:#fb923c;"></i>
                            Gestionar denuncias / incidencias
                        </a>
                        <a href="{{ route('notificaciones.index') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU17</span>
                            <i class="fas fa-bell cu-icon" style="color:#fb923c;"></i>
                            Enviar notificaciones a residentes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ PAQUETE 4 — Comunicación y Reportes ══ --}}
    <div class="pkg-card">
        <div class="pkg-header hdr-p4 text-white"
             data-bs-toggle="collapse" data-bs-target="#pkg4"
             aria-expanded="true" aria-controls="pkg4">
            <div class="pkg-header-left">
                <div class="pkg-icon-wrap"><i class="fas fa-chart-bar"></i></div>
                <div>
                    <div>Paquete 4 — Comunicación y Reportes</div>
                    <div class="pkg-cu-tags">CU11 · CU12 · CU14 · CU18 · CU19</div>
                </div>
            </div>
            <i class="fas fa-chevron-down chevron"></i>
        </div>
        <div id="pkg4" class="collapse show">
            <div class="pkg-body">
                <div class="row g-0">
                    <div class="col-md-6 pe-md-2">
                        <a href="{{ route('comunicados.index') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU11</span>
                            <i class="fas fa-envelope cu-icon" style="color:#a78bfa;"></i>
                            Gestionar comunicados internos
                        </a>
                        <a href="{{ route('informes.administrativo') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU12</span>
                            <i class="fas fa-file-alt cu-icon" style="color:#a78bfa;"></i>
                            Generar informes administrativos
                        </a>
                        <a href="{{ route('informes.pagos') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU14</span>
                            <i class="fas fa-receipt cu-icon" style="color:#a78bfa;"></i>
                            Generar reportes de pagos
                        </a>
                    </div>
                    <div class="col-md-6 ps-md-2 col-divider">
                        <a href="{{ route('reclamos.index') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU18</span>
                            <i class="fas fa-exclamation-circle cu-icon" style="color:#a78bfa;"></i>
                            Gestionar reclamos administrativos
                        </a>
                        <a href="{{ route('eventos.index') }}" class="cu-item">
                            <span class="cu-badge badge-done">CU19</span>
                            <i class="fas fa-calendar-star cu-icon" style="color:#a78bfa;"></i>
                            Gestionar eventos comunitarios
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection