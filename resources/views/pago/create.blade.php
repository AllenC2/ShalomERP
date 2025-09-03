@extends('layouts.app')

@section('template_title')
    {{ __('Crear Pago') }}
@endsection

@section('content')
<div class="modern-container">
    <div class="page-wrapper">
        <a href="{{ url()->previous() }}" class="modern-link mb-3 d-inline-block">
            <i class="bi bi-arrow-left me-1"></i>
            {{ __('Regresar') }}
        </a>
        <!-- Header moderno -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-icon">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div class="header-text">
                    <h1 class="page-title">{{ __('Crear Nuevo Pago') }}</h1>
                    <p class="page-subtitle">Complete la información para registrar un nuevo pago</p>
                </div>
            </div>
        </div>

        <!-- Formulario -->
        <div class="">
            <form method="POST" action="{{ route('pagos.store') }}" enctype="multipart/form-data" autocomplete="off" class="">
                @csrf
                @include('pago.form')
            </form>
        </div>
    </div>
</div>


@endsection
