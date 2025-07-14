<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10col-md-12">
            <div class="card border-0">
        
                <div class="card-body">
                    <form>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="contrato_id" class="form-control @error('contrato_id') is-invalid @enderror" value="{{ old('contrato_id', $comisione?->contrato_id) }}" id="contrato_id" placeholder="Contrato Id">
                                    <label for="contrato_id">{{ __('Contrato Id') }}</label>
                                    {!! $errors->first('contrato_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="empleado_id" class="form-control @error('empleado_id') is-invalid @enderror" value="{{ old('empleado_id', $comisione?->empleado_id) }}" id="empleado_id" placeholder="Empleado Id">
                                    <label for="empleado_id">{{ __('Empleado Id') }}</label>
                                    {!! $errors->first('empleado_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="date" name="fecha_comision" class="form-control @error('fecha_comision') is-invalid @enderror"
                                        value="{{ old('fecha_comision', $comisione?->fecha_comision ? \Carbon\Carbon::parse($comisione->fecha_comision)->format('Y-m-d') : \Carbon\Carbon::now()->format('Y-m-d')) }}"
                                        id="fecha_comision" placeholder="Fecha Comision">
                                    <label for="fecha_comision">{{ __('Fecha Comision') }}</label>
                                    {!! $errors->first('fecha_comision', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select name="tipo_comision" class="form-select @error('tipo_comision') is-invalid @enderror" id="tipo_comision">
                                        <option value="">{{ __('Seleccione Tipo') }}</option>
                                        <option value="Asesor" {{ old('tipo_comision', $comisione?->tipo_comision) == 'Asesor' ? 'selected' : '' }}>Asesor</option>
                                        <option value="Lider" {{ old('tipo_comision', $comisione?->tipo_comision) == 'Lider' ? 'selected' : '' }}>Líder</option>
                                        <option value="Coordinador" {{ old('tipo_comision', $comisione?->tipo_comision) == 'Coordinador' ? 'selected' : '' }}>Coordinador</option>
                                        <option value="Gerente" {{ old('tipo_comision', $comisione?->tipo_comision) == 'Gerente' ? 'selected' : '' }}>Gerente</option>
                                        <option value="Administrador" {{ old('tipo_comision', $comisione?->tipo_comision) == 'Administrador' ? 'selected' : '' }}>Administrador</option>
                                    </select>
                                    <label for="tipo_comision">{{ __('Tipo Comision') }}</label>
                                    {!! $errors->first('tipo_comision', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="number" step="0.01" name="monto" class="form-control @error('monto') is-invalid @enderror" value="{{ old('monto', $comisione?->monto) }}" id="monto" placeholder="Monto">
                                    <label for="monto">{{ __('Monto') }}</label>
                                    {!! $errors->first('monto', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <textarea name="observaciones" class="form-control @error('observaciones') is-invalid @enderror" id="observaciones" placeholder="Observaciones" style="height: 80px">{{ old('observaciones', $comisione?->observaciones) }}</textarea>
                                    <label for="observaciones">{{ __('Observaciones') }}</label>
                                    {!! $errors->first('observaciones', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="documento" class="form-control @error('documento') is-invalid @enderror" value="{{ old('documento', $comisione?->documento ?? 'No') }}" id="documento" placeholder="Documento" readonly>
                                    <label for="documento">{{ __('Documento') }}</label>
                                    {!! $errors->first('documento', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select name="estado" class="form-select @error('estado') is-invalid @enderror" id="estado">
                                        <option value="">{{ __('Seleccione Estado') }}</option>
                                        <option value="Entregada" {{ old('estado', $comisione?->estado) == 'Entregada' ? 'selected' : '' }}>Entregada</option>
                                        <option value="Pendiente" {{ old('estado', $comisione?->estado) == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                                    </select>
                                    <label for="estado">{{ __('Estado') }}</label>
                                    {!! $errors->first('estado', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary px-4">{{ __('Submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
