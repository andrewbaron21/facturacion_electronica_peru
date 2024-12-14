@extends('tenant.layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Editar Empleado</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('employees.update', $employee->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="name" class="form-control" value="{{ $employee->name }}" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="{{ $employee->email }}" required>
        </div>
        <div class="form-group">
            <label>Teléfono</label>
            <input type="text" name="phone" class="form-control" value="{{ $employee->phone }}">
        </div>
        <div class="form-group">
            <label>Nueva Contraseña (opcional)</label>
            <input type="password" name="password" class="form-control">
            <small class="form-text text-muted">Dejar en blanco si no deseas cambiar la contraseña.</small>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Guardar Cambios</button>
    </form>
</div>
@endsection
