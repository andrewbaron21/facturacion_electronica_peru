@extends('tenant.layouts.app')

@section('content')
<div class="container">
    <h1>Sedes de {{ $restaurant->name }}</h1>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('branches.store', $restaurant->id) }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Nombre de la Sede</label>
            <input type="text" name="name" id="name" class="form-control" placeholder="Ingrese el nombre de la sede" required>
        </div>
        <div class="form-group">
            <label for="address">Dirección</label>
            <input type="text" name="address" id="address" class="form-control" placeholder="Ingrese la dirección">
        </div>
        <div class="form-group">
            <label for="phone">Teléfono</label>
            <input type="text" name="phone" id="phone" class="form-control" placeholder="Ingrese el teléfono">
        </div>
        <button type="submit" class="btn btn-primary mt-2">Crear Sede</button>
    </form>

    <h2 class="mt-5">Lista de Sedes</h2>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Dirección</th>
                <th>Teléfono</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($branches as $branch)
                <tr>
                    <td>{{ $branch->id }}</td>
                    <td>{{ $branch->name }}</td>
                    <td>{{ $branch->address }}</td>
                    <td>{{ $branch->phone }}</td>
                    <td>
                        <form action="{{ route('branches.destroy', $branch->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
