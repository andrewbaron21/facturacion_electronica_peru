@extends('tenant.layouts.app')

@section('content')
<div class="container">
    <h1>Ítems de Pedido #{{ $order->id }} (Mesa {{ $order->table->number }})</h1>

    <h3>Agregar Plato</h3>
    @if(session('error'))
        <div class="alert alert-danger mt-3">
            {{ session('error') }}
        </div>
    @endif
    <form action="{{ route('orderItems.create') }}" method="POST">
        @csrf
        <input type="hidden" name="order_id" value="{{ $order->id }}">

        <div class="form-group">
            <label for="menu_id">Plato</label>
            <select name="menu_id" id="menu_id" class="form-control" required>
                <option value="">Seleccione un Plato</option>
                @foreach($menus as $menu)
                <option value="{{ $menu->id }}">{{ $menu->name }} - ${{ $menu->price }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="quantity">Cantidad</label>
            <input type="number" name="quantity" id="quantity" class="form-control" min="1" required>
        </div>

        <button type="submit" class="btn btn-success">Agregar Plato</button>
    </form>

    @if(session('success'))
        <div class="alert alert-success mt-3">
            {{ session('success') }}
        </div>
    @endif

    <h3 class="mt-5">Lista de Ítems</h3>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Plato</th>
                <th>Cantidad</th>
                <th>Precio</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orderItems as $item)
            <tr>
                <td>{{ $item->menu->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>${{ $item->price }}</td>
                <td>${{ $item->quantity * $item->price }}</td>
                <td>{{ ucfirst($item->status) }}</td>
                <td>
                    @if($item->status !== 'listo')
                        <form action="{{ route('orderItems.markReady', $item->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-warning">Marcar Listo</button>
                        </form>
                    @endif
                    <form action="{{ route('orderItems.delete', $item->id) }}" method="POST" class="d-inline">
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
