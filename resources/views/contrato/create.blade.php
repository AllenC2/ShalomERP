@extends('layouts.app')

@section('template_title')
    {{ __('Crear Contrato') }}
@endsection

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white d-flex align-items-center">
                    <i class="bi bi-file-earmark-plus me-2"></i>
                    <span class="fs-5">{{ __('Crear Contrato') }}</span>
                </div>
                <div class="card-body bg-light">
                    <form method="POST" action="{{ route('contratos.store') }}" enctype="multipart/form-data" autocomplete="off">
                        @csrf

                        @include('contrato.form')
                
                        
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
