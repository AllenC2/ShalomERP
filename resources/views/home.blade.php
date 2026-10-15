@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

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

<style>
.agenda-card {
    transition: all 0.3s ease;
    min-height: 300px;
}


.agenda-card.today {
    background: linear-gradient(135deg, #e3f2fd 0%, #f8f9fa 100%);
}

.pago-item {
    transition: all 0.2s ease;
    cursor: pointer;
}

.pago-item:hover {
    background-color: #f8f9fa !important;
    transform: translateX(2px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.pago-link {
    transition: all 0.2s ease;
}

.pago-link:hover .pago-item {
    transform: translateX(4px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.agenda-nav-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
    border: 2px solid #E1B240;
    color: white;
    transition: all 0.3s ease;
}

.agenda-nav-btn:hover {
    background: linear-gradient(135deg, #79481D 0%, #E1B240 100%);
    border-color: #79481D;
    color: white;
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(225, 178, 64, 0.4);
}

.agenda-nav-btn.btn-primary {
    background: linear-gradient(135deg, #79481D 0%, #E1B240 100%);
    border-color: #79481D;
}

.agenda-nav-btn.btn-primary:hover {
    background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
    border-color: #E1B240;
}

.agenda-header {
    background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Estilos para el mini calendario */
.mini-calendario {
    background: transparent;
    border: 1px solid rgba(222, 226, 230, 0.3);
    border-radius: 8px;
    box-shadow: none;
    position: relative;
    width: 100%;
    max-width: 300px;
}

.calendario-header {
    background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
    color: white;
    padding: 10px;
    border-radius: 8px 8px 0 0;
    text-align: center;
    font-weight: bold;
}

.calendario-nav {
    background: none;
    border: none;
    color: white;
    font-size: 1.2rem;
    cursor: pointer;
    padding: 5px 8px;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.calendario-nav:hover {
    background: rgba(255,255,255,0.2);
}

.calendario-dias-semana {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    background: transparent;
    padding: 8px 5px;
    font-size: 0.7rem;
    font-weight: bold;
    text-align: center;
    color: #6c757d;
}

.calendario-dias {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    background: transparent;
    padding: 5px;
}

.calendario-dia {
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.8rem;
    transition: all 0.2s ease;
    position: relative;
    color: #495057;
}

.calendario-dia:hover {
    background: rgba(227, 242, 253, 0.3);
    border-color: rgba(227, 242, 253, 0.5);
}

.calendario-dia.hoy {
    background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
    color: white;
    font-weight: bold;
}

.calendario-dia.seleccionado {
    background: #2196f3;
    color: white;
    font-weight: bold;
}

.calendario-dia.otro-mes {
    color: #adb5bd;
    background: rgba(248, 249, 250, 0.1);
}

.calendario-dia.con-pagos::after {
    content: '';
    position: absolute;
    bottom: 2px;
    left: 50%;
    transform: translateX(-50%);
    width: 4px;
    height: 4px;
    background: #E1B240;
    border-radius: 50%;
}

.calendario-dia.hoy.con-pagos::after {
    background: white;
}

/* Estilos para paginación */
#controlesPaginacion {
    background: rgba(248, 249, 250, 0.3);
    border-radius: 8px;
    padding: 12px;
    margin-top: 15px;
}

.btn-outline-gold {
    color: #79481D;
    border-color: #E1B240;
    background: transparent;
}
.btn-check:checked + .btn-outline-gold {
    background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
    color: white;
    border-color: #79481D;
    box-shadow: inset 0 3px 5px rgba(0,0,0,0.125);
}
.btn-outline-gold:hover {
    color: #79481D;
    background-color: rgba(225, 178, 64, 0.1);
    border-color: #E1B240;
}

#controlesPaginacion .btn-outline-secondary {
    border-color: #79481D;
    color: #79481D;
    background: transparent;
    font-size: 0.8rem;
    padding: 0.375rem 0.75rem;
    transition: all 0.2s ease;
}

#controlesPaginacion .btn-outline-secondary:hover:not(:disabled) {
    background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
    border-color: #E1B240;
    color: white;
}

#controlesPaginacion .btn-outline-secondary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

#itemsPorPagina {
    border-color: #79481D;
    font-size: 0.8rem;
    padding: 0.25rem 0.5rem;
    min-width: 70px;
}

#itemsPorPagina:focus {
    border-color: #E1B240;
    box-shadow: 0 0 0 0.2rem rgba(225, 178, 64, 0.25);
}

#infoPaginacion {
    background: rgba(121, 72, 29, 0.1);
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
}

/* Estilos para el badge de rango de fechas */
#rangoFechas {
    transition: all 0.3s ease;
    border: 1px solid rgba(121, 72, 29, 0.2);
    font-weight: 500;
    letter-spacing: 0.02em;
}

#rangoFechas:hover {
    background: rgba(121, 72, 29, 0.15) !important;
    border-color: rgba(121, 72, 29, 0.3);
    transform: translateY(-1px);
}

@media (max-width: 768px) {
    .agenda-card {
        min-height: 150px;
    }
    
    .col {
        min-width: 120px;
    }
    
    .mini-calendario {
        max-width: 250px;
    }
    
    /* Ajustes responsive para paginación */
    #controlesPaginacion {
        flex-direction: column;
        gap: 10px;
        align-items: stretch !important;
    }
    
    #controlesPaginacion .btn-group {
        justify-content: center;
    }
    
    #controlesPaginacion .d-flex {
        justify-content: center;
    }
    
    #infoPaginacion {
        text-align: center;
        margin-top: 5px;
    }
}

/* Rutas del empleado - Mobile First (Apple-like) */
.custom-marker { background: transparent !important; border: none !important; }
.leaflet-popup-content-wrapper { border-radius: 14px !important; }
.leaflet-popup-content { margin: 12px 16px !important; font-size: 0.85rem !important; }
#visitaGpsMap { position: relative; z-index: 0; }
#visitaGpsMap .leaflet-pane,
#visitaGpsMap .leaflet-top,
#visitaGpsMap .leaflet-bottom { z-index: 1; }
#visitaConfirmModal,
#omitirConfirmModal,
#misRutasModal { z-index: 1065; }

.ios-confirm-dialog { max-width: 360px; margin: 1rem auto; }
.ios-confirm-card {
    background: rgba(255,255,255,0.92) !important;
    backdrop-filter: saturate(180%) blur(28px);
    -webkit-backdrop-filter: saturate(180%) blur(28px);
    border: none !important;
    border-radius: 28px !important;
    box-shadow: 0 24px 80px rgba(0,0,0,0.22) !important;
    overflow: hidden;
    font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'SF Pro Text', system-ui, sans-serif;
    text-align: center;
    padding: 22px 18px 12px;
}
.ios-confirm-icon {
    width: 56px; height: 56px; border-radius: 16px; margin: 0 auto 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.45rem; color: #fff;
}
.ios-confirm-icon-visit { background: #34C759; }
.ios-confirm-icon-skip { background: #FF3B30; }
.ios-confirm-title {
    font-size: 1.22rem; font-weight: 700; color: #1c1c1e;
    letter-spacing: -0.03em; margin: 0 0 6px; line-height: 1.2;
}
.ios-confirm-msg {
    font-size: 0.84rem; color: #636366; line-height: 1.35;
    margin: 0 0 14px; letter-spacing: -0.01em;
}
.visita-form-header {
    display: flex; align-items: flex-start; gap: 12px;
    text-align: left; margin: 0 0 14px;
    padding: 12px; border-radius: 16px;
    background: rgba(242,242,247,0.95);
}
.visita-form-header .ios-confirm-icon {
    width: 44px; height: 44px; border-radius: 12px; margin: 0;
    font-size: 1.15rem; flex-shrink: 0;
}
.visita-form-header-body { min-width: 0; flex: 1; }
.visita-form-header-top {
    display: flex; align-items: center; justify-content: space-between;
    gap: 8px; margin-bottom: 2px;
}
.visita-form-header .ios-confirm-title {
    font-size: 1.05rem; margin: 0; line-height: 1.2;
}
.visita-form-contrato {
    font-size: 0.68rem; font-weight: 700; color: #79481D;
    background: rgba(121,72,29,0.1); border-radius: 980px;
    padding: 3px 8px; white-space: nowrap; flex-shrink: 0;
}
.visita-form-nombre {
    font-size: 0.92rem; font-weight: 700; color: #1c1c1e;
    letter-spacing: -0.02em; line-height: 1.25;
    margin: 0 0 2px;
}
.visita-form-domicilio {
    font-size: 0.75rem; color: #8e8e93; line-height: 1.3;
    margin: 0; display: -webkit-box; -webkit-line-clamp: 2;
    -webkit-box-orient: vertical; overflow: hidden;
}
.visita-abono-confirm {
    margin-top: 10px;
    text-align: left;
}
.visita-abono-confirm .ios-banner {
    margin-bottom: 0;
}
.visita-abono-confirm-title {
    display: block; font-weight: 700; margin-bottom: 6px;
}
.visita-abono-confirm-resumen {
    font-size: 0.8rem; line-height: 1.4;
}
.visita-abono-confirm-resumen strong {
    font-weight: 700; color: #1c1c1e;
}
.ios-grouped {
    list-style: none; margin: 0 0 12px; padding: 0;
    background: rgba(242,242,247,0.9); border-radius: 14px;
    overflow: hidden; text-align: left;
}
.ios-grouped li {
    display: flex; justify-content: space-between; align-items: flex-start; gap: 12px;
    padding: 10px 14px; font-size: 0.8rem;
    border-bottom: 0.5px solid rgba(60,60,67,0.12);
}
.ios-grouped li:last-child { border-bottom: none; }
.ios-grouped li span { color: #8e8e93; flex-shrink: 0; }
.ios-grouped li strong {
    font-weight: 600; color: #1c1c1e; text-align: right;
    letter-spacing: -0.01em; word-break: break-word;
}
.ios-confirm-map-wrap {
    position: relative;
    margin-bottom: 10px;
}
.ios-confirm-map {
    height: 180px; border-radius: 14px; overflow: hidden;
    background: #e9ecef; margin-bottom: 0;
}
.visita-gps-recenter {
    position: absolute; right: 10px; bottom: 10px; z-index: 1000;
    width: 40px; height: 40px; border: none; border-radius: 12px;
    background: #fff; color: #007AFF; box-shadow: 0 2px 10px rgba(0,0,0,0.18);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.15rem; -webkit-tap-highlight-color: transparent;
}
.visita-gps-recenter:active { transform: scale(0.94); opacity: 0.85; }
.visita-gps-recenter:disabled { opacity: 0.45; }
.ios-chip-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    width: 100%; border: 1px dashed rgba(60,60,67,0.28); border-radius: 12px;
    background: rgba(242,242,247,0.7); color: #636366; font-size: 0.84rem; font-weight: 600;
    padding: 10px 12px; margin-bottom: 10px; -webkit-tap-highlight-color: transparent;
}
.ios-chip-btn.active {
    border-style: solid; border-color: rgba(255,59,48,0.35);
    background: rgba(255,59,48,0.1); color: #FF3B30;
}
.visita-metodo-group {
    display: grid; grid-template-columns: 1fr 1fr; gap: 8px;
}
.visita-metodo-option { position: relative; }
.visita-metodo-option input {
    position: absolute; opacity: 0; width: 0; height: 0;
}
.visita-metodo-option label {
    display: flex; align-items: center; gap: 6px; margin: 0;
    padding: 10px 8px; border: 1px solid #d1d9e0; border-radius: 10px;
    background: #fff; font-size: 0.72rem; font-weight: 600; color: #24292f;
    cursor: pointer; min-height: 42px;
}
.visita-metodo-option input:checked + label {
    border-color: #0969da; background: #dbeafe; color: #0969da;
}
.visita-metodo-option label i { font-size: 0.95rem; }
.ios-wizard-dots {
    display: flex; justify-content: center; gap: 6px; margin: 0 0 12px;
}
.ios-wizard-dot {
    width: 7px; height: 7px; border-radius: 50%; background: #d1d1d6;
}
.ios-wizard-dot.active { background: #007AFF; }
.ios-wizard-dot.done { background: #34C759; }
.ios-field {
    text-align: left; margin: 0 0 10px;
}
.ios-field label {
    display: block; font-size: 0.72rem; font-weight: 600; color: #8e8e93;
    margin: 0 0 4px; letter-spacing: 0.02em; text-transform: uppercase;
}
.ios-field input, .ios-field select {
    width: 100%; border: none; border-radius: 12px; padding: 10px 12px;
    background: rgba(242,242,247,0.95); font-size: 0.92rem; color: #1c1c1e;
}
.ios-seg-mini {
    display: flex; background: rgba(118,118,128,0.12); border-radius: 12px; padding: 3px; gap: 3px;
}
.ios-seg-mini button {
    flex: 1; border: none; background: transparent; border-radius: 10px;
    padding: 8px 6px; font-size: 0.82rem; font-weight: 600; color: #636366;
}
.ios-seg-mini button.active { background: #fff; color: #1c1c1e; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
.ios-folio-card {
    text-align: left; background: rgba(242,242,247,0.9); border-radius: 14px;
    padding: 12px; margin-bottom: 10px;
}
.ios-folio-card h4 { font-size: 0.88rem; font-weight: 700; margin: 0 0 4px; color: #1c1c1e; }
.ios-folio-card .saldo { font-size: 0.75rem; color: #8e8e93; margin-bottom: 8px; }
.visita-monto-field { margin: 10px 0 12px; }
.visita-monto-field > label {
    display: block; font-size: 0.72rem; font-weight: 700; color: #79481D;
    margin: 0 0 6px; letter-spacing: 0.04em; text-transform: uppercase;
}
.visita-monto-wrap {
    display: flex; align-items: center; gap: 4px;
    background: #fff;
    border: 2px solid #79481D;
    border-radius: 16px;
    padding: 4px 14px;
    box-shadow: 0 4px 16px rgba(121, 72, 29, 0.12);
}
.visita-monto-wrap:focus-within {
    border-color: #E1B240;
    box-shadow: 0 0 0 4px rgba(225, 178, 64, 0.25);
}
.visita-monto-prefix {
    font-size: 1.55rem; font-weight: 800; color: #79481D;
    letter-spacing: -0.03em; line-height: 1;
}
.visita-folio-monto {
    flex: 1; width: 100%; border: none !important; background: transparent !important;
    font-size: 2rem !important; font-weight: 800 !important; color: #1c1c1e !important;
    letter-spacing: -0.04em; padding: 10px 0 !important; border-radius: 0 !important;
    outline: none; -moz-appearance: textfield;
}
.visita-folio-monto::-webkit-outer-spin-button,
.visita-folio-monto::-webkit-inner-spin-button {
    -webkit-appearance: none; margin: 0;
}
.visita-folio-monto::placeholder { color: #c7c7cc; font-weight: 700; }
.visita-ticket-preview {
    display: flex;
    flex-direction: column;
    flex: 1 1 auto;
    min-height: 280px;
    max-height: min(62vh, 520px);
    overflow: hidden;
    border-radius: 12px;
    background: #f2f2f7;
    border: 1px solid rgba(60, 60, 67, 0.12);
}
.visita-ticket-preview-scroll {
    flex: 1 1 auto;
    min-height: 0;
    overflow-x: hidden;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    overscroll-behavior: contain;
    touch-action: pan-y;
}
.visita-ticket-frame {
    display: block;
    width: 100%;
    min-height: 100%;
    height: 420px;
    border: none;
    background: #fff;
}
@media (hover: none) and (pointer: coarse) {
    .visita-ticket-frame {
        /* En touch el iframe no captura el gesto; el scroll vive en el wrapper. */
        pointer-events: none;
    }
}
.visita-wizard-card {
    display: flex;
    flex-direction: column;
    max-height: min(92vh, 760px);
}
.visita-wizard-body {
    flex: 1;
    min-height: 0;
    overflow-x: hidden;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    overscroll-behavior: contain;
    display: flex;
    flex-direction: column;
}
.visita-wizard-step {
    width: 100%;
}
.visita-wizard-step[data-step="2"]:not(.d-none) {
    flex: 1 1 auto;
    min-height: 0;
    display: flex;
    flex-direction: column;
}
.visita-wizard-card.is-ticket-step .visita-wizard-body {
    overflow: hidden;
    padding-bottom: 0;
}
.visita-wizard-card.is-ticket-step .visita-ticket-preview {
    max-height: none;
    height: 100%;
    border-radius: 12px;
}
.visita-wizard-actions {
    flex-shrink: 0;
    margin-top: 4px;
}

/* Visita wizard: pantalla completa en celular */
@media (max-width: 767.98px) {
    #visitaConfirmModal {
        padding: 0 !important;
    }
    #visitaConfirmModal .modal-dialog.visita-wizard-dialog {
        max-width: 100%;
        width: 100%;
        height: 100%;
        margin: 0;
        max-height: none;
    }
    #visitaConfirmModal .visita-wizard-card {
        border-radius: 0 !important;
        box-shadow: none !important;
        background: #ffffff !important;
        backdrop-filter: none;
        -webkit-backdrop-filter: none;
        height: 100dvh;
        max-height: 100dvh;
        padding: 0;
        overflow: hidden;
    }
    #visitaConfirmModal .visita-wizard-body {
        padding: max(14px, env(safe-area-inset-top)) 16px 12px;
        overscroll-behavior: contain;
        touch-action: pan-y;
    }
    #visitaConfirmModal .visita-wizard-card.is-ticket-step .visita-wizard-body {
        padding: max(8px, env(safe-area-inset-top)) 0 0;
        overflow: hidden;
    }
    #visitaConfirmModal .visita-wizard-actions {
        margin: 0;
        padding: 10px 18px max(14px, env(safe-area-inset-bottom));
        border-top: 0.5px solid rgba(60,60,67,0.12);
        background: #fff;
    }
    #visitaConfirmModal .ios-confirm-map {
        height: min(28vh, 200px);
    }
    #visitaConfirmModal .visita-ticket-preview {
        min-height: 0;
        max-height: none;
        border-radius: 0;
        border-left: none;
        border-right: none;
        border-bottom: none;
        background: #fff;
    }
    #visitaConfirmModal .visita-ticket-frame {
        min-height: 100%;
        height: auto;
    }
    #visitaConfirmModal .ios-confirm-icon {
        margin-top: 4px;
    }
    body.modal-open {
        overflow: hidden !important;
        overscroll-behavior: none;
    }
}
.ios-banner {
    text-align: left; font-size: 0.75rem; line-height: 1.35;
    border-radius: 12px; padding: 9px 12px; margin: 0 0 8px;
}
.ios-banner-info { background: rgba(0,122,255,0.1); color: #007AFF; }
.ios-banner-success { background: rgba(52,199,89,0.12); color: #248A3D; }
.ios-banner-warning { background: rgba(255,149,0,0.14); color: #C93400; }
.ios-banner-danger { background: rgba(255,59,48,0.12); color: #FF3B30; }
.ios-banner .ios-retry {
    display: block; width: 100%; margin-top: 8px; border: none;
    background: rgba(0,0,0,0.06); color: #1c1c1e; font-weight: 600;
    font-size: 0.75rem; border-radius: 10px; padding: 8px 10px;
}
.ios-confirm-actions { display: flex; flex-direction: column; gap: 4px; margin-top: 6px; }
.ios-btn {
    border: none; background: none; width: 100%; border-radius: 14px;
    font-size: 1.02rem; font-weight: 600; padding: 12px 14px;
    letter-spacing: -0.02em; -webkit-tap-highlight-color: transparent;
}
.ios-btn:active { opacity: 0.65; }
.ios-btn-primary { background: #007AFF; color: #fff; }
.ios-btn-primary:disabled { background: #007AFF; color: #fff; opacity: 0.38; }
.ios-btn-danger { background: #FF3B30; color: #fff; }
.ios-btn-plain { color: #007AFF; font-weight: 500; padding-top: 8px; padding-bottom: 10px; }
.modal-backdrop.show {
    background: rgba(22,22,23,0.38);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

/* Screens */
.shlom-screen { display: none; }
.shlom-screen.active { display: block; }
#screenRutaDetalle.active {
    position: fixed;
    inset: 0;
    z-index: 20;
    overflow: hidden;
    overscroll-behavior: none;
}
#screenRutaDetalle.active .shlom-map-full {
    touch-action: manipulation;
    overscroll-behavior: none;
}
body.shlom-ruta-detalle-open,
body.shlom-ruta-detalle-open .rainbow-background {
    overflow: hidden !important;
    overscroll-behavior: none;
    height: 100dvh;
    max-height: 100dvh;
}

/* ====== HEADER ====== */
.shlom-header { padding: 12px 16px 8px; }
.shlom-header-top { display: flex; justify-content: space-between; align-items: center; }
.shlom-title { font-size: 2rem; font-weight: 800; color: #1c1c1e; margin: 0; letter-spacing: -0.5px; }
.shlom-subtitle { font-size: 0.9rem; color: #8E8E93; margin: 0; }
.shlom-avatar {
    width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); color: white;
    font-weight: 700; font-size: 1rem; box-shadow: 0 2px 8px rgba(225,178,64,0.3);
}

/* ====== ROUTE LIST ====== */
.shlom-day-nav {
    display: flex; align-items: center; gap: 10px;
    padding: 4px 16px 12px;
}
.shlom-day-nav-btn {
    width: 36px; height: 36px; border: none; border-radius: 50%;
    background: rgba(118,118,128,0.12); color: #1c1c1e;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; flex-shrink: 0;
    cursor: pointer; text-decoration: none;
    position: relative; z-index: 2;
    -webkit-tap-highlight-color: transparent;
}
.shlom-day-nav-btn:hover,
.shlom-day-nav-btn:focus { color: #1c1c1e; text-decoration: none; }
.shlom-day-nav-btn:active { opacity: 0.65; transform: scale(0.94); }
.shlom-day-nav-center {
    flex: 1; text-align: center; min-width: 0;
}
.shlom-day-nav-title {
    display: block; font-size: 1.15rem; font-weight: 700; color: #1c1c1e;
    letter-spacing: -0.03em; line-height: 1.2;
}
.shlom-day-nav-sub {
    display: block; font-size: 0.78rem; color: #8e8e93; margin-top: 1px;
}
.shlom-section-label {
    font-size: 0.72rem; font-weight: 600; color: #8e8e93;
    text-transform: uppercase; letter-spacing: 0.06em;
    padding: 4px 20px 8px;
}
.shlom-btn-mis-rutas {
    display: inline-flex; align-items: center; gap: 6px;
    border: none; border-radius: 980px;
    background: rgba(121, 72, 29, 0.1); color: #79481D;
    font-size: 0.78rem; font-weight: 600; letter-spacing: -0.01em;
    padding: 7px 12px 7px 10px;
    -webkit-tap-highlight-color: transparent;
    white-space: nowrap;
}
.shlom-btn-mis-rutas:active { opacity: 0.7; transform: scale(0.96); }
.shlom-btn-mis-rutas .badge {
    background: #79481D; color: #fff;
    font-size: 0.65rem; font-weight: 700;
    min-width: 18px; padding: 2px 5px; border-radius: 980px;
}
#misRutasModal .modal-dialog { max-width: 400px; margin: 1rem auto; }
#misRutasModal .modal-content {
    border: none; border-radius: 22px;
    box-shadow: 0 24px 80px rgba(0,0,0,0.22);
    overflow: hidden;
}
#misRutasModal .modal-header {
    border-bottom: 0.5px solid rgba(60,60,67,0.12);
    padding: 16px 18px 12px;
}
#misRutasModal .modal-title {
    font-size: 1.1rem; font-weight: 700; color: #1c1c1e;
    letter-spacing: -0.02em;
}
#misRutasModal .modal-body {
    padding: 12px 14px 18px;
    max-height: min(70vh, 520px);
    overflow-y: auto;
}
#misRutasModal .mis-rutas-search {
    display: flex; align-items: center; gap: 8px;
    background: #f2f2f7; border-radius: 12px;
    padding: 0 12px; margin-bottom: 10px;
    border: 1px solid transparent;
    transition: border-color 0.15s ease, background 0.15s ease;
}
#misRutasModal .mis-rutas-search:focus-within {
    background: #fff;
    border-color: rgba(121,72,29,0.35);
}
#misRutasModal .mis-rutas-search > i {
    color: #8e8e93; font-size: 0.9rem; flex-shrink: 0;
}
#misRutasModal .mis-rutas-search input {
    flex: 1; min-width: 0; border: none; background: transparent;
    font-size: 0.9rem; color: #1c1c1e; padding: 11px 0;
    outline: none; box-shadow: none;
}
#misRutasModal .mis-rutas-search input::placeholder { color: #8e8e93; }
#misRutasModal .mis-rutas-search-clear {
    border: none; background: rgba(60,60,67,0.12); color: #636366;
    width: 22px; height: 22px; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 0.85rem; line-height: 1; padding: 0; flex-shrink: 0;
}
#misRutasModal .mis-rutas-search-clear.d-none { display: none !important; }
#misRutasModal .mis-rutas-search-wrap.d-none { display: none !important; }
#misRutasModal .mis-rutas-search-results {
    display: none; flex-direction: column; gap: 10px;
}
#misRutasModal .mis-rutas-search-results.active { display: flex; }
#misRutasModal .mis-rutas-search-hit {
    display: flex; align-items: flex-start; gap: 10px;
    background: #fff; border: 1px solid rgba(60,60,67,0.12);
    border-radius: 14px; padding: 12px; cursor: pointer;
    -webkit-tap-highlight-color: transparent;
    text-align: left; width: 100%;
}
#misRutasModal .mis-rutas-search-hit:active {
    transform: scale(0.98);
    border-color: rgba(121,72,29,0.35);
}
#misRutasModal .mis-rutas-search-hit-route {
    font-size: 0.68rem; font-weight: 600; color: #79481D;
    margin: 0 0 4px;
}
#misRutasModal .mis-rutas-page {
    display: none;
    flex-direction: column;
    gap: 10px;
}
#misRutasModal .mis-rutas-page.active {
    display: flex;
}
#misRutasModal .mis-rutas-page.mis-rutas-page-dimmed {
    display: none !important;
}
#misRutasModal .shlom-plantilla-card {
    box-shadow: none;
    background: #fff;
    border: 1px solid rgba(60,60,67,0.12);
    margin-bottom: 0;
    cursor: pointer;
    -webkit-tap-highlight-color: transparent;
    transition: transform 0.15s ease, border-color 0.15s ease;
}
#misRutasModal .shlom-plantilla-card:active {
    transform: scale(0.98);
    border-color: rgba(121,72,29,0.35);
}
#misRutasModal .shlom-plantilla-chevron {
    color: #c7c7cc; font-size: 1rem; flex-shrink: 0;
}
#misRutasModal .mis-rutas-parada {
    display: flex; align-items: flex-start; gap: 10px;
    background: #fff; border: 1px solid rgba(60,60,67,0.12);
    border-radius: 14px; padding: 12px;
}
#misRutasModal .mis-rutas-parada-num {
    width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0;
    background: #79481D; color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.75rem; font-weight: 700;
}
#misRutasModal .mis-rutas-parada-body { min-width: 0; flex: 1; }
#misRutasModal .mis-rutas-parada-name {
    font-size: 0.9rem; font-weight: 700; color: #1c1c1e;
    letter-spacing: -0.02em; line-height: 1.25; margin: 0 0 2px;
}
#misRutasModal .mis-rutas-parada-addr {
    font-size: 0.74rem; color: #8e8e93; line-height: 1.35; margin: 0;
}
#misRutasModal .mis-rutas-parada-meta {
    display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px;
}
#misRutasModal .mis-rutas-parada-chip {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 0.68rem; font-weight: 600; color: #636366;
    background: rgba(242,242,247,0.95); border-radius: 980px; padding: 3px 8px;
}
#misRutasModal .mis-rutas-detail-empty {
    text-align: center; color: #8e8e93; font-size: 0.85rem; padding: 24px 8px;
}
#misRutasModal .btn-mis-rutas-back {
    display: inline-flex; align-items: center; gap: 4px;
    border: none; background: transparent; color: #007AFF;
    font-size: 0.9rem; font-weight: 600; padding: 0; margin: 0 0 4px;
}
#misRutasModal .mis-rutas-detail-title {
    font-size: 1.05rem; font-weight: 700; color: #1c1c1e;
    letter-spacing: -0.02em; margin: 0 0 2px;
}
#misRutasModal .mis-rutas-detail-sub {
    font-size: 0.78rem; color: #8e8e93; margin: 0 0 6px;
}
#misRutasModal #misRutasDetailList {
    display: flex; flex-direction: column; gap: 10px;
}
#misRutasModal .btn-ver-ruta-plantilla {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    width: 100%; border: none; border-radius: 12px;
    background: #79481D; color: #fff;
    font-size: 0.92rem; font-weight: 700; letter-spacing: -0.01em;
    padding: 12px 14px; margin: 2px 0 4px;
    -webkit-tap-highlight-color: transparent;
    transition: opacity 0.15s ease, transform 0.15s ease;
}
#misRutasModal .btn-ver-ruta-plantilla:active { transform: scale(0.98); }
#misRutasModal .btn-ver-ruta-plantilla:disabled {
    opacity: 0.65; cursor: wait;
}
#misRutasModal .shlom-empty-state { padding: 28px 12px; }

/* Mis rutas: pantalla completa en celular */
@media (max-width: 767.98px) {
    #misRutasModal {
        padding: 0 !important;
    }
    #misRutasModal .modal-dialog {
        max-width: 100%;
        width: 100%;
        height: 100%;
        margin: 0;
        max-height: none;
    }
    #misRutasModal .modal-content {
        border: none;
        border-radius: 0;
        box-shadow: none;
        height: 100%;
        height: 100dvh;
        max-height: 100dvh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        background: #fff;
    }
    #misRutasModal .modal-header {
        flex-shrink: 0;
        border-bottom: 0.5px solid rgba(60,60,67,0.12);
        padding: max(12px, env(safe-area-inset-top)) 16px 12px;
        background: #fff;
    }
    #misRutasModal .modal-body {
        flex: 1 1 auto;
        min-height: 0;
        max-height: none !important;
        height: auto;
        overflow-x: hidden;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior: contain;
        touch-action: pan-y;
        padding: 12px 16px max(20px, env(safe-area-inset-bottom));
        background: #fff;
    }
    #misRutasModal .mis-rutas-search-wrap {
        position: sticky;
        top: -12px;
        z-index: 3;
        background: #fff;
        margin: -12px -16px 10px;
        padding: 12px 16px 8px;
    }
    #misRutasModal .mis-rutas-search {
        margin-bottom: 0;
    }
    #misRutasModal .mis-rutas-page,
    #misRutasModal .mis-rutas-search-results {
        padding-bottom: 8px;
    }
}
.shlom-route-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 0 16px 40px;
}
.shlom-plantilla-card {
    background: white; border-radius: 16px; padding: 12px 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    display: flex; align-items: center; gap: 12px;
}
.shlom-plantilla-icon {
    width: 40px; height: 40px; border-radius: 12px; flex-shrink: 0;
    background: linear-gradient(135deg, rgba(225,178,64,0.18) 0%, rgba(121,72,29,0.14) 100%);
    color: #79481D;
    display: flex; align-items: center; justify-content: center; font-size: 0.95rem;
}
.shlom-plantilla-body { min-width: 0; flex: 1; }
.shlom-plantilla-name {
    font-size: 0.98rem; font-weight: 700; color: #1c1c1e;
    letter-spacing: -0.02em; line-height: 1.25;
    margin: 0 0 3px;
}
.shlom-plantilla-meta {
    display: flex; align-items: center; flex-wrap: wrap; gap: 6px;
    font-size: 0.74rem; color: #8e8e93; font-weight: 500; line-height: 1.2;
}
.shlom-plantilla-meta-item {
    display: inline-flex; align-items: center; gap: 4px;
}
.shlom-plantilla-meta-item i { font-size: 0.7rem; opacity: 0.9; }
.shlom-plantilla-meta-sep {
    width: 3px; height: 3px; border-radius: 50%;
    background: #c7c7cc; flex-shrink: 0;
}
.shlom-route-card {
    background: white; border-radius: 18px; padding: 16px; position: relative;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06); transition: transform 0.2s ease, box-shadow 0.2s ease;
    cursor: pointer; user-select: none; -webkit-tap-highlight-color: transparent;
}
.shlom-route-card:active { transform: scale(0.98); box-shadow: 0 1px 6px rgba(0,0,0,0.1); }
.shlom-route-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
.shlom-route-status {
    display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px;
    border-radius: 20px; font-size: 0.72rem; font-weight: 600;
}
.shlom-route-status i { font-size: 0.7rem; }
.shlom-route-id { font-size: 0.72rem; color: #8E8E93; font-weight: 500; }
.shlom-route-card-body { }
.shlom-route-date { display: flex; align-items: center; gap: 6px; font-size: 0.85rem; color: #1c1c1e; font-weight: 600; margin-bottom: 8px; }
.shlom-route-date i { color: #79481D; font-size: 0.8rem; }
.shlom-route-stats { display: flex; align-items: center; gap: 12px; margin-bottom: 10px; }
.shlom-stat { display: flex; align-items: center; gap: 4px; font-size: 0.8rem; color: #8E8E93; }
.shlom-stat i { font-size: 0.75rem; }
.shlom-stat small { font-size: 0.72rem; }
.shlom-stat-success { color: #34C759; }
.shlom-stat-danger { color: #FF3B30; }
.shlom-progress-track {
    height: 6px; background: rgba(120,120,128,0.12); border-radius: 3px; overflow: hidden;
}
.shlom-progress-fill {
    height: 100%; background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
    border-radius: 3px; transition: width 0.4s ease;
}
.shlom-route-card-arrow {
    position: absolute; right: 16px; top: 50%; transform: translateY(-50%);
    color: #C7C7CC; font-size: 1.1rem;
}
.shlom-empty-state {
    text-align: center; padding: 60px 20px; color: #8E8E93;
}
.shlom-empty-icon {
    width: 80px; height: 80px; margin: 0 auto 16px; border-radius: 20px;
    background: rgba(225,178,64,0.1); display: flex; align-items: center; justify-content: center;
    font-size: 2rem; color: #E1B240;
}
.shlom-empty-state h3 { font-size: 1.1rem; font-weight: 700; color: #1c1c1e; margin-bottom: 4px; }
.shlom-empty-state p { font-size: 0.9rem; margin: 0; }

/* ====== DETAIL SCREEN ====== */
.shlom-detail-topbar {
    position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
    display: flex; align-items: center; gap: 10px; padding: 10px 16px;
    background: rgba(255,255,255,0.85); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
    border-bottom: 0.5px solid rgba(60,60,67,0.12);
}
.shlom-back-btn {
    width: 36px; height: 36px; border: none; background: none; display: flex;
    align-items: center; justify-content: center; font-size: 1.3rem;
    color: #79481D; border-radius: 50%; -webkit-tap-highlight-color: transparent;
}
.shlom-back-btn:active { background: rgba(121,72,29,0.1); }
.shlom-detail-info { flex-grow: 1; display: flex; flex-direction: column; min-width: 0; gap: 1px; }
.shlom-detail-title {
    font-size: 1.05rem; font-weight: 700; color: #1c1c1e; line-height: 1.2;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.shlom-detail-freq {
    font-size: 0.74rem; font-weight: 500; color: #8e8e93; line-height: 1.2;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.shlom-detail-freq.d-none { display: none !important; }
.shlom-ruta-meta {
    display: flex; flex-direction: column; gap: 8px;
    padding: 0 16px;
    flex-shrink: 0;
}
.shlom-ruta-meta-row {
    display: flex; align-items: center; flex-wrap: wrap; gap: 6px 8px;
}
.shlom-ruta-meta-item {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 0.78rem; font-weight: 500; color: #636366;
}
.shlom-ruta-meta-item i { font-size: 0.72rem; color: #8e8e93; }
.shlom-ruta-meta-badge {
    margin-left: auto;
    padding: 4px 10px; border-radius: 20px; font-size: 0.72rem; font-weight: 600;
}
.shlom-ruta-meta-progress {
    display: flex; align-items: center; gap: 8px;
}
.shlom-ruta-meta-progress-track {
    flex: 1; height: 4px;
    background: rgba(120,120,128,0.12);
    border-radius: 3px; overflow: hidden;
}
.shlom-ruta-meta-progress-fill {
    height: 100%; width: 0%;
    background: linear-gradient(90deg, #E1B240 0%, #79481D 100%);
    border-radius: 3px;
    transition: width 0.35s ease;
}
.shlom-ruta-meta-progress-pct {
    flex-shrink: 0;
    font-size: 0.68rem; font-weight: 600; color: #8e8e93;
    min-width: 2.2em; text-align: right;
}

/* ====== MAP (full screen behind sheet) ====== */
.shlom-map-full { position: fixed; top: 57px; left: 0; right: 0; bottom: 0; z-index: 1; }
.shlom-map { width: 100%; height: 100%; background: #e9ecef; }

/* ====== BOTTOM SHEET ====== */
.shlom-bottom-sheet {
    position: fixed; left: 0; right: 0; bottom: 0; z-index: 1000;
    background: white; border-radius: 20px 20px 0 0;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
    height: var(--shlom-sheet-collapsed, 320px);
    max-height: 85vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    will-change: height;
    -webkit-user-select: none; user-select: none;
    transition: height 0.35s cubic-bezier(0.32, 0.72, 0, 1);
    transform: none;
    touch-action: none;
    overscroll-behavior: none;
}
.shlom-bottom-sheet.expanded {
    height: 85vh;
    transform: none;
}
.shlom-sheet-handle {
    display: flex; justify-content: center; padding: 10px 0 6px; cursor: grab;
    -webkit-tap-highlight-color: transparent;
    flex-shrink: 0;
}
.shlom-sheet-handle:active { cursor: grabbing; }
.shlom-handle-bar {
    width: 40px; height: 5px; border-radius: 3px; background: rgba(60,60,67,0.25);
}
.shlom-sheet-content {
    padding: 0 0 14px;
    flex: 1;
    min-height: 0;
    overflow-x: hidden;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    overscroll-behavior: none;
    touch-action: pan-y;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.shlom-bottom-sheet:not(.expanded) .shlom-sheet-content {
    overflow-y: hidden;
    touch-action: none;
}
.shlom-bottom-sheet.expanded .shlom-sheet-content {
    touch-action: pan-y;
}
.shlom-bottom-sheet.expanded .shlom-slider-track {
    touch-action: pan-x pan-y;
}
.shlom-bottom-sheet:not(.expanded) .shlom-ruta-meta,
.shlom-bottom-sheet:not(.expanded) .shlom-slider-track,
.shlom-bottom-sheet:not(.expanded) .shlom-slider-controls {
    flex-shrink: 0;
}
.shlom-seg-label {
    font-size: 0.65rem; color: #8e8e93; font-weight: 600;
    text-transform: uppercase; letter-spacing: 0.05em;
    padding: 0 16px 6px;
    font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Text', system-ui, sans-serif;
}
.shlom-seg {
    display: flex; background: rgba(118,118,128,0.12); border-radius: 9px;
    padding: 2px; margin: 0 16px;
}
.shlom-seg-tab {
    flex: 1; border: none; background: transparent; border-radius: 7px;
    font-size: 0.78rem; font-weight: 600; padding: 7px 4px; color: #1c1c1e;
    font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Text', system-ui, sans-serif;
    -webkit-tap-highlight-color: transparent;
}
.shlom-seg-tab.active {
    background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,0.12);
}
.shlom-seg-tab:disabled { opacity: 0.4; }
.shlom-punto-casa-block { margin: 0; position: relative; }
.shlom-punto-casa-block.d-none { display: none !important; }
.shlom-punto-casa-block.is-loading .shlom-seg {
    opacity: 0.45; pointer-events: none;
}
.shlom-punto-casa-loading {
    display: none;
    align-items: center; justify-content: center; gap: 8px;
    margin: 0 16px 0;
    padding: 8px 10px;
    border-radius: 10px;
    background: rgba(242,242,247,0.95);
    color: #636366;
    font-size: 0.78rem; font-weight: 600;
}
.shlom-punto-casa-block.is-loading .shlom-punto-casa-loading {
    display: flex;
}
.shlom-punto-casa-loading .spinner-border {
    width: 1rem; height: 1rem;
    border-width: 0.15em;
    color: #79481D;
}
.shlom-parada-registro {
    margin: 0 16px;
    padding: 12px 14px;
    background: #f2f2f7;
    border-radius: 14px;
    font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Text', system-ui, sans-serif;
}
.shlom-parada-registro.d-none { display: none !important; }
.shlom-parada-registro-kicker {
    font-size: 0.65rem; font-weight: 700; color: #8e8e93;
    text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 8px;
}
.shlom-parada-registro-row {
    display: flex; align-items: flex-start; justify-content: space-between;
    gap: 10px; margin-bottom: 8px;
}
.shlom-parada-registro-row:last-child { margin-bottom: 0; }
.shlom-parada-registro-label {
    font-size: 0.72rem; color: #8e8e93; font-weight: 500; margin: 0 0 2px;
}
.shlom-parada-registro-value {
    font-size: 0.88rem; color: #1c1c1e; font-weight: 600; margin: 0; line-height: 1.3;
}
.shlom-parada-registro-value small {
    display: block; font-size: 0.74rem; font-weight: 500; color: #636366; margin-top: 2px;
}
.shlom-parada-registro-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 4px 8px; border-radius: 980px;
    font-size: 0.68rem; font-weight: 700; flex-shrink: 0;
}
.shlom-parada-registro-badge.is-abono {
    background: rgba(52,199,89,0.16); color: #34C759;
}
.shlom-parada-registro-badge.is-visita {
    background: rgba(255,149,0,0.16); color: #FF9500;
}
.shlom-parada-registro-pagos {
    margin-top: 8px; padding-top: 8px;
    border-top: 1px solid rgba(60,60,67,0.08);
    display: flex; flex-direction: column; gap: 6px;
}
.shlom-parada-registro-pago {
    display: flex; align-items: center; justify-content: space-between; gap: 8px;
    font-size: 0.8rem;
}
.shlom-parada-registro-pago .metodo { color: #636366; font-weight: 500; }
.shlom-parada-registro-pago .monto { color: #1c1c1e; font-weight: 700; }
.shlom-parada-registro-actions {
    display: flex; gap: 8px; margin-top: 10px;
}
.shlom-parada-registro-actions .shlom-card-btn {
    flex: 1;
}
.shlom-ruta-resumen {
    margin: 0 16px;
    padding: 12px 14px 10px;
    background: #f2f2f7;
    border-radius: 16px;
    font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Text', system-ui, sans-serif;
}
.shlom-ruta-resumen.d-none { display: none !important; }
.shlom-resumen-kicker {
    font-size: 0.65rem; font-weight: 600; color: #8e8e93;
    text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;
}
.shlom-resumen-title {
    font-size: 1.05rem; font-weight: 700; color: #1c1c1e;
    letter-spacing: -0.03em; margin: 0 0 4px; line-height: 1.25;
}
.shlom-resumen-text {
    font-size: 0.82rem; color: #636366; line-height: 1.35;
    margin: 0 0 10px;
}
.shlom-resumen-stats { margin-bottom: 0 !important; }
/* ====== SLIDER (inside sheet) ====== */
.shlom-slider-track {
    display: flex; gap: 12px; overflow-x: auto; overflow-y: visible;
    scroll-snap-type: x mandatory;
    padding: 0 16px; scrollbar-width: none; -ms-overflow-style: none;
    touch-action: pan-x;
    flex-shrink: 0;
}
.shlom-slider-track::-webkit-scrollbar { display: none; }

.shlom-slider-card {
    flex: 0 0 88%; max-width: 380px; scroll-snap-align: center;
    background: #f2f2f7; border-radius: 16px; padding: 16px;
    border: 1px solid transparent; min-width: 0;
    display: flex; flex-direction: column;
    font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'SF Pro Text', system-ui, sans-serif;
    transition: background 0.2s ease, border-color 0.2s ease;
}
.shlom-slider-card.is-pendiente {
    background: rgba(121,72,29,0.08);
    border-color: rgba(121,72,29,0.12);
}
.shlom-slider-card.is-pendiente .shlom-card-num { background: #79481D; }
.shlom-slider-card.is-abonada {
    background: rgba(52,199,89,0.14);
    border-color: rgba(52,199,89,0.22);
}
.shlom-slider-card.is-abonada .shlom-card-num { background: #34C759; }
.shlom-slider-card.is-visitada {
    background: rgba(255,149,0,0.14);
    border-color: rgba(255,149,0,0.22);
}
.shlom-slider-card.is-visitada .shlom-card-num { background: #FF9500; }
.shlom-slider-card.is-omitida {
    background: rgba(255,59,48,0.12);
    border-color: rgba(255,59,48,0.2);
}
.shlom-slider-card.is-omitida .shlom-card-num { background: #FF3B30; }
.shlom-slider-card .shlom-parada-estado {
    display: inline-flex; align-items: center; gap: 4px;
    margin-left: auto; flex-shrink: 0;
    padding: 3px 8px; border-radius: 980px;
    font-size: 0.68rem; font-weight: 700;
}
.shlom-slider-card-locked {
    opacity: 0.5;
    filter: grayscale(0.2);
}
.shlom-slider-card-locked .shlom-card-actions {
    pointer-events: none;
    opacity: 0.35;
}
.shlom-card-lock-note {
    display: flex; align-items: center; gap: 6px;
    font-size: 0.75rem; color: #8e8e93; margin-top: 2px; margin-bottom: 8px;
}

.shlom-card-header-row {
    display: flex; align-items: center; gap: 10px; margin-bottom: 10px;
}
.shlom-card-num {
    width: 28px; height: 28px; border-radius: 50%; display: flex;
    align-items: center; justify-content: center; color: white; font-weight: 700;
    font-size: 0.8rem; flex-shrink: 0;
    background: linear-gradient(135deg, #E1B240, #79481D);
}
.shlom-card-header-text {
    display: flex; align-items: center; gap: 6px; min-width: 0; flex: 1;
}
.shlom-card-client {
    font-size: 0.95rem; font-weight: 600; color: #1c1c1e;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    letter-spacing: -0.01em;
}
.shlom-card-multi-badge {
    font-size: 0.62rem; font-weight: 500; color: #8e8e93;
    background: rgba(142,142,147,0.12); padding: 2px 7px; border-radius: 6px;
    flex-shrink: 0;
}
.shlom-card-address {
    display: flex; align-items: flex-start; gap: 5px;
    font-size: 0.82rem; color: #8e8e93; margin-bottom: 12px;
    line-height: 1.35;
}
.shlom-card-address i { margin-top: 1px; flex-shrink: 0; font-size: 0.75rem; color: #aeaeb2; }

/* Action buttons */
.shlom-card-actions {
    display: flex; gap: 8px; margin-bottom: 12px;
}
.shlom-card-btn {
    flex: 1; display: flex; align-items: center; justify-content: center; gap: 5px;
    padding: 9px 0; border: none; border-radius: 12px;
    font-size: 0.78rem; font-weight: 600; cursor: pointer;
    font-family: inherit; letter-spacing: -0.01em;
    transition: transform 0.15s, opacity 0.15s; -webkit-tap-highlight-color: transparent;
}
.shlom-card-btn:active { transform: scale(0.96); opacity: 0.7; }
.shlom-card-btn i { font-size: 0.85rem; }
.shlom-card-btn-maps { background: rgba(0,122,255,0.1); color: #007AFF; }
.shlom-card-btn-visit { background: rgba(52,199,89,0.1); color: #34C759; }
.shlom-card-btn-ausente { background: rgba(255,59,48,0.1); color: #FF3B30; }

.shlom-card-content {
    display: flex; flex-direction: column; min-width: 0;
}
/* Contract sub-items */
.shlom-card-contracts {
    border-top: 1px solid rgba(60,60,67,0.08); padding-top: 10px;
}
.shlom-contract-row {
    display: flex; align-items: center; gap: 8px;
    padding: 5px 0; font-size: 0.8rem;
}
.shlom-contract-row + .shlom-contract-row { border-top: 1px solid rgba(60,60,67,0.04); }
.shlom-contract-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.shlom-contract-id { color: #8e8e93; font-size: 0.75rem; font-weight: 500; }
.shlom-contract-amount { font-weight: 600; color: #1c1c1e; }

/* Slider controls */
.shlom-slider-wrap {
    position: relative;
    display: flex; align-items: center; gap: 0;
}
.shlom-slider-wrap .shlom-slider-track { flex: 1; min-width: 0; }
.shlom-slider-nav {
    display: none;
    align-items: center; justify-content: center;
    width: 36px; height: 36px; flex-shrink: 0;
    border: none; border-radius: 50%;
    background: rgba(242,242,247,0.98); color: #1c1c1e;
    box-shadow: 0 1px 4px rgba(0,0,0,0.12);
    font-size: 1.1rem; cursor: pointer;
    z-index: 2;
    -webkit-tap-highlight-color: transparent;
    transition: opacity 0.15s ease, transform 0.15s ease;
}
.shlom-slider-nav:hover { background: #fff; }
.shlom-slider-nav:active { transform: scale(0.94); }
.shlom-slider-nav:disabled {
    opacity: 0.35; cursor: default; pointer-events: none;
}
.shlom-slider-prev { margin-left: 6px; }
.shlom-slider-next { margin-right: 6px; }
.shlom-slider-controls { display: flex; justify-content: center; padding: 0; margin-top: -4px; flex-shrink: 0; }
.shlom-slider-dots { display: flex; gap: 6px; align-items: center; }
.shlom-dot {
    width: 7px; height: 7px; border-radius: 50%; background: rgba(60,60,67,0.2);
    transition: all 0.25s ease; cursor: pointer;
}
.shlom-dot.active { width: 22px; border-radius: 4px; background: #1c1c1e; }
.shlom-sheet-detalles {
    display: none;
    align-items: center; justify-content: center; gap: 6px;
    width: calc(100% - 24px); margin: 0 12px 12px;
    border: none; border-radius: 12px;
    background: #79481D; color: #fff;
    font-size: 0.9rem; font-weight: 700; letter-spacing: -0.01em;
    padding: 12px 14px; flex-shrink: 0;
    -webkit-tap-highlight-color: transparent;
    transition: opacity 0.15s ease, transform 0.15s ease;
}
.shlom-sheet-detalles:active { transform: scale(0.98); opacity: 0.9; }
.shlom-sheet-detalles i { font-size: 0.95rem; transition: transform 0.25s ease; }
.shlom-bottom-sheet.expanded .shlom-sheet-detalles i {
    transform: rotate(180deg);
}

.current-pos-marker { background: transparent !important; border: none !important; }

/* ====== RESPONSIVE: TABLET/DESKTOP ====== */
@media (min-width: 768px) {
    .shlom-bottom-sheet {
        left: auto;
        right: 20px;
        width: 100%;
        max-width: 425px;
        transform: none;
        border-radius: 20px 20px 0 0;
    }
    .shlom-bottom-sheet.expanded {
        transform: none;
    }
    .shlom-sheet-handle { display: none; }
    /* Sin handle en PC: deja aire arriba para fecha + badge de estado */
    .shlom-bottom-sheet .shlom-ruta-meta {
        padding-top: 18px;
    }
    .shlom-slider-nav { display: flex; }
    .shlom-sheet-detalles { display: flex; }
    .shlom-sheet-content { padding-bottom: 4px; }
    .shlom-slider-card {
        flex: 0 0 min(88%, 320px);
        max-width: 320px;
    }
}
@media (min-width: 992px) {
    .shlom-bottom-sheet {
        right: 28px;
    }
    .shlom-header { padding: 20px 20px 12px; }
    .shlom-title { font-size: 2.5rem; }
    .shlom-route-list { padding: 12px 20px 100px; }
    .shlom-route-card,
    .shlom-plantilla-card { max-width: 600px; margin: 0 auto; width: 100%; }
    .shlom-bottom-sheet .shlom-ruta-meta {
        padding-top: 22px;
    }
    .shlom-detail-topbar { max-width: 100%; padding: 12px 20px; }
    .shlom-detail-info { min-width: 0; }
}
</style>

{{-- Rainbow background container --}}
<div class="rainbow-background">
    @for ($i = 1; $i <= 25; $i++)
        <div class="rainbow"></div>
    @endfor
    
    <div class="h"></div>
    <div class="v"></div>
</div>

<div class="container" style="position: relative; z-index: 10;">
    {{-- Saludo solo para administradores --}}
    @if(Auth::user()->role === 'admin')
    <div class="mb-4 text-start">
        <h3 class="display-2 fw-bold" style="line-height: 0.8; opacity: 0.6; letter-spacing: -2px;">¡{{ $greeting }}, 
            <br>
        {{ Auth::user()->name }}!</h3>
    </div>
    @endif
    
    {{-- Agenda Minimalista - Solo para administradores --}}
    @if(Auth::user()->role === 'admin')
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex justify-content-start mt-3 mb-3">
                <div class="text-start">
                    <h4 class="fw-bold agenda-header mb-0 me-3">
                        <i class="bi bi-calendar me-2"></i>Agenda de Pagos
                    </h4>
                    <div>
                        @if($currentDayOffset == 0)

                            <span class="badge" style="background: linear-gradient(135deg, #79481D 0%, #E1B240 100%); color: white;">
                                Hoy
                            </span>

                        @endif
                        <small class="text-muted">
                            {{ $agendaDia['fecha']->translatedFormat('l, d \d\e F \d\e Y') }}
                        </small>
                    </div>
                       
                </div>
            </div>
            <div class="btn-group" role="group">
                <a href="{{ route('home', ['day' => $currentDayOffset - 1]) }}" 
                   class="btn btn-outline-primary agenda-nav-btn" 
                   title="Día anterior"
                   style="background: none; color: #79481D;">
                    <i class="bi bi-chevron-left"></i>
                </a>
                @if($currentDayOffset != 0)
                    <a href="{{ route('home') }}" 
                       class="btn btn-outline-primary agenda-nav-btn"
                       title="Hoy"
                       style="background: none; color: #79481D;">
                        <i class="bi bi-house-fill"></i>
                    </a>
                @endif
                <a href="{{ route('home', ['day' => $currentDayOffset + 1]) }}" 
                   class="btn btn-outline-primary agenda-nav-btn"
                   title="Día siguiente"
                   style="background: none; color: #79481D;">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>
        
        
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="agenda-card {{ $agendaDia['fecha']->isToday() ? 'today' : '' }}" style="background: transparent;">
                    <div class="">
                        <div class="row">
                            {{-- Columna izquierda: Información del día y contadores --}}
                            <div class="col-md-3">
                                {{-- Cabecera del día --}}
                                <div class="text-start mb-4">
                                    {{-- Mini calendario siempre visible --}}
                                    <div id="miniCalendario" class="mini-calendario w-100">
                                        <!-- El calendario se generará dinámicamente con JavaScript -->
                                    </div>
                                </div>

                                {{-- Resumen de pagos --}}
                                @if($agendaDia['pagos_pendientes']->count() > 0 || $agendaDia['pagos_hechos']->count() > 0)
                                    <div class="border-top pt-3">
                                        @if($agendaDia['pagos_pendientes']->count() > 0)
                                            <div class="mb-3">
                                                <span class="badge badge-lg w-100" style="background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); color: white; font-size: 0.8rem; padding: 10px;">
                                                    <i class="bi bi-clock me-1"></i>{{ $agendaDia['pagos_pendientes']->count() }} pendientes
                                                </span>
                                            </div>
                                        @endif
                                        @if($agendaDia['pagos_hechos']->count() > 0)
                                            <div class="mb-3">
                                                <span class="badge bg-success badge-lg w-100" style="font-size: 0.8rem; padding: 10px;">
                                                    <i class="bi bi-check-circle me-1"></i>{{ $agendaDia['pagos_hechos']->count() }} completados
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            {{-- Columna derecha: Lista de pagos --}}
                            <div class="col-md-9">
                                @if($agendaDia['pagos_pendientes']->count() > 0 || $agendaDia['pagos_hechos']->count() > 0)
                                    <div class="row g-3">
                                        {{-- Pagos pendientes --}}
                                        @foreach($agendaDia['pagos_pendientes'] as $pago)
                                            @if($pago->contrato)
                                            <div class="col-md-6 col-lg-3">
                                                <a href="{{ route('contratos.show', $pago->contrato->id) }}" class="text-decoration-none pago-link">
                                                    <div class="pago-item p-3 bg-white rounded border-start border-4 h-100" 
                                                         style="border-left-color: #E1B240 !important;"
                                                         title="Click para ver detalles">
                                                        <div class="fw-bold text-truncate mb-2">
                                                            <i class="bi bi-person-fill me-2" style="color: #E1B240;"></i>
                                                            {{ $pago->contrato->cliente->nombre ?? 'Sin cliente' }}
                                                        </div>
                                                        <div class="fw-bold h5 mb-2" style="color: #E1B240;">
                                                            <i class="bi bi-currency-dollar"></i>{{ number_format($pago->monto, 0) }}
                                                        </div>
                                                        @if($pago->numero_cuota)
                                                            <div class="text-muted small">
                                                                <i class="bi bi-list-ol me-1"></i>Cuota #{{ $pago->numero_cuota }}
                                                            </div>
                                                        @endif
                                                        <div class="text-muted small">
                                                            <i class="bi bi-file-text me-1"></i>Contrato #{{ $pago->contrato->id }}
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                            @endif
                                        @endforeach
                                        
                                        {{-- Pagos hechos --}}
                                        @foreach($agendaDia['pagos_hechos'] as $pago)
                                            @if($pago->contrato)
                                            <div class="col-md-6 col-lg-3">
                                                <a href="{{ route('contratos.show', $pago->contrato->id) }}" class="text-decoration-none pago-link">
                                                    <div class="pago-item p-3 bg-white rounded border-start border-4 border-success h-100"
                                                         title="Click para ver detalles">
                                                        <div class="fw-bold text-truncate mb-2">
                                                            <i class="bi bi-person-check-fill me-2 text-success"></i>
                                                            {{ $pago->contrato->cliente->nombre ?? 'Sin cliente' }}
                                                        </div>
                                                        <div class="text-success fw-bold h5 mb-2">
                                                            <i class="bi bi-check-circle me-2"></i>${{ number_format($pago->monto, 0) }}
                                                        </div>
                                                        @if($pago->numero_cuota)
                                                            <div class="text-muted small">
                                                                <i class="bi bi-list-ol me-1"></i>Cuota #{{ $pago->numero_cuota }}
                                                            </div>
                                                        @endif
                                                        <div class="text-muted small">
                                                            <i class="bi bi-file-text me-1"></i>Contrato #{{ $pago->contrato->id }}
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center text-muted py-5">
                                        <i class="bi bi-calendar-x display-1" style="color: #E1B240;"></i>
                                        <div class="mt-3">
                                            <h4 class="fw-bold">Sin pagos programados</h4>
                                            <p class="mb-0">No hay pagos programados para este día.</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        
    </div>
    @endif
    
    {{-- Sección para Empleados - Mobile First --}}
    @if(Auth::user()->role !== 'admin')

    {{-- Pantalla 1: Lista de Rutas --}}
    <div id="screenRutaList" class="shlom-screen active">
        <div class="shlom-header">
            <div class="shlom-header-top">
                <div>
                    <h1 class="shlom-title">Rutas</h1>
                    <p class="shlom-subtitle">Rutas agendadas a tu nombre</p>
                </div>
                <button type="button" class="shlom-btn-mis-rutas" data-bs-toggle="modal" data-bs-target="#misRutasModal">
                    <i class="fa-solid fa-route"></i>
                    Mis rutas
                    @if($empleadoPlantillas->count() > 0)
                        <span class="badge">{{ $empleadoPlantillas->count() }}</span>
                    @endif
                </button>
            </div>
        </div>

        <div class="shlom-day-nav">
            <a href="?day={{ (int) $currentDayOffset - 1 }}" class="shlom-day-nav-btn" id="btnRutasDiaPrev" aria-label="Día anterior">
                <i class="bi bi-chevron-left"></i>
            </a>
            <div class="shlom-day-nav-center">
                <span class="shlom-day-nav-title" id="rutasDiaTitulo">{{ $rutasDiaTitulo }}</span>
                <span class="shlom-day-nav-sub" id="rutasDiaSubtitulo">{{ $rutasDiaSubtitulo }}</span>
            </div>
            <a href="?day={{ (int) $currentDayOffset + 1 }}" class="shlom-day-nav-btn" id="btnRutasDiaNext" aria-label="Día siguiente">
                <i class="bi bi-chevron-right"></i>
            </a>
        </div>

        <div class="shlom-section-label">Del día</div>
        <div class="shlom-route-list" id="listaRutasDia">
            @forelse($empleadoRutas as $ruta)
                @php
                        $domiciliosUnicos = [];
                        foreach ($ruta->paradas as $parada) {
                            $dkey = $parada->cliente_id . '_' . md5($parada->direccion_destino);
                            if (!isset($domiciliosUnicos[$dkey])) {
                                $domiciliosUnicos[$dkey] = ['paradas' => collect()];
                            }
                            $domiciliosUnicos[$dkey]['paradas']->push($parada);
                        }
                        $totalParadas = count($domiciliosUnicos);
                        $completadas = 0;
                        $omitidas = 0;
                        foreach ($domiciliosUnicos as $d) {
                            $t = $d['paradas']->count();
                            $c = $d['paradas']->where('estado', 'visitada')->count();
                            $o = $d['paradas']->where('estado', 'omitida')->count();
                            if ($c === $t) $completadas++;
                            elseif ($o === $t) $omitidas++;
                        }
                        $porcentaje = $totalParadas > 0 ? round(($completadas / $totalParadas) * 100) : 0;
                        $estadoConfig = match($ruta->estado) {
                            'planeada' => ['color' => '#007AFF', 'bg' => 'rgba(0,122,255,0.1)', 'label' => 'Planeada', 'icon' => 'bi-calendar'],
                            'en_curso' => ['color' => '#FF9500', 'bg' => 'rgba(255,149,0,0.1)', 'label' => 'En Curso', 'icon' => 'bi-play-circle'],
                            'detenida' => ['color' => '#FF3B30', 'bg' => 'rgba(255,59,48,0.1)', 'label' => 'Detenida', 'icon' => 'bi-slash-circle'],
                            'completada' => ['color' => '#34C759', 'bg' => 'rgba(52,199,89,0.1)', 'label' => 'Completada', 'icon' => 'bi-check-circle'],
                        'incompleta' => ['color' => '#8E8E93', 'bg' => 'rgba(142,142,147,0.1)', 'label' => 'Incompleta', 'icon' => 'bi-dash-circle'],
                        'vencida' => ['color' => '#1C1C1E', 'bg' => 'rgba(28,28,30,0.1)', 'label' => 'Vencida', 'icon' => 'bi-clock-history'],
                            default => ['color' => '#8E8E93', 'bg' => 'rgba(142,142,147,0.1)', 'label' => $ruta->estado, 'icon' => 'bi-circle'],
                        };
                    @endphp
                    <div class="shlom-route-card" data-ruta-id="{{ $ruta->id }}">
                        <div class="shlom-route-card-header">
                            <div class="shlom-route-status" style="background: {{ $estadoConfig['bg'] }}; color: {{ $estadoConfig['color'] }};">
                                <i class="bi {{ $estadoConfig['icon'] }}"></i>
                                <span>{{ $estadoConfig['label'] }}</span>
                            </div>
                        </div>
                        <div class="shlom-route-card-body">
                            <div class="shlom-route-date">
                            <i class="bi bi-signpost-2"></i>
                            <span>{{ $ruta->nombre ?: ('Ruta #' . $ruta->id) }}</span>
                            </div>
                            <div class="shlom-route-stats">
                                <div class="shlom-stat">
                                    <i class="bi bi-geo-alt-fill"></i>
                                    <span>{{ $totalParadas }}</span>
                                    <small>domicilios</small>
                                </div>
                                @if($completadas > 0)
                                <div class="shlom-stat shlom-stat-success">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <span>{{ $completadas }}</span>
                                </div>
                                @endif
                                @if($omitidas > 0)
                                <div class="shlom-stat shlom-stat-danger">
                                    <i class="bi bi-x-circle-fill"></i>
                                    <span>{{ $omitidas }}</span>
                                </div>
                                @endif
                                <div class="shlom-stat ms-auto">
                                    <small class="fw-bold" style="color: #79481D;">{{ $porcentaje }}%</small>
                                </div>
                            </div>
                            <div class="shlom-progress-track">
                                <div class="shlom-progress-fill" style="width: {{ $porcentaje }}%;"></div>
                            </div>
                        </div>
                        <div class="shlom-route-card-arrow">
                            <i class="bi bi-chevron-right"></i>
                        </div>
                    </div>
            @empty
                <div class="shlom-empty-state" id="rutasDiaEmpty">
                    <div class="shlom-empty-icon">
                        <i class="fa-solid fa-route"></i>
                    </div>
                    <h3>Sin rutas este día</h3>
                    <p>Cambia de día o abre <strong>Mis rutas</strong> para ver tus asignaciones.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Modal: rutas asignadas --}}
    <div class="modal fade" id="misRutasModal" tabindex="-1" aria-labelledby="misRutasModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="misRutasModalLabel">Mis rutas</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" id="listaPlantillas">
                    @php
                        $misRutasParadasById = [];
                        foreach ($empleadoPlantillas as $plantillaForParadas) {
                            $misRutasParadasById[(string) $plantillaForParadas->id] = $plantillaForParadas->paradas->map(function ($parada) {
                                $cliente = $parada->cliente ?: $parada->contrato?->cliente;
                                $nombre = $cliente
                                    ? trim(($cliente->nombre ?? '') . ' ' . ($cliente->apellido ?? ''))
                                    : 'Sin cliente';
                                $domicilio = $cliente
                                    ? (
                                        $cliente->domicilio_completo
                                        ?: collect([
                                            $cliente->calle_y_numero,
                                            $cliente->colonia,
                                            $cliente->municipio,
                                        ])->filter()->implode(', ')
                                    )
                                    : '';

                                return [
                                    'orden' => (int) ($parada->orden ?: 0),
                                    'contrato_id' => $parada->contrato_id,
                                    'cliente' => $nombre !== '' ? $nombre : 'Sin cliente',
                                    'domicilio' => $domicilio !== '' ? $domicilio : 'Sin domicilio',
                                ];
                            })->values()->all();
                        }
                    @endphp
                    <script type="application/json" id="misRutasParadasData">@json($misRutasParadasById)</script>
                    <div class="mis-rutas-search-wrap" id="misRutasSearchWrap">
                        <div class="mis-rutas-search">
                            <i class="bi bi-search" aria-hidden="true"></i>
                            <input type="search"
                                   id="misRutasSearch"
                                   placeholder="Folio, titular o domicilio"
                                   autocomplete="off"
                                   enterkeyhint="search">
                            <button type="button" class="mis-rutas-search-clear d-none" id="misRutasSearchClear" aria-label="Limpiar">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mis-rutas-search-results" id="misRutasSearchResults" hidden></div>
                    <div class="mis-rutas-page active" id="misRutasPageList" data-page="list">
                        @forelse($empleadoPlantillas as $plantilla)
                            @php
                                $freqIcon = match ($plantilla->frecuencia) {
                                    \App\Models\RutaPlantilla::FRECUENCIA_DIARIA => 'bi-sun',
                                    \App\Models\RutaPlantilla::FRECUENCIA_SEMANAL => 'bi-calendar-week',
                                    \App\Models\RutaPlantilla::FRECUENCIA_MENSUAL => 'bi-calendar-month',
                                    default => 'bi-calendar3',
                                };
                            @endphp
                            <div class="shlom-plantilla-card"
                                 role="button"
                                 tabindex="0"
                                 data-plantilla-id="{{ $plantilla->id }}"
                                 data-plantilla-nombre="{{ e($plantilla->nombre ?: ('Ruta #' . $plantilla->id)) }}"
                                 data-plantilla-freq="{{ e($plantilla->etiquetaFrecuencia()) }}">
                                <div class="shlom-plantilla-icon">
                                    <i class="fa-solid fa-route"></i>
                                </div>
                                <div class="shlom-plantilla-body">
                                    <div class="shlom-plantilla-name">{{ $plantilla->nombre ?: ('Ruta #' . $plantilla->id) }}</div>
                                    <div class="shlom-plantilla-meta">
                                        <span class="shlom-plantilla-meta-item">
                                            <i class="bi {{ $freqIcon }}"></i>
                                            {{ $plantilla->etiquetaFrecuencia() }}
                                        </span>
                                        <span class="shlom-plantilla-meta-sep" aria-hidden="true"></span>
                                        <span class="shlom-plantilla-meta-item">
                                            <i class="bi bi-geo-alt-fill"></i>
                                            {{ $plantilla->paradas_count }} parada{{ $plantilla->paradas_count === 1 ? '' : 's' }}
                                        </span>
                                    </div>
                                </div>
                                <i class="bi bi-chevron-right shlom-plantilla-chevron"></i>
                            </div>
                        @empty
                            <div class="shlom-empty-state">
                                <div class="shlom-empty-icon">
                                    <i class="fa-solid fa-map"></i>
                                </div>
                                <h3>Sin rutas asignadas</h3>
                                <p>Cuando te asignen una ruta, aparecerá aquí.</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="mis-rutas-page" id="misRutasPageDetail" data-page="detail">
                        <button type="button" class="btn-mis-rutas-back" id="btnMisRutasBack">
                            <i class="bi bi-chevron-left"></i> Mis rutas
                        </button>
                        <div>
                            <h3 class="mis-rutas-detail-title" id="misRutasDetailTitle">Ruta</h3>
                            <p class="mis-rutas-detail-sub" id="misRutasDetailSub"></p>
                        </div>
                        <button type="button"
                                class="btn-ver-ruta-plantilla"
                                id="btnVerRutaPlantilla"
                                data-plantilla-id="">
                            <i class="bi bi-signpost-2-fill"></i>
                            <span>Ver ruta</span>
                        </button>
                        <div id="misRutasDetailList"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Pantalla 2: Mapa + Bottom Sheet --}}
    <div id="screenRutaDetalle" class="shlom-screen">
        {{-- Top bar --}}
        <div class="shlom-detail-topbar">
            <button class="shlom-back-btn" id="btnBackToList">
                <i class="bi bi-chevron-left"></i>
            </button>
            <div class="shlom-detail-info">
                <span class="shlom-detail-title" id="detailRutaTitle">Ruta</span>
                <span class="shlom-detail-freq d-none" id="detailRutaFreq"></span>
            </div>
        </div>

        {{-- Mapa (full screen behind sheet) --}}
        <div class="shlom-map-full">
            <div id="homeMap" class="shlom-map"></div>
        </div>

        {{-- Bottom Sheet --}}
        <div class="shlom-bottom-sheet" id="bottomSheet">
            <div class="shlom-sheet-handle" id="sheetHandle">
                <div class="shlom-handle-bar"></div>
            </div>
            <div class="shlom-sheet-content">
                <div class="shlom-ruta-meta" id="rutaDetalleMeta">
                    <div class="shlom-ruta-meta-row">
                        <span class="shlom-ruta-meta-item" id="detailRutaFechaWrap">
                            <i class="bi bi-calendar3"></i>
                            <span id="detailRutaFecha">—</span>
                        </span>
                        <span class="shlom-ruta-meta-badge" id="detailRutaEstado">—</span>
                    </div>
                    <div class="shlom-ruta-meta-progress" id="detailRutaProgress" aria-label="Progreso de la ruta">
                        <div class="shlom-ruta-meta-progress-track">
                            <div class="shlom-ruta-meta-progress-fill" id="detailRutaProgressFill"></div>
                        </div>
                        <span class="shlom-ruta-meta-progress-pct" id="detailRutaProgressPct">0%</span>
                    </div>
                </div>
                <div class="shlom-slider-wrap">
                    <button type="button" class="shlom-slider-nav shlom-slider-prev" id="btnSliderPrev" aria-label="Parada anterior">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <div class="shlom-slider-track" id="paradasSlider"></div>
                    <button type="button" class="shlom-slider-nav shlom-slider-next" id="btnSliderNext" aria-label="Parada siguiente">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
                <div class="shlom-slider-controls">
                    <div class="shlom-slider-dots" id="sliderDots"></div>
                </div>
                <div id="puntoCasaBlock" class="shlom-punto-casa-block">
                    <div class="shlom-seg-label">Tu domicilio como</div>
                    <div class="shlom-seg" id="puntoCasaTabs" role="tablist">
                        <button type="button" class="shlom-seg-tab active" data-value="inicio">Inicio</button>
                        <button type="button" class="shlom-seg-tab" data-value="final">Final</button>
                        <button type="button" class="shlom-seg-tab" data-value="ambos">Ambos</button>
                    </div>
                    <div class="shlom-punto-casa-loading" id="puntoCasaLoading" role="status" aria-live="polite">
                        <span class="spinner-border" aria-hidden="true"></span>
                        <span>Obteniendo ubicación…</span>
                    </div>
                </div>
                <div id="paradaRegistroDetalle" class="shlom-parada-registro d-none" aria-live="polite"></div>
                <div id="rutaResumenHistorico" class="shlom-ruta-resumen d-none">
                    <div class="shlom-resumen-kicker">Progreso</div>
                    <div class="shlom-progress-track mb-2">
                        <div class="shlom-progress-fill" id="rutaResumenProgressFill" style="width: 0%;"></div>
                    </div>
                    <div class="shlom-resumen-text mb-2" id="rutaResumenPorcentaje">0%</div>
                    <ul class="ios-grouped shlom-resumen-stats">
                        <li>
                            <span>Visitados</span>
                            <strong id="rutaResumenVisitados">0</strong>
                        </li>
                        <li>
                            <span>Omitidos</span>
                            <strong id="rutaResumenOmitidos">0</strong>
                        </li>
                        <li>
                            <span>Pendientes</span>
                            <strong id="rutaResumenPendientes">0</strong>
                        </li>
                    </ul>
                </div>
            </div>
            <button type="button" class="shlom-sheet-detalles" id="btnSheetDetalles">
                <span id="btnSheetDetallesLabel">Detalles</span>
                <i class="bi bi-chevron-up" aria-hidden="true"></i>
            </button>
        </div>
    </div>

    <div class="modal fade" id="visitaConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered ios-confirm-dialog visita-wizard-dialog">
            <div class="modal-content ios-confirm-card visita-wizard-card">
                <div class="visita-wizard-body">
                    <div id="visitaWizardError" class="ios-banner ios-banner-danger d-none"></div>
                    <div class="visita-form-header" id="visitaFormHeader">
                        <div class="ios-confirm-icon ios-confirm-icon-visit" id="visitaWizardIcon">
                            <i class="bi bi-cash-coin"></i>
                        </div>
                        <div class="visita-form-header-body">
                            <div class="visita-form-header-top">
                                <h2 class="ios-confirm-title" id="visitaWizardTitle">Abono</h2>
                                <span class="visita-form-contrato" id="visitaVinculoContrato">—</span>
                            </div>
                            <p class="visita-form-nombre" id="visitaVinculoNombre">—</p>
                            <p class="visita-form-domicilio" id="visitaVinculoDomicilio">—</p>
                        </div>
                    </div>

                    <div class="visita-wizard-step" data-step="1">
                        <div class="ios-confirm-map-wrap">
                            <div id="visitaGpsMap" class="ios-confirm-map"></div>
                            <button type="button" class="visita-gps-recenter" id="btnVisitaGpsRecenter" title="Centrar mi ubicación" disabled>
                                <i class="bi bi-crosshair"></i>
                            </button>
                        </div>
                        <div id="visitaGpsStatus" class="ios-banner ios-banner-info">
                            <span class="spinner-border spinner-border-sm me-2"></span>Obteniendo tu ubicación...
                        </div>
                        <div id="visitaDistanciaAlert" class="ios-banner ios-banner-warning d-none"></div>
                        <div id="visitaFoliosPago" class="mt-2"></div>
                    </div>

                    <div class="visita-wizard-step d-none" data-step="2">
                        <div class="visita-ticket-preview">
                            <div class="visita-ticket-preview-scroll" id="visitaTicketPreviewScroll">
                                <iframe id="visitaTicketFrame" class="visita-ticket-frame" title="Ticket de visita" scrolling="no"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ios-confirm-actions visita-wizard-actions">
                    <button type="button" class="ios-btn ios-btn-primary" id="btnVisitaWizardNext" disabled>Registrar abono</button>
                    <button type="button" class="ios-btn ios-btn-plain" id="btnVisitaWizardCancel" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="ios-btn ios-btn-primary d-none" id="btnVisitaWizardPrint">Imprimir</button>
                    <button type="button" class="ios-btn ios-btn-plain d-none" id="btnVisitaWizardUndo" style="color:#FF3B30;">Deshacer la visita</button>
                    <button type="button" class="ios-btn ios-btn-plain d-none" id="btnVisitaWizardClose">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="omitirConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered ios-confirm-dialog">
            <div class="modal-content ios-confirm-card">
                <div class="ios-confirm-icon ios-confirm-icon-skip">
                    <i class="bi bi-skip-forward-fill"></i>
                </div>
                <h2 class="ios-confirm-title">Omitir parada</h2>
                <p class="ios-confirm-msg" id="omitirConfirmTexto">Esta parada se marcará como omitida.</p>
                <ul class="ios-grouped">
                    <li>
                        <span>Contrato</span>
                        <strong id="omitirVinculoContrato">—</strong>
                    </li>
                    <li>
                        <span>Nombre</span>
                        <strong id="omitirVinculoNombre">—</strong>
                    </li>
                    <li>
                        <span>Domicilio</span>
                        <strong id="omitirVinculoDomicilio">—</strong>
                    </li>
                </ul>
                <div id="omitirConfirmError" class="ios-banner ios-banner-danger d-none"></div>
                <div class="ios-confirm-actions">
                    <button type="button" class="ios-btn ios-btn-danger" id="btnConfirmarOmitir">Omitir y continuar</button>
                    <button type="button" class="ios-btn ios-btn-plain" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    @endif
    
    {{-- Sección de Pagos Vencidos - Solo para empleados --}}
    @if(Auth::user()->role !== 'admin' && $empleadoPagosVencidos->count() > 0)
    <div class="mb-5">
        <div class="d-flex justify-content-start mt-3 mb-4">
            <div class="text-start">
                <h4 class="fw-bold mb-0 me-3" style="color: #dc3545;">
                    <i class="bi bi-exclamation-triangle me-2"></i>Pagos Vencidos
                </h4>
                <small class="text-muted">
                    {{ $empleadoPagosVencidos->count() }} pagos que exceden el período de tolerancia
                </small>
                <div class="mt-1">
                    <span class="badge bg-danger">
                        <i class="bi bi-clock-history me-1"></i>Requieren atención inmediata
                    </span>
                    @if(toleranciaPagos() > 0)
                        <span class="badge bg-warning text-dark ms-1">
                            <i class="bi bi-info-circle me-1"></i>Tolerancia: {{ toleranciaPagos() }} días
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-start border-4 border-danger" style="border-top: 0; border-right: 0; border-bottom: 0;">
            <div class="card-header bg-light border-0 pb-2">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-danger">
                        <i class="bi bi-calendar-x me-2"></i>Pagos Atrasados
                    </h5>
                    <span class="badge bg-danger">{{ $empleadoPagosVencidos->count() }} pendientes</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($empleadoPagosVencidos as $pago)
                        @if($pago->contrato)
                        <div class="col-lg-4 col-md-6">
                            <a href="{{ route('contratos.show', $pago->contrato->id) }}" class="text-decoration-none">
                                <div class="card border-0 bg-light h-100 pago-item" style="border-left: 4px solid #dc3545 !important;">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="flex-grow-1">
                                                <div class="fw-bold text-truncate text-danger mb-1">
                                                    <i class="bi bi-person-fill me-1"></i>
                                                    {{ $pago->contrato->cliente->nombre ?? 'Sin cliente' }} {{ $pago->contrato->cliente->apellido ?? '' }}
                                                </div>
                                                <div class="small text-muted mb-1">
                                                    <i class="bi bi-box me-1"></i>
                                                    Contrato #{{ $pago->contrato->id }} - {{ $pago->contrato->paquete->nombre ?? 'Sin paquete' }}
                                                </div>
                                                @if($pago->numero_cuota)
                                                    <div class="small text-muted mb-2">
                                                        <i class="bi bi-list-ol me-1"></i>Cuota #{{ $pago->numero_cuota }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="fw-bold h5 mb-0 text-danger">
                                                <i class="bi bi-currency-dollar"></i>{{ number_format($pago->monto, 0) }}
                                            </div>
                                            <div class="text-end">
                                                <div class="small text-danger fw-bold">
                                                    <i class="bi bi-calendar-x me-1"></i>
                                                    Venció: {{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}
                                                </div>
                                                <div class="small text-muted">
                                                    @php
                                                        $diasRetraso = diasDeRetraso($pago->fecha_pago, $pago->estado);
                                                    @endphp
                                                    @if($diasRetraso > 0)
                                                        <span class="text-danger fw-bold">
                                                            <i class="bi bi-exclamation-triangle me-1"></i>
                                                            {{ $diasRetraso }} día{{ $diasRetraso != 1 ? 's' : '' }} de retraso
                                                        </span>
                                                    @else
                                                        <span class="text-warning">
                                                            <i class="bi bi-clock me-1"></i>
                                                            En período de tolerancia
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endif
                    @endforeach
                </div>
                
                @if($empleadoPagosVencidos->count() > 6)
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Mostrando los pagos vencidos más recientes. 
                            <a href="#" class="text-primary">Ver todos los pagos vencidos</a>
                        </small>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif
    
    {{-- Script para tooltips y calendario --}}
    <script>
        // Variables globales para el calendario (solo si es admin)
        @if(Auth::user()->role === 'admin')
        let fechaActual = new Date(@json($agendaDia['fecha']->format('Y')), @json($agendaDia['fecha']->format('n') - 1), @json($agendaDia['fecha']->format('j')));
        let mesCalendario = new Date(fechaActual);
        
        // Datos de pagos por fecha (se pasarían desde el controlador)
        const diasConPagos = @json($diasConPagos ?? []);
        @endif
        
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM cargado');
            
            // Inicializar tooltips de Bootstrap si están disponibles
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
            
            // Generar calendario solo si existe (para administradores)
            if (document.getElementById('miniCalendario')) {
                console.log('Generando calendario...');
                generarCalendario();
            }
            
            // Inicializar buscador de contratos solo si existe (para empleados)
            if (document.getElementById('buscadorContratos')) {
                console.log('Inicializando buscador...');
                inicializarBuscadorContratos();
            }
        });

        function generarCalendario() {
            const calendario = document.getElementById('miniCalendario');
            if (!calendario) {
                console.log('No se encontró el elemento miniCalendario');
                return;
            }
            
            const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                          'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            const diasSemana = ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'];
            
            const primerDiaMes = new Date(mesCalendario.getFullYear(), mesCalendario.getMonth(), 1);
            const ultimoDiaMes = new Date(mesCalendario.getFullYear(), mesCalendario.getMonth() + 1, 0);
            const primerDiaSemana = primerDiaMes.getDay();
            
            let html = `
                <div class="calendario-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <button class="calendario-nav" onclick="cambiarMes(-1)">‹</button>
                        <span>${meses[mesCalendario.getMonth()]} de ${mesCalendario.getFullYear()}</span>
                        <button class="calendario-nav" onclick="cambiarMes(1)">›</button>
                    </div>
                </div>
                <div class="calendario-dias-semana">
                    ${diasSemana.map(dia => `<div>${dia}</div>`).join('')}
                </div>
                <div class="calendario-dias">
            `;
            
            // Días del mes anterior
            for (let i = primerDiaSemana; i > 0; i--) {
                const dia = new Date(mesCalendario.getFullYear(), mesCalendario.getMonth(), 1 - i);
                html += `<button class="calendario-dia otro-mes" onclick="seleccionarFecha('${formatearFecha(dia)}')">${dia.getDate()}</button>`;
            }
            
            // Días del mes actual
            for (let dia = 1; dia <= ultimoDiaMes.getDate(); dia++) {
                const fechaDia = new Date(mesCalendario.getFullYear(), mesCalendario.getMonth(), dia);
                const fechaFormateada = formatearFecha(fechaDia);
                const esHoy = esMismaFecha(fechaDia, new Date());
                const esSeleccionado = esMismaFecha(fechaDia, fechaActual);
                const tienePagos = diasConPagos.includes(fechaFormateada);
                
                let clases = ['calendario-dia'];
                if (esHoy) clases.push('hoy');
                if (esSeleccionado) clases.push('seleccionado');
                if (tienePagos) clases.push('con-pagos');
                
                html += `<button class="${clases.join(' ')}" onclick="seleccionarFecha('${fechaFormateada}')">${dia}</button>`;
            }
            
            // Días del mes siguiente para completar la grilla
            const diasRestantes = 42 - (primerDiaSemana + ultimoDiaMes.getDate());
            for (let dia = 1; dia <= diasRestantes; dia++) {
                const fechaDia = new Date(mesCalendario.getFullYear(), mesCalendario.getMonth() + 1, dia);
                html += `<button class="calendario-dia otro-mes" onclick="seleccionarFecha('${formatearFecha(fechaDia)}')">${dia}</button>`;
            }
            
            html += '</div>';
            calendario.innerHTML = html;
        }

        function cambiarMes(direccion) {
            if (typeof mesCalendario !== 'undefined') {
                mesCalendario.setMonth(mesCalendario.getMonth() + direccion);
                generarCalendario();
            }
        }

        function seleccionarFecha(fecha) {
            // Calcular la diferencia en días desde hoy
            const hoy = new Date();
            const fechaSeleccionada = new Date(fecha);
            const diferenciaTiempo = fechaSeleccionada.getTime() - hoy.getTime();
            const diferenciaDias = Math.ceil(diferenciaTiempo / (1000 * 3600 * 24));
            
            // Redirigir a la URL con el parámetro day
            window.location.href = `{{ route('home') }}?day=${diferenciaDias}`;
        }

        function formatearFecha(fecha) {
            return fecha.toISOString().split('T')[0];
        }

        function formatearFechaLargaEs(fechaStr) {
            var parts = String(fechaStr || '').split('-');
            if (parts.length !== 3) return fechaStr || '—';
            var y = parseInt(parts[0], 10);
            var m = parseInt(parts[1], 10) - 1;
            var d = parseInt(parts[2], 10);
            var date = new Date(y, m, d);
            if (isNaN(date.getTime())) return fechaStr;
            var dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            var meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            return dias[date.getDay()] + ' ' + d + ' de ' + meses[m] + ' de ' + y;
        }

        function esMismaFecha(fecha1, fecha2) {
            return fecha1.toDateString() === fecha2.toDateString();
        }

        // Variables globales para paginación
        let paginaActualContratos = 1;
        let itemsPorPaginaContratos = 10;
        let contratosFiltrados = [];

        // Funcionalidad del buscador de contratos con paginación
        function inicializarBuscadorContratos() {
            const buscador = document.getElementById('buscadorContratos');
            if (!buscador) {
                console.log('No se encontró el elemento buscadorContratos');
                return;
            }

            const contratos = document.querySelectorAll('.contrato-item');
            const contador = document.getElementById('contadorContratos');
            const sinResultados = document.getElementById('sinResultados');
            const totalContratos = contratos.length;
            
            // Inicializar array de contratos
            contratosFiltrados = Array.from(contratos);
            
            console.log('Buscador inicializado. Contratos encontrados:', totalContratos);

            // Inicializar paginación
            actualizarPaginacion();

            const radiosBusqueda = document.querySelectorAll('input[name="tipoBusqueda"]');
            
            const quitarAcentos = (str) => {
                return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            };

            function ejecutarBusqueda() {
                const termino = quitarAcentos(buscador.value.toLowerCase().trim());
                const tipoBusqueda = document.querySelector('input[name="tipoBusqueda"]:checked').value;
                
                contratosFiltrados = [];
                console.log('Buscando:', termino, 'Tipo:', tipoBusqueda);

                contratos.forEach((contrato, index) => {
                    const cliente = quitarAcentos((contrato.getAttribute('data-cliente') || '').toLowerCase());
                    const contratoId = quitarAcentos((contrato.getAttribute('data-contrato-id') || '').toLowerCase());
                    const domicilio = quitarAcentos((contrato.getAttribute('data-domicilio') || '').toLowerCase());

                    let coincide = false;
                    
                    if (termino === '') {
                        coincide = true;
                    } else if (tipoBusqueda === 'cliente') {
                        coincide = cliente.includes(termino);
                    } else if (tipoBusqueda === 'contrato') {
                        coincide = contratoId.includes(termino);
                    } else if (tipoBusqueda === 'domicilio') {
                        coincide = domicilio.includes(termino);
                    }

                    if (coincide) {
                        contratosFiltrados.push(contrato);
                    }
                });

                // Reiniciar paginación cuando se busca
                paginaActualContratos = 1;
                actualizarPaginacion();
                console.log('Contratos filtrados:', contratosFiltrados.length);
            }

            buscador.addEventListener('input', ejecutarBusqueda);
            
            radiosBusqueda.forEach(radio => {
                radio.addEventListener('change', function() {
                    let placeholder = "Buscar...";
                    if (this.value === 'cliente') placeholder = "Buscar por nombre del cliente...";
                    else if (this.value === 'contrato') placeholder = "Buscar por folio de contrato...";
                    else if (this.value === 'domicilio') placeholder = "Buscar por domicilio...";
                    buscador.placeholder = placeholder;
                    
                    ejecutarBusqueda();
                });
            });

            // Limpiar búsqueda con Escape
            buscador.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    this.value = '';
                    this.dispatchEvent(new Event('input'));
                }
            });
        }

        function actualizarPaginacion() {
            const totalContratos = contratosFiltrados.length;
            const totalPaginas = Math.ceil(totalContratos / itemsPorPaginaContratos);
            const inicio = (paginaActualContratos - 1) * itemsPorPaginaContratos;
            const fin = inicio + itemsPorPaginaContratos;

            // Actualizar contadores
            const contador = document.getElementById('contadorContratos');
            const paginaActualSpan = document.getElementById('paginaActual');
            const totalPaginasSpan = document.getElementById('totalPaginas');
            const sinResultados = document.getElementById('sinResultados');
            const controlesPaginacion = document.getElementById('controlesPaginacion');

            if (contador) contador.textContent = totalContratos;
            if (paginaActualSpan) paginaActualSpan.textContent = totalPaginas > 0 ? paginaActualContratos : 0;
            if (totalPaginasSpan) totalPaginasSpan.textContent = totalPaginas;

            // Mostrar/ocultar todos los contratos
            document.querySelectorAll('.contrato-item').forEach(contrato => {
                contrato.style.display = 'none';
            });

            // Mostrar solo los contratos de la página actual
            contratosFiltrados.slice(inicio, fin).forEach(contrato => {
                contrato.style.display = 'block';
            });

            // Mostrar/ocultar mensaje sin resultados
            if (sinResultados) {
                sinResultados.style.display = totalContratos === 0 ? 'block' : 'none';
            }

            // Mostrar/ocultar controles de paginación
            if (controlesPaginacion) {
                controlesPaginacion.style.display = totalContratos > itemsPorPaginaContratos ? 'flex' : 'none';
            }

            // Actualizar botones
            const btnAnterior = document.getElementById('btnAnterior');
            const btnSiguiente = document.getElementById('btnSiguiente');

            if (btnAnterior) {
                btnAnterior.disabled = paginaActualContratos <= 1;
            }

            if (btnSiguiente) {
                btnSiguiente.disabled = paginaActualContratos >= totalPaginas;
            }
        }

        function cambiarPagina(direccion) {
            const totalPaginas = Math.ceil(contratosFiltrados.length / itemsPorPaginaContratos);
            
            if (direccion === -1 && paginaActualContratos > 1) {
                paginaActualContratos--;
            } else if (direccion === 1 && paginaActualContratos < totalPaginas) {
                paginaActualContratos++;
            }

            actualizarPaginacion();
        }

        function cambiarItemsPorPagina() {
            const select = document.getElementById('itemsPorPagina');
            if (select) {
                itemsPorPaginaContratos = parseInt(select.value);
                paginaActualContratos = 1; // Reiniciar a la primera página
                actualizarPaginacion();
            }
        }

        // Funcionalidad de navegación por semanas
        let semanaOffset = 0;
        const userRole = @json(Auth::user()->role);

        // Función auxiliar para actualizar el rango de fechas
        function actualizarRangoFechas(agendaDatos = null) {
            if (userRole === 'admin') return;
            
            // Buscar el elemento con mayor precisión
            let rangoFechas = document.getElementById('rangoFechas');
            
            // Si no lo encuentra, intentar múltiples formas
            if (!rangoFechas) {
                rangoFechas = document.querySelector('#rangoFechas');
                if (!rangoFechas) {
                    rangoFechas = document.querySelector('span[id="rangoFechas"]');
                }
                if (!rangoFechas) {
                    rangoFechas = document.querySelector('.badge[id="rangoFechas"]');
                }
            }
            
            if (!rangoFechas) {
                console.log('No se encontró elemento rangoFechas después de múltiples intentos');
                return;
            }
            
            let fechaInicio, fechaFin;
            
            if (agendaDatos && agendaDatos.length > 0) {
                // Usar fechas de los datos del backend
                fechaInicio = new Date(agendaDatos[0].fecha);
                fechaFin = new Date(agendaDatos[agendaDatos.length - 1].fecha);
                console.log('Usando fechas del backend:', agendaDatos[0].fecha, 'a', agendaDatos[agendaDatos.length - 1].fecha);
            } else {
                // Calcular rango basado en semanaOffset
                const hoy = new Date();
                fechaInicio = new Date(hoy);
                fechaInicio.setDate(hoy.getDate() + (semanaOffset * 7));
                fechaFin = new Date(fechaInicio);
                fechaFin.setDate(fechaInicio.getDate() + 6);
                console.log('Calculando fechas con offset:', semanaOffset);
            }
            
            const fechaInicioStr = fechaInicio.toLocaleDateString('es-ES', { day: 'numeric', month: 'short', year: 'numeric' });
            const fechaFinStr = fechaFin.toLocaleDateString('es-ES', { day: 'numeric', month: 'short', year: 'numeric' });
            
            const nuevoRango = `<i class="bi bi-calendar-range me-1"></i>${fechaInicioStr} - ${fechaFinStr}`;
            
            // Verificar que el elemento sigue existiendo antes de actualizarlo
            if (rangoFechas && rangoFechas.parentNode) {
                rangoFechas.innerHTML = nuevoRango;
                console.log('Rango actualizado exitosamente:', nuevoRango);
            } else {
                console.log('El elemento rangoFechas ya no existe en el DOM');
            }
        }

        function cambiarSemana(direccion) {
            // Solo ejecutar si el usuario no es admin
            if (userRole === 'admin') {
                console.log('Usuario es admin, navegación por semanas no disponible');
                return;
            }
            
            if (direccion === 0) {
                // Volver a la semana actual
                semanaOffset = 0;
            } else {
                // Cambiar semana (-1 = anterior, 1 = siguiente)
                semanaOffset += direccion;
            }
            
            actualizarAgendaSemana();
        }

        function actualizarAgendaSemana() {
            // Solo ejecutar si el usuario no es admin
            if (userRole === 'admin') {
                console.log('Usuario es admin, no actualizar agenda de semana');
                return;
            }
            
            // Actualizar texto descriptivo
            const textoSemana = document.getElementById('textoSemana');
            
            if (textoSemana) {
                if (semanaOffset === 0) {
                    textoSemana.textContent = 'Pagos programados para los próximos 7 días';
                } else if (semanaOffset > 0) {
                    textoSemana.textContent = `Pagos programados para la semana ${semanaOffset + 1}`;
                } else {
                    textoSemana.textContent = `Pagos programados para ${Math.abs(semanaOffset)} semana${Math.abs(semanaOffset) > 1 ? 's' : ''} atrás`;
                }
            }
            
            // Actualizar el rango de fechas inmediatamente (antes de la petición AJAX)
            actualizarRangoFechas();

            // Hacer petición AJAX para obtener los datos de la nueva semana
            const url = `{{ route('home') }}?week=${semanaOffset}&ajax=1`;
            
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Datos recibidos del servidor:', data);
                if (data.success) {
                    actualizarVistaAgenda(data.empleadoAgenda);
                } else {
                    console.error('Error al cargar la agenda:', data.message);
                }
            })
            .catch(error => {
                console.error('Error en la petición:', error);
                // Fallback: recargar la página con el parámetro week
                window.location.href = `{{ route('home') }}?week=${semanaOffset}`;
            });
        }

        function actualizarVistaAgenda(agendaDatos) {
            // Solo ejecutar si el usuario no es admin (la sección de empleados existe)
            if (userRole === 'admin') {
                console.log('Usuario es admin, no hay sección de empleados para actualizar');
                return;
            }
            
            const agendaContainer = document.querySelector('.col-lg-8 .card-body');
            
            // Buscar el elemento rangoFechas de múltiples formas
            let rangoFechas = document.getElementById('rangoFechas');
            if (!rangoFechas) {
                rangoFechas = document.querySelector('#rangoFechas');
            }
            if (!rangoFechas) {
                rangoFechas = document.querySelector('span[id="rangoFechas"]');
            }
            
            console.log('Actualizando vista agenda con:', agendaDatos);
            console.log('Elemento rangoFechas encontrado:', rangoFechas);
            
            // Actualizar el rango de fechas usando la función auxiliar
            actualizarRangoFechas(agendaDatos);
            
            if (!agendaContainer) {
                console.error('No se encontró el contenedor de agenda');
                return;
            }
            
            if (!agendaDatos || agendaDatos.length === 0) {
                // Mostrar mensaje de sin pagos
                agendaContainer.innerHTML = `
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-calendar-x fs-1" style="color: #E1B240;"></i>
                        <div class="mt-3">
                            <h6 class="fw-bold">No hay pagos programados</h6>
                            <p class="mb-0">No hay pagos programados para esta semana.</p>
                        </div>
                    </div>
                `;
                return;
            }

            let html = '';
            agendaDatos.forEach(dia => {
                const esHoy = dia.fecha === new Date().toISOString().split('T')[0];
                
                html += `
                    <div class="rounded p-3 mb-2 ${esHoy ? 'bg-light border-primary' : ''}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <div class="fw-bold small text-uppercase text-muted">
                                    ${esHoy ? '<i class="bi bi-circle-fill text-primary me-1" style="font-size: .5rem;"></i>' : ''}
                                    ${dia.dia_nombre}
                                </div>
                                <div class="h6 mb-0 fw-bold" style="color: #79481D;">
                                    ${dia.dia_numero} ${dia.mes}
                                </div>
                            </div>
                            <div class="text-end">
                `;
                
                if (dia.pagos_pendientes_count > 0) {
                    html += `
                        <span class="badge badge-sm me-1" style="background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); color: white; font-size: 0.6rem;">
                            ${dia.pagos_pendientes_count} pendientes
                        </span>
                    `;
                }
                
                if (dia.pagos_hechos_count > 0) {
                    html += `
                        <span class="badge bg-success badge-sm" style="font-size: 0.6rem;">
                            ${dia.pagos_hechos_count} hechos
                        </span>
                    `;
                }
                
                html += `
                            </div>
                        </div>
                `;
                
                if (dia.pagos && dia.pagos.length > 0) {
                    html += `
                        <div class="small">
                            <div class="row g-2">
                    `;
                    
                    dia.pagos.forEach(pago => {
                        const esPendiente = pago.estado === 'pendiente';
                        html += `
                            <div class="col-6 col-md-3">
                                <a href="/contratos/${pago.contrato_id}" class="text-decoration-none">
                                    <div class="py-2 px-2 bg-white rounded border-start border-3 mb-2 h-100" 
                                         style="border-left-color: ${esPendiente ? '#E1B240' : '#28a745'} !important;">
                                        <div class="fw-bold text-truncate ${esPendiente ? '' : 'text-success'}">
                                            <i class="bi bi-person${esPendiente ? '' : '-check'}-fill me-1" style="color: ${esPendiente ? '#E1B240' : '#28a745'};"></i>
                                            ${pago.cliente_nombre.length > 15 ? pago.cliente_nombre.substring(0, 15) + '...' : pago.cliente_nombre}
                                        </div>
                                        <div class="fw-bold" style="color: ${esPendiente ? '#E1B240' : '#28a745'};">
                                            ${esPendiente ? '' : '<i class="bi bi-check-circle me-1"></i>'}$${new Intl.NumberFormat().format(pago.monto)}
                                        </div>
                                    </div>
                                </a>
                            </div>
                        `;
                    });
                    
                    html += `
                            </div>
                        </div>
                    `;
                } else {
                    html += `
                        <div class="text-center text-muted small">
                            <i class="bi bi-calendar-x" style="color: #E1B240;"></i>
                            Sin pagos programados
                        </div>
                    `;
                }
                
                html += `</div>`;
            });

            agendaContainer.innerHTML = html;
            
            // Asegurar que el rango de fechas se actualice después de modificar el contenido
            setTimeout(() => {
                actualizarRangoFechas(agendaDatos);
            }, 50);
        }


        // ==================== RUTAS DEL EMPLEADO (MOBILE FIRST) ====================
        var homeMap = null;
        var homeMarkerLayer = null;
        var homeRouteLine = null;
        var homeHomeMarker = null;
        var currentParadas = [];
        var currentDomicilios = [];
        var currentRutaId = null;
        var currentRutaMeta = null;
        var sliderIndex = 0;
        var paradaMarkers = [];

        var homeCurrentPosMarker = null;
        var homeCurrentPosCircle = null;
        var lastKnownGps = null;
        var homeGpsWatchId = null;

        function rememberGps(pos) {
            if (!pos || !pos.coords) return;
            lastKnownGps = {
                lat: pos.coords.latitude,
                lng: pos.coords.longitude,
                acc: pos.coords.accuracy || 0,
                ts: Date.now()
            };
        }

        function applyHomeGpsMarker(lat, lng, acc) {
            if (!homeMap) return;
                    if (homeCurrentPosMarker) {
                        homeCurrentPosMarker.setLatLng([lat, lng]);
                if (homeCurrentPosCircle) {
                    homeCurrentPosCircle.setLatLng([lat, lng]).setRadius(acc || 30);
                }
                return;
            }
                        homeCurrentPosMarker = L.marker([lat, lng], {
                            icon: L.divIcon({
                                className: 'current-pos-marker',
                                html: '<div style="width:18px;height:18px;background:#007AFF;border:3px solid white;border-radius:50%;box-shadow:0 0 12px rgba(0,122,255,0.5);"></div>',
                                iconSize: [18, 18], iconAnchor: [9, 9]
                            }),
                            zIndexOffset: 900
                        }).addTo(homeMap);
                        homeCurrentPosCircle = L.circle([lat, lng], {
                radius: acc || 30,
                            color: '#007AFF',
                            fillColor: '#007AFF',
                            fillOpacity: 0.08,
                            weight: 1,
                            opacity: 0.3
                        }).addTo(homeMap);
                    }

        function startHomeGpsWatch() {
            if (!navigator.geolocation || homeGpsWatchId !== null) return;
            homeGpsWatchId = navigator.geolocation.watchPosition(function(pos) {
                rememberGps(pos);
                applyHomeGpsMarker(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy);
                }, function(err) {
                console.log('Geolocation error:', err && err.message);
            }, { enableHighAccuracy: true, maximumAge: 60000, timeout: 30000 });
        }

        startHomeGpsWatch();

        function initHomeMap() {
            if (homeMap) {
                homeMap.invalidateSize();
                if (lastKnownGps) applyHomeGpsMarker(lastKnownGps.lat, lastKnownGps.lng, lastKnownGps.acc);
                return;
            }
            homeMap = L.map('homeMap', { zoomControl: false, attributionControl: false }).setView([20.67, -103.36], 12);
            L.control.zoom({ position: 'topright' }).addTo(homeMap);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OSM', maxZoom: 19
            }).addTo(homeMap);
            homeMarkerLayer = L.layerGroup().addTo(homeMap);
            startHomeGpsWatch();
            if (lastKnownGps) applyHomeGpsMarker(lastKnownGps.lat, lastKnownGps.lng, lastKnownGps.acc);
        }

        function clearHomeMap() {
            if (homeMarkerLayer) homeMarkerLayer.clearLayers();
            if (homeRouteLine && homeMap) { homeMap.removeLayer(homeRouteLine); homeRouteLine = null; }
            if (homeHomeMarker && homeMap) { homeMap.removeLayer(homeHomeMarker); homeHomeMarker = null; }
            paradaMarkers = [];
            // Keep current position marker — don't remove it
        }

        function makeHomeIcon(bg, sz, txt) {
            return L.divIcon({
                className: 'custom-marker',
                html: '<div style="background:' + bg + ';width:' + sz + 'px;height:' + sz + 'px;border-radius:50%;border:2px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;"><span style="color:white;font-size:' + Math.round(sz * 0.4) + 'px;font-weight:bold;">' + txt + '</span></div>',
                iconSize: [sz, sz], iconAnchor: [sz / 2, sz / 2]
            });
        }

        function escHtml(s) {
            return String(s == null ? '' : s).replace(/[&<>"']/g, function(c) {
                return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
            });
        }

        // El container tiene z-index:10; el backdrop va a body y tapaba el modal.
        (function setupMisRutasModal() {
            var modal = document.getElementById('misRutasModal');
            if (!modal) return;
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }

            var pageList = document.getElementById('misRutasPageList');
            var pageDetail = document.getElementById('misRutasPageDetail');
            var titleEl = document.getElementById('misRutasModalLabel');
            var detailTitle = document.getElementById('misRutasDetailTitle');
            var detailSub = document.getElementById('misRutasDetailSub');
            var detailList = document.getElementById('misRutasDetailList');
            var btnVerRuta = document.getElementById('btnVerRutaPlantilla');
            var searchWrap = document.getElementById('misRutasSearchWrap');
            var searchInput = document.getElementById('misRutasSearch');
            var searchClear = document.getElementById('misRutasSearchClear');
            var searchResults = document.getElementById('misRutasSearchResults');
            var rutaProximaUrlTpl = @json(route('home.rutaProximaPlantilla', ['plantilla' => '__ID__'], absolute: false));
            var paradasById = {};
            var dataEl = document.getElementById('misRutasParadasData');
            if (dataEl) {
                try {
                    paradasById = JSON.parse(dataEl.textContent || '{}') || {};
                } catch (e) {
                    paradasById = {};
                }
            }

            function normTxt(s) {
                return String(s == null ? '' : s)
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .trim();
            }

            function plantillaMeta(id) {
                var card = pageList
                    ? pageList.querySelector('.shlom-plantilla-card[data-plantilla-id="' + id + '"]')
                    : null;
                return {
                    id: String(id),
                    nombre: card ? (card.getAttribute('data-plantilla-nombre') || ('Ruta #' + id)) : ('Ruta #' + id),
                    freq: card ? (card.getAttribute('data-plantilla-freq') || '') : '',
                    card: card
                };
            }

            function showMisRutasPage(page) {
                if (pageList) pageList.classList.toggle('active', page === 'list');
                if (pageDetail) pageDetail.classList.toggle('active', page === 'detail');
                if (titleEl) titleEl.textContent = page === 'detail' ? 'Paradas' : 'Mis rutas';
                if (searchWrap) searchWrap.classList.toggle('d-none', page !== 'list');
                if (page === 'detail') {
                    clearSearch(true);
                } else {
                    applySearch();
                }
                if (modal.querySelector('.modal-body')) {
                    modal.querySelector('.modal-body').scrollTop = 0;
                }
            }

            function setVerRutaLoading(loading) {
                if (!btnVerRuta) return;
                btnVerRuta.disabled = !!loading;
                var label = btnVerRuta.querySelector('span');
                if (label) label.textContent = loading ? 'Abriendo…' : 'Ver ruta';
            }

            function renderParadasList(paradas) {
                if (!detailList) return;
                if (!paradas.length) {
                    detailList.innerHTML = '<div class="mis-rutas-detail-empty">Esta ruta no tiene paradas asignadas.</div>';
                    return;
                }
                detailList.innerHTML = paradas.map(function(p, idx) {
                    var orden = p.orden || (idx + 1);
                    return '<div class="mis-rutas-parada">' +
                        '<div class="mis-rutas-parada-num">' + orden + '</div>' +
                        '<div class="mis-rutas-parada-body">' +
                        '<p class="mis-rutas-parada-name">' + escHtml(p.cliente) + '</p>' +
                        '<p class="mis-rutas-parada-addr"><i class="bi bi-geo-alt me-1"></i>' + escHtml(p.domicilio) + '</p>' +
                        (p.contrato_id ? '<div class="mis-rutas-parada-meta"><span class="mis-rutas-parada-chip">Folio #' + p.contrato_id + '</span></div>' : '') +
                        '</div></div>';
                }).join('');
            }

            function openPlantillaDetailById(id) {
                var meta = plantillaMeta(id);
                var paradas = Array.isArray(paradasById[id]) ? paradasById[id] : [];
                if (detailTitle) detailTitle.textContent = meta.nombre;
                if (detailSub) {
                    detailSub.textContent = meta.freq
                        + (paradas.length ? (' · ' + paradas.length + ' parada' + (paradas.length === 1 ? '' : 's')) : '');
                }
                if (btnVerRuta) {
                    btnVerRuta.setAttribute('data-plantilla-id', id);
                    setVerRutaLoading(false);
                }
                renderParadasList(paradas);
                showMisRutasPage('detail');
            }

            function openPlantillaDetail(card) {
                openPlantillaDetailById(String(card.getAttribute('data-plantilla-id') || ''));
            }

            function clearSearch(silent) {
                if (searchInput && searchInput.value) searchInput.value = '';
                if (searchClear) searchClear.classList.add('d-none');
                if (!silent) applySearch();
                else {
                    if (searchResults) {
                        searchResults.classList.remove('active');
                        searchResults.hidden = true;
                        searchResults.innerHTML = '';
                    }
                    if (pageList) pageList.classList.remove('mis-rutas-page-dimmed');
                }
            }

            function applySearch() {
                var q = searchInput ? normTxt(searchInput.value) : '';
                if (searchClear) searchClear.classList.toggle('d-none', !q);
                if (!searchResults || !pageList) return;

                if (!q) {
                    searchResults.classList.remove('active');
                    searchResults.hidden = true;
                    searchResults.innerHTML = '';
                    pageList.classList.remove('mis-rutas-page-dimmed');
                    pageList.classList.add('active');
                    return;
                }

                var hits = [];
                Object.keys(paradasById).forEach(function(pid) {
                    var meta = plantillaMeta(pid);
                    (paradasById[pid] || []).forEach(function(p, idx) {
                        var folio = String(p.contrato_id == null ? '' : p.contrato_id);
                        var hay = normTxt(folio) + ' ' + normTxt(p.cliente) + ' ' + normTxt(p.domicilio);
                        if (hay.indexOf(q) === -1) return;
                        hits.push({
                            plantillaId: pid,
                            plantillaNombre: meta.nombre,
                            orden: p.orden || (idx + 1),
                            cliente: p.cliente,
                            domicilio: p.domicilio,
                            contrato_id: p.contrato_id
                        });
                    });
                });

                pageList.classList.add('mis-rutas-page-dimmed');
                pageList.classList.remove('active');
                searchResults.hidden = false;
                searchResults.classList.add('active');

                if (!hits.length) {
                    searchResults.innerHTML = '<div class="mis-rutas-detail-empty">Sin paradas para “' + escHtml(searchInput.value.trim()) + '”.</div>';
                    return;
                }

                searchResults.innerHTML = hits.map(function(h) {
                    return '<button type="button" class="mis-rutas-search-hit" data-plantilla-id="' + escHtml(h.plantillaId) + '">' +
                        '<div class="mis-rutas-parada-num">' + h.orden + '</div>' +
                        '<div class="mis-rutas-parada-body">' +
                        '<p class="mis-rutas-search-hit-route">' + escHtml(h.plantillaNombre) + '</p>' +
                        '<p class="mis-rutas-parada-name">' + escHtml(h.cliente) + '</p>' +
                        '<p class="mis-rutas-parada-addr"><i class="bi bi-geo-alt me-1"></i>' + escHtml(h.domicilio) + '</p>' +
                        (h.contrato_id ? '<div class="mis-rutas-parada-meta"><span class="mis-rutas-parada-chip">Folio #' + h.contrato_id + '</span></div>' : '') +
                        '</div></button>';
                }).join('');
            }

            function abrirRutaProxima(plantillaId) {
                if (!plantillaId || typeof openRutaDetalle !== 'function') return;
                setVerRutaLoading(true);
                var url = rutaProximaUrlTpl.replace('__ID__', encodeURIComponent(plantillaId));
                fetch(url, { headers: { 'Accept': 'application/json' } })
                    .then(function(r) { return r.json().then(function(data) { return { ok: r.ok, data: data }; }); })
                    .then(function(res) {
                        if (!res.ok || !res.data || !res.data.success || !res.data.ruta_id) {
                            throw new Error((res.data && res.data.message) || 'No se pudo abrir la ruta.');
                        }
                        var modalInst = bootstrap.Modal.getInstance(modal);
                        if (modalInst) modalInst.hide();
                        else {
                            var closeBtn = modal.querySelector('[data-bs-dismiss="modal"]');
                            if (closeBtn) closeBtn.click();
                        }
                        openRutaDetalle(res.data.ruta_id);
                    })
                    .catch(function(err) {
                        alert(err && err.message ? err.message : 'No se pudo abrir la ruta.');
                    })
                    .finally(function() {
                        setVerRutaLoading(false);
                    });
            }

            if (searchInput) {
                searchInput.addEventListener('input', applySearch);
                searchInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        e.preventDefault();
                        clearSearch();
                    }
                });
            }
            if (searchClear) {
                searchClear.addEventListener('click', function(e) {
                    e.preventDefault();
                    clearSearch();
                    if (searchInput) searchInput.focus();
                });
            }

            modal.addEventListener('click', function(e) {
                var hit = e.target.closest('.mis-rutas-search-hit');
                if (hit) {
                    e.preventDefault();
                    openPlantillaDetailById(String(hit.getAttribute('data-plantilla-id') || ''));
                    return;
                }
                var card = e.target.closest('.shlom-plantilla-card');
                if (card && pageList && pageList.contains(card)) {
                    e.preventDefault();
                    openPlantillaDetail(card);
                    return;
                }
                if (e.target.closest('#btnMisRutasBack')) {
                    e.preventDefault();
                    showMisRutasPage('list');
                    return;
                }
                var verBtn = e.target.closest('#btnVerRutaPlantilla');
                if (verBtn) {
                    e.preventDefault();
                    if (verBtn.disabled) return;
                    abrirRutaProxima(verBtn.getAttribute('data-plantilla-id'));
                }
            });

            modal.addEventListener('keydown', function(e) {
                if (e.target && e.target.id === 'misRutasSearch') return;
                if (e.key !== 'Enter' && e.key !== ' ') return;
                var card = e.target.closest('.shlom-plantilla-card');
                if (card && pageList && pageList.contains(card)) {
                    e.preventDefault();
                    openPlantillaDetail(card);
                }
            });

            modal.addEventListener('hidden.bs.modal', function() {
                showMisRutasPage('list');
                clearSearch();
                setVerRutaLoading(false);
            });
        })();

        var rutasDiaOffset = {{ (int) $currentDayOffset }};
        var rutasDiaEndpoint = @json(route('home.rutasDia', absolute: false));

        function htmlRutaDiaCard(r) {
            var extra = '';
            if (r.completadas > 0) {
                extra += '<div class="shlom-stat shlom-stat-success"><i class="bi bi-check-circle-fill"></i><span>' + r.completadas + '</span></div>';
            }
            if (r.omitidas > 0) {
                extra += '<div class="shlom-stat shlom-stat-danger"><i class="bi bi-x-circle-fill"></i><span>' + r.omitidas + '</span></div>';
            }
            return '<div class="shlom-route-card" data-ruta-id="' + r.id + '">' +
                '<div class="shlom-route-card-header"><div class="shlom-route-status" style="background:' + r.estado_bg + ';color:' + r.estado_color + ';">' +
                '<i class="bi ' + r.estado_icon + '"></i><span>' + r.estado_label + '</span></div></div>' +
                '<div class="shlom-route-card-body"><div class="shlom-route-date"><i class="bi bi-signpost-2"></i><span>' + escHtml(r.nombre) + '</span></div>' +
                '<div class="shlom-route-stats"><div class="shlom-stat"><i class="bi bi-geo-alt-fill"></i><span>' + r.total_domicilios + '</span><small>domicilios</small></div>' +
                extra +
                '<div class="shlom-stat ms-auto"><small class="fw-bold" style="color:#79481D;">' + r.porcentaje + '%</small></div></div>' +
                '<div class="shlom-progress-track"><div class="shlom-progress-fill" style="width:' + r.porcentaje + '%;"></div></div></div>' +
                '<div class="shlom-route-card-arrow"><i class="bi bi-chevron-right"></i></div></div>';
        }

        function pintarRutasDia(data) {
            var lista = document.getElementById('listaRutasDia');
            var titulo = document.getElementById('rutasDiaTitulo');
            var sub = document.getElementById('rutasDiaSubtitulo');
            if (titulo) titulo.textContent = data.titulo;
            if (sub) sub.textContent = data.subtitulo;
            if (!lista) return;
            if (!data.rutas || !data.rutas.length) {
                lista.innerHTML = '<div class="shlom-empty-state"><div class="shlom-empty-icon"><i class="fa-solid fa-route"></i></div><h3>Sin rutas este día</h3><p>Cambia de día o abre <strong>Mis rutas</strong> para ver tus asignaciones.</p></div>';
                return;
            }
            lista.innerHTML = data.rutas.map(htmlRutaDiaCard).join('');
        }

        function syncRutasDiaLinks() {
            var prev = document.getElementById('btnRutasDiaPrev');
            var next = document.getElementById('btnRutasDiaNext');
            if (prev) prev.setAttribute('href', '?day=' + (rutasDiaOffset - 1));
            if (next) next.setAttribute('href', '?day=' + (rutasDiaOffset + 1));
        }

        function cargarRutasDia(offset, fallbackHref) {
            var url = rutasDiaEndpoint + (rutasDiaEndpoint.indexOf('?') >= 0 ? '&' : '?') + 'day=' + offset;
            fetch(url, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(function(r) {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                })
                .then(function(data) {
                    if (!data || !data.success) throw new Error('Respuesta inválida');
                    rutasDiaOffset = data.offset;
                    pintarRutasDia(data);
                    syncRutasDiaLinks();
                    var loc = new URL(window.location.href);
                    if (rutasDiaOffset === 0) loc.searchParams.delete('day');
                    else loc.searchParams.set('day', String(rutasDiaOffset));
                    history.replaceState({}, '', loc);
                })
                .catch(function() {
                    if (fallbackHref) window.location.href = fallbackHref;
                });
        }

        function onRutasDiaNavClick(e) {
            var link = e.currentTarget;
            if (!link) return;
            e.preventDefault();
            var nextOffset = link.id === 'btnRutasDiaPrev' ? rutasDiaOffset - 1 : rutasDiaOffset + 1;
            cargarRutasDia(nextOffset, link.getAttribute('href'));
        }

        var btnRutasDiaPrev = document.getElementById('btnRutasDiaPrev');
        var btnRutasDiaNext = document.getElementById('btnRutasDiaNext');
        if (btnRutasDiaPrev) {
            btnRutasDiaPrev.addEventListener('click', onRutasDiaNavClick);
        }
        if (btnRutasDiaNext) {
            btnRutasDiaNext.addEventListener('click', onRutasDiaNavClick);
        }

        var listaRutasDia = document.getElementById('listaRutasDia');
        if (listaRutasDia) {
            listaRutasDia.addEventListener('click', function(e) {
                var card = e.target.closest('.shlom-route-card');
                if (!card) return;
                var rutaId = card.getAttribute('data-ruta-id');
                if (rutaId) openRutaDetalle(rutaId);
            });
        }

        function setRutaDetalleBodyLock(on) {
            document.body.classList.toggle('shlom-ruta-detalle-open', !!on);
            if (!on) {
                document.body.style.removeProperty('top');
                document.body.style.removeProperty('position');
                document.body.style.removeProperty('width');
            }
        }

        // Volver a la lista
        var btnBackToList = document.getElementById('btnBackToList');
        if (btnBackToList) {
            btnBackToList.addEventListener('click', function() {
                document.getElementById('screenRutaList').classList.add('active');
                document.getElementById('screenRutaDetalle').classList.remove('active');
                setRutaDetalleBodyLock(false);
                // Refrescar progreso/estado de las tarjetas del día.
                if (typeof cargarRutasDia === 'function') {
                    cargarRutasDia(rutasDiaOffset);
                }
            });
        }

        // ==================== BOTTOM SHEET ====================
        (function() {
            var sheet = document.getElementById('bottomSheet');
            var handle = document.getElementById('sheetHandle');
            var content = sheet ? sheet.querySelector('.shlom-sheet-content') : null;
            if (!sheet || !handle || !content) return;

            var startY = 0;
            var startX = 0;
            var baseH = 0;
            var isDragging = false;
            var directionLocked = null;
            var THRESHOLD = 40;
            var moved = false;
            var fromHandle = false;

            function isDesktopSheetUi() {
                return window.matchMedia && window.matchMedia('(min-width: 768px)').matches;
            }

            function syncDetallesButton() {
                var btn = document.getElementById('btnSheetDetalles');
                var label = document.getElementById('btnSheetDetallesLabel');
                if (!btn) return;
                var expanded = sheet.classList.contains('expanded');
                if (label) label.textContent = expanded ? 'Ocultar' : 'Detalles';
                btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            }

            function measureCollapsedSheetHeight() {
                var meta = document.getElementById('rutaDetalleMeta');
                var sliderWrap = sheet.querySelector('.shlom-slider-wrap') || document.getElementById('paradasSlider');
                var controls = sheet.querySelector('.shlom-slider-controls');
                var detallesBtn = document.getElementById('btnSheetDetalles');
                var handleVisible = handle && getComputedStyle(handle).display !== 'none';
                var handleH = handleVisible ? (handle.getBoundingClientRect().height || 28) : 0;
                var detallesH = 0;
                if (detallesBtn && getComputedStyle(detallesBtn).display !== 'none') {
                    detallesH = detallesBtn.getBoundingClientRect().height + 12;
                }
                var gap = 10;
                var padBottom = handleVisible ? 14 : 4;
                var controlsPull = 4;
                var parts = [meta, sliderWrap, controls].filter(Boolean);
                var contentH = 0;
                parts.forEach(function(el, i) {
                    contentH += el.getBoundingClientRect().height;
                    if (i < parts.length - 1) contentH += gap;
                });
                contentH = Math.max(contentH - controlsPull, 0);
                var h = Math.ceil(handleH + contentH + padBottom + detallesH);
                var minH = 240;
                var maxH = Math.round(window.innerHeight * 0.68);
                return Math.max(minH, Math.min(maxH, h || 320));
            }

            function syncCollapsedSheetHeight() {
                var h = measureCollapsedSheetHeight();
                sheet.style.setProperty('--shlom-sheet-collapsed', h + 'px');
                return h;
            }

            function collapsedSheetHeight() {
                var raw = getComputedStyle(sheet).getPropertyValue('--shlom-sheet-collapsed').trim();
                var parsed = parseFloat(raw);
                if (parsed && !isNaN(parsed)) return parsed;
                return measureCollapsedSheetHeight();
            }

            function expandedSheetHeight() {
                return Math.round(window.innerHeight * 0.85);
            }

            window.syncBottomSheetCollapsedHeight = syncCollapsedSheetHeight;

            function contentAtTop() {
                return content.scrollTop <= 1;
            }

            function beginSheetDrag(clientY, clientX, handleStart) {
                isDragging = true;
                moved = false;
                directionLocked = null;
                fromHandle = !!handleStart;
                startY = clientY;
                startX = clientX;
                baseH = sheet.getBoundingClientRect().height || (
                    sheet.classList.contains('expanded')
                        ? expandedSheetHeight()
                        : collapsedSheetHeight()
                );
                sheet.style.transition = 'none';
            }

            function canStartSheetDragFromTarget(target, handleStart) {
                if (handleStart) return true;
                if (target.closest('.shlom-card-btn, .shlom-seg-tab, button, a, input, select, textarea')) {
                    return false;
                }
                if (!sheet.classList.contains('expanded')) return true;
                return contentAtTop();
            }

            function applySheetHeight(dy) {
                var collapsedH = collapsedSheetHeight();
                var expandedH = expandedSheetHeight();
                var newH = baseH - dy;
                newH = Math.max(collapsedH, Math.min(expandedH, newH));
                sheet.style.height = newH + 'px';
            }

            function endSheetDrag(clientY) {
                if (!isDragging) return;
                isDragging = false;
                sheet.style.transition = 'height 0.35s cubic-bezier(0.32, 0.72, 0, 1)';

                if (directionLocked !== 'v' || !moved) {
                    sheet.style.height = '';
                    return;
                }

                var dy = clientY - startY;
                var collapsedH = collapsedSheetHeight();
                var expandedH = expandedSheetHeight();
                var currentH = parseFloat(sheet.style.height) || baseH;
                var mid = (collapsedH + expandedH) / 2;

                // Prefer gesture direction; fall back to midpoint.
                if (dy < -THRESHOLD) {
                    sheet.classList.add('expanded');
                } else if (dy > THRESHOLD) {
                    sheet.classList.remove('expanded');
                    content.scrollTop = 0;
                } else if (currentH > mid) {
                    sheet.classList.add('expanded');
                } else {
                    sheet.classList.remove('expanded');
                    content.scrollTop = 0;
                }
                sheet.style.height = '';
            }

            function onTouchStart(e, handleStart) {
                if (e.touches.length !== 1) return;
                if (!canStartSheetDragFromTarget(e.target, handleStart)) return;
                beginSheetDrag(e.touches[0].clientY, e.touches[0].clientX, handleStart);
            }

            function onTouchMove(e) {
                if (!isDragging) return;

                var dy = e.touches[0].clientY - startY;
                var dx = e.touches[0].clientX - startX;

                if (directionLocked === null) {
                    if (Math.abs(dy) < 4 && Math.abs(dx) < 4) return;
                    directionLocked = Math.abs(dy) >= Math.abs(dx) ? 'v' : 'h';
                    if (directionLocked === 'h') {
                        isDragging = false;
                        sheet.style.height = '';
                        sheet.style.transition = '';
                        return;
                    }
                }

                if (directionLocked === 'h') return;

                var expanded = sheet.classList.contains('expanded');

                // Expanded and already scrolled: native content scroll.
                if (!fromHandle && expanded && !contentAtTop()) {
                    isDragging = false;
                    sheet.style.height = '';
                    sheet.style.transition = '';
                    return;
                }

                // Expanded at top, finger going up: let content scroll.
                if (!fromHandle && expanded && contentAtTop() && dy < 0) {
                    isDragging = false;
                    sheet.style.height = '';
                    sheet.style.transition = '';
                    return;
                }

                moved = true;
                if (e.cancelable) e.preventDefault();
                applySheetHeight(dy);
            }

            handle.addEventListener('touchstart', function(e) { onTouchStart(e, true); }, { passive: true });
            content.addEventListener('touchstart', function(e) { onTouchStart(e, false); }, { passive: true });
            sheet.addEventListener('touchmove', onTouchMove, { passive: false });
            sheet.addEventListener('touchend', function(e) {
                endSheetDrag(e.changedTouches[0] ? e.changedTouches[0].clientY : startY);
            }, { passive: true });
            sheet.addEventListener('touchcancel', function(e) {
                endSheetDrag(e.changedTouches[0] ? e.changedTouches[0].clientY : startY);
            }, { passive: true });

            // Block page pull-to-refresh while the finger is on the sheet.
            document.addEventListener('touchmove', function(e) {
                if (!isDragging || directionLocked !== 'v') return;
                if (e.cancelable) e.preventDefault();
            }, { passive: false });

            function toggleSheetDetalles() {
                sheet.classList.toggle('expanded');
                if (!sheet.classList.contains('expanded')) content.scrollTop = 0;
                syncDetallesButton();
            }

            handle.addEventListener('click', function() {
                if (moved) return;
                toggleSheetDetalles();
            });

            var btnDetalles = document.getElementById('btnSheetDetalles');
            if (btnDetalles) {
                btnDetalles.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleSheetDetalles();
                });
            }

            window.resetBottomSheet = function(historica) {
                sheet.classList.remove('expanded');
                sheet.classList.toggle('historica', !!historica);
                sheet.style.transition = 'none';
                sheet.style.height = '';
                sheet.style.transform = '';
                content.scrollTop = 0;
                sheet.offsetHeight;
                syncDetallesButton();
                syncCollapsedSheetHeight();
                sheet.style.transition = '';
            };

            window.addEventListener('resize', function() {
                if (!document.getElementById('screenRutaDetalle') ||
                    !document.getElementById('screenRutaDetalle').classList.contains('active')) {
                    return;
                }
                syncDetallesButton();
                syncCollapsedSheetHeight();
            });

            sheet.addEventListener('transitionend', function() {
                syncDetallesButton();
                if (typeof homeMap !== 'undefined' && homeMap) {
                    setTimeout(function() { homeMap.invalidateSize(); }, 50);
                }
            });

            syncDetallesButton();
        })();

        function domicilioCerrada(dom) {
            return (dom.paradas || []).every(function(p) {
                return p.estado === 'visitada' || p.estado === 'omitida';
            });
        }

        function paradaDesbloqueada(idx, domicilios) {
            // Todas las paradas son accionables; el orden es solo una sugerencia de recorrido.
            return true;
        }

        function calcularProgresoDomicilios(domicilios) {
            var total = (domicilios || []).length;
            if (!total) return { porcentaje: 0, hechos: 0, total: 0 };
            var hechos = 0;
            domicilios.forEach(function(dom) {
                if (!domicilioPendienteRuta(dom)) hechos++;
            });
            return {
                porcentaje: Math.round((hechos / total) * 100),
                hechos: hechos,
                total: total
            };
        }

        function pintarProgresoRutaMeta(ruta, domicilios) {
            var fill = document.getElementById('detailRutaProgressFill');
            var pctEl = document.getElementById('detailRutaProgressPct');
            var r = (ruta && ruta.resumen) || {};
            var pct = r.porcentaje;
            if (pct == null && domicilios) {
                pct = calcularProgresoDomicilios(domicilios).porcentaje;
            }
            if (pct == null) pct = 0;
            if (fill) fill.style.width = pct + '%';
            if (pctEl) pctEl.textContent = pct + '%';
        }

        function pintarResumenHistorico(ruta) {
            var box = document.getElementById('rutaResumenHistorico');
            var puntoCasa = document.getElementById('puntoCasaBlock');
            if (!box) return;
            var pasada = !!ruta.es_pasada;
            // En días pasados se muestra progreso, pero se puede seguir abonando.
            box.classList.toggle('d-none', !pasada);
            if (puntoCasa) puntoCasa.classList.toggle('d-none', false);
            setPuntoCasaUI(
                (ruta && ruta.punto_casa) || 'inicio',
                !!(ruta && ruta.empleado_lat && ruta.empleado_lng)
            );
            if (!pasada) return;
            var r = ruta.resumen || {};
            var pct = r.porcentaje != null ? r.porcentaje : 0;
            var fill = document.getElementById('rutaResumenProgressFill');
            var pctLabel = document.getElementById('rutaResumenPorcentaje');
            if (fill) fill.style.width = pct + '%';
            if (pctLabel) pctLabel.textContent = pct + '% completado';
            var vis = document.getElementById('rutaResumenVisitados');
            var omi = document.getElementById('rutaResumenOmitidos');
            var pen = document.getElementById('rutaResumenPendientes');
            if (vis) vis.textContent = String(r.visitados != null ? r.visitados : 0);
            if (omi) omi.textContent = String(r.omitidos != null ? r.omitidos : 0);
            if (pen) pen.textContent = String(r.pendientes != null ? r.pendientes : 0);
        }

        function openRutaDetalle(rutaId, focusIdx) {
            fetch('/rutas/' + rutaId + '/datos', {
                headers: { 'Accept': 'application/json' }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    showRutaDetalle(data, focusIdx);
                }
            })
            .catch(function() {
                alert('Error al cargar la ruta.');
            });
        }

        function showRutaDetalle(data, focusIdx) {
            var ruta = data.ruta;
            var paradas = data.paradas;
            currentParadas = paradas;
            currentRutaMeta = ruta;
            currentRutaId = ruta.id;
            currentDomicilios = ordenarDomiciliosPorCasa(groupParadasByDomicilio(paradas), ruta);
            var esPasada = !!ruta.es_pasada;
            setPuntoCasaUI(ruta.punto_casa || 'inicio', !!(ruta.empleado_lat && ruta.empleado_lng));
            // Por defecto centrar la #1 de la ruta óptima (primera pendiente).
            if (typeof focusIdx !== 'number' || focusIdx < 0) {
                focusIdx = indicePrimeraPendienteOptima(currentDomicilios);
            }
            sliderIndex = focusIdx;

            // Transición de pantallas
            document.getElementById('screenRutaList').classList.remove('active');
            document.getElementById('screenRutaDetalle').classList.add('active');
            setRutaDetalleBodyLock(true);

            // Reset bottom sheet to collapsed (pasadas también permiten acciones)
            if (window.resetBottomSheet) window.resetBottomSheet(false);

            pintarResumenHistorico(ruta);

            // Topbar: nombre + frecuencia
            document.getElementById('detailRutaTitle').textContent = ruta.nombre || ('Ruta #' + ruta.id);
            var freqEl = document.getElementById('detailRutaFreq');
            if (freqEl) {
                if (ruta.frecuencia) {
                    freqEl.textContent = ruta.frecuencia;
                    freqEl.classList.remove('d-none');
                } else {
                    freqEl.textContent = '';
                    freqEl.classList.add('d-none');
                }
            }

            // Meta en bottom sheet: fecha, estado y progreso
            var fechaEl = document.getElementById('detailRutaFecha');
            if (fechaEl) fechaEl.textContent = ruta.fecha ? formatearFechaLargaEs(ruta.fecha) : '—';
            var estadoConfig = {
                'planeada': { color: '#007AFF', label: 'Planeada' },
                'en_curso': { color: '#FF9500', label: 'En Curso' },
                'detenida': { color: '#FF3B30', label: 'Detenida' },
                'completada': { color: '#34C759', label: 'Completada' },
                'incompleta': { color: '#8E8E93', label: 'Incompleta' },
                'vencida': { color: '#1C1C1E', label: 'Vencida' }
            };
            var ec = estadoConfig[ruta.estado] || { color: '#8E8E93', label: ruta.estado || '—' };
            var badge = document.getElementById('detailRutaEstado');
            if (badge) {
                badge.textContent = ec.label;
                badge.style.background = ec.color + '20';
                badge.style.color = ec.color;
            }
            pintarProgresoRutaMeta(ruta, currentDomicilios);

            // Render slider
            renderSlider(currentDomicilios, focusIdx);

            // Init map; si casa es destino, reordena desde GPS actual.
            setTimeout(function() {
                initHomeMap();
                if ((ruta.punto_casa || 'inicio') === 'final') {
                    aplicarPuntoCasa('final', false);
                    return;
                }
                clearHomeMap();
                renderHomeParadas(ruta);
                centerOnDomicilio(sliderIndex);
                updateSliderControls();
            }, 300);
        }

        function openMapsApp(lat, lng) {
            if (!lat || !lng) return;
            var isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
            if (isIOS) {
                window.open('https://maps.apple.com/?daddr=' + lat + ',' + lng, '_blank');
            } else {
                window.open('https://www.google.com/maps/dir/?api=1&destination=' + lat + ',' + lng, '_blank');
            }
        }

        function groupParadasByDomicilio(paradas) {
            var groups = {};
            var order = [];
            paradas.forEach(function(p) {
                var key = p.cliente_id + '_' + p.domicilio;
                if (!groups[key]) {
                    groups[key] = {
                        cliente_id: p.cliente_id,
                        cliente_nombre: p.cliente_nombre,
                        domicilio: p.domicilio,
                        latitud: p.latitud,
                        longitud: p.longitud,
                        paradas: []
                    };
                    order.push(key);
                }
                groups[key].paradas.push(p);
            });
            return order.map(function(k) { return groups[k]; });
        }

        function distanciaKm(lat1, lng1, lat2, lng2) {
            var r = 6371;
            var dLat = (lat2 - lat1) * Math.PI / 180;
            var dLng = (lng2 - lng1) * Math.PI / 180;
            var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLng / 2) * Math.sin(dLng / 2);
            return r * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }

        function ordenarPorCercania(items, originLat, originLng) {
            var con = [];
            var sin = [];
            items.forEach(function(item) {
                var lat = parseFloat(item.latitud);
                var lng = parseFloat(item.longitud);
                if (lat && lng) {
                    item.latitud = lat;
                    item.longitud = lng;
                    con.push(item);
                } else {
                    sin.push(item);
                }
            });
            if (!originLat || !originLng || !con.length) return items.slice();
            var ordenadas = [];
            var curLat = originLat;
            var curLng = originLng;
            while (con.length) {
                var best = 0;
                var bestD = Infinity;
                for (var i = 0; i < con.length; i++) {
                    var d = distanciaKm(curLat, curLng, con[i].latitud, con[i].longitud);
                    if (d < bestD) {
                        bestD = d;
                        best = i;
                    }
                }
                var next = con.splice(best, 1)[0];
                ordenadas.push(next);
                curLat = next.latitud;
                curLng = next.longitud;
            }
            return ordenadas.concat(sin);
        }

        /** Parada/domicilio aún pendiente de atención (sin visita/abono completo). */
        function domicilioPendienteRuta(dom) {
            if (!dom || !dom.paradas || !dom.paradas.length) return false;
            return dom.paradas.some(function(p) { return p.estado === 'pendiente'; });
        }

        /** Timestamp del primer registro (visita/abono) en el domicilio. */
        function tiempoRegistroDomicilio(dom) {
            var minT = Infinity;
            var found = false;
            (dom.paradas || []).forEach(function(p) {
                var regs = p.registros && p.registros.length ? p.registros : (p.registros_ubicacion || []);
                regs.forEach(function(r) {
                    if (!r.created_at) return;
                    var ms = Date.parse(r.created_at);
                    if (!isNaN(ms)) {
                        found = true;
                        if (ms < minT) minT = ms;
                    }
                });
            });
            return found ? minT : Number.MAX_SAFE_INTEGER;
        }

        function indicePrimeraPendienteOptima(domicilios) {
            for (var i = 0; i < (domicilios || []).length; i++) {
                if (domicilioPendienteRuta(domicilios[i])) return i;
            }
            return 0;
        }

        function etiquetaPuntoCasa(modo) {
            if (modo === 'final') return 'Punto final';
            if (modo === 'ambos') return 'Inicio y fin';
            return 'Punto de partida';
        }

        function origenParaOrdenRuta(ruta, modo) {
            // Destino = casa: partir de la ubicación actual del cobrador.
            if (modo === 'final' && lastKnownGps && lastKnownGps.lat && lastKnownGps.lng) {
                return { lat: lastKnownGps.lat, lng: lastKnownGps.lng, source: 'gps' };
            }
            var lat = ruta && parseFloat(ruta.empleado_lat);
            var lng = ruta && parseFloat(ruta.empleado_lng);
            if (lat && lng) return { lat: lat, lng: lng, source: 'casa' };
            return null;
        }

        function ordenarDomiciliosPorCasa(domicilios, ruta) {
            var modo = (ruta && ruta.punto_casa) || 'inicio';
            var origen = origenParaOrdenRuta(ruta, modo);
            var lista = domicilios.slice();
            var pendientes = [];
            var hechas = [];
            lista.forEach(function(dom) {
                if (domicilioPendienteRuta(dom)) pendientes.push(dom);
                else hechas.push(dom);
            });
            // Visitadas/abonos primero (por hora de registro); pendientes después en orden óptimo.
            hechas.sort(function(a, b) {
                return tiempoRegistroDomicilio(a) - tiempoRegistroDomicilio(b);
            });
            var pendientesOrd = origen
                ? ordenarPorCercania(pendientes, origen.lat, origen.lng)
                : pendientes;
            hechas.forEach(function(dom) { dom._optNum = null; });
            pendientesOrd.forEach(function(dom, i) { dom._optNum = i + 1; });
            return hechas.concat(pendientesOrd);
        }

        function setPuntoCasaUI(modo, enabled) {
            var tabs = document.querySelectorAll('#puntoCasaTabs .shlom-seg-tab');
            var loading = document.getElementById('puntoCasaBlock')
                && document.getElementById('puntoCasaBlock').classList.contains('is-loading');
            tabs.forEach(function(tab) {
                var on = tab.getAttribute('data-value') === modo;
                tab.classList.toggle('active', on);
                tab.setAttribute('aria-selected', on ? 'true' : 'false');
                tab.disabled = !enabled || loading;
            });
        }

        function setPuntoCasaLoading(loading) {
            var block = document.getElementById('puntoCasaBlock');
            if (!block) return;
            block.classList.toggle('is-loading', !!loading);
            var tabs = document.querySelectorAll('#puntoCasaTabs .shlom-seg-tab');
            var enabled = !!(currentRutaMeta && currentRutaMeta.empleado_lat && currentRutaMeta.empleado_lng);
            tabs.forEach(function(tab) {
                tab.disabled = !!loading || !enabled;
            });
        }

        function aplicarPuntoCasa(modo, persist) {
            if (!currentRutaMeta) return;
            currentRutaMeta.punto_casa = modo;
            setPuntoCasaUI(modo, !!(currentRutaMeta.empleado_lat && currentRutaMeta.empleado_lng));

            var finish = function() {
                setPuntoCasaLoading(false);
                currentDomicilios = ordenarDomiciliosPorCasa(groupParadasByDomicilio(currentParadas), currentRutaMeta);
                setPuntoCasaUI(modo, !!(currentRutaMeta.empleado_lat && currentRutaMeta.empleado_lng));
                renderSlider(currentDomicilios);
                if (homeMap) {
                    clearHomeMap();
                    renderHomeParadas(currentRutaMeta);
                    centerOnDomicilio(sliderIndex);
                    updateSliderControls();
                }
                if (persist) guardarPuntoCasa();
            };

            // Si eligen casa como destino, pide GPS fresco para usarlo como primer punto.
            if (modo === 'final' && navigator.geolocation) {
                setPuntoCasaLoading(true);
                navigator.geolocation.getCurrentPosition(function(pos) {
                    rememberGps(pos);
                    if (homeMap && lastKnownGps) {
                        applyHomeGpsMarker(lastKnownGps.lat, lastKnownGps.lng, lastKnownGps.acc);
                    }
                    finish();
                }, function() {
                    finish();
                }, { enableHighAccuracy: true, timeout: 8000, maximumAge: 15000 });
                return;
            }

            setPuntoCasaLoading(false);
            finish();
        }

        function guardarPuntoCasa() {
            if (!currentRutaId || !currentRutaMeta) return;
            var ids = [];
            currentDomicilios.forEach(function(dom) {
                (dom.paradas || []).forEach(function(p) { ids.push(p.id); });
            });
            var csrf = document.querySelector('meta[name="csrf-token"]');
            fetch('/rutas/' + currentRutaId + '/punto-casa', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf ? csrf.getAttribute('content') : '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    punto_casa: currentRutaMeta.punto_casa || 'inicio',
                    ruta_parada_ids: ids
                })
            }).catch(function() {});
        }

        function renderHomeParadas(ruta) {
            var pts = [];
            var routePts = [];
            var domicilios = currentDomicilios;
            var modo = (ruta && ruta.punto_casa) || 'inicio';
            var homeLat = ruta && parseFloat(ruta.empleado_lat);
            var homeLng = ruta && parseFloat(ruta.empleado_lng);

            // Destino = casa: la ubicación actual es el primer punto del recorrido.
            if (modo === 'final' && lastKnownGps && lastKnownGps.lat && lastKnownGps.lng) {
                routePts.push([lastKnownGps.lat, lastKnownGps.lng]);
                pts.push([lastKnownGps.lat, lastKnownGps.lng]);
            }

            if (homeLat && homeLng) {
                homeHomeMarker = L.marker([homeLat, homeLng], {
                    icon: L.divIcon({
                        className: 'custom-marker',
                        html: '<div style="background:#34C759;width:34px;height:34px;border-radius:50%;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-house" style="color:white;font-size:15px;"></i></div>',
                        iconSize: [34, 34], iconAnchor: [17, 17]
                    }),
                    zIndexOffset: 1000
                }).addTo(homeMap);
                homeHomeMarker.bindPopup('<strong>' + etiquetaPuntoCasa(modo) + '</strong>');
                pts.push([homeLat, homeLng]);
            }

            domicilios.forEach(function(dom, idx) {
                var esPendiente = domicilioPendienteRuta(dom);
                if (dom.latitud && dom.longitud) {
                    pts.push([dom.latitud, dom.longitud]);
                    // Trayectoria óptima: solo pendientes (visita/abono fuera de la ruta).
                    if (esPendiente) {
                        routePts.push([dom.latitud, dom.longitud]);
                    }
                    var totalP = dom.paradas.length;
                    var compP = dom.paradas.filter(function(p) { return p.estado === 'visitada'; }).length;
                    var omitP = dom.paradas.filter(function(p) { return p.estado === 'omitida'; }).length;
                    var tieneAbonoMap = (dom.paradas || []).some(function(p) {
                        return (p.registros_ubicacion || []).some(function(r) { return !!r.tiene_pago; });
                    });
                    var color = omitP === totalP
                        ? '#FF3B30'
                        : (compP === totalP ? (tieneAbonoMap ? '#34C759' : '#FF9500') : '#79481D');
                    var sz = idx === sliderIndex ? 36 : 28;
                    var markerLabel = esPendiente && dom._optNum != null
                        ? String(dom._optNum)
                        : (compP === totalP ? '✓' : '·');
                    var m = L.marker([dom.latitud, dom.longitud], {
                        icon: makeHomeIcon(color, sz, markerLabel),
                        zIndexOffset: 500
                    });
                    m._domIndex = idx;
                    m.on('click', function() {
                        goToSlide(idx);
                    });
                    homeMarkerLayer.addLayer(m);
                    paradaMarkers.push(m);
                }

                // Banderas: ubicación real donde se registró la visita/abono
                (dom.paradas || []).forEach(function(p) {
                    (p.registros_ubicacion || []).forEach(function(reg) {
                        var rLat = parseFloat(reg.latitud);
                        var rLng = parseFloat(reg.longitud);
                        if (!rLat || !rLng) return;
                        pts.push([rLat, rLng]);
                        var flagColor = reg.tiene_pago ? '#007AFF' : '#FF9500';
                        var flagLabel = reg.tiene_pago ? 'Abono registrado aquí' : 'Visita registrada aquí';
                        var flag = L.marker([rLat, rLng], {
                            icon: makeRegistroFlagIcon(flagColor),
                            zIndexOffset: 850
                        });
                        flag.bindPopup(
                            '<div style="min-width:140px"><strong style="color:' + flagColor + '">' + flagLabel + '</strong>' +
                            '<br><small>' + escHtml(dom.cliente_nombre || '') + '</small>' +
                            (p.contrato_id ? '<br><small>Folio #' + p.contrato_id + '</small>' : '') +
                            '</div>'
                        );
                        homeMarkerLayer.addLayer(flag);
                    });
                });
            });

            if (homeLat && homeLng) {
                if (modo === 'inicio' || modo === 'ambos') routePts.unshift([homeLat, homeLng]);
                if (modo === 'final' || modo === 'ambos') routePts.push([homeLat, homeLng]);
            }

            if (pts.length > 0) homeMap.fitBounds(pts, { padding: [60, 200] });

            if (routePts.length > 1) {
                homeRouteLine = L.polyline(routePts, { color: '#79481D', weight: 3, opacity: 0.6, dashArray: '10,8' }).addTo(homeMap);
            }
        }

        function makeRegistroFlagIcon(color) {
            color = color || '#007AFF';
            return L.divIcon({
                className: 'custom-marker shlom-registro-flag',
                html: '<div style="position:relative;width:28px;height:34px;filter:drop-shadow(0 2px 4px rgba(0,0,0,0.35));">' +
                    '<svg viewBox="0 0 24 34" width="28" height="34" xmlns="http://www.w3.org/2000/svg">' +
                    '<path d="M6 2v30" stroke="#3a3a3c" stroke-width="2.2" stroke-linecap="round"/>' +
                    '<path d="M7 3h11.5c.8 0 1.2.9.7 1.5L16 9l3.2 4.5c.5.6.1 1.5-.7 1.5H7V3z" fill="' + color + '"/>' +
                    '</svg></div>',
                iconSize: [28, 34],
                iconAnchor: [6, 34],
                popupAnchor: [8, -30]
            });
        }

        function renderSlider(domicilios, focusIdx) {
            var container = document.getElementById('paradasSlider');
            var dots = document.getElementById('sliderDots');
            container.innerHTML = '';
            dots.innerHTML = '';

            if (typeof focusIdx === 'number' && focusIdx >= 0) {
                sliderIndex = Math.min(focusIdx, Math.max(domicilios.length - 1, 0));
            } else {
                sliderIndex = indicePrimeraPendienteOptima(domicilios);
            }

            domicilios.forEach(function(dom, idx) {
                var totalP = dom.paradas.length;
                var compP = dom.paradas.filter(function(p) { return p.estado === 'visitada'; }).length;
                var omitP = dom.paradas.filter(function(p) { return p.estado === 'omitida'; }).length;
                var tieneAbono = (dom.paradas || []).some(function(p) {
                    return (p.registros_ubicacion || []).some(function(r) { return !!r.tiene_pago; });
                });
                var estadoDom = compP === totalP
                    ? (tieneAbono ? 'abonada' : 'visitada')
                    : (omitP === totalP ? 'omitida' : 'pendiente');
                var estadoConfig = {
                    'abonada': { color: '#34C759', bg: 'rgba(52,199,89,0.18)', label: 'Abono', icon: 'bi-cash-coin', cardClass: 'is-abonada' },
                    'visitada': { color: '#FF9500', bg: 'rgba(255,149,0,0.18)', label: 'Visitada', icon: 'bi-check-circle-fill', cardClass: 'is-visitada' },
                    'omitida': { color: '#FF3B30', bg: 'rgba(255,59,48,0.16)', label: 'Omitida', icon: 'bi-x-circle-fill', cardClass: 'is-omitida' },
                    'pendiente': { color: '#79481D', bg: 'rgba(121,72,29,0.14)', label: 'Pendiente', icon: 'bi-clock', cardClass: 'is-pendiente' }
                };
                var ec = estadoConfig[estadoDom] || estadoConfig['pendiente'];
                var multiBadge = totalP > 1 ? ' <span style="background:rgba(142,142,147,0.15);color:#636366;font-size:0.6rem;padding:2px 6px;border-radius:10px;margin-left:4px;">' + totalP + ' contratos</span>' : '';

                var subItems = '';
                dom.paradas.forEach(function(p) {
                    var pTieneAbono = (p.registros_ubicacion || []).some(function(r) { return !!r.tiene_pago; });
                    var pEstado = p.estado === 'visitada'
                        ? (pTieneAbono ? 'abonada' : 'visitada')
                        : (p.estado === 'omitida' ? 'omitida' : 'pendiente');
                    var subEstado = estadoConfig[pEstado] || estadoConfig['pendiente'];
                    subItems += '<div class="shlom-contract-row">';
                    subItems += '<div class="shlom-contract-dot" style="background:' + subEstado.color + ';"></div>';
                    subItems += '<span class="shlom-contract-id">C#' + (p.contrato_id || p.id) + '</span>';
                    subItems += '<span class="shlom-contract-amount">$' + (p.cuota || '0') + '</span>';
                    subItems += '</div>';
                });

                var pendientes = dom.paradas.filter(function(p) { return p.estado === 'pendiente'; }).length;
                var visitada = estadoDom === 'visitada' || estadoDom === 'abonada';
                var visitBtn = '';
                var ausenteBtn = '';
                // Abonos permitidos también en fechas anteriores.
                if (pendientes > 0) {
                    visitBtn = `<button type="button" class="shlom-card-btn shlom-card-btn-visit" data-dom-index="${idx}">
                                <i class="bi bi-geo-alt-fill"></i>
                                <span>Abono</span>
                            </button>`;
                    ausenteBtn = `<button type="button" class="shlom-card-btn shlom-card-btn-ausente" data-dom-index="${idx}">
                                <i class="bi bi-x-lg"></i>
                                <span>Ausente</span>
                            </button>`;
                } else if (visitada) {
                    var ticketUrl = '';
                    var visitaId = '';
                    (dom.paradas || []).forEach(function(p) {
                        if (p.estado === 'visitada' && p.ticket_url && !ticketUrl) {
                            ticketUrl = p.ticket_url;
                            visitaId = p.visita_id || '';
                        }
                    });
                    var ticketLabel = estadoDom === 'abonada' ? 'Abono' : 'Visitada';
                    if (ticketUrl) {
                        visitBtn = `<button type="button" class="shlom-card-btn shlom-card-btn-visit shlom-card-btn-ticket" data-dom-index="${idx}" data-ticket-url="${ticketUrl}" data-visita-id="${visitaId}">
                                    <i class="bi bi-receipt"></i>
                                    <span>${ticketLabel}</span>
                                </button>`;
                    } else {
                        visitBtn = `<button type="button" class="shlom-card-btn shlom-card-btn-visit" disabled style="opacity:.45;">
                                    <i class="bi bi-check-circle"></i>
                                    <span>${ticketLabel}</span>
                                </button>`;
                    }
                }

                var card = document.createElement('div');
                card.className = 'shlom-slider-card ' + ec.cardClass;
                card.dataset.index = idx;
                card.dataset.estado = estadoDom;
                var estadoBadge = '<span class="shlom-parada-estado" style="background:' + ec.bg + ';color:' + ec.color + ';">' +
                    '<i class="bi ' + ec.icon + '"></i>' + ec.label + '</span>';
                var numLabel = dom._optNum != null ? String(dom._optNum) : (estadoDom === 'abonada' || estadoDom === 'visitada' ? '✓' : '·');
                card.innerHTML = `
                    <div class="shlom-card-content">
                        <div class="shlom-card-header-row">
                            <div class="shlom-card-num">${numLabel}</div>
                            <div class="shlom-card-header-text">
                                <span class="shlom-card-client">${dom.cliente_nombre}</span>
                                ${multiBadge}
                            </div>
                            ${estadoBadge}
                        </div>
                        <div class="shlom-card-address">
                            <i class="bi bi-location"></i>
                            <span>${dom.domicilio}</span>
                        </div>
                        <div class="shlom-card-actions">
                            <button class="shlom-card-btn shlom-card-btn-maps" data-lat="${dom.latitud}" data-lng="${dom.longitud}" onclick="event.stopPropagation(); openMapsApp(${dom.latitud}, ${dom.longitud})">
                                <i class="bi bi-map"></i>
                                <span>Mapa</span>
                            </button>
                            ${visitBtn}
                            ${ausenteBtn}
                        </div>
                        ${totalP > 1 ? '<div class="shlom-card-contracts">' + subItems + '</div>' : ''}
                    </div>
                `;
                card.addEventListener('click', function() {
                    goToSlide(idx);
                });
                container.appendChild(card);

                // Dot
                var dot = document.createElement('span');
                dot.className = 'shlom-dot' + (idx === 0 ? ' active' : '');
                dot.dataset.index = idx;
                dot.addEventListener('click', function() {
                    goToSlide(idx);
                });
                dots.appendChild(dot);
            });

            updateSliderPosition();
            updateSliderControls();
            updateSliderNavButtons();
            pintarDetalleParadaCentrada(sliderIndex);
            requestAnimationFrame(function() {
                if (typeof window.syncBottomSheetCollapsedHeight === 'function') {
                    window.syncBottomSheetCollapsedHeight();
                }
            });
        }

        function formatearFechaHoraRegistro(iso) {
            if (!iso) return '—';
            var d = new Date(iso);
            if (isNaN(d.getTime())) return '—';
            var dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            var meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
            var hh = String(d.getHours()).padStart(2, '0');
            var mm = String(d.getMinutes()).padStart(2, '0');
            return dias[d.getDay()] + ' ' + d.getDate() + ' ' + meses[d.getMonth()] + ', ' + hh + ':' + mm;
        }

        function registrosDeDomicilio(dom) {
            var out = [];
            (dom.paradas || []).forEach(function(p) {
                var regs = p.registros && p.registros.length
                    ? p.registros
                    : (p.registros_ubicacion || []);
                regs.forEach(function(r) {
                    out.push({
                        registro: r,
                        contrato_id: p.contrato_id,
                        ticket_url: r.ticket_url || p.ticket_url || '',
                        visita_id: r.visita_id || p.visita_id || ''
                    });
                });
            });
            out.sort(function(a, b) {
                var ta = a.registro.created_at ? Date.parse(a.registro.created_at) : 0;
                var tb = b.registro.created_at ? Date.parse(b.registro.created_at) : 0;
                return ta - tb;
            });
            return out;
        }

        function pintarDetalleParadaCentrada(index) {
            var box = document.getElementById('paradaRegistroDetalle');
            if (!box) return;
            var dom = currentDomicilios[index];
            if (!dom || domicilioPendienteRuta(dom)) {
                box.classList.add('d-none');
                box.innerHTML = '';
                return;
            }

            var regs = registrosDeDomicilio(dom);
            if (!regs.length) {
                var tieneAbonoFallback = (dom.paradas || []).some(function(p) {
                    return (p.registros_ubicacion || []).some(function(r) { return !!r.tiene_pago; });
                });
                var tipoFallback = tieneAbonoFallback ? 'Abono' : 'Visitada';
                var badgeClass = tieneAbonoFallback ? 'is-abono' : 'is-visita';
                var ticketUrl = '';
                var visitaId = '';
                (dom.paradas || []).forEach(function(p) {
                    if (p.ticket_url && !ticketUrl) {
                        ticketUrl = p.ticket_url;
                        visitaId = p.visita_id || '';
                    }
                });
                box.innerHTML =
                    '<div class="shlom-parada-registro-kicker">Detalle del registro</div>' +
                    '<div class="shlom-parada-registro-row">' +
                    '<div><p class="shlom-parada-registro-label">Estado</p>' +
                    '<p class="shlom-parada-registro-value">' + escHtml(dom.cliente_nombre || '') + '</p></div>' +
                    '<span class="shlom-parada-registro-badge ' + badgeClass + '">' + tipoFallback + '</span></div>' +
                    (ticketUrl
                        ? '<div class="shlom-parada-registro-actions">' +
                          '<button type="button" class="shlom-card-btn shlom-card-btn-visit shlom-card-btn-ticket" data-ticket-url="' + escHtml(ticketUrl) + '" data-visita-id="' + escHtml(String(visitaId)) + '">' +
                          '<i class="bi bi-receipt"></i><span>Ver ticket</span></button></div>'
                        : '');
                box.classList.remove('d-none');
                return;
            }

            var html = '<div class="shlom-parada-registro-kicker">Detalle del registro</div>';
            regs.forEach(function(item, i) {
                var r = item.registro;
                var esAbono = !!r.tiene_pago;
                var badgeClass = esAbono ? 'is-abono' : 'is-visita';
                var badgeLabel = esAbono ? 'Abono' : 'Visitada';
                html += '<div class="shlom-parada-registro-block"' + (i > 0 ? ' style="margin-top:12px;padding-top:12px;border-top:1px solid rgba(60,60,67,0.08);"' : '') + '>';
                html += '<div class="shlom-parada-registro-row">' +
                    '<div><p class="shlom-parada-registro-label">Tipo</p>' +
                    '<p class="shlom-parada-registro-value">' + badgeLabel +
                    (item.contrato_id ? '<small>Folio #' + item.contrato_id + '</small>' : '') +
                    '</p></div>' +
                    '<span class="shlom-parada-registro-badge ' + badgeClass + '">' +
                    '<i class="bi ' + (esAbono ? 'bi-cash-coin' : 'bi-check-circle-fill') + '"></i>' + badgeLabel +
                    '</span></div>';
                html += '<div class="shlom-parada-registro-row">' +
                    '<div><p class="shlom-parada-registro-label">Fecha y hora</p>' +
                    '<p class="shlom-parada-registro-value">' + escHtml(formatearFechaHoraRegistro(r.created_at)) + '</p></div></div>';
                if (r.latitud != null && r.longitud != null) {
                    html += '<div class="shlom-parada-registro-row">' +
                        '<div><p class="shlom-parada-registro-label">Ubicación del registro</p>' +
                        '<p class="shlom-parada-registro-value">' +
                        Number(r.latitud).toFixed(5) + ', ' + Number(r.longitud).toFixed(5) +
                        '</p></div></div>';
                }
                if (esAbono && r.pagos && r.pagos.length) {
                    html += '<div class="shlom-parada-registro-pagos">';
                    r.pagos.forEach(function(pago) {
                        html += '<div class="shlom-parada-registro-pago">' +
                            '<span class="metodo">' + escHtml(pago.metodo_label || pago.metodo_pago || 'Pago') + '</span>' +
                            '<span class="monto">$' + escHtml(pago.monto_fmt != null ? pago.monto_fmt : Number(pago.monto || 0).toFixed(2)) + '</span>' +
                            '</div>';
                    });
                    if (r.pagos.length > 1 && r.monto_total_fmt) {
                        html += '<div class="shlom-parada-registro-pago">' +
                            '<span class="metodo">Total</span>' +
                            '<span class="monto">$' + escHtml(r.monto_total_fmt) + '</span></div>';
                    }
                    html += '</div>';
                } else if (!esAbono) {
                    html += '<div class="shlom-parada-registro-row">' +
                        '<div><p class="shlom-parada-registro-label">Resultado</p>' +
                        '<p class="shlom-parada-registro-value">Sin recepción / ausente</p></div></div>';
                }
                if (item.ticket_url) {
                    html += '<div class="shlom-parada-registro-actions">' +
                        '<button type="button" class="shlom-card-btn shlom-card-btn-visit shlom-card-btn-ticket" data-ticket-url="' + escHtml(item.ticket_url) + '" data-visita-id="' + escHtml(String(item.visita_id || '')) + '">' +
                        '<i class="bi bi-receipt"></i><span>Ver ticket</span></button></div>';
                }
                html += '</div>';
            });

            box.innerHTML = html;
            box.classList.remove('d-none');
        }

        function updateSliderNavButtons() {
            var prev = document.getElementById('btnSliderPrev');
            var next = document.getElementById('btnSliderNext');
            var total = (currentDomicilios || []).length;
            if (prev) prev.disabled = !total || sliderIndex <= 0;
            if (next) next.disabled = !total || sliderIndex >= total - 1;
        }

        function goToSlide(index) {
            sliderIndex = Math.max(0, Math.min(index, currentDomicilios.length - 1));
            updateSliderPosition();
            updateSliderControls();
            updateSliderNavButtons();
            centerOnDomicilio(sliderIndex);
            pintarDetalleParadaCentrada(sliderIndex);
            if (typeof window.syncBottomSheetCollapsedHeight === 'function') {
                requestAnimationFrame(window.syncBottomSheetCollapsedHeight);
            }
        }

        function updateSliderPosition() {
            var container = document.getElementById('paradasSlider');
            var cards = container.querySelectorAll('.shlom-slider-card');
            if (cards.length === 0) return;

            var cardWidth = cards[0].offsetWidth + 12; // card + gap
            container.scrollTo({ left: sliderIndex * cardWidth, behavior: 'smooth' });

            // Update dots
            document.querySelectorAll('.shlom-dot').forEach(function(d, i) {
                d.classList.toggle('active', i === sliderIndex);
            });
        }

        function updateSliderControls() {
            // Update marker sizes
            paradaMarkers.forEach(function(m) {
                var i = typeof m._domIndex === 'number' ? m._domIndex : paradaMarkers.indexOf(m);
                var dom = currentDomicilios[i];
                if (!dom) return;
                var totalP = dom.paradas.length;
                var compP = dom.paradas.filter(function(p) { return p.estado === 'visitada'; }).length;
                var omitP = dom.paradas.filter(function(p) { return p.estado === 'omitida'; }).length;
                var tieneAbono = (dom.paradas || []).some(function(p) {
                    return (p.registros_ubicacion || []).some(function(r) { return !!r.tiene_pago; });
                });
                var color = omitP === totalP
                    ? '#FF3B30'
                    : (compP === totalP ? (tieneAbono ? '#34C759' : '#FF9500') : '#79481D');
                var sz = i === sliderIndex ? 36 : 28;
                var markerLabel = domicilioPendienteRuta(dom) && dom._optNum != null
                    ? String(dom._optNum)
                    : (compP === totalP ? '✓' : '·');
                m.setIcon(makeHomeIcon(color, sz, markerLabel));
            });
        }

        function centerOnDomicilio(index) {
            if (!currentDomicilios[index] || !currentDomicilios[index].latitud) return;
            var dom = currentDomicilios[index];
            homeMap.panTo([dom.latitud, dom.longitud], { animate: true });
        }

        // Touch swipe on slider
        var puntoCasaTabs = document.getElementById('puntoCasaTabs');
        if (puntoCasaTabs) {
            puntoCasaTabs.addEventListener('click', function(e) {
                var tab = e.target.closest('.shlom-seg-tab');
                if (!tab || tab.disabled) return;
                var block = document.getElementById('puntoCasaBlock');
                if (block && block.classList.contains('is-loading')) return;
                e.preventDefault();
                e.stopPropagation();
                var modo = tab.getAttribute('data-value');
                if (currentRutaMeta && currentRutaMeta.punto_casa === modo) return;
                aplicarPuntoCasa(modo, true);
            });
        }

        var btnSliderPrev = document.getElementById('btnSliderPrev');
        var btnSliderNext = document.getElementById('btnSliderNext');
        if (btnSliderPrev) {
            btnSliderPrev.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (sliderIndex > 0) goToSlide(sliderIndex - 1);
            });
        }
        if (btnSliderNext) {
            btnSliderNext.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (sliderIndex < currentDomicilios.length - 1) goToSlide(sliderIndex + 1);
            });
        }

        document.addEventListener('keydown', function(e) {
            var screen = document.getElementById('screenRutaDetalle');
            if (!screen || !screen.classList.contains('active')) return;
            if (e.target && /^(INPUT|TEXTAREA|SELECT)$/i.test(e.target.tagName)) return;
            if (e.target && e.target.isContentEditable) return;
            // No interferir con modales abiertos.
            if (document.querySelector('.modal.show')) return;
            if (e.key === 'ArrowLeft') {
                e.preventDefault();
                if (sliderIndex > 0) goToSlide(sliderIndex - 1);
            } else if (e.key === 'ArrowRight') {
                e.preventDefault();
                if (sliderIndex < currentDomicilios.length - 1) goToSlide(sliderIndex + 1);
            }
        });

        var sliderEl = document.getElementById('paradasSlider');
        var sliderTouchStartX = 0;
        var sliderTouchStartY = 0;
        var sliderTouchOnContracts = false;
        if (sliderEl) {
        sliderEl.addEventListener('touchstart', function(e) {
            sliderTouchStartX = e.touches[0].clientX;
            sliderTouchStartY = e.touches[0].clientY;
            sliderTouchOnContracts = !!e.target.closest('.shlom-card-contracts');
        }, { passive: true });
        sliderEl.addEventListener('touchend', function(e) {
            if (sliderTouchOnContracts) return;
            var endX = e.changedTouches[0].clientX;
            var endY = e.changedTouches[0].clientY;
            var dx = sliderTouchStartX - endX;
            var dy = sliderTouchStartY - endY;
            if (Math.abs(dx) <= Math.abs(dy)) return;
            if (Math.abs(dx) > 50) {
                if (dx > 0 && sliderIndex < currentDomicilios.length - 1) goToSlide(sliderIndex + 1);
                else if (dx < 0 && sliderIndex > 0) goToSlide(sliderIndex - 1);
            }
        }, { passive: true });
        }

        // Also detect scroll stop on slider
        var scrollTimer;
        if (sliderEl) {
        sliderEl.addEventListener('scroll', function() {
            clearTimeout(scrollTimer);
            scrollTimer = setTimeout(function() {
                var cards = sliderEl.querySelectorAll('.shlom-slider-card');
                if (cards.length === 0) return;
                var cardWidth = cards[0].offsetWidth + 12;
                var newIdx = Math.round(sliderEl.scrollLeft / cardWidth);
                if (newIdx !== sliderIndex) {
                    sliderIndex = newIdx;
                    updateSliderPosition();
                    updateSliderControls();
                    updateSliderNavButtons();
                    centerOnDomicilio(sliderIndex);
                    pintarDetalleParadaCentrada(sliderIndex);
                    if (typeof window.syncBottomSheetCollapsedHeight === 'function') {
                        requestAnimationFrame(window.syncBottomSheetCollapsedHeight);
                    }
                }
            }, 150);
        });
        }

        var visitaGpsMap = null;
        var visitaGpsMarker = null;
        var visitaParadaMarker = null;
        var visitaLinea = null;
        var VISITA_DISTANCIA_MAX_M = 150;
        var VISITA_METODOS = {
            'efectivo': 'Efectivo',
            'tarjeta credito/debito': 'Tarjeta'
        };
        var VISITA_METODO_ICONOS = {
            'efectivo': 'bi-cash-coin',
            'tarjeta credito/debito': 'bi-credit-card'
        };
        var visitaPending = {
            paradaIds: [],
            punto: '',
            paradaLat: null,
            paradaLng: null,
            step: 1,
            gpsOk: false,
            enDomicilio: false,
            clienteNombre: '',
            ticketUrl: '',
            visitaId: null,
            pendientes: [],
            userLat: null,
            userLng: null,
            modo: 'abono',
            enviandoAusente: false,
            soloVistaTicket: false,
            deshaciendo: false,
            registrandoAbono: false
        };

        function visitaIdDesdeTicketUrl(url) {
            if (!url) return null;
            var m = String(url).match(/\/visitas\/(\d+)\/ticket/);
            return m ? parseInt(m[1], 10) : null;
        }

        function setVisitaGpsStatus(type, html, canConfirm) {
            var el = document.getElementById('visitaGpsStatus');
            if (!el) return;
            visitaPending.gpsOk = !!canConfirm;
            var recenter = document.getElementById('btnVisitaGpsRecenter');
            if (recenter) recenter.disabled = !visitaPending.userLat;
            if (canConfirm && !html) {
                el.classList.add('d-none');
                el.innerHTML = '';
                syncVisitaWizardButtons();
                return;
            }
            el.classList.remove('d-none');
            el.className = 'ios-banner ios-banner-' + type;
            el.innerHTML = html;
            syncVisitaWizardButtons();
        }

        function setVisitaDistanciaAlert(html) {
            var el = document.getElementById('visitaDistanciaAlert');
            if (!el) return;
            if (!html) {
                el.classList.add('d-none');
                el.innerHTML = '';
                return;
            }
            el.className = 'ios-banner ios-banner-warning';
            el.innerHTML = html;
        }

        function distanciaMetros(lat1, lng1, lat2, lng2) {
            var R = 6371000;
            var toRad = function(d) { return d * Math.PI / 180; };
            var dLat = toRad(lat2 - lat1);
            var dLng = toRad(lng2 - lng1);
            var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
                Math.sin(dLng / 2) * Math.sin(dLng / 2);
            return 2 * R * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }

        function formatearDistancia(metros) {
            if (metros < 1000) return Math.round(metros) + ' m';
            return (metros / 1000).toFixed(1).replace(/\.0$/, '') + ' km';
        }

        function destroyVisitaGpsMap() {
            visitaParadaMarker = null;
            visitaLinea = null;
            if (visitaGpsMap) {
                visitaGpsMap.remove();
                visitaGpsMap = null;
                visitaGpsMarker = null;
            }
        }

        function initVisitaGpsMap(lat, lng) {
            var el = document.getElementById('visitaGpsMap');
            if (!el) return;
            if (!visitaGpsMap) {
                visitaGpsMap = L.map(el, { zoomControl: false, attributionControl: false }).setView([lat, lng], 17);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(visitaGpsMap);
            } else {
                visitaGpsMap.setView([lat, lng], 17);
            }
            if (visitaGpsMarker) {
                visitaGpsMarker.setLatLng([lat, lng]);
            } else {
                visitaGpsMarker = L.marker([lat, lng], {
                    icon: L.divIcon({
                        className: 'current-pos-marker',
                        html: '<div style="width:18px;height:18px;background:#007AFF;border:3px solid white;border-radius:50%;box-shadow:0 0 12px rgba(0,122,255,0.5);"></div>',
                        iconSize: [18, 18], iconAnchor: [9, 9]
                    })
                }).addTo(visitaGpsMap);
                visitaGpsMarker.bindPopup('Tu ubicación');
            }

            var pLat = visitaPending.paradaLat;
            var pLng = visitaPending.paradaLng;
            var tieneParada = pLat != null && pLng != null && !isNaN(pLat) && !isNaN(pLng);

            if (tieneParada) {
                if (visitaParadaMarker) {
                    visitaParadaMarker.setLatLng([pLat, pLng]);
                } else {
                    visitaParadaMarker = L.marker([pLat, pLng], {
                        icon: L.divIcon({
                            className: 'custom-marker',
                            html: '<div style="background:#79481D;width:22px;height:22px;border-radius:50%;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.3);"></div>',
                            iconSize: [22, 22], iconAnchor: [11, 11]
                        })
                    }).addTo(visitaGpsMap);
                    visitaParadaMarker.bindPopup('Parada');
                }
                var lineaPts = [[lat, lng], [pLat, pLng]];
                if (visitaLinea) {
                    visitaLinea.setLatLngs(lineaPts);
                } else {
                    visitaLinea = L.polyline(lineaPts, {
                        color: '#79481D',
                        weight: 3,
                        opacity: 0.75,
                        dashArray: '6 6',
                        lineCap: 'round'
                    }).addTo(visitaGpsMap);
                }
            } else {
                if (visitaParadaMarker) {
                    visitaGpsMap.removeLayer(visitaParadaMarker);
                    visitaParadaMarker = null;
                }
                if (visitaLinea) {
                    visitaGpsMap.removeLayer(visitaLinea);
                    visitaLinea = null;
                }
            }

            function encuadrarVisitaMapa() {
                if (!visitaGpsMap) return;
                visitaGpsMap.invalidateSize();
                if (tieneParada) {
                    var bounds = L.latLngBounds([
                        [lat, lng],
                        [pLat, pLng]
                    ]);
                    visitaGpsMap.fitBounds(bounds, { padding: [36, 36], maxZoom: 17 });
                } else {
                    visitaGpsMap.setView([lat, lng], 17);
                }
            }

            encuadrarVisitaMapa();
            setTimeout(encuadrarVisitaMapa, 80);
            setTimeout(encuadrarVisitaMapa, 350);
        }

        function aplicarGpsVisita(lat, lng) {
            visitaPending.userLat = lat;
            visitaPending.userLng = lng;
            visitaPending.punto = 'POINT(' + lng + ' ' + lat + ')';
            initVisitaGpsMap(lat, lng);
            setVisitaGpsStatus('success', '', true);
            advertirSiNoCoincideConParada(lat, lng);
            if (visitaPending.modo === 'ausente' && !visitaPending.enviandoAusente && visitaPending.step === 1) {
                submitVisitaAusente();
            }
        }

        function recentrarGpsVisita() {
            if (visitaPending.userLat == null || visitaPending.userLng == null) {
                pedirUbicacionVisita(true);
                return;
            }
            if (visitaGpsMap) {
                visitaGpsMap.setView([visitaPending.userLat, visitaPending.userLng], 17);
                if (visitaGpsMarker) visitaGpsMarker.setLatLng([visitaPending.userLat, visitaPending.userLng]);
            } else {
                pedirUbicacionVisita(true);
            }
        }

        function advertirSiNoCoincideConParada(lat, lng) {
            var pLat = visitaPending.paradaLat;
            var pLng = visitaPending.paradaLng;
            if (pLat == null || pLng == null || isNaN(pLat) || isNaN(pLng)) {
                visitaPending.enDomicilio = false;
                setVisitaDistanciaAlert('<i class="bi bi-exclamation-triangle me-1"></i>Esta parada no tiene coordenadas registradas; no se puede comprobar si coinciden.');
                return;
            }
            var metros = distanciaMetros(lat, lng, pLat, pLng);
            visitaPending.enDomicilio = metros <= VISITA_DISTANCIA_MAX_M;
            if (visitaPending.enDomicilio) {
                setVisitaDistanciaAlert('');
                return;
            }
            setVisitaDistanciaAlert(
                '<i class="bi bi-exclamation-triangle-fill me-1"></i>' +
                'Tu ubicación actual no coincide con la de la parada (estás a ' + formatearDistancia(metros) + '). ' +
                'Puedes registrar la visita de todos modos.'
            );
        }

        function mostrarErrorGps(err) {
            if (visitaPending.punto) return;
            var msg = 'Permite el uso de la ubicación para registrar la visita.';
            if (typeof window.isSecureContext !== 'undefined' && !window.isSecureContext) {
                msg = 'El navegador bloquea la ubicación en HTTP. Abre el sistema en HTTPS o en localhost.';
            } else if (err && err.code === 1) {
                msg = 'Activa el permiso de ubicación en el navegador para registrar la visita.';
            } else if (err && err.code === 2) {
                msg = 'No se pudo determinar tu posición. Revisa que el GPS esté activo.';
            } else if (err && err.code === 3) {
                msg = 'No se obtuvo la ubicación a tiempo. Revisa que el GPS esté activo e inténtalo de nuevo.';
            }
            setVisitaGpsStatus(
                'warning',
                '<i class="bi bi-geo-alt me-1"></i>' + msg +
                ' <button type="button" class="ios-retry" id="btnRetryGps">Reintentar ubicación</button>',
                false
            );
            var retry = document.getElementById('btnRetryGps');
            if (retry) retry.addEventListener('click', function() {
                pedirUbicacionVisita(true);
            });
        }

        function pedirUbicacionVisita(fromRetry) {
            if (lastKnownGps && !visitaPending.punto) {
                aplicarGpsVisita(lastKnownGps.lat, lastKnownGps.lng);
            }

            if (!navigator.geolocation) {
                if (!visitaPending.punto) {
                    setVisitaGpsStatus('warning', '<i class="bi bi-exclamation-triangle me-1"></i>Este dispositivo no permite geolocalización. Activa la ubicación para registrar la visita.', false);
                }
                return;
            }

            if (typeof window.isSecureContext !== 'undefined' && !window.isSecureContext && !lastKnownGps) {
                mostrarErrorGps({ code: 1 });
                return;
            }

            if (!visitaPending.punto) {
                setVisitaGpsStatus('info', '<span class="spinner-border spinner-border-sm me-2"></span>Obteniendo tu ubicación...', false);
            }

            var onOk = function(pos) {
                rememberGps(pos);
                aplicarGpsVisita(pos.coords.latitude, pos.coords.longitude);
            };

            navigator.geolocation.getCurrentPosition(onOk, function() {
                navigator.geolocation.getCurrentPosition(onOk, mostrarErrorGps, {
                    enableHighAccuracy: false,
                    timeout: 20000,
                    maximumAge: 120000
                });
            }, {
                enableHighAccuracy: true,
                timeout: fromRetry ? 25000 : 8000,
                maximumAge: 120000
            });
        }

        function setVisitaWizardError(msg) {
            var el = document.getElementById('visitaWizardError');
            if (!el) return;
            if (!msg) {
                el.classList.add('d-none');
                el.textContent = '';
                return;
            }
            el.classList.remove('d-none');
            el.textContent = msg;
        }

        function syncVisitaWizardButtons() {
            var next = document.getElementById('btnVisitaWizardNext');
            var cancel = document.getElementById('btnVisitaWizardCancel');
            var printBtn = document.getElementById('btnVisitaWizardPrint');
            var undoBtn = document.getElementById('btnVisitaWizardUndo');
            var closeBtn = document.getElementById('btnVisitaWizardClose');
            var step = visitaPending.step;
            var enTicket = step === 2;
            var esAusente = visitaPending.modo === 'ausente';
            if (cancel) cancel.classList.toggle('d-none', enTicket || (esAusente && visitaPending.enviandoAusente));
            if (printBtn) printBtn.classList.toggle('d-none', !enTicket);
            if (undoBtn) {
                undoBtn.classList.toggle('d-none', !enTicket || !visitaPending.visitaId);
                undoBtn.disabled = !!visitaPending.deshaciendo;
                undoBtn.textContent = visitaPending.deshaciendo ? 'Deshaciendo...' : 'Deshacer la visita';
            }
            if (closeBtn) closeBtn.classList.toggle('d-none', !enTicket);
            if (next) {
                next.classList.toggle('d-none', enTicket || esAusente);
                next.disabled = !visitaPending.gpsOk || !!visitaPending.registrandoAbono;
                next.innerHTML = visitaPending.registrandoAbono
                    ? '<span class="spinner-border spinner-border-sm me-1"></span> Registrando...'
                    : 'Registrar abono';
            }
            var title = document.getElementById('visitaWizardTitle');
            if (title) {
                if (enTicket) title.textContent = 'Ticket';
                else if (esAusente) title.textContent = 'Ausente';
                else title.textContent = 'Abono';
            }
            var icon = document.querySelector('#visitaWizardIcon i');
            if (icon) {
                icon.className = esAusente && !enTicket ? 'bi bi-person-x-fill' : 'bi bi-cash-coin';
            }
            var header = document.getElementById('visitaFormHeader');
            if (header) header.classList.toggle('d-none', enTicket);
        }

        function ajustarAlturaTicketFrame() {
            var frame = document.getElementById('visitaTicketFrame');
            var scrollWrap = document.getElementById('visitaTicketPreviewScroll');
            if (!frame) return;
            try {
                var doc = frame.contentDocument || (frame.contentWindow && frame.contentWindow.document);
                if (!doc) return;
                var root = doc.documentElement;
                var body = doc.body;
                var height = Math.max(
                    root ? root.scrollHeight : 0,
                    root ? root.offsetHeight : 0,
                    body ? body.scrollHeight : 0,
                    body ? body.offsetHeight : 0,
                    320
                );
                frame.style.height = height + 'px';
                if (scrollWrap) scrollWrap.scrollTop = 0;
            } catch (e) {
                frame.style.height = '70vh';
            }
        }

        function bindTicketFrameResize(frame) {
            if (!frame || frame.dataset.resizeBound === '1') return;
            frame.dataset.resizeBound = '1';
            frame.addEventListener('load', function() {
                ajustarAlturaTicketFrame();
                setTimeout(ajustarAlturaTicketFrame, 80);
                setTimeout(ajustarAlturaTicketFrame, 320);
                try {
                    var doc = frame.contentDocument || (frame.contentWindow && frame.contentWindow.document);
                    if (doc && doc.fonts && doc.fonts.ready) {
                        doc.fonts.ready.then(ajustarAlturaTicketFrame).catch(function() {});
                    }
                } catch (e) {}
            });
        }

        function showVisitaWizardStep(step) {
            visitaPending.step = step;
            document.querySelectorAll('.visita-wizard-step').forEach(function(el) {
                el.classList.toggle('d-none', parseInt(el.getAttribute('data-step'), 10) !== step);
            });
            var card = document.querySelector('#visitaConfirmModal .visita-wizard-card');
            if (card) card.classList.toggle('is-ticket-step', step === 2);
            var body = document.querySelector('#visitaConfirmModal .visita-wizard-body');
            if (body) body.scrollTop = 0;
            var ticketScroll = document.getElementById('visitaTicketPreviewScroll');
            if (ticketScroll) ticketScroll.scrollTop = 0;
            syncVisitaWizardButtons();
            if (step === 1 && visitaGpsMap) {
                setTimeout(function() { visitaGpsMap.invalidateSize(); }, 50);
                setTimeout(function() { if (visitaGpsMap) visitaGpsMap.invalidateSize(); }, 300);
            }
            if (step === 2) {
                setTimeout(ajustarAlturaTicketFrame, 40);
            }
        }

        function metodoButtonsHtml(contratoId, selected) {
            var html = '<div class="visita-metodo-group">';
            Object.keys(VISITA_METODOS).forEach(function(key, idx) {
                var id = 'visita-metodo-' + contratoId + '-' + idx;
                var icon = VISITA_METODO_ICONOS[key] || 'bi-wallet2';
                html += '<div class="visita-metodo-option">' +
                    '<input type="radio" class="visita-folio-metodo" name="visita-metodo-' + contratoId + '" id="' + id + '" value="' + key + '"' + (key === selected ? ' checked' : '') + '>' +
                    '<label for="' + id + '"><i class="bi ' + icon + '"></i><span>' + VISITA_METODOS[key] + '</span></label>' +
                    '</div>';
            });
            html += '</div>';
            return html;
        }

        function renderFoliosPago() {
            var wrap = document.getElementById('visitaFoliosPago');
            if (!wrap) return;
            wrap.innerHTML = visitaPending.pendientes.map(function(p) {
                var saldo = typeof p.saldo_raw === 'number' ? p.saldo_raw : parseFloat(String(p.saldo || '0').replace(/,/g, '')) || 0;
                var cuota = typeof p.cuota_raw === 'number' ? p.cuota_raw : parseFloat(String(p.cuota || '0').replace(/,/g, '')) || 0;
                var sugeridoNum = cuota > 0 ? Math.min(cuota, saldo > 0 ? saldo : cuota) : (saldo > 0 ? saldo : 0);
                var sugerido = sugeridoNum > 0 ? String(Math.round(sugeridoNum)) : '';
                var saldoEntero = Math.round(saldo);
                var cuotaEntera = Math.round(cuota);
                return '<div class="ios-folio-card" data-contrato-id="' + p.contrato_id + '" data-saldo="' + saldoEntero + '">' +
                    '<h4>Folio #' + p.contrato_id + '</h4>' +
                    '<div class="saldo">Saldo disponible: $' + saldoEntero + (cuotaEntera > 0 ? ' · Cuota $' + cuotaEntera : '') + '</div>' +
                    '<div class="visita-monto-field">' +
                        '<label>Monto del pago</label>' +
                        '<div class="visita-monto-wrap">' +
                            '<span class="visita-monto-prefix">$</span>' +
                            '<input type="number" inputmode="numeric" pattern="[0-9]*" min="1" step="1" class="visita-folio-monto" placeholder="0" value="' + sugerido + '">' +
                        '</div>' +
                    '</div>' +
                    '<div class="ios-field"><label>Método de pago *</label>' + metodoButtonsHtml(p.contrato_id, 'efectivo') + '</div>' +
                    '<div class="ios-field"><label>Observaciones</label><input type="text" class="visita-folio-obs" maxlength="500" placeholder="Opcional"></div>' +
                    '</div>';
            }).join('') || '<p class="ios-confirm-msg">No hay folios asignados en esta parada.</p>';
            wrap.querySelectorAll('.visita-folio-monto').forEach(function(inp) {
                inp.addEventListener('input', function() {
                    var raw = String(inp.value || '').replace(/[^\d]/g, '');
                    if (inp.value !== raw) inp.value = raw;
                });
            });
        }

        function collectPagosWizard() {
            var pagos = [];
            document.querySelectorAll('#visitaFoliosPago .ios-folio-card').forEach(function(card) {
                var monto = parseInt(card.querySelector('.visita-folio-monto').value, 10);
                if (!monto || monto <= 0) return;
                var metodoEl = card.querySelector('.visita-folio-metodo:checked');
                var obsEl = card.querySelector('.visita-folio-obs');
                pagos.push({
                    contrato_id: parseInt(card.getAttribute('data-contrato-id'), 10),
                    monto: monto,
                    metodo_pago: metodoEl ? metodoEl.value : 'efectivo',
                    tipo_pago: 'parcialidad',
                    observaciones: obsEl && obsEl.value ? String(obsEl.value).trim() : null
                });
            });
            return pagos;
        }

        function validarPagosWizard(pagos) {
            if (!pagos.length) return 'Indica el monto de al menos un folio.';
            var cards = document.querySelectorAll('#visitaFoliosPago .ios-folio-card');
            for (var i = 0; i < pagos.length; i++) {
                var pago = pagos[i];
                if (!Number.isInteger(pago.monto)) {
                    return 'El monto del folio #' + pago.contrato_id + ' debe ser un número entero.';
                }
                var card = null;
                cards.forEach(function(c) {
                    if (parseInt(c.getAttribute('data-contrato-id'), 10) === pago.contrato_id) card = c;
                });
                var saldo = card ? parseInt(card.getAttribute('data-saldo'), 10) : 0;
                if (pago.monto > saldo) {
                    return 'El monto del folio #' + pago.contrato_id + ' supera el saldo disponible del contrato.';
                }
                if (!pago.metodo_pago) {
                    return 'Selecciona el método de pago del folio #' + pago.contrato_id + '.';
                }
            }
            return '';
        }

        function submitVisitaWizard() {
            if (visitaPending.registrandoAbono) return;
            if (!visitaPending.punto || !visitaPending.paradaIds.length) return;
            var pagos = collectPagosWizard();
            var errPago = validarPagosWizard(pagos);
            if (errPago) {
                setVisitaWizardError(errPago);
                return;
            }
            setVisitaWizardError('');
            visitaPending.registrandoAbono = true;
            syncVisitaWizardButtons();
            var csrf = document.querySelector('meta[name="csrf-token"]');
            var payload = {
                ruta_parada_ids: visitaPending.paradaIds,
                ubicacion_evidencia: visitaPending.punto,
                en_domicilio: !!visitaPending.enDomicilio,
                registrar_pago: true,
                pagos: pagos
            };
            fetch('{{ route("visitas.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf ? csrf.getAttribute('content') : '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            })
            .then(function(r) { return r.json().then(function(data) { return { ok: r.ok, data: data }; }); })
            .then(function(res) {
                if (!res.ok) {
                    throw new Error((res.data && (res.data.message || (res.data.errors && Object.values(res.data.errors)[0][0]))) || 'No se pudo registrar el abono.');
                }
                visitaPending.ticketUrl = (res.data && res.data.ticket_url) || '';
                visitaPending.visitaId = (res.data && res.data.visita_id) || visitaIdDesdeTicketUrl(visitaPending.ticketUrl);
                var frame = document.getElementById('visitaTicketFrame');
                if (frame && visitaPending.ticketUrl) {
                    bindTicketFrameResize(frame);
                    frame.src = visitaPending.ticketUrl;
                }
                showVisitaWizardStep(2);
            })
            .catch(function(err) {
                setVisitaWizardError(err.message || 'Error al registrar el abono.');
            })
            .finally(function() {
                visitaPending.registrandoAbono = false;
                syncVisitaWizardButtons();
            });
        }

        function submitVisitaAusente() {
            if (visitaPending.enviandoAusente) return;
            if (!visitaPending.punto || !visitaPending.paradaIds.length) return;
            visitaPending.enviandoAusente = true;
            setVisitaWizardError('');
            setVisitaGpsStatus(
                'info',
                '<span class="spinner-border spinner-border-sm me-2"></span>Registrando visita sin recepción...',
                false
            );
            syncVisitaWizardButtons();
            var csrf = document.querySelector('meta[name="csrf-token"]');
            var payload = {
                ruta_parada_ids: visitaPending.paradaIds,
                ubicacion_evidencia: visitaPending.punto,
                en_domicilio: !!visitaPending.enDomicilio,
                ausente: true,
                recibido: false,
                registrar_pago: false,
                pagos: []
            };
            fetch('{{ route("visitas.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf ? csrf.getAttribute('content') : '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            })
            .then(function(r) { return r.json().then(function(data) { return { ok: r.ok, data: data }; }); })
            .then(function(res) {
                if (!res.ok) {
                    throw new Error((res.data && (res.data.message || (res.data.errors && Object.values(res.data.errors)[0][0]))) || 'No se pudo registrar la visita.');
                }
                visitaPending.ticketUrl = (res.data && res.data.ticket_url) || '';
                visitaPending.visitaId = (res.data && res.data.visita_id) || visitaIdDesdeTicketUrl(visitaPending.ticketUrl);
                var frame = document.getElementById('visitaTicketFrame');
                if (frame && visitaPending.ticketUrl) {
                    bindTicketFrameResize(frame);
                    frame.src = visitaPending.ticketUrl;
                }
                showVisitaWizardStep(2);
            })
            .catch(function(err) {
                visitaPending.enviandoAusente = false;
                setVisitaGpsStatus('warning', '<i class="bi bi-exclamation-triangle me-1"></i>' + (err.message || 'Error al registrar la visita.') +
                    ' <button type="button" class="ios-retry" id="btnRetryAusente">Reintentar</button>', !!visitaPending.punto);
                var retry = document.getElementById('btnRetryAusente');
                if (retry) {
                    retry.addEventListener('click', function() {
                        if (visitaPending.punto) submitVisitaAusente();
                        else pedirUbicacionVisita(true);
                    });
                }
            })
            .finally(function() {
                if (visitaPending.step === 2) visitaPending.enviandoAusente = false;
                syncVisitaWizardButtons();
            });
        }

        function cerrarVisitaWizardYAvanzar() {
            var modal = visitaModalEl ? bootstrap.Modal.getInstance(visitaModalEl) : null;
            if (modal) modal.hide();
        }

        function prepararVisitaWizard(idx, modo) {
            if (!paradaDesbloqueada(idx)) return false;
            var dom = currentDomicilios[idx];
            if (!dom) return false;
            var pendientes = (dom.paradas || []).filter(function(p) { return p.estado === 'pendiente'; });
            if (!pendientes.length) return false;
            visitaPending.modo = modo || 'abono';
            visitaPending.enviandoAusente = false;
            visitaPending.soloVistaTicket = false;
            visitaPending.paradaIds = pendientes.map(function(p) { return p.id; });
            visitaPending.pendientes = pendientes;
            visitaPending.domIndex = idx;
            visitaPending.punto = '';
            visitaPending.gpsOk = false;
            visitaPending.enDomicilio = false;
            visitaPending.clienteNombre = dom.cliente_nombre || '';
            visitaPending.ticketUrl = '';
            visitaPending.visitaId = null;
            visitaPending.deshaciendo = false;
            visitaPending.registrandoAbono = false;
            visitaPending.userLat = null;
            visitaPending.userLng = null;
            visitaPending.paradaLat = (dom.latitud === null || dom.latitud === undefined || dom.latitud === '') ? null : parseFloat(dom.latitud);
            visitaPending.paradaLng = (dom.longitud === null || dom.longitud === undefined || dom.longitud === '') ? null : parseFloat(dom.longitud);
            if (visitaPending.paradaLat != null && isNaN(visitaPending.paradaLat)) visitaPending.paradaLat = null;
            if (visitaPending.paradaLng != null && isNaN(visitaPending.paradaLng)) visitaPending.paradaLng = null;
            setVisitaDistanciaAlert('');
            setVisitaWizardError('');
            var contratosLabel = pendientes.map(function(p) {
                return 'C#' + (p.contrato_id || p.id);
            }).join(', ');
            var setTxt = function(id, value) {
                var n = document.getElementById(id);
                if (n) n.textContent = value || '—';
            };
            setTxt('visitaVinculoContrato', contratosLabel);
            setTxt('visitaVinculoNombre', dom.cliente_nombre);
            setTxt('visitaVinculoDomicilio', dom.domicilio);
            var folios = document.getElementById('visitaFoliosPago');
            if (folios) {
                if (visitaPending.modo === 'ausente') {
                    folios.classList.add('d-none');
                    folios.innerHTML = '';
                } else {
                    folios.classList.remove('d-none');
                    renderFoliosPago();
                }
            }
            showVisitaWizardStep(1);
            setVisitaGpsStatus(
                'info',
                visitaPending.modo === 'ausente'
                    ? '<span class="spinner-border spinner-border-sm me-2"></span>Obteniendo ubicación para registrar ausencia...'
                    : '<span class="spinner-border spinner-border-sm me-2"></span>Obteniendo tu ubicación...',
                false
            );
            destroyVisitaGpsMap();
            pedirUbicacionVisita(false);
            var modalEl = document.getElementById('visitaConfirmModal');
            if (modalEl && modalEl.parentElement !== document.body) {
                document.body.appendChild(modalEl);
            }
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
            return true;
        }

        function openVisitaConfirm(idx) {
            prepararVisitaWizard(idx, 'abono');
        }

        function openVisitaAusente(idx) {
            prepararVisitaWizard(idx, 'ausente');
        }

        function openVisitaTicketPreview(url, idx, visitaId) {
            if (!url) return;
            visitaPending.modo = 'ticket';
            visitaPending.soloVistaTicket = true;
            visitaPending.enviandoAusente = false;
            visitaPending.deshaciendo = false;
            visitaPending.registrandoAbono = false;
            visitaPending.gpsOk = false;
            visitaPending.punto = '';
            visitaPending.ticketUrl = url;
            visitaPending.visitaId = visitaId || visitaIdDesdeTicketUrl(url);
            visitaPending.domIndex = typeof idx === 'number' && !isNaN(idx) ? idx : null;
            setVisitaDistanciaAlert('');
            setVisitaWizardError('');
            destroyVisitaGpsMap();
            var frame = document.getElementById('visitaTicketFrame');
            if (frame) {
                bindTicketFrameResize(frame);
                frame.src = url;
            }
            showVisitaWizardStep(2);
            var modalEl = document.getElementById('visitaConfirmModal');
            if (modalEl && modalEl.parentElement !== document.body) {
                document.body.appendChild(modalEl);
            }
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }

        function deshacerVisitaActual() {
            if (!visitaPending.visitaId || visitaPending.deshaciendo) return;
            if (!window.confirm('¿Deshacer esta visita? La parada volverá a pendiente y se anularán los pagos asociados.')) {
                return;
            }
            visitaPending.deshaciendo = true;
            syncVisitaWizardButtons();
            setVisitaWizardError('');
            var csrf = document.querySelector('meta[name="csrf-token"]');
            fetch('{{ url("visitas") }}/' + visitaPending.visitaId + '/deshacer', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf ? csrf.getAttribute('content') : '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({})
            })
            .then(function(r) { return r.json().then(function(data) { return { ok: r.ok, data: data }; }); })
            .then(function(res) {
                if (!res.ok) {
                    throw new Error((res.data && (res.data.message || (res.data.errors && Object.values(res.data.errors)[0][0]))) || 'No se pudo deshacer la visita.');
                }
                var stayIdx = visitaPending.domIndex;
                visitaPending.soloVistaTicket = true;
                visitaPending.ticketUrl = '';
                visitaPending.visitaId = null;
                var modal = visitaModalEl ? bootstrap.Modal.getInstance(visitaModalEl) : null;
                if (modal) modal.hide();
                if (currentRutaId) {
                    openRutaDetalle(currentRutaId, stayIdx != null ? stayIdx : undefined);
                }
            })
            .catch(function(err) {
                setVisitaWizardError(err.message || 'Error al deshacer la visita.');
            })
            .finally(function() {
                visitaPending.deshaciendo = false;
                syncVisitaWizardButtons();
            });
        }

        function onParadaAccionClick(e) {
            var ausenteBtn = e.target.closest('.shlom-card-btn-ausente');
            if (ausenteBtn) {
                e.preventDefault();
                e.stopPropagation();
                var ausenteCard = ausenteBtn.closest('.shlom-slider-card');
                var ausenteIdx = ausenteCard
                    ? parseInt(ausenteCard.dataset.index, 10)
                    : parseInt(ausenteBtn.getAttribute('data-dom-index'), 10);
                openVisitaAusente(ausenteIdx);
                return;
            }
            var ticketBtn = e.target.closest('.shlom-card-btn-ticket');
            if (ticketBtn) {
                e.preventDefault();
                e.stopPropagation();
                var ticketCard = ticketBtn.closest('.shlom-slider-card');
                var ticketIdx = ticketCard
                    ? parseInt(ticketCard.dataset.index, 10)
                    : (typeof sliderIndex === 'number' ? sliderIndex : parseInt(ticketBtn.getAttribute('data-dom-index'), 10));
                var vIdAttr = ticketBtn.getAttribute('data-visita-id');
                var vId = vIdAttr ? parseInt(vIdAttr, 10) : null;
                openVisitaTicketPreview(ticketBtn.getAttribute('data-ticket-url'), ticketIdx, vId);
                return;
            }
            var btn = e.target.closest('.shlom-card-btn-visit');
            if (!btn || btn.disabled || btn.classList.contains('shlom-card-btn-ticket')) return;
            e.preventDefault();
            e.stopPropagation();
            var card = btn.closest('.shlom-slider-card');
            var idx = card ? parseInt(card.dataset.index, 10) : parseInt(btn.getAttribute('data-dom-index'), 10);
            openVisitaConfirm(idx);
        }
        if (sliderEl) {
            sliderEl.addEventListener('click', onParadaAccionClick);
        }
        var paradaRegistroDetalleEl = document.getElementById('paradaRegistroDetalle');
        if (paradaRegistroDetalleEl) {
            paradaRegistroDetalleEl.addEventListener('click', onParadaAccionClick);
        }

        var visitaModalEl = document.getElementById('visitaConfirmModal');
        if (visitaModalEl && visitaModalEl.parentElement !== document.body) {
            document.body.appendChild(visitaModalEl);
        }
        if (visitaModalEl) {
            visitaModalEl.addEventListener('shown.bs.modal', function() {
                if (visitaPending.soloVistaTicket || visitaPending.step === 2 || visitaPending.modo === 'ticket') {
                    setTimeout(ajustarAlturaTicketFrame, 40);
                    return;
                }
                if (visitaGpsMap) {
                    setTimeout(function() { visitaGpsMap.invalidateSize(); }, 50);
                } else if (!visitaPending.punto) {
                    pedirUbicacionVisita(false);
                }
            });
            visitaModalEl.addEventListener('hidden.bs.modal', function() {
                var debeAvanzar = !!visitaPending.ticketUrl && !visitaPending.soloVistaTicket;
                var nextIdx = (visitaPending.domIndex || 0) + 1;
                destroyVisitaGpsMap();
                visitaPending.punto = '';
                visitaPending.gpsOk = false;
                visitaPending.paradaLat = null;
                visitaPending.paradaLng = null;
                visitaPending.ticketUrl = '';
                visitaPending.visitaId = null;
                visitaPending.modo = 'abono';
                visitaPending.enviandoAusente = false;
                visitaPending.soloVistaTicket = false;
                visitaPending.deshaciendo = false;
                visitaPending.registrandoAbono = false;
                var frame = document.getElementById('visitaTicketFrame');
                if (frame) {
                    frame.src = 'about:blank';
                    frame.style.height = '';
                }
                var ticketScroll = document.getElementById('visitaTicketPreviewScroll');
                if (ticketScroll) ticketScroll.scrollTop = 0;
                var card = document.querySelector('#visitaConfirmModal .visita-wizard-card');
                if (card) card.classList.remove('is-ticket-step');
                var folios = document.getElementById('visitaFoliosPago');
                if (folios) folios.classList.remove('d-none');
                setVisitaDistanciaAlert('');
                setVisitaWizardError('');
                setVisitaGpsStatus('info', '<span class="spinner-border spinner-border-sm me-2"></span>Obteniendo tu ubicación...', false);
                document.querySelectorAll('.modal-backdrop').forEach(function(el) { el.remove(); });
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('padding-right');
                if (debeAvanzar && currentRutaId) openRutaDetalle(currentRutaId, nextIdx);
            });
        }

        var omitPending = { paradaIds: [], domIndex: null };

        function openOmitirConfirm(idx) {
            if (!paradaDesbloqueada(idx)) return;
            var dom = currentDomicilios[idx];
            if (!dom) return;
            var pendientes = (dom.paradas || []).filter(function(p) { return p.estado === 'pendiente'; });
            if (!pendientes.length) return;
            omitPending.paradaIds = pendientes.map(function(p) { return p.id; });
            omitPending.domIndex = idx;
            var haySiguiente = idx < currentDomicilios.length - 1;
            var texto = document.getElementById('omitirConfirmTexto');
            if (texto) {
                texto.textContent = haySiguiente
                    ? 'Esta parada se marcará como omitida.'
                    : 'Esta parada se marcará como omitida. Es la última de la ruta.';
            }
            var contratosLabel = pendientes.map(function(p) {
                return 'C#' + (p.contrato_id || p.id);
            }).join(', ');
            var setTxt = function(id, value) {
                var n = document.getElementById(id);
                if (n) n.textContent = value || '—';
            };
            setTxt('omitirVinculoContrato', contratosLabel);
            setTxt('omitirVinculoNombre', dom.cliente_nombre);
            setTxt('omitirVinculoDomicilio', dom.domicilio);
            var err = document.getElementById('omitirConfirmError');
            if (err) {
                err.classList.add('d-none');
                err.textContent = '';
            }
            var modalEl = document.getElementById('omitirConfirmModal');
            if (modalEl && modalEl.parentElement !== document.body) {
                document.body.appendChild(modalEl);
            }
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }

        var omitirModalEl = document.getElementById('omitirConfirmModal');
        if (omitirModalEl && omitirModalEl.parentElement !== document.body) {
            document.body.appendChild(omitirModalEl);
        }
        if (omitirModalEl) {
            omitirModalEl.addEventListener('hidden.bs.modal', function() {
                document.querySelectorAll('.modal-backdrop').forEach(function(el) { el.remove(); });
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('padding-right');
            });
        }

        var btnConfirmarOmitir = document.getElementById('btnConfirmarOmitir');
        if (btnConfirmarOmitir) {
            btnConfirmarOmitir.addEventListener('click', function() {
                if (!omitPending.paradaIds.length || !currentRutaId) return;
                var btn = this;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Omitiendo...';
                var csrf = document.querySelector('meta[name="csrf-token"]');
                fetch('/rutas/' + currentRutaId + '/paradas/omitir', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf ? csrf.getAttribute('content') : '',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ ruta_parada_ids: omitPending.paradaIds })
                })
                .then(function(r) { return r.json().then(function(data) { return { ok: r.ok, data: data }; }); })
                .then(function(res) {
                    if (!res.ok) {
                        throw new Error((res.data && (res.data.message || (res.data.errors && Object.values(res.data.errors)[0][0]))) || 'No se pudo omitir la parada.');
                    }
                    var modal = bootstrap.Modal.getInstance(omitirModalEl);
                    if (modal) modal.hide();
                    openRutaDetalle(currentRutaId, (omitPending.domIndex || 0) + 1);
                })
                .catch(function(err) {
                    var el = document.getElementById('omitirConfirmError');
                    if (el) {
                        el.classList.remove('d-none');
                        el.textContent = err.message || 'Error al omitir la parada.';
                    }
                })
                .finally(function() {
                    btn.disabled = false;
                    btn.innerHTML = 'Omitir y continuar';
                });
            });
        }

        var btnVisitaGpsRecenter = document.getElementById('btnVisitaGpsRecenter');
        if (btnVisitaGpsRecenter) {
            btnVisitaGpsRecenter.addEventListener('click', function() {
                recentrarGpsVisita();
            });
        }
        var btnVisitaWizardNext = document.getElementById('btnVisitaWizardNext');
        if (btnVisitaWizardNext) {
            btnVisitaWizardNext.addEventListener('click', function() {
                setVisitaWizardError('');
                if (visitaPending.step !== 1 || !visitaPending.gpsOk || visitaPending.registrandoAbono) return;
                submitVisitaWizard();
            });
        }
        var btnVisitaWizardPrint = document.getElementById('btnVisitaWizardPrint');
        if (btnVisitaWizardPrint) {
            btnVisitaWizardPrint.addEventListener('click', function() {
                var frame = document.getElementById('visitaTicketFrame');
                try {
                    if (frame && frame.contentWindow) {
                        frame.contentWindow.focus();
                        frame.contentWindow.print();
                        return;
                    }
                } catch (e) {}
                if (visitaPending.ticketUrl) window.open(visitaPending.ticketUrl, '_blank');
            });
        }
        var btnVisitaWizardUndo = document.getElementById('btnVisitaWizardUndo');
        if (btnVisitaWizardUndo) {
            btnVisitaWizardUndo.addEventListener('click', function() {
                deshacerVisitaActual();
            });
        }
        var btnVisitaWizardClose = document.getElementById('btnVisitaWizardClose');
        if (btnVisitaWizardClose) {
            btnVisitaWizardClose.addEventListener('click', cerrarVisitaWizardYAvanzar);
        }

    </script>
    
</div>
@endsection
