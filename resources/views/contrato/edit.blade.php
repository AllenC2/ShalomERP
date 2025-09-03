@extends('layouts.app')

@section('template_title')
    {{ __('Editar Contrato') }}
@endsection

@section('content')
<div class="modern-container">
    <div class="page-wrapper">
        <a href="{{ route('contratos.index') }}" class="modern-link mb-3 d-inline-block">
            <i class="bi bi-arrow-left me-1"></i>
            {{ __('Regresar') }}
        </a>
        <!-- Header moderno para edición -->
        <div class="page-header edit-header">
            <div class="header-content">
                <div class="header-icon edit-icon">
                    <i class="bi bi-pencil-square"></i>
                </div>
                <div class="header-text">
                    <h1 class="page-title edit-title">{{ __('Editar Contrato') }}</h1>
                    <p class="page-subtitle">
                        Modificando contrato de {{ $contrato->cliente->nombre ?? 'Cliente' }} 
                        <span class="contract-badge">#{{ $contrato->id }}</span>
                    </p>
                </div>
            </div>
            <div class="header-actions">
        
                <div class="text-md-end">
                    <span class="badge status-badge 
                        {{ 
                            $contrato->estado == 'activo' ? 'bg-success' : 
                            ($contrato->estado == 'suspendido' ? 'bg-warning' : 
                            ($contrato->estado == 'cancelado' ? 'bg-danger' : 
                            ($contrato->estado == 'finalizado' ? 'bg-primary' : 'bg-secondary'))) 
                        }}">
                        {{ strtoupper($contrato->estado) }}
                    </span>
                    <p class="text-muted mb-0 mt-2">
                        Creado: {{ $contrato->created_at->format('d') }} de {{ ucfirst($contrato->created_at->locale('es')->monthName) }} de {{ $contrato->created_at->format('Y') }}
                    </p>
                </div>
            </div>
        </div>
        <!-- Información de Edición -->
        <div class="simple-warning mb-4">
            <div class="warning-item align-items-center">
                <i class="bi bi-exclamation-circle"></i>
                <p>Los cambios financieros, de fechas o comisiones pueden <strong>corromper los datos</strong> y causar errores en el sistema.</p>
            </div>
            
            <div class="recommendation align-items-center">
                <i class="bi bi-lightbulb"></i>
                <p><strong>Recomendación:</strong> Para modificaciones mayores, es mejor <strong>crear un nuevo contrato</strong> en lugar de editar este.</p>
            </div>
        </div>
        <!-- Formulario -->
        <div class="form-container">
            <form method="POST" action="{{ route('contratos.update', $contrato->id) }}" enctype="multipart/form-data" autocomplete="off" class="modern-form">
                @method('PATCH')
                @csrf
                @include('contrato.form')
            </form>
        </div>
    </div>
</div>
@endsection
