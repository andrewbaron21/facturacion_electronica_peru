@extends('tenant.layouts.app')

@section('content')
<div class="container">
    <h1>Gestión de Platos</h1>

    {{-- Mensajes de éxito --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <a href="{{ route('menus.create') }}" class="btn btn-primary mb-3">Agregar Plato</a>
    <table class="table mt-3">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Precio</th>
            <th>Estado</th>
            <th>Descripción</th>
            <!-- <th>Imagen</th> -->
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($menus as $menu)
        <tr>
            <td>{{ $menu->name }}</td>
            <td>S/. {{ number_format($menu->price, 2) }}</td>
            <td>{{ $menu->status ? 'Disponible' : 'No Disponible' }}</td>
            <td>{{ $menu->description }}</td>
            <td>
                @if($menu->image)
                    <img src="{{ asset('storage/' . $menu->image) }}" alt="Imagen del menú" class="img-fluid" style="max-width: 100px;">
                @else
                    Sin imagen
                @endif
            </td>
            <td>
                <a href="{{ route('menus.edit', $menu->id) }}" class="btn btn-warning">Editar</a>
                <form action="{{ route('menus.destroy', $menu->id) }}" method="POST" class="d-inline">
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
