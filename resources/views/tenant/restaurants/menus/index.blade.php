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

    @foreach($branches as $branchId => $menus)
        <h2>Sede: {{ $menus->first()->branch_name }}</h2>
        <p>Dirección: {{ $menus->first()->address ?? 'Sin dirección' }}</p>
        <p>Teléfono: {{ $menus->first()->phone ?? 'Sin teléfono' }}</p>

        @if($menus->isEmpty() || $menus->first()->menu_id === null)
            <p>No hay menús disponibles para esta sede.</p>
        @else
            <table class="table mt-3">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Moneda</th>
                        <th>Descripción</th>
                        <th>Imagen</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($menus as $menu)
                        <tr>
                            <td>{{ $menu->menu_name }}</td>
                            <td>S/. {{ number_format($menu->menu_price, 2) }}</td>
                            <td>
                                @if($menu->menu_currency === 'USD')
                                    Dólares (USD)
                                @elseif($menu->menu_currency === 'PEN')
                                    Soles (PEN)
                                @else
                                    Desconocido
                                @endif
                            </td>
                            <td>{{ $menu->menu_description }}</td>
                            <td>
                                @if($menu->menu_image)
                                    <img src="{{ asset('storage/uploads/items/' . $menu->menu_image) }}" alt="Imagen del menú" class="img-fluid" style="max-width: 100px;">
                                @else
                                    Sin imagen
                                @endif
                            </td>
                            <td>
                                <!-- <a href="{{ route('menus.edit', $menu->menu_id) }}" class="btn btn-warning">Editar</a> -->
                                <form action="{{ route('menus.destroy', $menu->menu_id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
        <hr>
    @endforeach
</div>
@endsection
