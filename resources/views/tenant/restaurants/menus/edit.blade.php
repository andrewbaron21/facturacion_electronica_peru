@extends('tenant.layouts.app')

@section('content')
<div class="container">
    <h1>Editar Plato</h1>

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

    <form action="{{ route('menus.update', $menu->menu_id) }}" method="POST" enctype="multipart/form-data">
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
            <label>Tipo de Moneda</label>
            <select name="currency" class="form-control" required>
                <option value="USD" {{ $menu->currency === 'USD' ? 'selected' : '' }}>Dólares (USD)</option>
                <option value="PEN" {{ $menu->currency === 'PEN' ? 'selected' : '' }}>Soles (PEN)</option>
            </select>
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
            <label for="customFile">Imagen</label>
            <div class="custom-file-wrapper">
                <input type="file" id="customFile" name="image" class="d-none">
                <button type="button" id="customButton" class="btn btn-secondary">Seleccionar imagen</button>
                <span id="fileName" class="ml-2">No se ha seleccionado</span>
            </div>
            
            <div class="mt-3">
                <label>Imagen actual:</label><br>
                @if($menu->image)
                    <img src="{{ asset('storage/' . $menu->image) }}" alt="Imagen del menú" class="img-fluid" style="max-width: 200px;">
                @else
                    <p>No hay imagen disponible</p>
                @endif
            </div>
            
            <div class="mt-3" id="newImagePreview" style="display: none;">
                <label>Imagen nueva:</label><br>
                <img id="previewImage" src="#" alt="Previsualización de la nueva imagen" class="img-fluid" style="max-width: 200px;">
            </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Actualizar</button>
    </form>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const customFileInput = document.getElementById('customFile');
        const customButton = document.getElementById('customButton');
        const fileNameDisplay = document.getElementById('fileName');
        const newImagePreview = document.getElementById('newImagePreview');
        const previewImage = document.getElementById('previewImage');

        customButton.addEventListener('click', function () {
            customFileInput.click();
        });

        customFileInput.addEventListener('change', function () {
            const file = customFileInput.files[0];
            if (file) {
                fileNameDisplay.textContent = file.name;
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImage.src = e.target.result;
                    newImagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                fileNameDisplay.textContent = 'No se ha seleccionado ninguna imagen';
                newImagePreview.style.display = 'none';
            }
        });
    });
</script>
@endsection
