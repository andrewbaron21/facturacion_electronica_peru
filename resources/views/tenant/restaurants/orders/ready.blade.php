@extends('tenant.layouts.app')

@section('content')
<div class="container">
    <h1>Pedidos Listos para Entregar</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>ID Pedido</th>
                <th>Mesa</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="ready-orders-list">
            @foreach($orders as $order)
            <tr>
                <td>{{ $order->id }}</td>
                <td>Mesa {{ $order->table->number }}</td>
                <td>{{ ucfirst($order->status) }}</td>
                <td>
                    <a href="{{ route('orderItems.list', $order->id) }}" class="btn btn-info">Editar/Añadir Órdenes</a>
                    <form action="{{ route('orders.deliver', $order->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn btn-success">Enviar a Facturación</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Script para actualizar pedidos listos -->
<script>
    setInterval(() => {
        fetch("{{ route('orders.ready.polling') }}")
            .then(response => response.json())
            .then(data => {
                let ordersList = document.getElementById('ready-orders-list');
                ordersList.innerHTML = '';
                data.orders.forEach(order => {
                    ordersList.innerHTML += `
                        <tr>
                            <td>${order.id}</td>
                            <td>Mesa ${order.table.number}</td>
                            <td>${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</td>
                            <td>
                                <a href="/restaurants/orders/${order.id}/items" class="btn btn-info">Editar/Añadir Órdenes</a>
                                <form action="/restaurants/orders/${order.id}/deliver" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-success">Enviar a Facturación</button>
                                </form>
                            </td>
                        </tr>
                    `;
                });
            });
    }, 5000);
</script>
@endsection
