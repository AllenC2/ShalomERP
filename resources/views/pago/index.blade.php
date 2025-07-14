@extends('layouts.app')

@section('template_title')
    Pagos
@endsection

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-11">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="bi bi-cash-stack"></i> {{ __('Pagos') }}
                    </h4>
                </div>
                @if ($message = Session::get('success'))
                    <div class="alert alert-success m-4">
                        <p class="mb-0">{{ $message }}</p>
                    </div>
                @endif
                <div class="card-body bg-white">
                    <form method="GET" action="{{ route('pagos.index') }}" class="row g-2 mb-3 align-items-end">
                        <div class="col-md-4">
                            <label for="search_contrato" class="form-label">Buscar por # Contrato</label>
                            <input type="number" name="search_contrato" id="search_contrato" class="form-control" value="{{ request('search_contrato') }}" placeholder="Número de contrato">
                        </div>
                        <div class="col-md-4">
                            <label for="estado" class="form-label">Estado del pago</label>
                            <select name="estado" id="estado" class="form-select">
                                <option value="">Todos</option>
                                <option value="Hecho" {{ request('estado') == 'Hecho' ? 'selected' : '' }}>Hecho</option>
                                <option value="Pendiente" {{ request('estado') == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-primary">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Contrato</th>
                                    <th scope="col">Monto</th>
                                    <th scope="col">Observaciones</th>
                                    <th scope="col">Fecha Pago</th>
                                    <th scope="col">Método</th>
                                    <th scope="col">Estado</th>
                                    <th scope="col" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pagos as $pago)
                                    <tr>
                                        <td>{{ ($pagos->currentPage() - 1) * $pagos->perPage() + $loop->iteration }}</td>
                                        <td>{{ $pago->contrato_id }}</td>
                                        <td>${{ number_format($pago->monto, 2) }}</td>
                                        <td>{{ $pago->observaciones }}</td>
                                        <td>{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}</td>
                                        <td>{{ $pago->metodo_pago }}</td>
                                        <td>
                                            <span class="badge 
                                                @if($pago->estado == 'Hecho') bg-primary 
                                                @elseif($pago->estado == 'Pendiente') bg-warning text-dark 
                                                @elseif($pago->estado == 'Retrasado') bg-danger 
                                                @else bg-secondary 
                                                @endif">
                                                {{ $pago->estado }}
                                            </span>
                                        </td>

                                        <td class="text-center">
                                            <a class="btn btn-sm btn-outline-primary" href="{{ route('pagos.show', $pago->id) }}" title="Ver">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a class="btn btn-sm btn-outline-success" href="{{ route('pagos.edit', $pago->id) }}" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('pagos.destroy', $pago->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="return confirm('¿Seguro que desea eliminar este pago?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if($pagos->isEmpty())
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-info-circle"></i> No hay pagos registrados.
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-footer bg-white">
                    {!! $pagos->withQueryString()->links() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
