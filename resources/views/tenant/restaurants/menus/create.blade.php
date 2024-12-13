@extends('tenant.layouts.app')

@section('content')
<div class="container">
    <h1>Añadir Plato a la Carta</h1>
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
            <label>Tipo de Moneda</label>
            <select name="currency" class="form-control" required>
                <option value="USD">Dólares Americanos (USD)</option>
                <option value="PEN">Soles (PEN)</option>
            </select>
        </div>
        <div class="form-group">
            <label>Descripción</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
        </div>
        <div class="form-group">
            <label for="customFile">Imagen</label>
            <div class="custom-file-wrapper">
                <input type="file" id="customFile" name="image" class="d-none"> <!-- Ocultamos el input -->
                <button type="button" id="customButton" class="btn btn-secondary">Seleccionar imagen</button>
                <span id="fileName" class="ml-2">No se ha seleccionado ninguna imagen</span>
            </div>
        </div>
        <div class="form-group">
            <label>Sucursal</label>
            <select name="branch_id" class="form-control" required>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Guardar</button>
    </form>
</div>
<style>
    .custom-file-wrapper {
        display: flex;
        align-items: center;
    }
    #customButton {
        margin-right: 10px;
    }
    #fileName {
        font-style: italic;
        color: gray;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const customFileInput = document.getElementById('customFile');
        const customButton = document.getElementById('customButton');
        const fileNameDisplay = document.getElementById('fileName');

        customButton.addEventListener('click', function () {
            customFileInput.click(); // Abrimos el selector de archivos
        });

        customFileInput.addEventListener('change', function () {
            const fileName = customFileInput.files[0]?.name || 'No se ha seleccionado ninguna imagen';
            fileNameDisplay.textContent = fileName; // Actualizamos el nombre del archivo seleccionado
        });
    });
</script>
@endsection
