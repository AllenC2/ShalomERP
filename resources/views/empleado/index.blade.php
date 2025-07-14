@extends('layouts.app')

@section('template_title')
    Empleados
@endsection

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-people-fill me-2"></i>{{ __('Empleados') }}
                        </h5>
                        <a href="{{ route('empleados.create') }}" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-plus-circle me-1"></i>{{ __('Registrar nuevo empleado') }}
                        </a>
                    </div>
                    @if ($message = Session::get('success'))
                        <div class="alert alert-success m-4">
                            <p class="mb-0">{{ $message }}</p>
                        </div>
                    @endif

                    <div class="card-body bg-white">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Nombre</th>
                                        <th scope="col">Apellido</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Teléfono</th>
                                        <th scope="col">Domicilio</th>
                                        <th scope="col" class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($empleados as $empleado)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            <td>{{ $empleado->nombre }}</td>
                                            <td>{{ $empleado->apellido }}</td>
                                            <td>{{ $empleado->email }}</td>
                                            <td>{{ $empleado->telefono }}</td>
                                            <td>{{ $empleado->domicilio }}</td>
                                            <td class="text-end">
                                                <a class="btn btn-outline-primary btn-sm me-1" href="{{ route('empleados.show', $empleado->id) }}" title="Ver">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a class="btn btn-outline-success btn-sm me-1" href="{{ route('empleados.edit', $empleado->id) }}" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form action="{{ route('empleados.destroy', $empleado->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar"
                                                        onclick="event.preventDefault(); if(confirm('¿Seguro que deseas eliminar?')) this.closest('form').submit();">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            {!! $empleados->withQueryString()->links() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
