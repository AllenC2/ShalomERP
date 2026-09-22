@extends('layouts.app')

@section('template_title')
    Editar Ruta #{{ $ruta->id }}
@endsection

@section('content')
<div class="container py-4" style="max-width: 1200px;">
    <a href="{{ route('rutas.show', $ruta->id) }}" class="d-inline-block mb-3 text-decoration-none" style="color: #79481D;">
        <i class="bi bi-arrow-left me-1"></i> Regresar
    </a>

    <div class="page-header">
        <div class="header-content">
            <div class="header-icon">
                <i class="fa-solid fa-route"></i>
            </div>
            <div class="header-text">
                <h1 class="page-title">Editar Ruta #{{ $ruta->id }}</h1>
                <p class="page-subtitle">Arrastra las paradas para reordenar la ruta</p>
            </div>
        </div>
    </div>

    <!-- Editar datos generales -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold" style="color: #79481D;">
                <i class="bi bi-gear me-2"></i> Datos de la Ruta
            </h5>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="{{ route('rutas.update', $ruta->id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Empleado</label>
                        <input type="text" class="form-control" value="{{ $ruta->empleado->nombre }} {{ $ruta->empleado->apellido }}" disabled>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Nombre</label>
                        <input type="text" name="nombre" class="form-control" value="{{ $ruta->nombre }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Fecha</label>
                        <input type="date" name="fecha" class="form-control" value="{{ optional($ruta->fecha)->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Estado</label>
                        <select name="estado" class="form-select">
                            @foreach(\App\Models\Ruta::getEstadosValidos() as $key => $label)
                                <option value="{{ $key }}" {{ $ruta->estado === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-9">
                        <label class="form-label fw-semibold">Notas</label>
                        <input type="text" name="notas" class="form-control" value="{{ $ruta->notas }}" placeholder="Observaciones...">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn text-white fw-bold w-100" style="background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);">
                            <i class="bi bi-check-lg me-1"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Reordenar paradas -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold" style="color: #79481D;">
                <i class="bi bi-list-ol me-2"></i> Orden de Paradas
            </h5>
            <span class="text-muted small"><i class="bi bi-grip-vertical me-1"></i> Arrastra para reordenar</span>
        </div>
        <div class="card-body p-0">
            <form method="POST" action="{{ route('rutas.actualizarOrden', $ruta->id) }}" id="ordenForm">
                @csrf
                <div id="sortable-paradas" class="list-group list-group-flush">
                    @foreach($ruta->paradas as $parada)
                        <div class="list-group-item px-4 py-3 d-flex align-items-center sortable-item" data-id="{{ $parada->id }}" style="cursor: grab;">
                            <div class="me-3 text-muted">
                                <i class="bi bi-grip-vertical fs-5"></i>
                            </div>
                            <div class="me-3 d-flex align-items-center justify-content-center rounded-circle fw-bold text-white flex-shrink-0"
                                 style="width: 32px; height: 32px; background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); font-size: 0.8rem;">
                                {{ $parada->orden }}
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $parada->cliente->nombre }} {{ $parada->cliente->apellido }}</div>
                                <small class="text-muted">{{ $parada->direccion_destino }}</small>
                            </div>
                            <div class="ms-3">
                                <span class="badge {{ $parada->estado_badge }}">{{ $parada->estado_label }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="p-4 text-end">
                    <button type="submit" class="btn text-white fw-bold px-4" style="background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);">
                        <i class="bi bi-check-lg me-1"></i> Actualizar Orden
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var el = document.getElementById('sortable-paradas');
    if (el) {
        Sortable.create(el, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            onEnd: function() {
                var items = el.querySelectorAll('.sortable-item');
                items.forEach(function(item, index) {
                    var numEl = item.querySelector('.rounded-circle');
                    if (numEl) numEl.textContent = index + 1;
                });
            }
        });
    }

    document.getElementById('ordenForm').addEventListener('submit', function(e) {
        var items = el.querySelectorAll('.sortable-item');
        var form = this;
        var existingInputs = form.querySelectorAll('input[name^="orden"]');
        existingInputs.forEach(function(inp) { inp.remove(); });

        items.forEach(function(item) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'orden[]';
            input.value = item.getAttribute('data-id');
            form.appendChild(input);
        });
    });
});
</script>

<style>
    .page-header {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        border: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .header-content { display: flex; align-items: center; gap: 20px; }
    .page-title { font-size: 2rem; font-weight: 700; color: #1f2937; margin: 0 0 4px 0; }
    .page-subtitle { font-size: 1rem; color: #6b7280; margin: 0; }
    .sortable-ghost {
        opacity: 0.4;
        background: #f0f4ff !important;
    }
    .sortable-item {
        transition: all 0.2s ease;
        user-select: none;
    }
    .sortable-item:hover {
        background: #f8f9ff;
    }
    @media (max-width: 768px) {
        .page-header { flex-direction: column; gap: 20px; text-align: center; }
        .header-content { flex-direction: column; gap: 16px; }
    }
</style>
@endsection
