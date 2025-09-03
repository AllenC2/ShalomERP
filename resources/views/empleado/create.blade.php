@extends('layouts.app')

@section('template_title')
    Nuevo Empleado
@endsection

@section('content')
<div class="modern-container">
    <div class="page-wrapper">
        <!-- Header moderno -->
        <a href="{{ route('empleados.index') }}" class="modern-link mb-3 d-inline-block">
            <i class="bi bi-arrow-left me-1"></i>
            {{ __('Regresar') }}
        </a>
        <div class="page-header">
            <div class="header-content px-4">
                <div class="header-icon" style="background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); box-shadow: 0 8px 16px rgba(225, 178, 64, 0.3);">
                    <i class="bi bi-person-plus"></i>
                </div>
                <div class="header-text">
                    <h1 class="page-title" style="background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                        {{ __('Registrar Empleado') }}
                    </h1>
                    <p class="page-subtitle">Complete la información para registrar un nuevo empleado</p>
                </div>
            </div>
        
        </div>
        
        <div class="modern-form-wrapper">
            <form method="POST" action="{{ route('empleados.store') }}" role="form" enctype="multipart/form-data">
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
        background: #667eea;
        color: white;
        border-color: #667eea;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.25);
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
        }

        .header-actions .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endsection
