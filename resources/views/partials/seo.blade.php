@php
    $metaTitle = $title ?? 'Shalom ERP | Sistema Integral de Gestión Empresarial';
    $metaDescription = $description ?? 'Shalom ERP es la plataforma profesional y segura para la administración centralizada de clientes, contratos, cobranza, comisiones y reportes financieros.';
    $metaKeywords = $keywords ?? 'shalom erp, shalom erp gestion, administracion de contratos, control de comisiones, software erp administrativo, gestion de clientes, cobranza automatica, imallen dev';
    $metaImage = $og_image ?? asset('og-image.png');
    $metaCanonical = $canonical ?? request()->url();
    $metaRobots = $robots ?? 'index, follow';
@endphp

<!-- Primary Meta Tags -->
<title>{{ $metaTitle }}</title>
<meta name="title" content="{{ $metaTitle }}">
<meta name="description" content="{{ $metaDescription }}">
<meta name="keywords" content="{{ $metaKeywords }}">
<meta name="author" content="imallen.dev">
<meta name="robots" content="{{ $metaRobots }}">
<link rel="canonical" href="{{ $metaCanonical }}">

<!-- Open Graph / Facebook / LinkedIn / WhatsApp -->
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $metaCanonical }}">
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:image" content="{{ $metaImage }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:site_name" content="Shalom ERP">

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="{{ $metaCanonical }}">
<meta property="twitter:title" content="{{ $metaTitle }}">
<meta property="twitter:description" content="{{ $metaDescription }}">
<meta property="twitter:image" content="{{ $metaImage }}">

<!-- Apple Mobile Web App Meta -->
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Shalom ERP">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

<!-- Progressive Web App Manifest -->
<link rel="manifest" href="{{ asset('site.webmanifest') }}">
<meta name="theme-color" content="#79481D">

<!-- Favicons Suite -->
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
<link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
<link rel="icon" type="image/svg+xml" href="{{ asset('shalom_ico.svg') }}">
