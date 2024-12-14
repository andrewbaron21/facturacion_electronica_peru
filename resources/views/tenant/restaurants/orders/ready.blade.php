@extends('tenant.layouts.app')

@section('content')
<div class="container mt-5">
    <h1 class="mb-4 text-center">Pedidos Listos para Entregar</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>ID Pedido</th>
                    <th>Mesa</th>
                    <th>Estado</th>
                    <th>Ítems</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="ready-orders-list">
                <!-- Se llenará dinámicamente con JS -->
            </tbody>
        </table>
    </div>
</div>

<!-- Script para actualizar pedidos listos -->
<script>
    function fetchReadyOrders() {
        fetch("{{ route('orders.ready.polling') }}")
            .then(response => response.json())
            .then(data => {
                let ordersList = document.getElementById('ready-orders-list');
                ordersList.innerHTML = '';
                data.orders.forEach(order => {
                    let itemsHTML = '';
                    if (order.items && order.items.length > 0) {
                        itemsHTML = '<ul>';
                        order.items.forEach(item => {
                            itemsHTML += `<li>${item.menu ? item?.menu?.item?.description : 'Sin información'} - Cantidad: ${item.quantity}</li>`;
                        });
                        itemsHTML += '</ul>';
                    } else {
                        itemsHTML = 'No items';
                    }

                    ordersList.innerHTML += `
                        <tr>
                            <td>${order.id}</td>
                            <td>${order.table ? 'Mesa ' + order.table.number : 'Sin asignar'}</td>
                            <td>${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</td>
                            <td>${itemsHTML}</td>
                            <td>
                                <div class="d-flex flex-column gap-2">
                                    <a href="/restaurants/orders/${order.id}/items" class="btn btn-info btn-sm">Editar/Añadir Órdenes</a>
                                    <form action="/restaurants/orders/${order.id}/deliver" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-success btn-sm">Enviar a Facturación</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    `;
                });
            })
            .catch(error => console.error('Error al cargar las órdenes:', error));
    }

    // Ejecutar la función de polling cada 5 segundos
    setInterval(fetchReadyOrders, 5000);
    fetchReadyOrders(); // Llamar inmediatamente para evitar espera
</script>
@endsection
