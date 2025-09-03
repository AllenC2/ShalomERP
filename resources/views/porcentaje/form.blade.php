<div class="row padding-1 p-1">
    <div class="col-md-12">
        
        <div class="form-group mb-2 mb20">
            <label for="paquete_id" class="form-label">{{ __('Paquete Id') }}</label>
            <input type="text" name="paquete_id" class="form-control @error('paquete_id') is-invalid @enderror" value="{{ old('paquete_id', $porcentaje?->paquete_id) }}" id="paquete_id" placeholder="Paquete Id">
            {!! $errors->first('paquete_id', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="cantidad_porcentaje" class="form-label">{{ __('Cantidad Porcentaje') }}</label>
            <input type="text" name="cantidad_porcentaje" class="form-control @error('cantidad_porcentaje') is-invalid @enderror" value="{{ old('cantidad_porcentaje', $porcentaje?->cantidad_porcentaje) }}" id="cantidad_porcentaje" placeholder="Cantidad Porcentaje">
            {!! $errors->first('cantidad_porcentaje', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="tipo_porcentaje" class="form-label">{{ __('Tipo Porcentaje') }}</label>
            <input type="text" name="tipo_porcentaje" class="form-control @error('tipo_porcentaje') is-invalid @enderror" value="{{ old('tipo_porcentaje', $porcentaje?->tipo_porcentaje) }}" id="tipo_porcentaje" placeholder="Tipo Porcentaje">
            {!! $errors->first('tipo_porcentaje', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>
        <div class="form-group mb-2 mb20">
            <label for="observaciones" class="form-label">{{ __('Observaciones') }}</label>
            <input type="text" name="observaciones" class="form-control @error('observaciones') is-invalid @enderror" value="{{ old('observaciones', $porcentaje?->observaciones) }}" id="observaciones" placeholder="Observaciones">
            {!! $errors->first('observaciones', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>