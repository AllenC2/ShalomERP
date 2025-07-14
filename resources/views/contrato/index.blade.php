@extends('layouts.app')

@section('template_title')
    Contratos
@endsection

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fa fa-file-contract me-2"></i> {{ __('Contratos') }}
                    </h5>
                    <a href="{{ route('contratos.create') }}" class="btn btn-light btn-sm">
                        <i class="fa fa-plus me-1"></i> {{ __('Crear Nuevo') }}
                    </a>
                </div>
                <!-- Formulario de búsqueda -->
                <div class="card-body pt-3 pb-0">
                    <form method="GET" action="{{ route('contratos.index') }}" class="row g-2 align-items-center mb-3">


                        <div class="col-auto">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="solo_activos" id="solo_activos" value="1" {{ request('solo_activos', '1') ? 'checked' : '' }}>
                                <label class="form-check-label" for="solo_activos">
                                    Solo Activos
                                </label>
                            </div>
                        </div>
                        <div class="col-auto">
                            <input type="text" name="search" class="form-control" placeholder="Buscar cliente..." value="{{ request('search') }}" id="searchInput">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fa fa-search"></i> Buscar
                            </button>
                        </div>

                        @if(request('search'))
                        <div class="col-auto">
                            <a href="{{ route('contratos.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
                        </div>
                        @endif
                        

                    </form>
                </div>
                @if ($message = Session::get('success'))
                    <div class="alert alert-success m-4">
                        <p class="mb-0">{{ $message }}</p>
                    </div>
                @endif
                <div class="card-body bg-white">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Cliente</th>
                                    <th scope="col">Paquete</th>
                                    <th scope="col">Pagado</th>
                                    <th scope="col">Estado</th>
                                    <th scope="col" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($contratos as $contrato)
                                    <tr>
                                        <td>{{ $contrato->id }}</td>
                                        <td>{{ $contrato->cliente->nombre }} {{ $contrato->cliente->apellido }}</td>
                                        <td>{{ $contrato->paquete->nombre }}</td>
                                        <td>
                                            <div class="d-flex align-items-center" style="min-width:140px;">
                                                <span class="fw-bold" style="min-width:40px;">{{ $contrato->porcentaje_pagado }}%</span>
                                                <div class="progress flex-grow-1 me-2" style="height: 12px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $contrato->porcentaje_pagado }}%;" aria-valuenow="{{ $contrato->porcentaje_pagado }}" aria-valuemin="0" aria-valuemax="100">
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ 
                                                $contrato->estado == 'Activo' ? 'primary' : 
                                                ($contrato->estado == 'Finalizado' ? 'success' : 
                                                ($contrato->estado == 'Cancelado' ? 'danger' : 'secondary')) 
                                            }}">
                                                {{ $contrato->estado }}
                                            </span>
                                        </td>
                                        <td class="text-center">

                                            <div class="btn-group" role="group">
                                                <a class="btn btn-sm btn-outline-primary" href="{{ route('contratos.show', $contrato->id) }}">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a class="btn btn-sm btn-outline-success" href="{{ route('contratos.edit', $contrato->id) }}">
                                                    <i class="bi bi-pencil"></i>
                                                </a>

                                            </div>

                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {!! $contratos->withQueryString()->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    function fetchContratos() {
        var search = $('#searchInput').val();
        var soloActivos = $('#solo_activos').is(':checked') ? 1 : 0;
        $.ajax({
            url: "{{ route('contratos.index') }}",
            type: 'GET',
            data: {
                search: search,
                solo_activos: soloActivos
            },
            success: function(data) {
                // Extraer solo la tabla del HTML recibido
                var html = $(data);
                var newTable = html.find('.table-responsive').html();
                $('.table-responsive').html(newTable);
                // Actualizar la paginación si es necesario
                var newPagination = html.find('.d-flex.justify-content-center.mt-3').html();
                $('.d-flex.justify-content-center.mt-3').html(newPagination);
            }
        });
    }
    $('#searchInput').on('input', function() {
        fetchContratos();
    });
    $('#solo_activos').on('change', function() {
        fetchContratos();
    });
});
</script>
@endpush
