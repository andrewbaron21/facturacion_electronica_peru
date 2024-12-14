@extends('tenant.layouts.app')

@section('content')
<div class="container">
    <h1>Añadir Plato a la Carta</h1>

    <!-- Formulario para filtros -->
    <form action="{{ route('menus.create') }}" method="GET" class="mb-4">
        <div class="form-row">
            <!-- <div class="form-group col-md-4">
                <label for="filter_name">Nombre</label>
                <input type="text" name="filter_name" id="filter_name" class="form-control" value="{{ request('filter_name') }}">
            </div>
            <div class="form-group col-md-4">
                <label for="filter_description">Descripción</label>
                <input type="text" name="filter_description" id="filter_description" class="form-control" value="{{ request('filter_description') }}">
            </div> -->
            <div class="form-group col-md-4">
                <label for="filter_id">ID</label>
                <input type="number" name="filter_id" id="filter_id" class="form-control" value="{{ request('filter_id') }}">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="{{ route('menus.create') }}" class="btn btn-secondary">Limpiar Filtros</a>
    </form>

    <!-- Formulario para añadir al menú -->
    <form action="{{ route('menus.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="branch_id">Sucursal</label>
            <select name="branch_id" id="branch_id" class="form-control" required>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="item_id">Seleccionar Producto</label>
            <select name="item_id" id="item_id" class="form-control" required>
                @foreach($items as $item)
                    <option value="{{ $item->id }}">{{ $item->name }} - {{ $item->description }} (ID: {{ $item->id }})</option>
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
