@extends('tenant.layouts.app')

@section('content')
<div class="container mt-5">
    <h1 class="mb-4 text-center">Pedidos facturados</h1>

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
        <div class="col-md-4 mt-4">
            <button id="filter-btn" class="btn btn-primary w-100">Aplicar Filtros</button>
        </div>
    </div>

    <!-- Tabla de Pedidos Entregados -->
    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>ID Pedido</th>
                    <th>Mesa</th>
                    <th>Fecha de Entrega</th>
                    <th>Ítems del Pedido</th>
                    <th>Costo Total</th>
                </tr>
            </thead>
            <tbody id="delivered-orders-list">
                <!-- Contenido dinámico cargado con JavaScript -->
            </tbody>
        </table>
    </div>
</div>

<script>
    // Función para realizar el polling de las órdenes entregadas
    function fetchDeliveredOrders() {
        const date = document.getElementById('date').value;
        const tableId = document.getElementById('table_id').value;

        fetch("{{ route('orders.invoiced.polling') }}?date=" + date + "&table_id=" + tableId)
            .then(response => response.json())
            .then(data => {
                let ordersList = document.getElementById('delivered-orders-list');
                ordersList.innerHTML = '';
                data.orders.forEach(order => {
                    let itemsHtml = order.items.map(item => 
                        `<li>Código interno: <strong>${item?.menu?.item?.internal_id}</strong> <br> ${item?.menu?.item?.name} (x${item.quantity}) - $${parseFloat(item.price).toFixed(2)}</li>`
                    ).join('');

                    ordersList.innerHTML += `
                        <tr>
                            <td>${order.id}</td>
                            <td>Mesa ${order.table?.number || 'N/A'}</td>
                            <td>${new Date(order.updated_at).toLocaleString()}</td>
                            <td><ul>${itemsHtml}</ul></td>
                            <td>$${order.items.reduce((sum, item) => sum + (item.price * item.quantity), 0).toFixed(2)}</td>
                        </tr>
                    `;
                });
            })
            .catch(error => console.error("Error al obtener las órdenes entregadas: ", error));
    }

    // Event Listener para el botón de filtros
    document.getElementById('filter-btn').addEventListener('click', fetchDeliveredOrders);

    // Ejecutar la función de polling cada 5 segundos
    setInterval(fetchDeliveredOrders, 5000);
    fetchDeliveredOrders(); // Llamar inmediatamente para evitar espera
</script>
@endsection
