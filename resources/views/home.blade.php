@extends('layouts.app')

@section('content')
@php
    // Zona horaria de México
    date_default_timezone_set('America/Mexico_City');
    $hour = date('H');
    if ($hour >= 6 && $hour < 12) {
        $greeting = 'Buenos días';
    } elseif ($hour >= 12 && $hour < 19) {
        $greeting = 'Buenas tardes';
    } else {
        $greeting = 'Buenas noches';
    }
@endphp

<div class="container py-5">
    <div class="mb-4 text-start">
        <h3 class="fw-bold">¡{{ $greeting }}, {{ Auth::user()->name }}!</h3>
    </div>
    <div class="row mb-4">
        <!-- Contratos -->
        <div class="col-md-6">
            <a href="{{ route('contratos.index') }}" class="text-decoration-none">
                <div class="card h-100 shadow-lg text-center hover-shadow">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <span class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mb-3" style="width:70px;height:70px;font-size:2rem;">
                            <i class="bi bi-file-earmark-text"></i>
                        </span>
                        <h3 class="card-title mb-0">Contratos</h3>
                    </div>
                </div>
            </a>
        </div>
        <!-- Clientes -->
        <div class="col-md-6">
            <a href="{{ route('clientes.index') }}" class="text-decoration-none">
                <div class="card h-100 shadow-lg text-center hover-shadow">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <span class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center mb-3" style="width:70px;height:70px;font-size:2rem;">
                            <i class="bi bi-people"></i>
                        </span>
                        <h3 class="card-title mb-0">Clientes</h3>
                    </div>
                </div>
            </a>
        </div>
    </div>
    <div class="row">
        <!-- Paquetes -->
        <div class="col-md-4">
            <a href="{{ route('paquetes.index') }}" class="text-decoration-none">
                <div class="card h-100 shadow-lg text-center hover-shadow">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <span class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center mb-3" style="width:70px;height:70px;font-size:2rem;">
                            <i class="bi bi-box-seam"></i>
                        </span>
                        <h4 class="card-title mb-0">Paquetes</h4>
                    </div>
                </div>
            </a>
        </div>
        <!-- Empleados -->
        <div class="col-md-4">
            <a href="{{ route('empleados.index') }}" class="text-decoration-none">
                <div class="card h-100 shadow-lg text-center hover-shadow">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <span class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center mb-3" style="width:70px;height:70px;font-size:2rem;">
                            <i class="bi bi-person-badge"></i>
                        </span>
                        <h4 class="card-title mb-0">Empleados</h4>
                    </div>
                </div>
            </a>
        </div>
        <!-- Pagos -->
        <div class="col-md-4">
            <a href="{{ route('pagos.index') }}" class="text-decoration-none">
                <div class="card h-100 shadow-lg text-center hover-shadow">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <span class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center mb-3" style="width:70px;height:70px;font-size:2rem;">
                            <i class="bi bi-cash-stack"></i>
                        </span>
                        <h4 class="card-title mb-0">Pagos</h4>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
