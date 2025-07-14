@extends('layouts.app')

@section('template_title')
    {{ __('Create') }} Comisione
@endsection

@section('content')
<section class="content container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-plus-circle"></i>
                        {{ __('Formulario para Creacion') }} de Comision
                    </h5>
                </div>
                <div class="card-body bg-light">
                    <form method="POST" action="{{ route('comisiones.store') }}" role="form" enctype="multipart/form-data">
                        @csrf

                        @include('comisione.form')

                        
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
