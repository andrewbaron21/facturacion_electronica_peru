@extends('tenant.layouts.app')

@section('content')
<div class="container">
    <h1>Editar Platillo</h1>

    {{-- Mostrar errores --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('menus.update', $menu->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="name" class="form-control" value="{{ $menu->name }}" required>
        </div>
        <div class="form-group">
            <label>Precio</label>
            <input type="number" step="0.01" name="price" class="form-control" value="{{ $menu->price }}" required>
        </div>
        <div class="form-group">
            <label>Estado</label>
            <select name="status" class="form-control" required>
                <option value="1" {{ $menu->status ? 'selected' : '' }}>Disponible</option>
                <option value="0" {{ !$menu->status ? 'selected' : '' }}>No Disponible</option>
            </select>
        </div>
        <div class="form-group">
            <label>Descripción</label>
            <textarea name="description" class="form-control" rows="3">{{ $menu->description }}</textarea>
        </div>
        <div class="form-group">
            <label>Imagen</label>
            <input type="file" name="image" class="form-control">
            @if($menu->image)
                <img src="{{ asset('storage/' . $menu->image) }}" alt="Imagen del menú" class="img-fluid mt-2" style="max-width: 200px;">
            @endif
        </div>

        <button type="submit" class="btn btn-primary mt-3">Actualizar</button>
    </form>
</div>
@endsection
