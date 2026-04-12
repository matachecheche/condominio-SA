<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">

    <button class="btn btn-link btn-sm ms-3 me-2" id="sidebarToggle" href="#" title="Abrir menú">
        <i class="fas fa-bars"></i>
    </button>


    <a class="navbar-brand" href="{{ route('panel') }}" style ="font-size: 1.2rem; font-weight: bold;"><i class="fas fa-hotel"></i>
        Condominio San Diego
    </a>

    <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
        <div class="input-group" hidden>
            <input class="form-control" type="text" placeholder="Buscar..." aria-label="Buscar..." aria-describedby="btnNavbarSearch" />
            <button class="btn btn-primary" id="btnNavbarSearch" type="button"><i class="fas fa-search"></i></button>
        </div>
    </form>

    @php
        $usuario = Auth::user();
        $residenteId = $usuario->residente_id ?? null;
        $notificacionesNoLeidas = \App\Models\Notificacion::where('leida', false)
            ->where(function ($query) use ($residenteId) {
                $query->whereNull('residente_id')
                    ->orWhere('residente_id', $residenteId);
            })->count();
    @endphp


     
    <ul class="navbar-nav ms-auto me-3 me-lg-4 align-items-center">
        {{-- Notificación --}}
        <li class="nav-item position-relative me-3">
            <a class="nav-link" href="{{ route('notificaciones.index') }}" title="Notificaciones">
                <i class="fas fa-bell fa-lg"></i>
                @if($notificacionesNoLeidas > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        {{ $notificacionesNoLeidas }}
                        <span class="visually-hidden">notificaciones no leídas</span>
                    </span>
                @endif
            </a>
        </li>

        {{-- Perfil --}}
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user fa-fw"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="#">Perfil</a></li>
                <li><a class="dropdown-item" href="#">Configuración</a></li>
                <li><a class="dropdown-item" href="#">Registro de Actividad</a></li>
                <li><hr class="dropdown-divider" /></li>
                <li><a class="dropdown-item" href="{{ route('logout') }}">Cerrar sesión</a></li>
            </ul>
        </li>
    </ul>

</nav>
