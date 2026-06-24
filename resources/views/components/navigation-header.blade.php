<nav class="sb-topnav navbar navbar-expand navbar-dark shadow-sm border-bottom" 
     style="background: #090d16; border-color: rgba(255, 255, 255, 0.05) !important;">

    <button class="btn btn-link btn-sm ms-3 me-2" id="sidebarToggle" title="Abrir menú" style="color: #94a3b8;">
        <i class="fas fa-bars fs-5"></i>
    </button>

    <a class="navbar-brand fw-bold text-uppercase fs-6 tracking-wider text-white" href="{{ route('panel') }}">
        🏡 Condominio San Diego
    </a>

    <form action="{{ route('buscar.global') }}" method="GET" class="d-none d-md-inline-block form-inline ms-auto me-3 my-2">
        <div class="input-group">
            <input class="form-control text-white border-secondary small" 
                   style="background: #111827;"
                   name="query" 
                   type="text" 
                   placeholder="Buscar casa, residente..." 
                   value="{{ request('query') }}"
                   required />
            <button class="btn btn-primary" type="submit">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </form>

    <ul class="navbar-nav ms-auto me-3 me-lg-4">
        <li class="nav-item dropdown">

            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 px-3 py-1 rounded-pill" 
               id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" 
               style="color: #cbd5e1; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);">
                
                <i class="fas fa-user-circle fs-5" style="color: #38bdf8;"></i>
                <span class="d-none d-md-inline fw-medium small">
                    {{ Auth::user()->name ?? 'Usuario' }}
                </span>
            </a>

            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 py-2" 
                style="background: #111827; min-width: 180px; border: 1px solid rgba(255,255,255,0.08) !important;">

                <li>
                    <a class="dropdown-item py-2 small text-secondary d-flex align-items-center" href="#" 
                       onmouseover="this.style.background='rgba(255,255,255,0.05)'; this.style.color='#f1f5f9';" 
                       onmouseout="this.style.background='transparent'; this.style.color='#94a3b8';" style="color: #94a3b8;">
                        <i class="fas fa-user me-2 text-muted" style="width: 16px;"></i> Perfil
                    </a>
                </li>

                <li>
                    <a class="dropdown-item py-2 small text-secondary d-flex align-items-center" href="#" 
                       onmouseover="this.style.background='rgba(255,255,255,0.05)'; this.style.color='#f1f5f9';" 
                       onmouseout="this.style.background='transparent'; this.style.color='#94a3b8';" style="color: #94a3b8;">
                        <i class="fas fa-cog me-2 text-muted" style="width: 16px;"></i> Configuración
                    </a>
                </li>

                <li>
                    <a class="dropdown-item py-2 small text-secondary d-flex align-items-center" href="{{ route('bitacora.index') }}" 
                       onmouseover="this.style.background='rgba(255,255,255,0.05)'; this.style.color='#f1f5f9';" 
                       onmouseout="this.style.background='transparent'; this.style.color='#94a3b8';" style="color: #94a3b8;">
                        <i class="fas fa-list me-2 text-muted" style="width: 16px;"></i> Actividad
                    </a>
                </li>

                <li><hr class="dropdown-divider" style="border-color: rgba(255,255,255,0.08);" /></li>

                <li>
                    <a class="dropdown-item py-2 small d-flex align-items-center" href="{{ route('logout') }}" 
                       onmouseover="this.style.background='rgba(248,113,113,0.1)';" 
                       onmouseout="this.style.background='transparent';" style="color: #f87171;">
                        <i class="fas fa-sign-out-alt me-2" style="width: 16px;"></i> Cerrar sesión
                    </a>
                </li>

            </ul>
        </li>
    </ul>

</nav>