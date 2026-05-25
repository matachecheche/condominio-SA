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
