<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card border-0 ">
            <div class="card-body">
                <form>
                    <div class="mb-3">
                        <label for="nombre" class="form-label">{{ __('Nombre') }}</label>
                        <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $paquete?->nombre) }}" id="nombre" placeholder="Nombre">
                        {!! $errors->first('nombre', '<div class="invalid-feedback"><strong>:message</strong></div>') !!}
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">{{ __('Descripcion') }}</label>
                        <input type="text" name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" value="{{ old('descripcion', $paquete?->descripcion) }}" id="descripcion" placeholder="Descripcion">
                        {!! $errors->first('descripcion', '<div class="invalid-feedback"><strong>:message</strong></div>') !!}
                    </div>
                    <div class="mb-3">
                        <label for="precio" class="form-label">{{ __('Precio') }}</label>
                        <input type="text" name="precio" class="form-control @error('precio') is-invalid @enderror" value="{{ old('precio', $paquete?->precio) }}" id="precio" placeholder="Precio">
                        {!! $errors->first('precio', '<div class="invalid-feedback"><strong>:message</strong></div>') !!}
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary">{{ __('Guardar') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>