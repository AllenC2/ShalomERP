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
#omitirConfirmModal { z-index: 1065; }

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
.ios-confirm-map {
    height: 180px; border-radius: 14px; overflow: hidden;
    background: #e9ecef; margin-bottom: 10px;
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
.shlom-route-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 0 16px 40px;
}
.shlom-plantilla-card {
    background: white; border-radius: 16px; padding: 14px 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    display: flex; align-items: center; gap: 12px;
}
.shlom-plantilla-icon {
    width: 40px; height: 40px; border-radius: 12px; flex-shrink: 0;
    background: rgba(121,72,29,0.1); color: #79481D;
    display: flex; align-items: center; justify-content: center; font-size: 1.05rem;
}
.shlom-plantilla-body { min-width: 0; flex: 1; }
.shlom-plantilla-name {
    font-size: 0.95rem; font-weight: 600; color: #1c1c1e;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.shlom-plantilla-meta { font-size: 0.75rem; color: #8e8e93; margin-top: 2px; }
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
.shlom-detail-info { flex-grow: 1; display: flex; flex-direction: column; }
.shlom-detail-title { font-size: 1.05rem; font-weight: 700; color: #1c1c1e; line-height: 1.2; }
.shlom-detail-date { font-size: 0.78rem; color: #8E8E93; }
.shlom-detail-badge {
    padding: 4px 10px; border-radius: 20px; font-size: 0.72rem; font-weight: 600;
}

/* ====== MAP (full screen behind sheet) ====== */
.shlom-map-full { position: fixed; top: 57px; left: 0; right: 0; bottom: 0; z-index: 1; }
.shlom-map { width: 100%; height: 100%; background: #e9ecef; }

/* ====== BOTTOM SHEET ====== */
.shlom-bottom-sheet {
    position: fixed; left: 0; right: 0; bottom: 0; z-index: 1000;
    background: white; border-radius: 20px 20px 0 0;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
    height: 85vh;
    will-change: transform;
    -webkit-user-select: none; user-select: none;
    transition: transform 0.35s cubic-bezier(0.32, 0.72, 0, 1);
    transform: translateY(calc(100% - 268px));
}
.shlom-bottom-sheet.expanded {
    transform: translateY(0);
}
.shlom-sheet-handle {
    display: flex; justify-content: center; padding: 12px 0 8px; cursor: grab;
    -webkit-tap-highlight-color: transparent;
}
.shlom-sheet-handle:active { cursor: grabbing; }
.shlom-handle-bar {
    width: 40px; height: 5px; border-radius: 3px; background: rgba(60,60,67,0.25);
}
.shlom-sheet-content {
    padding: 0 0 16px; overflow-y: auto; max-height: calc(85vh - 30px);
}
.shlom-seg-label {
    font-size: 0.65rem; color: #8e8e93; font-weight: 600;
    text-transform: uppercase; letter-spacing: 0.05em;
    padding: 0 20px 6px;
    font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Text', system-ui, sans-serif;
}
.shlom-seg {
    display: flex; background: rgba(118,118,128,0.12); border-radius: 9px;
    padding: 2px; margin: 0 20px 8px;
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
.shlom-punto-casa-block.d-none { display: none !important; }
.shlom-ruta-resumen {
    margin: 0 16px 12px;
    padding: 14px 16px 10px;
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
.shlom-bottom-sheet.historica {
    transform: translateY(calc(100% - 360px));
}

/* ====== SLIDER (inside sheet) ====== */
.shlom-slider-track {
    display: flex; gap: 12px; overflow-x: auto; scroll-snap-type: x mandatory;
    padding: 8px 20px 4px; scrollbar-width: none; -ms-overflow-style: none;
}
.shlom-slider-track::-webkit-scrollbar { display: none; }

.shlom-slider-card {
    flex: 0 0 88%; max-width: 380px; scroll-snap-align: center;
    background: #f2f2f7; border-radius: 16px; padding: 16px;
    border: none; min-width: 0;
    font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'SF Pro Text', system-ui, sans-serif;
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
.shlom-card-btn-skip { background: rgba(255,59,48,0.1); color: #FF3B30; }

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
.shlom-slider-controls { display: flex; justify-content: center; padding: 6px 0 2px; }
.shlom-slider-dots { display: flex; gap: 6px; align-items: center; }
.shlom-dot {
    width: 7px; height: 7px; border-radius: 50%; background: rgba(60,60,67,0.2);
    transition: all 0.25s ease; cursor: pointer;
}
.shlom-dot.active { width: 22px; border-radius: 4px; background: #1c1c1e; }

.current-pos-marker { background: transparent !important; border: none !important; }

/* ====== RESPONSIVE: TABLET/DESKTOP ====== */
@media (min-width: 992px) {
    .shlom-header { padding: 20px 20px 12px; }
    .shlom-title { font-size: 2.5rem; }
    .shlom-route-list { padding: 12px 20px 100px; }
    .shlom-route-card,
    .shlom-plantilla-card { max-width: 600px; margin: 0 auto; width: 100%; }
    .shlom-detail-topbar { max-width: 100%; padding: 12px 20px; }
    .shlom-back-btn { display: none; }
    .shlom-detail-info { text-align: center; align-items: center; }
    .shlom-slider-card { max-width: 380px; }
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
    <div class="mb-4 text-start">
        <h3 class="display-2 fw-bold" style="line-height: 0.8; opacity: 0.6; letter-spacing: -2px;">¡{{ $greeting }}, 
            <br>
        {{ Auth::user()->name }}!</h3>
    </div>
    
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
                    <p class="shlom-subtitle">Tu trabajo del día</p>
                </div>
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
        <div class="shlom-route-list" id="listaRutasDia" style="padding-bottom: 16px;">
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
                            'cancelada' => ['color' => '#FF3B30', 'bg' => 'rgba(255,59,48,0.1)', 'label' => 'Cancelada', 'icon' => 'bi-slash-circle'],
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
                    <p>Cambia de día o revisa tus rutas asignadas abajo.</p>
                </div>
            @endforelse
        </div>

        <div class="shlom-section-label">Rutas asignadas</div>
        <div class="shlom-route-list" id="listaPlantillas" style="padding-top: 0;">
            @forelse($empleadoPlantillas as $plantilla)
                <div class="shlom-plantilla-card">
                    <div class="shlom-plantilla-icon">
                        <i class="fa-solid fa-route"></i>
                    </div>
                    <div class="shlom-plantilla-body">
                        <div class="shlom-plantilla-name">{{ $plantilla->nombre ?: ('Ruta #' . $plantilla->id) }}</div>
                        <div class="shlom-plantilla-meta">
                            {{ $plantilla->etiquetaFrecuencia() }}
                            · {{ $plantilla->paradas_count }} parada{{ $plantilla->paradas_count === 1 ? '' : 's' }}
                        </div>
                    </div>
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
                <span class="shlom-detail-date" id="detailRutaDate"></span>
            </div>
            <span class="shlom-detail-badge" id="detailRutaEstado"></span>
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
                <div id="rutaResumenHistorico" class="shlom-ruta-resumen d-none">
                    <div class="shlom-resumen-kicker">Resumen</div>
                    <div class="shlom-resumen-title" id="rutaResumenHeadline">—</div>
                    <p class="shlom-resumen-text" id="rutaResumenDetalle"></p>
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
                <div id="puntoCasaBlock" class="shlom-punto-casa-block">
                    <div class="shlom-seg-label">Tu domicilio como</div>
                    <div class="shlom-seg" id="puntoCasaTabs" role="tablist">
                        <button type="button" class="shlom-seg-tab active" data-value="inicio">Partida</button>
                        <button type="button" class="shlom-seg-tab" data-value="final">Final</button>
                        <button type="button" class="shlom-seg-tab" data-value="ambos">Ambos</button>
                    </div>
                </div>
                <div class="shlom-slider-track" id="paradasSlider"></div>
                <div class="shlom-slider-controls">
                    <div class="shlom-slider-dots" id="sliderDots"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="visitaConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered ios-confirm-dialog">
            <div class="modal-content ios-confirm-card">
                <div class="ios-confirm-icon ios-confirm-icon-visit">
                    <i class="bi bi-geo-alt-fill"></i>
                </div>
                <h2 class="ios-confirm-title">Confirmar visita</h2>
                <p class="ios-confirm-msg" id="visitaConfirmTexto">Se registrará una visita hecha en este domicilio.</p>
                <ul class="ios-grouped">
                    <li>
                        <span>Contrato</span>
                        <strong id="visitaVinculoContrato">—</strong>
                    </li>
                    <li>
                        <span>Nombre</span>
                        <strong id="visitaVinculoNombre">—</strong>
                    </li>
                    <li>
                        <span>Domicilio</span>
                        <strong id="visitaVinculoDomicilio">—</strong>
                    </li>
                </ul>
                <div id="visitaGpsMap" class="ios-confirm-map"></div>
                <div id="visitaGpsStatus" class="ios-banner ios-banner-info">
                    <span class="spinner-border spinner-border-sm me-2"></span>Obteniendo tu ubicación...
                </div>
                <div id="visitaDistanciaAlert" class="ios-banner ios-banner-warning d-none"></div>
                <div class="ios-confirm-actions">
                    <button type="button" class="ios-btn ios-btn-primary" id="btnConfirmarVisita" disabled>Registrar visita</button>
                    <button type="button" class="ios-btn ios-btn-plain" data-bs-dismiss="modal">Cancelar</button>
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
                <p class="ios-confirm-msg" id="omitirConfirmTexto">Esta parada se omitirá y se desbloqueará la siguiente.</p>
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
        var touchStartX = 0;
        var touchEndX = 0;

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
                lista.innerHTML = '<div class="shlom-empty-state"><div class="shlom-empty-icon"><i class="fa-solid fa-route"></i></div><h3>Sin rutas este día</h3><p>Cambia de día o revisa tus rutas asignadas abajo.</p></div>';
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

        // Volver a la lista
        var btnBackToList = document.getElementById('btnBackToList');
        if (btnBackToList) {
            btnBackToList.addEventListener('click', function() {
            document.getElementById('screenRutaList').classList.add('active');
            document.getElementById('screenRutaDetalle').classList.remove('active');
        });
        }

        // ==================== BOTTOM SHEET ====================
        (function() {
            var sheet = document.getElementById('bottomSheet');
            var handle = document.getElementById('sheetHandle');
            if (!sheet || !handle) return;

            var startY = 0;
            var startX = 0;
            var isDragging = false;
            var directionLocked = null;
            var THRESHOLD = 60;

            handle.addEventListener('touchstart', function(e) {
                if (e.touches.length !== 1) return;
                isDragging = true;
                directionLocked = null;
                startY = e.touches[0].clientY;
                startX = e.touches[0].clientX;
                sheet.style.transition = 'none';
            }, { passive: true });

            document.addEventListener('touchmove', function(e) {
                if (!isDragging) return;

                var dy = e.touches[0].clientY - startY;
                var dx = e.touches[0].clientX - startX;

                // Lock direction on first significant move
                if (directionLocked === null) {
                    if (Math.abs(dy) > 5 || Math.abs(dx) > 5) {
                        directionLocked = Math.abs(dy) > Math.abs(dx) ? 'v' : 'h';
                    }
                    return;
                }

                // If horizontal, let browser handle (don't interfere)
                if (directionLocked === 'h') return;

                // Vertical drag — prevent scroll
                e.preventDefault();

                var expanded = sheet.classList.contains('expanded');
                var baseY = expanded ? 0 : (window.innerHeight - 220);
                var newY = baseY + dy;
                var minY = 0;
                var maxY = window.innerHeight - 220;
                newY = Math.max(minY, Math.min(newY, maxY));
                sheet.style.transform = 'translateY(' + newY + 'px)';
            }, { passive: false });

            document.addEventListener('touchend', function(e) {
                if (!isDragging) return;
                isDragging = false;

                sheet.style.transition = 'transform 0.35s cubic-bezier(0.32, 0.72, 0, 1)';

                if (directionLocked !== 'v') {
                    // Was a tap or horizontal — just snap back
                    sheet.style.transform = '';
                    return;
                }

                var dy = e.changedTouches[0].clientY - startY;
                if (dy < -THRESHOLD) {
                    sheet.classList.add('expanded');
                } else if (dy > THRESHOLD) {
                    sheet.classList.remove('expanded');
                }
                sheet.style.transform = '';
            });

            // Click toggle for desktop
            handle.addEventListener('click', function(e) {
                // Only toggle if it wasn't a drag
                sheet.classList.toggle('expanded');
            });

            // Reset when opening a route
            window.resetBottomSheet = function(historica) {
                sheet.classList.remove('expanded');
                sheet.classList.toggle('historica', !!historica);
                sheet.style.transition = 'none';
                sheet.style.transform = '';
                sheet.offsetHeight; // force reflow
                sheet.style.transition = '';
            };

            // Invalidate map after animation
            sheet.addEventListener('transitionend', function() {
                if (typeof homeMap !== 'undefined' && homeMap) {
                    setTimeout(function() { homeMap.invalidateSize(); }, 50);
                }
            });
        })();

        function domicilioCerrada(dom) {
            return (dom.paradas || []).every(function(p) {
                return p.estado === 'visitada' || p.estado === 'omitida';
            });
        }

        function paradaDesbloqueada(idx, domicilios) {
            var list = domicilios || currentDomicilios;
            if (idx <= 0) return true;
            return !!(list[idx - 1] && domicilioCerrada(list[idx - 1]));
        }

        function pintarResumenHistorico(ruta) {
            var box = document.getElementById('rutaResumenHistorico');
            var puntoCasa = document.getElementById('puntoCasaBlock');
            if (!box) return;
            var pasada = !!ruta.es_pasada;
            box.classList.toggle('d-none', !pasada);
            if (puntoCasa) puntoCasa.classList.toggle('d-none', pasada);
            if (!pasada) return;
            var r = ruta.resumen || {};
            var headline = document.getElementById('rutaResumenHeadline');
            var detalle = document.getElementById('rutaResumenDetalle');
            if (headline) headline.textContent = r.headline || 'Resumen';
            if (detalle) detalle.textContent = r.detalle || '';
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
            setPuntoCasaUI(ruta.punto_casa || 'inicio', !esPasada && !!(ruta.empleado_lat && ruta.empleado_lng));
            sliderIndex = 0;

            // Transición de pantallas
            document.getElementById('screenRutaList').classList.remove('active');
            document.getElementById('screenRutaDetalle').classList.add('active');

            // Reset bottom sheet to collapsed
            if (window.resetBottomSheet) window.resetBottomSheet(esPasada);

            pintarResumenHistorico(ruta);

            // Topbar info
            document.getElementById('detailRutaTitle').textContent = ruta.nombre || ('Ruta #' + ruta.id);
            var fechaStr = ruta.fecha.split('-').reverse().join('/');
            if (ruta.frecuencia) fechaStr += ' · ' + ruta.frecuencia;
            document.getElementById('detailRutaDate').textContent = fechaStr;

            // Estado badge
            var estadoConfig = {
                'planeada': { color: '#007AFF', label: 'Planeada' },
                'en_curso': { color: '#FF9500', label: 'En Curso' },
                'cancelada': { color: '#FF3B30', label: 'Cancelada' },
                'completada': { color: '#34C759', label: 'Completada' },
                'incompleta': { color: '#8E8E93', label: 'Incompleta' },
                'vencida': { color: '#1C1C1E', label: 'Vencida' }
            };
            var ec = estadoConfig[ruta.estado] || { color: '#8E8E93', label: ruta.estado };
            var badge = document.getElementById('detailRutaEstado');
            badge.textContent = ec.label;
            badge.style.background = ec.color + '20';
            badge.style.color = ec.color;

            // Render slider
            renderSlider(currentDomicilios, focusIdx);

            // Init map
            setTimeout(function() {
                initHomeMap();
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

        function etiquetaPuntoCasa(modo) {
            if (modo === 'final') return 'Punto final';
            if (modo === 'ambos') return 'Inicio y fin';
            return 'Punto de partida';
        }

        function ordenarDomiciliosPorCasa(domicilios, ruta) {
            var modo = (ruta && ruta.punto_casa) || 'inicio';
            var lat = ruta && parseFloat(ruta.empleado_lat);
            var lng = ruta && parseFloat(ruta.empleado_lng);
            if (!lat || !lng) return domicilios.slice();
            var nn = ordenarPorCercania(domicilios.slice(), lat, lng);
            if (modo === 'final') nn.reverse();
            return nn;
        }

        function setPuntoCasaUI(modo, enabled) {
            var tabs = document.querySelectorAll('#puntoCasaTabs .shlom-seg-tab');
            tabs.forEach(function(tab) {
                var on = tab.getAttribute('data-value') === modo;
                tab.classList.toggle('active', on);
                tab.setAttribute('aria-selected', on ? 'true' : 'false');
                tab.disabled = !enabled;
            });
        }

        function aplicarPuntoCasa(modo, persist) {
            if (!currentRutaMeta) return;
            currentRutaMeta.punto_casa = modo;
            currentDomicilios = ordenarDomiciliosPorCasa(groupParadasByDomicilio(currentParadas), currentRutaMeta);
            setPuntoCasaUI(modo, !currentRutaMeta.es_pasada && !!(currentRutaMeta.empleado_lat && currentRutaMeta.empleado_lng));
            renderSlider(currentDomicilios);
            if (homeMap) {
                clearHomeMap();
                renderHomeParadas(currentRutaMeta);
                centerOnDomicilio(sliderIndex);
                updateSliderControls();
            }
            if (persist && !currentRutaMeta.es_pasada) guardarPuntoCasa();
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
                if (dom.latitud && dom.longitud) {
                    pts.push([dom.latitud, dom.longitud]);
                    routePts.push([dom.latitud, dom.longitud]);
                    var totalP = dom.paradas.length;
                    var compP = dom.paradas.filter(function(p) { return p.estado === 'visitada'; }).length;
                    var omitP = dom.paradas.filter(function(p) { return p.estado === 'omitida'; }).length;
                    var color = compP === totalP ? '#34C759' : (omitP === totalP ? '#FF3B30' : '#79481D');
                    var sz = idx === sliderIndex ? 36 : 28;
                    var m = L.marker([dom.latitud, dom.longitud], {
                        icon: makeHomeIcon(color, sz, String(idx + 1)),
                        zIndexOffset: 500
                    });
                    m._domIndex = idx;
                    m.on('click', function() {
                        goToSlide(idx);
                    });
                    homeMarkerLayer.addLayer(m);
                    paradaMarkers.push(m);
                }
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

        function renderSlider(domicilios, focusIdx) {
            var container = document.getElementById('paradasSlider');
            var dots = document.getElementById('sliderDots');
            container.innerHTML = '';
            dots.innerHTML = '';

            if (typeof focusIdx === 'number' && focusIdx >= 0) {
                sliderIndex = Math.min(focusIdx, Math.max(domicilios.length - 1, 0));
            } else {
            sliderIndex = 0;
                for (var i = 0; i < domicilios.length; i++) {
                    if (paradaDesbloqueada(i, domicilios) && !domicilioCerrada(domicilios[i])) {
                        sliderIndex = i;
                        break;
                    }
                }
            }

            domicilios.forEach(function(dom, idx) {
                var totalP = dom.paradas.length;
                var compP = dom.paradas.filter(function(p) { return p.estado === 'visitada'; }).length;
                var omitP = dom.paradas.filter(function(p) { return p.estado === 'omitida'; }).length;
                var estadoDom = compP === totalP ? 'visitada' : (omitP === totalP ? 'omitida' : 'pendiente');
                var estadoConfig = {
                    'visitada': { color: '#34C759', bg: 'rgba(52,199,89,0.1)', label: 'Visitada', icon: 'bi-check-circle-fill' },
                    'omitida': { color: '#FF3B30', bg: 'rgba(255,59,48,0.1)', label: 'Omitida', icon: 'bi-x-circle-fill' },
                    'pendiente': { color: '#79481D', bg: 'rgba(121,72,29,0.08)', label: 'Pendiente', icon: 'bi-clock' }
                };
                var ec = estadoConfig[estadoDom] || estadoConfig['pendiente'];
                var multiBadge = totalP > 1 ? ' <span style="background:rgba(142,142,147,0.15);color:#636366;font-size:0.6rem;padding:2px 6px;border-radius:10px;margin-left:4px;">' + totalP + ' contratos</span>' : '';

                var subItems = '';
                dom.paradas.forEach(function(p) {
                    var subEstado = estadoConfig[p.estado] || estadoConfig['pendiente'];
                    subItems += '<div class="shlom-contract-row">';
                    subItems += '<div class="shlom-contract-dot" style="background:' + subEstado.color + ';"></div>';
                    subItems += '<span class="shlom-contract-id">C#' + p.id + '</span>';
                    subItems += '<span class="shlom-contract-amount">$' + (p.cuota || '0') + '</span>';
                    subItems += '</div>';
                });

                var pendientes = dom.paradas.filter(function(p) { return p.estado === 'pendiente'; }).length;
                var visitada = estadoDom === 'visitada';
                var historica = !!(currentRutaMeta && currentRutaMeta.es_pasada);
                var desbloqueada = historica || paradaDesbloqueada(idx, domicilios);
                var visitBtn = '';
                var skipBtn = '';
                var lockNote = '';
                if (historica) {
                    lockNote = '';
                } else if (!desbloqueada) {
                    lockNote = '<div class="shlom-card-lock-note"><i class="bi bi-lock-fill"></i> Visita u omite la parada anterior para desbloquear</div>';
                } else if (pendientes > 0) {
                    visitBtn = `<button type="button" class="shlom-card-btn shlom-card-btn-visit" data-dom-index="${idx}">
                                <i class="bi bi-geo-alt-fill"></i>
                                <span>Visita</span>
                            </button>`;
                    skipBtn = `<button type="button" class="shlom-card-btn shlom-card-btn-skip" data-dom-index="${idx}">
                                <i class="bi bi-skip-forward"></i>
                                <span>Omitir</span>
                            </button>`;
                } else if (visitada) {
                    visitBtn = `<button type="button" class="shlom-card-btn shlom-card-btn-visit" disabled style="opacity:.45;">
                                <i class="bi bi-check-circle"></i>
                                <span>Visitada</span>
                            </button>`;
                }

                var card = document.createElement('div');
                card.className = 'shlom-slider-card' + (desbloqueada ? '' : ' shlom-slider-card-locked');
                card.dataset.index = idx;
                var histBadge = historica
                    ? '<span class="shlom-route-status" style="background:' + ec.bg + ';color:' + ec.color + ';margin-left:auto;">' + ec.label + '</span>'
                    : '';
                card.innerHTML = `
                    <div class="shlom-card-content">
                        <div class="shlom-card-header-row">
                            <div class="shlom-card-num">${idx + 1}</div>
                            <div class="shlom-card-header-text">
                                <span class="shlom-card-client">${dom.cliente_nombre}</span>
                                ${multiBadge}
                            </div>
                            ${histBadge}
                        </div>
                        <div class="shlom-card-address">
                            <i class="bi bi-location"></i>
                            <span>${dom.domicilio}</span>
                        </div>
                        ${lockNote}
                        <div class="shlom-card-actions">
                            <button class="shlom-card-btn shlom-card-btn-maps" data-lat="${dom.latitud}" data-lng="${dom.longitud}" onclick="event.stopPropagation(); openMapsApp(${dom.latitud}, ${dom.longitud})">
                                <i class="bi bi-map"></i>
                                <span>Mapa</span>
                            </button>
                            ${visitBtn}
                            ${skipBtn}
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
        }

        function goToSlide(index) {
            sliderIndex = Math.max(0, Math.min(index, currentDomicilios.length - 1));
            updateSliderPosition();
            updateSliderControls();
            centerOnDomicilio(sliderIndex);
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
                var color = compP === totalP ? '#34C759' : (omitP === totalP ? '#FF3B30' : '#79481D');
                var sz = i === sliderIndex ? 36 : 28;
                m.setIcon(makeHomeIcon(color, sz, String(i + 1)));
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
                e.preventDefault();
                e.stopPropagation();
                var modo = tab.getAttribute('data-value');
                if (currentRutaMeta && currentRutaMeta.punto_casa === modo) return;
                aplicarPuntoCasa(modo, true);
            });
        }

        var sliderEl = document.getElementById('paradasSlider');
        if (sliderEl) {
        sliderEl.addEventListener('touchstart', function(e) {
            touchStartX = e.touches[0].clientX;
        });
        sliderEl.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].clientX;
            var diff = touchStartX - touchEndX;
            if (Math.abs(diff) > 50) {
                if (diff > 0 && sliderIndex < currentDomicilios.length - 1) goToSlide(sliderIndex + 1);
                else if (diff < 0 && sliderIndex > 0) goToSlide(sliderIndex - 1);
            }
        });
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
                    centerOnDomicilio(sliderIndex);
                }
            }, 150);
        });
        }

        var visitaGpsMap = null;
        var visitaGpsMarker = null;
        var visitaParadaMarker = null;
        var VISITA_DISTANCIA_MAX_M = 150;
        var visitaPending = { paradaIds: [], punto: '', paradaLat: null, paradaLng: null };

        function setVisitaGpsStatus(type, html, canConfirm) {
            var el = document.getElementById('visitaGpsStatus');
            if (!el) return;
            el.className = 'ios-banner ios-banner-' + type;
            el.innerHTML = html;
            var btn = document.getElementById('btnConfirmarVisita');
            if (btn) btn.disabled = !canConfirm;
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
            var coincide = pLat != null && pLng != null && !isNaN(pLat) && !isNaN(pLng)
                && distanciaMetros(lat, lng, pLat, pLng) <= VISITA_DISTANCIA_MAX_M;

            if (coincide) {
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
            } else if (visitaParadaMarker) {
                visitaGpsMap.removeLayer(visitaParadaMarker);
                visitaParadaMarker = null;
            }

            visitaGpsMap.setView([lat, lng], 17);

            setTimeout(function() {
                if (visitaGpsMap) {
                    visitaGpsMap.invalidateSize();
                    visitaGpsMap.setView([lat, lng], 17);
                }
            }, 80);
            setTimeout(function() {
                if (visitaGpsMap) {
                    visitaGpsMap.invalidateSize();
                    visitaGpsMap.setView([lat, lng], 17);
                }
            }, 350);
        }

        function aplicarGpsVisita(lat, lng) {
            visitaPending.punto = 'POINT(' + lng + ' ' + lat + ')';
            initVisitaGpsMap(lat, lng);
            setVisitaGpsStatus('success', '<i class="bi bi-check-circle me-1"></i>Esta es tu ubicación actual.', true);
            advertirSiNoCoincideConParada(lat, lng);
        }

        function advertirSiNoCoincideConParada(lat, lng) {
            var pLat = visitaPending.paradaLat;
            var pLng = visitaPending.paradaLng;
            if (pLat == null || pLng == null || isNaN(pLat) || isNaN(pLng)) {
                setVisitaDistanciaAlert('<i class="bi bi-exclamation-triangle me-1"></i>Esta parada no tiene coordenadas registradas; no se puede comprobar si coinciden.');
                return;
            }
            var metros = distanciaMetros(lat, lng, pLat, pLng);
            if (metros <= VISITA_DISTANCIA_MAX_M) {
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

        function openVisitaConfirm(idx) {
            if (!paradaDesbloqueada(idx)) return;
            var dom = currentDomicilios[idx];
            if (!dom) return;
            var pendientes = (dom.paradas || []).filter(function(p) { return p.estado === 'pendiente'; });
            if (!pendientes.length) return;
            visitaPending.paradaIds = pendientes.map(function(p) { return p.id; });
            visitaPending.domIndex = idx;
            visitaPending.punto = '';
            visitaPending.paradaLat = (dom.latitud === null || dom.latitud === undefined || dom.latitud === '') ? null : parseFloat(dom.latitud);
            visitaPending.paradaLng = (dom.longitud === null || dom.longitud === undefined || dom.longitud === '') ? null : parseFloat(dom.longitud);
            if (visitaPending.paradaLat != null && isNaN(visitaPending.paradaLat)) visitaPending.paradaLat = null;
            if (visitaPending.paradaLng != null && isNaN(visitaPending.paradaLng)) visitaPending.paradaLng = null;
            setVisitaDistanciaAlert('');
            var texto = document.getElementById('visitaConfirmTexto');
            if (texto) {
                texto.textContent = pendientes.length > 1
                    ? 'Se registrará una visita hecha vinculada a estos contratos y a esta parada.'
                    : 'Se registrará una visita hecha vinculada a este contrato y a esta parada.';
            }
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
            setVisitaGpsStatus('info', '<span class="spinner-border spinner-border-sm me-2"></span>Obteniendo tu ubicación...', false);
            destroyVisitaGpsMap();
            pedirUbicacionVisita(false);
            var modalEl = document.getElementById('visitaConfirmModal');
            if (modalEl && modalEl.parentElement !== document.body) {
                document.body.appendChild(modalEl);
            }
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }

        if (sliderEl) {
            sliderEl.addEventListener('click', function(e) {
                var skipBtn = e.target.closest('.shlom-card-btn-skip');
                if (skipBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    var skipCard = skipBtn.closest('.shlom-slider-card');
                    var skipIdx = skipCard ? parseInt(skipCard.dataset.index, 10) : parseInt(skipBtn.getAttribute('data-dom-index'), 10);
                    openOmitirConfirm(skipIdx);
                    return;
                }
                var btn = e.target.closest('.shlom-card-btn-visit');
                if (!btn || btn.disabled) return;
                e.preventDefault();
                e.stopPropagation();
                var card = btn.closest('.shlom-slider-card');
                var idx = card ? parseInt(card.dataset.index, 10) : parseInt(btn.getAttribute('data-dom-index'), 10);
                openVisitaConfirm(idx);
            });
        }

        var visitaModalEl = document.getElementById('visitaConfirmModal');
        if (visitaModalEl && visitaModalEl.parentElement !== document.body) {
            document.body.appendChild(visitaModalEl);
        }
        if (visitaModalEl) {
            visitaModalEl.addEventListener('shown.bs.modal', function() {
                if (visitaGpsMap) {
                    setTimeout(function() { visitaGpsMap.invalidateSize(); }, 50);
                } else if (!visitaPending.punto) {
                    pedirUbicacionVisita(false);
                }
            });
            visitaModalEl.addEventListener('hidden.bs.modal', function() {
                destroyVisitaGpsMap();
                visitaPending.punto = '';
                visitaPending.paradaLat = null;
                visitaPending.paradaLng = null;
                setVisitaDistanciaAlert('');
                setVisitaGpsStatus('info', '<span class="spinner-border spinner-border-sm me-2"></span>Obteniendo tu ubicación...', false);
                document.querySelectorAll('.modal-backdrop').forEach(function(el) { el.remove(); });
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('padding-right');
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
                    ? 'Esta parada se omitirá y se desbloqueará la siguiente.'
                    : 'Esta parada se omitirá. Es la última de la ruta.';
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

        var btnConfirmarVisita = document.getElementById('btnConfirmarVisita');
        if (btnConfirmarVisita) {
            btnConfirmarVisita.addEventListener('click', function() {
                if (!visitaPending.punto || !visitaPending.paradaIds.length) return;
                var btn = this;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Registrando...';
                var csrf = document.querySelector('meta[name="csrf-token"]');
                fetch('{{ route("visitas.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf ? csrf.getAttribute('content') : '',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        ruta_parada_ids: visitaPending.paradaIds,
                        ubicacion_evidencia: visitaPending.punto
                    })
                })
                .then(function(r) { return r.json().then(function(data) { return { ok: r.ok, data: data }; }); })
                .then(function(res) {
                    if (!res.ok) {
                        throw new Error((res.data && (res.data.message || (res.data.errors && Object.values(res.data.errors)[0][0]))) || 'No se pudo registrar la visita.');
                    }
                    var modal = bootstrap.Modal.getInstance(visitaModalEl);
                    if (modal) modal.hide();
                    if (currentRutaId) openRutaDetalle(currentRutaId, (visitaPending.domIndex || 0) + 1);
                })
                .catch(function(err) {
                    setVisitaGpsStatus('danger', '<i class="bi bi-exclamation-circle me-1"></i>' + (err.message || 'Error al registrar la visita.'), !!visitaPending.punto);
                })
                .finally(function() {
                    btn.innerHTML = 'Registrar visita';
                    if (visitaPending.punto) btn.disabled = false;
                });
            });
        }

    </script>
    
</div>
@endsection
