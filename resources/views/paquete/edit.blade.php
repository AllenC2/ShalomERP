@extends('layouts.app')

@section('template_title')
    {{ __('Update') }} Paquete
@endsection

@section('content')
<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white border-bottom-0 text-center">
                    <h4 class="mb-0">{{ __('Actualizar') }} Paquete</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('paquetes.update', $paquete->id) }}" enctype="multipart/form-data">
                        {{ method_field('PATCH') }}
                        @csrf

                        @include('paquete.form')

                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
