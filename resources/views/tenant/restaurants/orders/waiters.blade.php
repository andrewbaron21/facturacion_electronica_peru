@extends('tenant.layouts.app')

@section('content')
<div class="container mt-5">
    <h1 class="mb-4">Gestión de Pedidos Meseros</h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-3">
        <a href="{{ route('orders.createView') }}" class="btn btn-primary">Crear Pedido</a>
    </div>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID Pedido</th>
                <th>Mesa</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="orders-list">
            @forelse($orders as $order)
            <tr>
                <td>{{ $order->id }}</td>
                <td>{{ $order->table ? 'Mesa ' . $order->table->number : 'Sin asignar' }}</td>
                <td>{{ ucfirst($order->status) }}</td>
                <td>
                    <a href="{{ route('orderItems.list', $order->id) }}" class="btn btn-info">Editar/Añadir Órdenes</a>
                    <form action="{{ route('orders.delete', $order->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Plato</th>
                                <th>Cantidad</th>
                                <th>Precio</th>
                                <th>Total</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td>{{ $item->menu->item ? $item->menu->item->name : 'Sin información' }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>${{ $item->menu->item ? $item->menu->item->sale_unit_price : 'N/A' }}</td>
                                    <td>${{ $item->quantity * ($item->menu->item ? $item->menu->item->sale_unit_price : 0) }}</td>
                                    <td>{{ ucfirst($item->status) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">No hay órdenes pendientes.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Script para actualizar pedidos -->
<script>
    setInterval(() => {
        fetch("{{ route('orders.polling.waiters') }}")
            .then(response => response.json())
            .then(data => {
                let ordersList = document.getElementById('orders-list');
                ordersList.innerHTML = '';

                data.orders.forEach(order => {
                    ordersList.innerHTML += `
                        <tr>
                            <td>${order.id}</td>
                            <td>${order.table_number ? 'Mesa ' + order.table_number : 'Sin asignar'}</td>
                            <td>${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</td>
                            <td>
                                <a href="/restaurants/orders/${order.id}/items" class="btn btn-info">Editar/Añadir Órdenes</a>
                                <form action="/restaurants/orders/${order.id}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Plato</th>
                                            <th>Cantidad</th>
                                            <th>Precio</th>
                                            <th>Total</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${order.items.map(item => `
                                            <tr>
                                                <td>${item.name}</td>
                                                <td>${item.quantity}</td>
                                                <td>$${item.price}</td>
                                                <td>$${item.total}</td>
                                                <td>${item.status}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    `;
                });
            })
            .catch(error => console.error('Error al cargar las órdenes:', error));
    }, 5000);
</script>

@endsection
