@extends('tenant.layouts.app')

@section('content')
<div class="container">
    <h1>Restaurantes</h1>
    <!-- @php $restaurantExists = App\Models\Tenant\Restaurant::exists(); @endphp
    @if (!$restaurantExists)
        <li>
            <a class="nav-link" href="{{ route('restaurants.create') }}">
                <i class="fas fa-plus-circle"></i>
                <span>Crear Restaurante</span>
            </a>
        </li>
    @endif -->
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Dirección</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($restaurants as $restaurant)
                <tr>
                    <td>{{ $restaurant->id }}</td>
                    <td>{{ $restaurant->name }}</td>
                    <td>{{ $restaurant->address }}</td>
                    <td>
                        <a href="{{ route('branches.index', $restaurant->id) }}" class="btn btn-info">Ver Sedes</a>
                        <form action="{{ route('restaurants.destroy', $restaurant->id) }}" method="POST" style="display:inline;">
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
