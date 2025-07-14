@extends('layouts.app')

@section('template_title')
    Comisiones
@endsection

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-11 col-md-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="bi bi-percent me-2"></i>{{ __('Comisiones') }}
                        </h4>
                    </div>
                    @if ($message = Session::get('success'))
                        <div class="alert alert-success m-4">
                            <p class="mb-0">{{ $message }}</p>
                        </div>
                    @endif

                    <div class="card-body bg-white">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-primary">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Contrato</th>
                                        <th scope="col">Empleado</th>
                                        <th scope="col">Tipo</th>
                                        <th scope="col">Monto</th>
                                        <th scope="col">Fecha</th>
                                        <th scope="col">Observaciones</th>

                                        <th scope="col">Estado</th>
                                        <th scope="col" class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($comisiones as $comisione)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            <td>{{ $comisione->contrato->paquete->nombre }}#{{$comisione->contrato_id}}</td>
                                            <td>{{ $comisione->empleado->nombre }} {{$comisione->empleado->apellido}}</td>
                                            <td>{{ $comisione->tipo_comision }}</td>
                                            <td>{{ $comisione->monto }}</td>
                                            <td>{{ \Carbon\Carbon::parse($comisione->fecha_comision)->locale('es')->translatedFormat('d \d\e F \d\e Y') }}</td>
                                            <td>{{ $comisione->observaciones }}</td>

                                            <td>
                                                <span class="badge {{ $comisione->estado == 'Entregada' ? 'bg-success' : 'bg-warning' }}">
                                                    {{ $comisione->estado }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <form action="{{ route('comisiones.destroy', $comisione->id) }}" method="POST" style="display:inline;">
                                                <div class="btn-group" role="group">
                                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('comisiones.show', $comisione->id) }}" title="Ver">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a class="btn btn-sm btn-outline-success" href="{{ route('comisiones.edit', $comisione->id) }}" title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"
                                                            onclick="return confirm('¿Está seguro de eliminar?')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            {!! $comisiones->withQueryString()->links() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
