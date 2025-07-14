<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card shadow-sm">
                @php
                    $bgClass = ($pago?->estado ?? 'Pendiente') === 'Hecho' ? 'bg-success' : 'bg-warning';
                @endphp
                <div class="card-header {{ $bgClass }} text-white">
                    <h4 class="mb-0">{{ __('Pago de Contrato') }}</h4>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5 class="fw-bold mb-1">{{$pago->contrato->paquete->nombre}}#{{$pago->contrato->id}}</h5>
                        <p class="text-muted mb-0">{{$pago->contrato->cliente->nombre}} {{$pago->contrato->cliente->apellido}}</p>
                    </div>
                    <form>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input hidden type="text" name="contrato_id" class="form-control @error('contrato_id') is-invalid @enderror" value="{{ old('contrato_id', $contrato_id ?? $pago?->contrato_id) }}" id="contrato_id" placeholder="Contrato Id" readonly>
                                <div class="form-floating mb-3">
                                    <input type="date" name="fecha_pago" class="form-control @error('fecha_pago') is-invalid @enderror" value="{{ old('fecha_pago', $pago?->fecha_pago) }}" id="fecha_pago" placeholder="Fecha Pago">
                                    <label for="fecha_pago">{{ __('Fecha Pago') }}</label>
                                    {!! $errors->first('fecha_pago', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                                </div>
                                <div class="form-floating mb-3">
                                    <input type="text" name="metodo_pago" class="form-control @error('metodo_pago') is-invalid @enderror" value="{{ old('metodo_pago', $pago?->metodo_pago) }}" id="metodo_pago" placeholder="Metodo Pago">
                                    <label for="metodo_pago">{{ __('Metodo Pago') }}</label>
                                    {!! $errors->first('metodo_pago', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                                </div>
                                <div class="form-floating mb-3">
                                    <input type="text" name="observaciones" class="form-control @error('observaciones') is-invalid @enderror" value="{{ old('observaciones', $pago?->observaciones) }}" id="observaciones" placeholder="Observaciones">
                                    <label for="observaciones">{{ __('Observaciones') }}</label>
                                    {!! $errors->first('observaciones', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select name="estado" id="estado" class="form-select @error('estado') is-invalid @enderror">
                                        <option value="Pendiente" {{ old('estado', $pago?->estado ?? 'Pendiente') == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                                        <option value="Hecho" {{ old('estado', $pago?->estado ?? 'Pendiente') == 'Hecho' ? 'selected' : '' }}>Hecho</option>
                                    </select>
                                    <label for="estado">{{ __('Estado') }}</label>
                                    {!! $errors->first('estado', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                                </div>
                                <div class="form-floating mb-3">
                                    <input type="text" name="monto" class="form-control @error('monto') is-invalid @enderror" value="{{ old('monto', $pago?->monto) }}" id="monto" placeholder="Monto" readonly>
                                    <label for="monto">{{ __('Monto') }}</label>
                                    {!! $errors->first('monto', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                                </div>
                                
                                <div class="form-floating mb-3">
                                    <input type="text" name="documento" class="form-control @error('documento') is-invalid @enderror" value="{{ old('documento', $pago?->documento ?? 'No') }}" id="documento" placeholder="Documento" readonly>
                                    <label for="documento">{{ __('Documento') }}</label>
                                    {!! $errors->first('documento', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-4 gap-2">
                            <a href="{{ route('contratos.show', $pago->contrato->id) }}" class="btn btn-outline-secondary">{{ __('Volver') }}</a>
                            <button type="submit" class="btn btn-primary ">{{ __('Registrar') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>