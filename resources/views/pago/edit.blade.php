@extends('layouts.app')

@section('template_title')
    {{ __('Editar Pago') }}
@endsection

@section('content')
    <div class="modern-container">
        <div class="page-wrapper">
            <!-- Header moderno -->
            <div class="page-header">
                <div class="header-content">
                    <div class="header-icon">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                    <div class="header-text">
                        <h1 class="page-title">{{ __('Editar Pago') }}</h1>
                        <p class="page-subtitle">Modifique la información del pago registrado</p>
                    </div>
                </div>
                <div class="header-actions">
                    <a href="{{ route('pagos.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Volver al listado
                    </a>
                </div>
            </div>

            <!-- Advertencia de modificación -->
            <div class="alert-warning-custom">
                <div class="alert-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div class="alert-content">
                    <h5 class="alert-title">⚠️ Advertencia importante</h5>
                    <p class="alert-text">La modificación de registros de pagos puede afectar la integridad financiera del
                        sistema. Asegúrese de verificar toda la información antes de guardar los cambios. Esta acción
                        quedará registrada en el historial del sistema.</p>
                </div>
            </div>

            <!-- Formulario -->
            <div class="">
                @include('pago.form')
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
            background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.8rem;
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

        /* Advertencia personalizada */
        .alert-warning-custom {
            background: linear-gradient(135deg, #fef3cd 0%, #fdeaa8 100%);
            border: 2px solid #f59e0b;
            border-radius: 16px;
            padding: 20px 24px;
            margin-bottom: 32px;
            display: flex;
            align-items: flex-start;
            gap: 16px;
            box-shadow: 0 4px 16px rgba(245, 158, 11, 0.15);
        }

        .alert-icon {
            width: 40px;
            height: 40px;
            background: #f59e0b;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .alert-content {
            flex: 1;
        }

        .alert-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #92400e;
            margin: 0 0 8px 0;
        }

        .alert-text {
            font-size: 0.95rem;
            color: #78350f;
            margin: 0;
            line-height: 1.5;
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

            .alert-warning-custom {
                padding: 16px 20px;
                margin-bottom: 24px;
            }

            .alert-icon {
                width: 36px;
                height: 36px;
                font-size: 1.1rem;
            }

            .alert-title {
                font-size: 1rem;
            }

            .alert-text {
                font-size: 0.9rem;
            }
        }
    </style>
@endsection