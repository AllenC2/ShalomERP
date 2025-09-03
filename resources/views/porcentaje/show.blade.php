@extends('layouts.app')

@section('template_title')
    {{ $porcentaje->name ?? __('Show') . " " . __('Porcentaje') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title">{{ __('Show') }} Porcentaje</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('porcentajes.index') }}"> {{ __('Back') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                                <div class="form-group mb-2 mb20">
                                    <strong>Paquete Id:</strong>
                                    {{ $porcentaje->paquete_id }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Cantidad Porcentaje:</strong>
                                    {{ $porcentaje->cantidad_porcentaje }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Tipo Porcentaje:</strong>
                                    {{ $porcentaje->tipo_porcentaje }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Observaciones:</strong>
                                    {{ $porcentaje->observaciones }}
                                </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
