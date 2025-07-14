@extends('layouts.app')

@section('template_title')
    Paquetes
@endsection

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">{{ __('Paquetes') }}</h2>
        <a href="{{ route('paquetes.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>{{ __('Crear Nuevo') }}
        </a>
    </div>

    @if ($message = Session::get('success'))
        <div class="alert alert-success mb-4">
            <p class="mb-0">{{ $message }}</p>
        </div>
    @endif

    <div class="row g-4">
        @foreach ($paquetes as $paquete)
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-semibold mb-2">{{ $paquete->nombre }}</h5>
                        <p class="card-text text-muted mb-2" style="font-size: 0.95em;">{{ $paquete->descripcion }}</p>
                        <div class="mb-3">
                            <span class="badge bg-primary fs-6">
                                <i class="bi bi-currency-dollar"></i>{{ number_format($paquete->precio, 2) }} MXN
                            </span>
                        </div>
                        <div class="mt-auto d-flex gap-2">

                            <a href="{{ route('paquetes.edit', $paquete->id) }}" class="btn btn-outline-success btn-sm" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('paquetes.destroy', $paquete->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar"
                                    onclick="event.preventDefault(); if(confirm('¿Seguro que deseas eliminar este paquete?')) this.closest('form').submit();">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4">
        {!! $paquetes->withQueryString()->links() !!}
    </div>
</div>
@endsection
