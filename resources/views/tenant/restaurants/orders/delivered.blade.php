@extends('tenant.layouts.app')

@section('content')
<div class="container">
    <h1>Pedidos Entregados</h1>

    <!-- Filtros -->
    <form method="GET" action="{{ route('orders.delivered') }}" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <label for="date">Fecha:</label>
                <input type="date" name="date" id="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="col-md-4">
                <label for="table_id">Mesa:</label>
                <select name="table_id" id="table_id" class="form-control">
                    <option value="">Todas las mesas</option>
                    @foreach($tables as $table)
                        <option value="{{ $table->id }}" {{ request('table_id') == $table->id ? 'selected' : '' }}>
                            Mesa {{ $table->number }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary mt-4">Filtrar</button>
            </div>
        </div>
    </form>

    <!-- Tabla de pedidos entregados -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID Pedido</th>
                <th>Mesa</th>
                <th>Fecha de Entrega</th>
                <th>Ítems del Pedido</th>
                <th>Costo Total</th>
            </tr>
        </thead>
        <tbody id="delivered-orders-list">
            @foreach($orders as $order)
            <tr>
                <td>{{ $order->id }}</td>
                <td>Mesa {{ $order->table->number }}</td>
                <td>{{ $order->updated_at->format('d/m/Y H:i') }}</td>
                <td>
                    <ul>
                        @foreach($order->items as $item)
                        <li>{{ $item->menu->name }} (x{{ $item->quantity }}) - ${{ number_format($item->price, 2) }}</li>
                        @endforeach
                    </ul>
                </td>
                <td>
                    ${{ number_format($order->items->sum(fn($item) => $item->price * $item->quantity), 2) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Script para actualizar la lista de pedidos entregados -->
<script>
    function fetchDeliveredOrders() {
        const date = document.getElementById('date').value;
        const tableId = document.getElementById('table_id').value;

        fetch("{{ route('orders.delivered.polling') }}?date=" + date + "&table_id=" + tableId)
            .then(response => response.json())
            .then(data => {
                let ordersList = document.getElementById('delivered-orders-list');
                ordersList.innerHTML = '';
                data.orders.forEach(order => {
                    let itemsHtml = order.items.map(item => 
                        `<li>${item.menu.name} (x${item.quantity}) - $${parseFloat(item.price).toFixed(2)}</li>`
                    ).join('');

                    ordersList.innerHTML += `
                        <tr>
                            <td>${order.id}</td>
                            <td>Mesa ${order.table.number}</td>
                            <td>${new Date(order.updated_at).toLocaleString()}</td>
                            <td><ul>${itemsHtml}</ul></td>
                            <td>$${order.items.reduce((sum, item) => sum + (item.price * item.quantity), 0).toFixed(2)}</td>
                        </tr>
                    `;
                });
            });
    }

    // Ejecutar el polling cada 5 segundos
    setInterval(fetchDeliveredOrders, 5000);
</script>
@endsection
