@extends('tenant.layouts.app')

@section('content')
<div class="container">
    <h1>Agregar Platillo</h1>
    <form action="{{ route('menus.store') }}" method="POST" enctype="multipart/form-data"> <!-- Cambia la acción a menus.store -->
        @csrf
        <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Precio</label>
            <input type="number" step="0.01" name="price" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Descripción</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
        </div>
        <div class="form-group">
            <label>Imagen</label>
            <input type="file" name="image" class="form-control">
        </div>
        <div class="form-group">
            <label>Restaurante</label>
            <select name="restaurant_id" class="form-control" required>
                @foreach($restaurants as $restaurant)
                    <option value="{{ $restaurant->id }}">{{ $restaurant->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Guardar</button>
    </form>
</div>
@endsection
