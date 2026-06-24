<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <style>
            #sidenavAccordion {
                background: #0b1120;
                border-right: 0.5px solid rgba(255,255,255,0.07);
                font-family: inherit;
                display: flex;
                flex-direction: column;
                height: 100vh;
            }
            .sidebar-logo {
                padding: 18px 16px 14px;
                display: flex;
                align-items: center;
                gap: 10px;
                border-bottom: 0.5px solid rgba(255,255,255,0.07);
                flex-shrink: 0;
            }
            .sidebar-logo-icon {
                width: 32px; height: 32px;
                border-radius: 8px;
                background: #1e3a5f;
                display: flex; align-items: center; justify-content: center;
            }
            .sidebar-logo-icon i { font-size: 16px; color: #38bdf8; }
            .sidebar-logo-text { font-size: 13px; font-weight: 500; color: #e2e8f0; }
            .sidebar-logo-sub  { font-size: 11px; color: #475569; margin-top: 1px; }

            .sb-sidenav-menu {
                flex: 1;
                overflow-y: auto;
                padding: 8px 0 16px;
                scrollbar-width: thin;
                scrollbar-color: #1e293b transparent;
            }
            .sb-sidenav-menu::-webkit-scrollbar { width: 3px; }
            .sb-sidenav-menu::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 99px; }

            /* Section headings */
            .sb-sidenav-menu-heading {
                font-size: 10px;
                font-weight: 500;
                letter-spacing: 0.08em;
                text-transform: uppercase;
                padding: 16px 16px 4px;
                color: #334155;
            }
            .sb-sidenav-menu-heading.pkg {
                border-top: 0.5px solid rgba(255,255,255,0.05);
                margin-top: 8px;
                padding-top: 12px;
            }
            .pkg-badge {
                display: inline-flex; align-items: center;
                font-size: 10px; font-weight: 500;
                letter-spacing: 0.05em;
                padding: 2px 6px; border-radius: 4px;
            }
            .pkg-badge-blue   { color: #60a5fa; background: rgba(96,165,250,0.1); }
            .pkg-badge-green  { color: #34d399; background: rgba(52,211,153,0.1); }
            .pkg-badge-orange { color: #fb923c; background: rgba(251,146,60,0.1); }
            .pkg-badge-purple { color: #a78bfa; background: rgba(167,139,250,0.1); }

            /* Nav links */
            .nav-link {
                display: flex !important;
                align-items: center;
                gap: 10px;
                padding: 8px 16px !important;
                color: #94a3b8 !important;
                font-size: 12.5px;
                border-radius: 0 !important;
                transition: background 0.15s;
                position: relative;
                text-decoration: none;
            }
            .nav-link:hover, .nav-link.active {
                background: rgba(255,255,255,0.05) !important;
                color: #e2e8f0 !important;
            }
            .nav-link.active::before {
                content: '';
                position: absolute;
                left: 0; top: 4px; bottom: 4px;
                width: 2.5px;
                background: currentColor;
                border-radius: 0 2px 2px 0;
                opacity: 0.7;
            }
            .nav-icon-box {
                width: 28px; height: 28px;
                display: flex; align-items: center; justify-content: center;
                border-radius: 6px;
                background: rgba(255,255,255,0.04);
                flex-shrink: 0;
                font-size: 14px;
            }
            .nav-icon-box.sky    { color: #38bdf8; background: rgba(56,189,248,0.1); }
            .nav-icon-box.blue   { color: #60a5fa; }
            .nav-icon-box.green  { color: #34d399; }
            .nav-icon-box.orange { color: #fb923c; }
            .nav-icon-box.purple { color: #a78bfa; }
            .nav-icon-box.gray   { color: #64748b; }
            .nav-icon-box.red    { background: rgba(248,113,113,0.08); color: #f87171; }

            .nav-label { flex: 1; }
            .cu-tag {
                font-size: 10px; color: #334155;
                font-weight: 500; flex-shrink: 0;
            }
            .sb-sidenav-collapse-arrow {
                font-size: 12px; color: #334155;
                transition: transform 0.2s; flex-shrink: 0;
            }
            .nav-link[aria-expanded="true"] .sb-sidenav-collapse-arrow {
                transform: rotate(180deg);
            }

            /* Submenus */
            .sb-sidenav-menu-nested {
                background: #060d1a !important;
                border-left: 0.5px solid rgba(255,255,255,0.05);
                margin-left: 20px;
            }
            .sb-sidenav-menu-nested .nav-link {
                font-size: 12px !important;
                padding: 7px 16px 7px 12px !important;
                color: #64748b !important;
                gap: 8px;
            }
            .sb-sidenav-menu-nested .nav-link:hover {
                background: rgba(255,255,255,0.04) !important;
                color: #94a3b8 !important;
            }
            .sb-sidenav-menu-nested i { font-size: 13px; flex-shrink: 0; }

            /* Footer */
            .sidebar-footer {
                flex-shrink: 0;
                padding: 12px 16px;
                border-top: 0.5px solid rgba(255,255,255,0.07);
            }
            .nav-link.logout-link { color: #f87171 !important; }
            .nav-link.logout-link:hover { background: rgba(248,113,113,0.08) !important; }
        </style>

        {{-- Logo / Brand --}}
        <div class="sidebar-logo">
            <div class="sidebar-logo-icon"><i class="fas fa-building"></i></div>
            <div>
                <div class="sidebar-logo-text">ResidencialApp</div>
                <div class="sidebar-logo-sub">Panel de administración</div>
            </div>
        </div>

        <div class="sb-sidenav-menu">
            <div class="nav">

                {{-- SISTEMA --}}
                <div class="sb-sidenav-menu-heading">Sistema</div>
                <a class="nav-link" href="{{ route('panel') }}">
                    <div class="nav-icon-box sky"><i class="fas fa-tachometer-alt"></i></div>
                    <span class="nav-label">Panel de Control</span>
                </a>

                {{-- PKG 1 --}}
                <div class="sb-sidenav-menu-heading pkg">
                    <span class="pkg-badge pkg-badge-blue">PKG 1 · Acceso y Seguridad</span>
                </div>

                <a class="nav-link" href="{{ route('login') }}">
                    <div class="nav-icon-box blue"><i class="fas fa-sign-in-alt"></i></div>
                    <span class="nav-label">Iniciar sesión</span>
                    <span class="cu-tag">CU1</span>
                </a>
                <a class="nav-link" href="{{ route('logout') }}">
                    <div class="nav-icon-box blue"><i class="fas fa-sign-out-alt"></i></div>
                    <span class="nav-label">Cerrar sesión</span>
                    <span class="cu-tag">CU2</span>
                </a>
                <a class="nav-link" href="{{ route('users.index') }}">
                    <div class="nav-icon-box blue"><i class="fas fa-users"></i></div>
                    <span class="nav-label">Gestionar Usuarios</span>
                    <span class="cu-tag">CU3</span>
                </a>
                <a class="nav-link" href="{{ route('roles.index') }}">
                    <div class="nav-icon-box blue"><i class="fas fa-user-shield"></i></div>
                    <span class="nav-label">Roles y Permisos</span>
                    <span class="cu-tag">CU4</span>
                </a>

                {{-- PKG 2 --}}
                <div class="sb-sidenav-menu-heading pkg">
                    <span class="pkg-badge pkg-badge-green">PKG 2 · Personas y Estructura</span>
                </div>

                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                   data-bs-target="#collapseEmpleados" aria-expanded="false">
                    <div class="nav-icon-box green"><i class="fas fa-id-card"></i></div>
                    <span class="nav-label">Empleados</span>
                    <span class="cu-tag">CU5</span>
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseEmpleados" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="{{ route('empleados.index') }}">
                            <i class="fas fa-list"></i> Lista de Empleados
                        </a>
                        <a class="nav-link" href="{{ route('cargos.index') }}">
                            <i class="fas fa-briefcase"></i> Cargos
                        </a>
                    </nav>
                </div>

                <a class="nav-link" href="{{ route('residentes.index') }}">
                    <div class="nav-icon-box green"><i class="fas fa-building"></i></div>
                    <span class="nav-label">Gestionar Residentes</span>
                    <span class="cu-tag">CU6</span>
                </a>
                <a class="nav-link" href="{{ route('unidades.index') }}">
                    <div class="nav-icon-box green"><i class="fas fa-link"></i></div>
                    <span class="nav-label">Vincular Residente-Unidad</span>
                    <span class="cu-tag">CU13</span>
                </a>
                <a class="nav-link" href="{{ route('propiedades.index') }}">
                    <div class="nav-icon-box green"><i class="fas fa-home"></i></div>
                    <span class="nav-label">Gestionar Propiedades</span>
                    <span class="cu-tag">CU20</span>
                </a>

                {{-- PKG 3 --}}
                <div class="sb-sidenav-menu-heading pkg">
                    <span class="pkg-badge pkg-badge-orange">PKG 3 · Gestión Operativa</span>
                </div>

                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                   data-bs-target="#collapseCuotas" aria-expanded="false">
                    <div class="nav-icon-box orange"><i class="fas fa-dollar-sign"></i></div>
                    <span class="nav-label">Cuotas y Pagos</span>
                    <span class="cu-tag">CU7</span>
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseCuotas" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="{{ route('cuotas.index') }}"><i class="fas fa-list"></i> Cuotas</a>
                        <a class="nav-link" href="{{ route('tipos-cuotas.index') }}"><i class="fas fa-tags"></i> Tipos de Cuota</a>
                        <a class="nav-link" href="{{ route('pagos.index') }}"><i class="fas fa-money-bill"></i> Pagos</a>
                        <a class="nav-link" href="{{ route('multas.index') }}"><i class="fas fa-exclamation-triangle"></i> Multas</a>
                    </nav>
                </div>

                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                   data-bs-target="#collapseReservas" aria-expanded="false">
                    <div class="nav-icon-box orange"><i class="fas fa-calendar-check"></i></div>
                    <span class="nav-label">Áreas y Reservas</span>
                    <span class="cu-tag">CU8</span>
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseReservas" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="{{ route('areas-comunes.index') }}"><i class="fas fa-map-marked"></i> Áreas Comunes</a>
                        <a class="nav-link" href="{{ route('reservas.index') }}"><i class="fas fa-calendar"></i> Reservas</a>
                    </nav>
                </div>

                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                   data-bs-target="#collapseMantenimiento" aria-expanded="false">
                    <div class="nav-icon-box orange"><i class="fas fa-tools"></i></div>
                    <span class="nav-label">Mantenimientos</span>
                    <span class="cu-tag">CU9</span>
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseMantenimiento" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="{{ route('mantenimientos.index') }}"><i class="fas fa-list"></i> Mantenimientos</a>
                    </nav>
                </div>

                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                   data-bs-target="#collapseVisitas" aria-expanded="false">
                    <div class="nav-icon-box orange"><i class="fas fa-door-open"></i></div>
                    <span class="nav-label">Visitas</span>
                    <span class="cu-tag">CU10</span>
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseVisitas" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="{{ route('visitas.index') }}"><i class="fas fa-list"></i> Visitas</a>
                        <a class="nav-link" href="{{ route('visitas.panel-guardia') }}"><i class="fas fa-shield-alt"></i> Panel Guardia</a>
                        <a class="nav-link" href="{{ route('visitas.mostrar-validar-codigo') }}"><i class="fas fa-key"></i> Validar Código</a>
                    </nav>
                </div>

                <a class="nav-link" href="{{ route('empresas.index') }}">
                    <div class="nav-icon-box orange"><i class="fas fa-handshake"></i></div>
                    <span class="nav-label">Empresas Externas</span>
                    <span class="cu-tag">CU15</span>
                </a>
                <a class="nav-link" href="{{ route('incidencias.index') }}">
                    <div class="nav-icon-box orange"><i class="fas fa-flag"></i></div>
                    <span class="nav-label">Denuncias / Incidencias</span>
                    <span class="cu-tag">CU16</span>
                </a>
                <a class="nav-link" href="{{ route('notificaciones.index') }}">
                    <div class="nav-icon-box orange"><i class="fas fa-bell"></i></div>
                    <span class="nav-label">Notificaciones</span>
                    <span class="cu-tag">CU17</span>
                </a>

                {{-- PKG 4 --}}
                <div class="sb-sidenav-menu-heading pkg">
                    <span class="pkg-badge pkg-badge-purple">PKG 4 · Comunicación y Reportes</span>
                </div>

                <a class="nav-link" href="{{ route('comunicados.index') }}">
                    <div class="nav-icon-box purple"><i class="fas fa-envelope"></i></div>
                    <span class="nav-label">Comunicados Internos</span>
                    <span class="cu-tag">CU11</span>
                </a>
                <a class="nav-link" href="{{ route('informes.administrativo') }}">
                    <div class="nav-icon-box purple"><i class="fas fa-file-alt"></i></div>
                    <span class="nav-label">Informes Administrativos</span>
                    <span class="cu-tag">CU12</span>
                </a>
                <a class="nav-link" href="{{ route('informes.pagos') }}">
                    <div class="nav-icon-box purple"><i class="fas fa-receipt"></i></div>
                    <span class="nav-label">Reportes de Pagos</span>
                    <span class="cu-tag">CU14</span>
                </a>
                <a class="nav-link" href="{{ route('reclamos.index') }}">
                    <div class="nav-icon-box purple"><i class="fas fa-exclamation-circle"></i></div>
                    <span class="nav-label">Reclamos Administrativos</span>
                    <span class="cu-tag">CU18</span>
                </a>
                <a class="nav-link" href="{{ route('eventos.index') }}">
                    <div class="nav-icon-box purple"><i class="fas fa-calendar-star"></i></div>
                    <span class="nav-label">Eventos Comunitarios</span>
                    <span class="cu-tag">CU19</span>
                </a>

                {{-- SISTEMA INFERIOR --}}
                <div class="sb-sidenav-menu-heading pkg">Sistema</div>
                <a class="nav-link" href="{{ route('bitacora.index') }}">
                    <div class="nav-icon-box gray"><i class="fas fa-book"></i></div>
                    <span class="nav-label">Bitácora</span>
                </a>

            </div>
        </div>

        {{-- Footer con botón Salir --}}
        <div class="sidebar-footer">
            <a class="nav-link logout-link" href="{{ route('logout') }}">
                <div class="nav-icon-box red"><i class="fa fa-sign-out"></i></div>
                <span class="nav-label">Salir del sistema</span>
            </a>
        </div>
    </nav>
</div>