@extends('layouts.app')

@section('template_title')
    {{ __('Update') }} Empleado
@endsection

@section('content')
<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-primary text-white border-bottom-0 text-center">
                    <h4 class="mb-0 fw-bold">
                        <i class="bi bi-person-badge me-2"></i>
                        {{ __('Actualizar Ficha de Empleado') }}
                    </h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('empleados.update', $empleado->id) }}" enctype="multipart/form-data" autocomplete="off">
                        {{ method_field('PATCH') }}
                        @csrf

                        @include('empleado.form')

                        
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
