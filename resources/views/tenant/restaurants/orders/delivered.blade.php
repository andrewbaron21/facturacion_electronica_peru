@extends('tenant.layouts.app')

@section('content')
<div class="container mt-5">
    <h1 class="mb-4 text-center">Pedidos Entregados</h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-12 col-md-4 mb-3 mb-md-0">
            <label for="date">Fecha:</label>
            <input type="date" name="date" id="date" class="form-control" value="{{ request('date') }}">
        </div>
        <div class="col-12 col-md-4 mb-3 mb-md-0">
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
        <div class="col-12 col-md-4">
            <button id="filter-btn" class="btn btn-primary w-100">Aplicar Filtros</button>
        </div>
    </div>

    <!-- Tabla de Pedidos Entregados -->
    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>ID Pedido</th>
                    <th class="d-none d-md-table-cell">Mesa</th>
                    <th>Fecha de Entrega</th>
                    <th>Ítems</th>
                    <th class="d-none d-md-table-cell">Costo Total</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="delivered-orders-list">
                <!-- Contenido dinámico cargado con JavaScript -->
            </tbody>
        </table>
    </div>

    <!-- Alternativa para pantallas pequeñas: Mostrar tarjetas (opcional) -->
    <div id="delivered-orders-cards" class="d-md-none">
        <!-- Contenido dinámico cargado con JavaScript -->
    </div>
</div>

<script>
    function fetchDeliveredOrders() {
        const date = document.getElementById('date').value;
        const tableId = document.getElementById('table_id').value;

        fetch("{{ route('orders.delivered.polling') }}?date=" + date + "&table_id=" + tableId)
            .then(response => response.json())
            .then(data => {
                let ordersList = document.getElementById('delivered-orders-list');
                let ordersCards = document.getElementById('delivered-orders-cards');
                ordersList.innerHTML = '';
                ordersCards.innerHTML = '';

                data.orders.forEach(order => {
                    let itemsHtml = order.items.map(item => 
                        `<li>
                            <small>
                                <strong>Código interno:</strong> ${item?.menu?.item?.internal_id} <br>
                                <strong>Nombre:</strong> ${item?.menu?.item?.description} <br>
                                <strong>Cantidad:</strong> (x${item.quantity}) - $${parseFloat(item.price).toFixed(2)}
                            </small>
                        </li>`).join('');

                    // Fila para la tabla (pantallas grandes)
                    ordersList.innerHTML += `
                        <tr>
                            <td>${order.id}</td>
                            <td class="d-none d-md-table-cell">Mesa ${order.table?.number || 'N/A'}</td>
                            <td>${new Date(order.updated_at).toLocaleString()}</td>
                            <td><ul>${itemsHtml}</ul></td>
                            <td class="d-none d-md-table-cell">$${order.items.reduce((sum, item) => sum + (item.price * item.quantity), 0).toFixed(2)}</td>
                            <td>
                                <form action="/restaurants/orders/${order.id}/mark-paid" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-success btn-sm">Marcar como facturado</button>
                                </form>
                            </td>
                        </tr>
                    `;

                    // Tarjeta para pantallas pequeñas
                    ordersCards.innerHTML += `
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Pedido #${order.id}</h5>
                                <p><strong>Mesa:</strong> ${order.table?.number || 'N/A'}</p>
                                <p><strong>Fecha de Entrega:</strong> ${new Date(order.updated_at).toLocaleString()}</p>
                                <p><strong>Ítems:</strong></p>
                                <ul>${itemsHtml}</ul>
                                <p><strong>Total:</strong> $${order.items.reduce((sum, item) => sum + (item.price * item.quantity), 0).toFixed(2)}</p>
                                <form action="/restaurants/orders/${order.id}/mark-paid" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-success btn-block">Marcar como facturado</button>
                                </form>
                            </div>
                        </div>
                    `;
                });
            })
            .catch(error => console.error("Error al obtener las órdenes entregadas: ", error));
    }

    document.getElementById('filter-btn').addEventListener('click', fetchDeliveredOrders);

    setInterval(fetchDeliveredOrders, 5000);
    fetchDeliveredOrders();
</script>
@endsection
