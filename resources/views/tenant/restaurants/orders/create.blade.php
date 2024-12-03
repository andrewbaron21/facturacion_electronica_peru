@extends('tenant.layouts.app')

@section('content')
<div class="container">
    <h1>Crear Pedido</h1>
    <form action="{{ route('orders.create') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="table_id">Mesa</label>
            <select name="table_id" id="table_id" class="form-control" required>
                <option value="">Seleccione una mesa</option>
                @foreach($tables as $table)
                <option value="{{ $table->id }}">Mesa {{ $table->number }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-success">Crear Pedido</button>
        <a href="{{ route('orders.waiters') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
