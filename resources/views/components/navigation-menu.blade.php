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

                <a class="nav-link d-flex align-items-center gap-2" href="{{ route('notificaciones.index') }}" style="color:#cbd5e1;">
                    <div class="sb-nav-link-icon" style="color:#fb923c;"><i class="fas fa-bell"></i></div>
                    <span>CU17 · Notificaciones</span>
                </a>

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

                <a class="nav-link d-flex align-items-center gap-2" href="{{ route('reclamos.index') }}" style="color:#cbd5e1;">
                    <div class="sb-nav-link-icon" style="color:#a78bfa;"><i class="fas fa-exclamation-circle"></i></div>
                    <span>CU18 · Reclamos Administrativos</span>
                </a>

                <a class="nav-link d-flex align-items-center gap-2" href="{{ route('eventos.index') }}" style="color:#cbd5e1;">
                    <div class="sb-nav-link-icon" style="color:#a78bfa;"><i class="fas fa-calendar-star"></i></div>
                    <span>CU19 · Eventos Comunitarios</span>
                </a>

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
