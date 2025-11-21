@extends('layouts.app')

@section('template_title')
    {{ __('Editar Paquete') }}
@endsection

@section('content')
<div class="modern-container">
    <div class="page-wrapper">
        <a href="{{ route('paquetes.index') }}" class="modern-link mb-3 d-inline-block">
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
                    <h1 class="page-title edit-title">{{ __('Editar Paquete') }}</h1>
                    <p class="page-subtitle">
                        Modificando paquete: <strong>{{ $paquete->nombre }}</strong>
                    </p>
                </div>
            </div>
            <div class="header-actions">
                <div class="text-md-end">
                    <span class="badge price-badge">
                        ${{ number_format($paquete->precio, 2) }}
                    </span>
                    <p class="text-muted mb-0 mt-2">
                        Creado: {{ $paquete->created_at->translatedFormat('d \d\e F \d\e Y') }}
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Información de Edición -->
        <div class="simple-warning mb-4">
            <div class="warning-item align-items-center">
                <i class="bi bi-exclamation-circle"></i>
                <p>Los cambios en porcentajes podrian <strong>afectar contratos existentes</strong> que usan este paquete.</p>
            </div>
            
            <div class="recommendation align-items-center">
                <i class="bi bi-lightbulb"></i>
                <p><strong>Recomendación:</strong> Revisa los contratos activos antes de modificar las comisiones.</p>
            </div>
        </div>
        
        <!-- Formulario -->
        <div class="form-container">
            <form method="POST" action="{{ route('paquetes.update', $paquete->id) }}" enctype="multipart/form-data" autocomplete="off" class="modern-form">
                @method('PATCH')
                @csrf
                @include('paquete.form')
            </form>
        </div>
    </div>
</div>

<style>
    .modern-container {
        min-height: 100vh;
        padding: 0;
    }

    .page-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        padding: 32px 24px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 32px;
        background: white;
        padding: 24px 32px;
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: 1px solid #e5e7eb;
    }

    .edit-header {
        background: linear-gradient(135deg, #fef3e2 0%, #fef7ed 100%);
        border: 1px solid #fed7aa;
    }

    .header-content {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .header-icon {
        width: 64px;
        height: 64px;
        background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.8rem;
        box-shadow: 0 8px 16px rgba(225, 178, 64, 0.3);
    }

    .edit-icon {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        box-shadow: 0 8px 16px rgba(245, 158, 11, 0.3);
    }

    .page-title {
        font-size: 2rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 4px 0;
        background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .edit-title {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .page-subtitle {
        font-size: 1rem;
        color: #6b7280;
        margin: 0;
        font-weight: 400;
    }

    .package-badge {
        display: inline-block;
        background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
        color: white;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-left: 8px;
    }

    .price-badge {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        font-size: 1.1rem;
        font-weight: 600;
        padding: 8px 16px;
        border-radius: 8px;
        border: none;
    }

    .simple-warning {
        background: linear-gradient(135deg, #fef3e2 0%, #fef7ed 100%);
        border: 1px solid #fed7aa;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 24px;
    }

    .warning-item,
    .recommendation {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 16px;
    }

    .recommendation {
        margin-bottom: 0;
    }

    .warning-item i,
    .recommendation i {
        color: #f59e0b;
        font-size: 1.2rem;
        margin-top: 2px;
        flex-shrink: 0;
    }

    .warning-item p,
    .recommendation p {
        margin: 0;
        color: #92400e;
        font-size: 0.95rem;
        line-height: 1.5;
    }

    .modern-link {
        color: #6b7280;
        text-decoration: none;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        transition: color 0.2s ease;
    }

    .modern-link:hover {
        color: #E1B240;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-wrapper {
            padding: 16px;
        }

        .page-header {
            flex-direction: column;
            gap: 20px;
            text-align: center;
        }

        .header-content {
            flex-direction: column;
            gap: 16px;
        }

        .page-title {
            font-size: 1.5rem;
        }

        .simple-warning {
            padding: 16px;
        }

        .warning-item,
        .recommendation {
            flex-direction: column;
            text-align: left;
            gap: 8px;
        }
    }
</style>
@endsection
