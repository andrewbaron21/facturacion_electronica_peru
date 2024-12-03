@extends('tenant.layouts.app')

@section('content')
<div class="container">
    <h1>Gestión de Pedidos</h1>
    <a href="{{ route('orders.createView') }}" class="btn btn-primary mb-3">Crear Pedido</a>

    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>ID Pedido</th>
                <th>Mesa</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="orders-list">
            @foreach($orders as $order)
            <tr>
                <td>{{ $order->id }}</td>
                <td>Mesa {{ $order->table->number }}</td>
                <td>{{ ucfirst($order->status) }}</td>
                <td>
                    <!-- Botón para agregar platillos -->
                    <a href="{{ route('orderItems.list', $order->id) }}" class="btn btn-info">Gestión de Platillos</a>
                    <form action="{{ route('orders.updateStatus', $order->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="ready">
                        <button type="submit" class="btn btn-warning">Marcar Listo</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Script para actualizar pedidos -->
<script>
    setInterval(() => {
        fetch("{{ route('orders.polling') }}")
            .then(response => response.json())
            .then(data => {
                let ordersList = document.getElementById('orders-list');
                ordersList.innerHTML = '';
                data.orders.forEach(order => {
                    ordersList.innerHTML += `
                        <tr>
                            <td>${order.id}</td>
                            <td>Mesa ${order.table.number}</td>
                            <td>${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</td>
                            <td>
                                <a href="/restaurants/orders/${order.id}/items" class="btn btn-info">Gestión de Platillos</a>
                                
                                <form action="/restaurants/orders/${order.id}/status" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="ready">
                                    <button type="submit" class="btn btn-warning">Marcar Listo</button>
                                </form>
                            </td>
                        </tr>
                    `;
                });
            });
    }, 5000);
</script>
@endsection
