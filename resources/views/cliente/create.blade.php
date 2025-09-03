@extends('layouts.app')

@section('template_title')
    {{ __('Crear Cliente') }}
@endsection

@section('content')
<div class="modern-container">
    <div class="page-wrapper">
        <!-- Header moderno -->
        <a href="{{ route('clientes.index') }}" class="modern-link mb-3 d-inline-block">
            <i class="bi bi-arrow-left me-1"></i>
            {{ __('Regresar') }}
        </a>
        <div class="page-header">
            <div class="header-content">
                <div class="header-icon" style="background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); box-shadow: 0 8px 16px rgba(225, 178, 64, 0.3);">
                    <i class="bi bi-person-plus"></i>
                </div>
                <div class="header-text">
                    <h1 class="page-title" style="background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                        {{ __('Registrar Cliente') }}
                    </h1>
                    <p class="page-subtitle">Complete la información para registrar un nuevo cliente</p>
                </div>
            </div>
         
        </div>

        <!-- Formulario -->
        <div class="">
            <form method="POST" action="{{ route('clientes.store') }}" enctype="multipart/form-data" autocomplete="off" class="modern-form p-0">
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

    .header-content {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .header-icon {
        width: 64px;
        height: 64px;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.8rem;
        box-shadow: 0 8px 16px rgba(16, 185, 129, 0.3);
    }

    .page-title {
        font-size: 2rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 4px 0;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .page-subtitle {
        font-size: 1rem;
        color: #6b7280;
        margin: 0;
        font-weight: 400;
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

    .form-container {
        padding: 8px;
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

        .header-actions .btn {
            width: 100%;
        }
    }
</style>
@endsection
