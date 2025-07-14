@extends('layouts.app')

@section('template_title')
    {{ __('Update') }} Comisione
@endsection

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-pencil-square me-2"></i>
                        {{ __('Modificar una') }} Comision
                    </h4>
                </div>
                <div class="card-body bg-light">
                    <form method="POST" action="{{ route('comisiones.update', $comisione->id) }}" role="form" enctype="multipart/form-data">
                        {{ method_field('PATCH') }}
                        @csrf

                        @include('comisione.form')

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
