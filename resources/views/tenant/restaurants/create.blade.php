@extends('tenant.layouts.app')

@section('content')
<div class="container">
    <h1>Crear Restaurante</h1>

    <form action="{{ route('restaurants.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Nombre del Restaurante</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="address">Dirección</label>
            <input type="text" name="address" id="address" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Crear Restaurante</button>
    </form>
</div>
@endsection
