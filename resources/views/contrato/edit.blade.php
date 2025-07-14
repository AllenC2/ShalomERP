@extends('layouts.app')

@section('template_title')
    {{ __('Actualizar Contrato') }}
@endsection

@section('content')
<section class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white d-flex align-items-center">
                    <i class="fas fa-file-contract me-2"></i>
                    <span class="fs-5">{{ __('Modificar Contrato') }}</span>
                </div>
                <div class="card-body bg-light">
                    <form method="POST" action="{{ route('contratos.update', $contrato->id) }}" enctype="multipart/form-data" autocomplete="off">
                        @method('PATCH')
                        @csrf

                        @include('contrato.form')
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
