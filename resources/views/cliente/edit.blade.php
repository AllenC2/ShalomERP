@extends('layouts.app')

@section('template_title')
    {{ __('Actualizar Cliente') }}
@endsection

@section('content')
<div class="modern-container">
    <div class="page-wrapper">
        <!-- Header moderno para edición -->
        <div style="max-width: 1200px; margin: 0 auto;">
            <a href="{{ route('clientes.index') }}" class="modern-link mb-3 d-inline-block">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('Regresar') }}
            </a>
        </div>
        <div class="page-header edit-header">
            <div class="header-content">
                <div class="header-icon edit-icon">
                    <i class="bi bi-person-gear"></i>
                </div>
                <div class="header-text">
                    <h1 class="page-title edit-title">{{ __('Editar Cliente') }}</h1>
                    <p class="page-subtitle">
                        Modificando información de {{ $cliente->nombre ?? 'Cliente' }} {{ $cliente->apellido ?? '' }}
                        <span class="client-badge">#{{ $cliente->id }}</span>
                    </p>
                </div>
            </div>
            <div class="header-actions">
                <a href="{{ route('clientes.show', $cliente->id) }}" class="btn text-secondary btn-secondary me-2">
                    <i class="bi bi-eye me-2"></i>Ver cliente
                </a>
            </div>
        </div>

        <!-- Formulario -->
        <div class="form-wrapper">
            <form method="POST" action="{{ route('clientes.update', $cliente->id) }}" enctype="multipart/form-data" autocomplete="off" class="modern-form p-0">
                @method('PATCH')
                @csrf
                @include('cliente.form')
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
        max-width: 1400px;
        margin: 0 auto;
        padding: 32px 24px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 0 auto 24px auto;
        max-width: 1200px;
        background: white;
        padding: 24px 32px;
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: 1px solid #e5e7eb;
    }

    .edit-header {
        border-left: 4px solid #E1B240;
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
        background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
        box-shadow: 0 8px 16px rgba(225, 178, 64, 0.3);
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
        background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .page-subtitle {
        font-size: 1rem;
        color: #6b7280;
        margin: 0;
        font-weight: 400;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .client-badge {
        background: #e5e7eb;
        color: #374151;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .header-actions {
        display: flex;
        gap: 8px;
    }

    .header-actions .btn {
        padding: 12px 24px;
        border-radius: 12px;
        font-weight: 500;
        transition: all 0.3s ease;
        border: 2px solid #e5e7eb;
    }

    .header-actions .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* Barra de información rápida */
    .client-info-bar {
        background: white;
        border-radius: 16px;
        padding: 20px 32px;
        margin-bottom: 24px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
        border: 1px solid #f1f3f4;
    }

    .info-item {
        text-align: center;
    }

    .info-label {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .info-value {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1f2937;
    }

    .form-container {
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }

    .form-wrapper {
        max-width: 1200px;
        margin: 0 auto;
    }

    .modern-form {

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

        .header-actions {
            flex-direction: column;
            width: 100%;
        }

        .header-actions .btn {
            width: 100%;
        }

        .client-info-bar {
            grid-template-columns: 1fr;
            gap: 16px;
            padding: 16px 20px;
        }
    }
</style>
@endsection
