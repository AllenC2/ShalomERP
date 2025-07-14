@extends('layouts.app')

@section('content')
<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-5">
                <h3 class="mb-4 text-center fw-bold">{{ __('Crear cuenta') }}</h3>
                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">{{ __('Nombre') }}</label>
                        <input id="name" type="text" class="form-control form-control-lg @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autofocus>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">{{ __('Correo electrónico') }}</label>
                        <input id="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">{{ __('Contraseña') }}</label>
                        <input id="password" type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" name="password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password-confirm" class="form-label">{{ __('Confirmar contraseña') }}</label>
                        <input id="password-confirm" type="password" class="form-control form-control-lg" name="password_confirmation" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        {{ __('Registrarse') }}
                    </button>
                </form>
                <div class="mt-4 text-center">
                    <a href="{{ route('login') }}" class="text-decoration-none text-secondary">¿Ya tienes cuenta? Inicia sesión</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
