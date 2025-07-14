@extends('layouts.app')

@section('template_title')
    {{ __('Update') }} Pago
@endsection

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">

              

                    <form method="POST" action="{{ route('pagos.update', $pago->id) }}" role="form" enctype="multipart/form-data">
                        {{ method_field('PATCH') }}
                        @csrf

                        @include('pago.form')

                    
                    </form>


        </div>
    </div>
</div>
@endsection
