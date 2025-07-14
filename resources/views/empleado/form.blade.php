<div class="container ">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="border-0">
            
                <div class="card-body">
                    <form>
                        <div class="mb-3">
                            <label for="nombre" class="form-label">{{ __('Nombre') }}</label>
                            <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $empleado?->nombre) }}" id="nombre" placeholder="Nombre">
                            {!! $errors->first('nombre', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                        </div>
                        <div class="mb-3">
                            <label for="apellido" class="form-label">{{ __('Apellido') }}</label>
                            <input type="text" name="apellido" class="form-control @error('apellido') is-invalid @enderror" value="{{ old('apellido', $empleado?->apellido) }}" id="apellido" placeholder="Apellido">
                            {!! $errors->first('apellido', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('Email') }}</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $empleado?->email) }}" id="email" placeholder="Email">
                            {!! $errors->first('email', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                        </div>
                        <div class="mb-3">
                            <label for="telefono" class="form-label">{{ __('Telefono') }}</label>
                            <input type="text" name="telefono" class="form-control @error('telefono') is-invalid @enderror" value="{{ old('telefono', $empleado?->telefono) }}" id="telefono" placeholder="Telefono">
                            {!! $errors->first('telefono', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                        </div>
                        <div class="mb-3">
                            <label for="domicilio" class="form-label">{{ __('Domicilio') }}</label>
                            <input type="text" name="domicilio" class="form-control @error('domicilio') is-invalid @enderror" value="{{ old('domicilio', $empleado?->domicilio) }}" id="domicilio" placeholder="Domicilio">
                            {!! $errors->first('domicilio', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary">{{ __('Guardar') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>