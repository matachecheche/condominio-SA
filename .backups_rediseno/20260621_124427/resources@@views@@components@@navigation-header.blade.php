
<nav class="sb-topnav navbar navbar-expand navbar-dark bg-black shadow">

    <!-- BOTÓN SIDEBAR -->
    <button class="btn btn-link btn-sm ms-3 me-2 text-white" id="sidebarToggle" title="Abrir menú">
        <i class="fas fa-bars fs-5"></i>
    </button>

    <!-- LOGO -->
    <a class="navbar-brand fw-bold text-uppercase" href="{{ route('panel') }}">
        🏢 Condominio San Diego
    </a>

    <!-- BUSCADOR (OCULTO) -->
    <form class="d-none d-md-inline-block form-inline ms-auto me-3 my-2">
        <div class="input-group" hidden>
            <input class="form-control bg-dark text-white border-secondary" type="text" placeholder="Buscar..." />
            <button class="btn btn-outline-light" type="button">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </form>

    <!-- USUARIO -->
    <ul class="navbar-nav ms-auto me-3 me-lg-4">
        <li class="nav-item dropdown">

            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" id="navbarDropdown"
               href="#" role="button" data-bs-toggle="dropdown">

                <i class="fas fa-user-circle fs-5"></i>
                <span class="d-none d-md-inline fw-semibold">
                    {{ Auth::user()->name ?? 'Usuario' }}
                </span>
            </a>

            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">

                <li>
                    <a class="dropdown-item" href="#">
                        <i class="fas fa-user me-2"></i> Perfil
                    </a>
                </li>

                <li>
                    <a class="dropdown-item" href="#">
                        <i class="fas fa-cog me-2"></i> Configuración
                    </a>
                </li>

                <li>
                    <a class="dropdown-item" href="#">
                        <i class="fas fa-list me-2"></i> Actividad
                    </a>
                </li>

                <li><hr class="dropdown-divider" /></li>

                <li>
                    <a class="dropdown-item text-danger" href="{{ route('logout') }}">
                        <i class="fas fa-sign-out-alt me-2"></i> Cerrar sesión
                    </a>
                </li>

            </ul>
        </li>
    </ul>

</nav>

