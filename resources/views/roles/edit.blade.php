@extends('layouts.ap')

@section('title','Editar rol')

@push('css')
<style>
    .btn {
        transition: all 0.2s ease-in-out;
    }
    .btn:hover {
        transform: translateY(-1px);
    }
    .permission-card {
        transition: background-color 0.2s ease;
        border-radius: 0.375rem;
    }
    .permission-card:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mt-3 mb-4 gap-2">
        <div>
            <h1 class="fw-bold text-dark mb-1 fs-3">Editar Rol</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 bg-transparent p-0">
                    <li class="breadcrumb-item"><a href="{{ route('panel') }}" class="text-decoration-none text-muted"><i class="fas fa-home me-1"></i>Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('roles.index') }}" class="text-decoration-none text-muted">Roles</a></li>
                    <li class="breadcrumb-item active text-primary fw-medium" aria-current="page">Editar rol</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary btn-sm px-3 d-inline-flex align-items-center gap-1.5 shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Volver a roles</span>
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4 p-md-5">
            
            <div class="alert alert-info border-0 shadow-sm d-flex align-items-center gap-2 mb-4" style="background-color: #e0f2fe; color: #0369a1;" role="alert">
                <i class="fas fa-info-circle fs-5 text-sky-600"></i>
                <span class="fw-medium small">Nota: Los roles agrupan un conjunto ordenado de permisos específicos para los usuarios del sistema.</span>
            </div>

            <form action="{{ route('roles.update',['role'=>$role]) }}" method="post">
                @method('PATCH')
                @csrf
                
                <div class="row align-items-center mb-4 g-3">
                    <div class="col-12 col-md-auto">
                        <label for="name" class="form-label fw-semibold text-secondary mb-0">Nombre del rol:</label>
                    </div>
                    <div class="col-12 col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="fas fa-user-shield"></i></span>
                            <input type="text" name="name" id="name" class="form-control px-3" placeholder="Ej. Administrador" value="{{old('name',$role->name)}}">
                        </div>
                        @error('name')
                        <div class="text-danger small mt-1 d-flex align-items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </div>
                        @enderror
                    </div>
                </div>

                <hr class="text-black-50 my-4 opacity-10">

                <div class="col-12 mb-2">
                    <h5 class="text-primary fw-semibold mb-3">
                        <i class="fas fa-list-check me-2"></i>Modificar Permisos del Rol
                    </h5>
                    <p class="text-muted small mb-3">Marque o desmarque los permisos que corresponden a este rol:</p>
                    
                    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3 px-1">
                        @foreach ($permisos as $item)
                            @if ( in_array($item->id, $role->permissions->pluck('id')->toArray() ) )
                            <div class="col">
                                <div class="form-check p-2 ps-4 permission-card border border-transparent">
                                    <input checked type="checkbox" name="permission[]" id="{{$item->id}}" class="form-check-input cursor-pointer" value="{{$item->id}}">
                                    <label for="{{$item->id}}" class="form-check-label text-dark fw-medium cursor-pointer ms-1">{{$item->name}}</label>
                                </div>
                            </div>
                            @else
                            <div class="col">
                                <div class="form-check p-2 ps-4 permission-card border border-transparent">
                                    <input type="checkbox" name="permission[]" id="{{$item->id}}" class="form-check-input cursor-pointer" value="{{$item->id}}">
                                    <label for="{{$item->id}}" class="form-check-label text-dark fw-medium cursor-pointer ms-1">{{$item->name}}</label>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                @error('permission')
                <div class="text-danger small mt-2 mb-4 d-flex align-items-center gap-1">
                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                </div>
                @enderror

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top border-light">
                    <button type="reset" class="btn btn-light border px-4">Reiniciar</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm fw-medium">
                        <i class="fas fa-save me-1.5"></i>Actualizar Rol
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
@endpush