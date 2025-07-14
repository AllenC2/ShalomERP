@extends('layouts.app')

@section('template_title')
    Clientes
@endsection

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-people me-2"></i>{{ __('Clientes') }}
                        </h5>
                        <a href="{{ route('clientes.create') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-plus-lg me-1"></i>{{ __('Agregar Nuevo') }}
                        </a>
                    </div>
                    <!-- Buscador -->
                    <div class="card-body pt-3 pb-0">
                        <form id="form-busqueda" method="GET" action="{{ route('clientes.index') }}" class="row g-2 align-items-center">
                            <div class="col-md-8">
                                <input type="text" id="input-busqueda" name="busqueda" value="{{ request('busqueda') }}" class="form-control" placeholder="Buscar por nombre o apellido...">
                            </div>
                            <div class="col-md-4 text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                            </div>
                        </form>
                    </div>
                    @if ($message = Session::get('success'))
                        <div class="alert alert-success m-4">
                            <p class="mb-0">{{ $message }}</p>
                        </div>
                    @endif

                    <div class="card-body bg-white">
                        <div class="table-responsive" id="tabla-clientes">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-primary">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Nombre</th>
                                        <th scope="col">Apellido</th>

                                        <th scope="col" class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($clientes as $cliente)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            <td>{{ $cliente->nombre }}</td>
                                            <td>{{ $cliente->apellido }}</td>

                               
                                            <td class="text-center">
                                                <a class="btn btn-sm btn-outline-primary me-1" href="{{ route('clientes.show', $cliente->id) }}">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a class="btn btn-sm btn-outline-success me-1" href="{{ route('clientes.edit', $cliente->id) }}">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form action="{{ route('clientes.destroy', $cliente->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Está seguro de eliminar este cliente?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            {!! $clientes->withQueryString()->links() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#input-busqueda').on('keyup', function() {
            var busqueda = $(this).val();
            $.ajax({
                url: "{{ route('clientes.index') }}",
                type: 'GET',
                data: { busqueda: busqueda },
                success: function(data) {
                    // Extrae solo la tabla de la respuesta
                    var html = $(data).find('#tabla-clientes').html();
                    $('#tabla-clientes').html(html);
                }
            });
        });
    });
    </script>
@endsection
