@extends('tenant.layouts.app')

@section('content')
<div class="container mt-5">
    <h1 class="mb-4">Crear Pedido</h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-4">
        <p class="text-muted">
            @if($isAdmin)
                Estás creando un pedido como <strong>Administrador</strong>. Tienes acceso a todas las mesas.
            @else
                Estás creando un pedido para la sede asignada.
            @endif
        </p>
    </div>

    <form action="{{ route('orders.create') }}" method="POST">
        @csrf
        <div class="form-group mb-4">
            <label for="table_id" class="form-label">Selecciona una Mesa</label>
            <select name="table_id" id="table_id" class="form-control" required>
                <option value="">Seleccione una mesa</option>
                @foreach($tables as $table)
                <option value="{{ $table->id }}">
                    Mesa {{ $table->number }} 
                    @if($isAdmin)
                        - Sede: {{ $table->branch->name ?? 'Sin sede' }}
                    @endif
                </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-success">Crear Pedido</button>
        <a href="{{ route('orders.waiters') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
