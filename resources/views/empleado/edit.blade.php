@extends('layouts.app')

@section('template_title')
    Editar Empleado
@endsection

@section('content')
<div class="modern-container">
    <div class="page-wrapper">

        <!-- Header moderno para edición -->
        <div style="max-width: 1200px; margin: 0 auto;">
            <a href="{{ route('empleados.index') }}" class="modern-link mb-3 d-inline-block">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('Regresar') }}
            </a>
        </div>
        <div class="page-header edit-header">
            <div class="header-content px-4">
                <div class="header-icon edit-icon">
                    <i class="bi bi-person-gear"></i>
                </div>
                <div class="header-text">
                    <h1 class="page-title edit-title">{{ __('Editar Empleado') }}</h1>
                    <p class="page-subtitle">
                        Modificando información de {{ $empleado->nombre ?? 'empleado' }} {{ $empleado->apellido ?? '' }}
                        <span class="client-badge">#{{ $empleado->id }}</span>
                    </p>
                </div>
            </div>
            <div class="header-actions">
                <a href="{{ route('empleados.show', $empleado->id) }}" class="btn text-secondary btn-secondary me-2">
                    <i class="bi bi-eye me-2"></i>Ver Empleado
                </a>
            </div>
        </div>
        
        <div class="modern-form-wrapper">
            <form method="POST" action="{{ route('empleados.update', $empleado->id) }}" enctype="multipart/form-data" autocomplete="off">
                {{ method_field('PATCH') }}
                @csrf
                @include('empleado.form')
            </form>
        </div>

    </div>
</div>

<style>
    /* Header page styles */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding: 1.5rem 0;
    }

    .header-content {
        display: flex;
        align-items: center;
    }

    .header-icon {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1.5rem;
        font-size: 1.5rem;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }

    .page-title {
        font-size: 2rem;
        font-weight: 700;
        color: #2d3748;
        margin: 0;
        line-height: 1.2;
    }

    .page-subtitle {
        color: #718096;
        font-size: 1rem;
        margin: 0;
        margin-top: 0.25rem;
    }

    .header-actions {
        display: flex;
        gap: 0.5rem;
    }

    .header-actions .btn {
        background: white;
        color: #667eea;
        border: 2px solid #e2e8f0;
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        text-decoration: none;
    }

    .header-actions .btn:hover {
        background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
        color: white;
        border-color: none;
        transform: translateY(-2px);
        box-shadow: none;
    }

    .modern-form-wrapper {
        background: white;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    /* Responsive del header */
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .header-content {
            flex-direction: column;
            align-items: flex-start;
            text-align: left;
        }

        .header-icon {
            margin-right: 0;
            margin-bottom: 1rem;
        }

        .page-title {
            font-size: 1.5rem;
        }

        .page-subtitle {
            font-size: 0.875rem;
        }

        .header-actions {
            width: 100%;
            flex-direction: column;
        }

        .header-actions .btn {
            width: 100%;
            justify-content: center;
            margin-bottom: 0.5rem;
        }

        .header-actions .btn:last-child {
            margin-bottom: 0;
        }
    }
</style>
@endsection
