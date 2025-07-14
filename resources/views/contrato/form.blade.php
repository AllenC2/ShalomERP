<div class="row g-4">
    <div class="col-md-6">

        <div class="row g-3">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="cliente_id" class="form-label">{{ __('Cliente Titular') }}</label>
                    <select name="cliente_id" class="form-select @error('cliente_id') is-invalid @enderror" id="cliente_id">
                        <option value="">{{ __('Selecciona un cliente') }}</option>
                        @foreach ($clientes as $id => $cliente)
                            <option value="{{ $id }}" {{ old('cliente_id', $contrato?->cliente_id) == $id ? 'selected' : '' }}>
                                {{ $cliente }}
                            </option>
                        @endforeach
                    </select>
                    {!! $errors->first('cliente_id', '<div class="invalid-feedback"><strong>:message</strong></div>') !!}
                </div>
                <div class="">
                    <label for="fecha_inicio" class="form-label">{{ __('Fecha de Inicio') }}</label>
                    <input type="date" name="fecha_inicio" class="form-control @error('fecha_inicio') is-invalid @enderror"
                    value="{{ old('fecha_inicio', $contrato?->fecha_inicio ?? now()->subDay()->format('Y-m-d')) }}"
                    id="fecha_inicio">
                    {!! $errors->first('fecha_inicio', '<div class="invalid-feedback"><strong>:message</strong></div>') !!}
                </div>
                
                
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="empleado_id" class="form-label">{{ __('Asesor') }}</label>
                    <select name="empleado_id" class="form-select @error('empleado_id') is-invalid @enderror" id="empleado_id">
                        <option value="">{{ __('Selecciona un empleado') }}</option>
                        @foreach ($empleados as $id => $name)
                            <option value="{{ $id }}" {{ old('empleado_id', $contrato?->empleado_id) == $id ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                    {!! $errors->first('empleado_id', '<div class="invalid-feedback"><strong>:message</strong></div>') !!}
                </div>
                <div class="">
                    <label for="documento" class="form-label">{{ __('Documento en PDF') }}</label>
                    <input type="text" name="documento" class="form-control @error('documento') is-invalid @enderror" value="{{ old('documento', $contrato?->documento ?? 'No') }}" id="documento" readonly>
                    {!! $errors->first('documento', '<div class="invalid-feedback"><strong>:message</strong></div>') !!}
                </div>


            </div>
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="observaciones" class="form-label">{{ __('Observaciones') }}</label>
                    <input type="text" name="observaciones" class="form-control @error('observaciones') is-invalid @enderror" value="{{ old('observaciones', $contrato?->observaciones) }}" id="observaciones" placeholder="Observaciones">
                    {!! $errors->first('observaciones', '<div class="invalid-feedback"><strong>:message</strong></div>') !!}
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        
        <div class="row">
            <div class="mb-3">
                <label for="paquete_id" class="form-label">{{ __('Paquete') }}</label>
                <select name="paquete_id" class="form-select @error('paquete_id') is-invalid @enderror" id="paquete_id">
                    <option value="">{{ __('Selecciona un paquete') }}</option>
                    @foreach ($paquetes as $id => $name)
                        <option value="{{ $id }}" {{ old('paquete_id', $contrato?->paquete_id) == $id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
                {!! $errors->first('paquete_id', '<div class="invalid-feedback"><strong>:message</strong></div>') !!}
            </div>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <div hidden class="mb-3">
                    <label for="estado" class="form-label">{{ __('Estado') }}</label>
                    <select name="estado" class="form-select @error('estado') is-invalid @enderror" id="estado">
                        <option value="">{{ __('Selecciona estado') }}</option>
                        <option value="Activo" {{ old('estado', $contrato?->estado ?? 'Activo') == 'Activo' ? 'selected' : '' }}>Activo</option>
                        <option value="Finalizado" {{ old('estado', $contrato?->estado ?? 'Activo') == 'Finalizado' ? 'selected' : '' }}>Finalizado</option>
                    </select>
                    {!! $errors->first('estado', '<div class="invalid-feedback"><strong>:message</strong></div>') !!}
                </div>
                <div class="mb-3">
                    <label for="plazo_tipo" class="form-label">{{ __('Tipo') }}</label>
                    <select name="plazo_tipo" class="form-select @error('plazo_tipo') is-invalid @enderror" id="plazo_tipo">
                        <option value="">{{ __('Selecciona tipo') }}</option>
                        <option value="Mensual" {{ old('plazo_tipo', $contrato?->plazo_tipo) == 'Mensual' ? 'selected' : '' }}>Mensual</option>
                        <option value="Semanal" {{ old('plazo_tipo', $contrato?->plazo_tipo) == 'Semanal' ? 'selected' : '' }}>Semanal</option>
                    </select>
                    {!! $errors->first('plazo_tipo', '<div class="invalid-feedback"><strong>:message</strong></div>') !!}
                </div>
                <div class="mb-3">
                    <label for="plazo_frencuencia" class="form-label">{{ __('Cada cuando') }}</label>
                    <input type="text" name="plazo_frencuencia" class="form-control @error('plazo_frencuencia') is-invalid @enderror" value="{{ old('plazo_frencuencia', $contrato?->plazo_frencuencia) }}" id="plazo_frencuencia">
                    {!! $errors->first('plazo_frencuencia', '<div class="invalid-feedback"><strong>:message</strong></div>') !!}
                </div>
                
                
                
                <!-- <div class="mb-3">
                    <label for="fecha_fin" class="form-label">{{ __('Fecha Fin') }}</label>
                    <input type="date" name="fecha_fin" class="form-control @error('fecha_fin') is-invalid @enderror" value="{{ old('fecha_fin', $contrato?->fecha_fin) }}" id="fecha_fin">
                    {!! $errors->first('fecha_fin', '<div class="invalid-feedback"><strong>:message</strong></div>') !!}
                </div> -->
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="monto_inicial" class="form-label">{{ __('Monto Inicial') }}</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="text" name="monto_inicial" class="form-control @error('monto_inicial') is-invalid @enderror" 
                            value="{{ old('monto_inicial', isset($contrato) ? number_format($contrato->monto_inicial, 2, '.', ',') : '') }}" 
                            id="monto_inicial">
                    </div>
                    {!! $errors->first('monto_inicial', '<div class="invalid-feedback"><strong>:message</strong></div>') !!}
                </div>
                <div class="mb-3">
                    <label for="plazo_cantidad" class="form-label">{{ __('En cuantos plazos') }}</label>
                    <input type="text" name="plazo_cantidad" class="form-control @error('plazo_cantidad') is-invalid @enderror" value="{{ old('plazo_cantidad', $contrato?->plazo_cantidad) }}" id="plazo_cantidad">
                    {!! $errors->first('plazo_cantidad', '<div class="invalid-feedback"><strong>:message</strong></div>') !!}
                </div>
            </div>
            <div class="col-md-12">
                <div class="card mb-3" id="pagos_card" style="display:none;">
            <div class="card-body">
            <h6 class="card-title">{{ __('Resumen de Pagos') }}</h6>
            <p class="mb-1" id="pagos_info"></p>
            <p class="mb-0 text-muted" id="total_restante"></p>
            </div>
        </div>
                <div class="mb-3">
                    <label for="monto_total" class="form-label">{{ __('Monto Total') }}</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="text" name="monto_total" class="form-control @error('monto_total') is-invalid @enderror" 
                            value="{{ old('monto_total', isset($contrato) ? number_format($contrato->monto_total, 2, '.', ',') : '') }}" 
                            id="monto_total" disabled>
                    </div>
                    {!! $errors->first('monto_total', '<div class="invalid-feedback"><strong>:message</strong></div>') !!}
                </div>
                
            </div>
        </div>
    </div>
    <div class="col-md-12">
       
        
        <script>
            function calcularPagos() {
            const tipo = document.getElementById('plazo_tipo').value;
            const cantidad = parseInt(document.getElementById('plazo_cantidad').value);
            const montoTotal = parseFloat(document.getElementById('monto_total').value.replace(/,/g, ''));
            const montoInicial = parseFloat(document.getElementById('monto_inicial').value.replace(/,/g, '')) || 0;

            if (!tipo || isNaN(cantidad) || isNaN(montoTotal)) {
            document.getElementById('pagos_card').style.display = 'none';
            return;
            }

            const montoPendiente = montoTotal - montoInicial;
            const pagos = cantidad > 0 ? cantidad : 0;
            const montoPorPago = pagos > 0 ? (montoPendiente / pagos) : 0;

            let frecuencia = document.getElementById('plazo_frencuencia').value;
            let frecuenciaTexto = '';
            if (tipo === 'Semanal' && frecuencia) {
            const dias = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
            frecuenciaTexto = dias[parseInt(frecuencia) % 7] || frecuencia;
            } else if (tipo === 'Mensual' && frecuencia) {
            frecuenciaTexto = `día ${frecuencia} de cada mes`;
            }

            document.getElementById('pagos_info').innerHTML = `
            <strong>${pagos}</strong> pagos de <strong>$${montoPorPago.toFixed(2)}</strong> cada uno.<br>
            <small>Frecuencia: ${frecuenciaTexto ? frecuenciaTexto : '-'}</small>
            `;
            document.getElementById('total_restante').innerHTML = `Total restante por pagar: <strong>$${montoPendiente.toFixed(2)}</strong>`;
            document.getElementById('pagos_card').style.display = 'block';
            }

            document.getElementById('plazo_tipo').addEventListener('change', calcularPagos);
            document.getElementById('plazo_cantidad').addEventListener('input', calcularPagos);
            document.getElementById('plazo_frencuencia').addEventListener('input', calcularPagos);
            document.getElementById('monto_inicial').addEventListener('input', calcularPagos);
            document.getElementById('monto_total').addEventListener('input', calcularPagos);

            // Inicializar al cargar
            window.addEventListener('DOMContentLoaded', calcularPagos);
        </script>
    </div>
    <div class="col-md-12 mt-4 text-end">
        <a href="{{ url()->previous() }}" class="btn btn-secondary px-4 me-2">
           {{ __('Cancelar') }}
        </a>
        <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-save me-2"></i>{{ __('Registrar') }}
        </button>
    </div>
</div>
<style>
    .form-label { font-weight: 500; }
    h5 { font-size: 1.1rem; color: #333; border-bottom: 1px solid #eee; padding-bottom: 6px; margin-bottom: 18px; }
    .form-control, .form-select { border-radius: 6px; }
    .invalid-feedback { font-size: 0.95em; }
    .g-4 > .col-md-6, .g-4 > .col-md-12 { margin-bottom: 24px; }
</style>
<script>
    // Precios de paquetes desde backend
    const paquetesPrecios = @json(App\Models\Paquete::pluck('precio', 'id'));
    document.getElementById('paquete_id').addEventListener('change', function() {
        const paqueteId = this.value;
        const montoTotalInput = document.getElementById('monto_total');
        montoTotalInput.value = paquetesPrecios[paqueteId] || '';
    });
</script>