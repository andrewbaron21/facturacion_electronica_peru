@extends('tenant.layouts.app')

@section('content')
<div class="container">
    <h1>Crear Mesa</h1>

    {{-- Mostrar mensaje de éxito --}}
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Mostrar errores generales --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('tables.create') }}">
        @csrf
        <div class="form-group">
            <label for="number">Número de Mesa</label>
            <input 
                type="text" 
                class="form-control @error('number')" 
                id="number" 
                name="number" 
                value="{{ old('number') }}" 
                required
            >
        </div>

        <div class="form-group">
            <label for="restaurant_id">Restaurante</label>
            <select 
                class="form-control @error('restaurant_id')" 
                id="restaurant_id" 
                name="restaurant_id" 
                required
            >
                <option value="">Seleccione un restaurante</option>
                @foreach($restaurants as $restaurant)
                    <option value="{{ $restaurant->id }}" {{ old('restaurant_id') == $restaurant->id ? 'selected' : '' }}>
                        {{ $restaurant->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Guardar Mesa</button>
    </form>
</div>
@endsection
