@extends('layouts.app')

@section('template_title')
    {{ __('Create') }} Empleado
@endsection

@section('content')
<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-primary text-white border-bottom-0 text-center">
                    <h4 class="mb-0 fw-bold">
                        <i class="bi bi-person-plus me-2"></i>{{ __('Create') }} Empleado
                    </h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('empleados.store') }}" role="form" enctype="multipart/form-data">
                        @csrf

                        <div class="p-3 rounded-3 bg-light">
                            @include('empleado.form')
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
