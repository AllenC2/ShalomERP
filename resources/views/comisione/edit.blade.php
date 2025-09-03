@extends('layouts.app')

@section('template_title')
    {{ __('Update') }} Comisione
@endsection

@section('content')
<!-- Header moderno -->
<div class="page-header" style="max-width: 1200px; margin: auto; margin-bottom: 40px;">
    <div class="header-content" style="">
        <div class="header-icon">
            <i class="bi bi-percent"></i>
        </div>
        <div class="header-text">
            <h1 class="page-title">{{ __('Editar Comisión') }}</h1>
            <p class="page-subtitle">Modifique la información de la comisión seleccionada</p>
        </div>
    </div>
</div>

<div class="container-fluid">
    <form method="POST" action="{{ route('comisiones.update', $comisione->id) }}" role="form" enctype="multipart/form-data">
        {{ method_field('PATCH') }}
        @csrf

        @include('comisione.form')

    </form>
</div>
@endsection
