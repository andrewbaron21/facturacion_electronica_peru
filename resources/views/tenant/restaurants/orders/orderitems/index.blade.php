@extends('tenant.layouts.app')

@section('content')
<div class="container">
    <h1>Ítems de Pedido #{{ $order->id }} (Mesa {{ $order->table->number }})</h1>

    <h3>Agregar Platillo</h3>
    <form action="{{ route('orderItems.create') }}" method="POST">
        @csrf
        <input type="hidden" name="order_id" value="{{ $order->id }}">

        <div class="form-group">
            <label for="menu_id">Platillo</label>
            <select name="menu_id" id="menu_id" class="form-control" required>
                <option value="">Seleccione un platillo</option>
                @foreach($menus as $menu)
                <option value="{{ $menu->id }}">{{ $menu->name }} - ${{ $menu->price }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="quantity">Cantidad</label>
            <input type="number" name="quantity" id="quantity" class="form-control" min="1" required>
        </div>

        <button type="submit" class="btn btn-success">Agregar Platillo</button>
    </form>

    <h3 class="mt-5">Lista de Ítems</h3>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Platillo</th>
                <th>Cantidad</th>
                <th>Precio</th>
                <th>Total</th>
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
                <td>
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
