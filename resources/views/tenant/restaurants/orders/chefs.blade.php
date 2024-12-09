@extends('tenant.layouts.app')

@section('content')
<div class="container mt-5">
    <div class="text-center mb-4">
        <h1 class="display-4">Panel de Pedidos de Cocina</h1>
        <p class="text-muted">
            @if(auth()->user()->type === 'admin')
                Visualizando todos los pedidos pendientes (Administrador)
            @else
                Visualizando pedidos de tu sede
            @endif
        </p>
    </div>

    <div id="branches-orders">
        <p class="text-center text-muted">Cargando pedidos...</p>
    </div>
</div>

<!-- Script para actualizar pedidos -->
<script>
    setInterval(() => {
        fetch("{{ route('orders.polling') }}")
            .then(response => response.json())
            .then(data => {
                let branchesOrders = document.getElementById('branches-orders');
                branchesOrders.innerHTML = '';

                if (data.orders.length === 0) {
                    branchesOrders.innerHTML = `
                        <div class="alert alert-info text-center" role="alert">
                            No hay pedidos pendientes en este momento.
                        </div>`;
                    return;
                }

                let groupedOrders = {};
                data.orders.forEach(order => {
                    if (!groupedOrders[order.branch_name]) {
                        groupedOrders[order.branch_name] = [];
                    }
                    groupedOrders[order.branch_name].push(order);
                });

                for (let branch in groupedOrders) {
                    branchesOrders.innerHTML += `
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Sede: ${branch}</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th scope="col">ID Pedido</th>
                                            <th scope="col">Mesa</th>
                                            <th scope="col">Estado</th>
                                            <th scope="col">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${groupedOrders[branch].map(order => `
                                            <tr>
                                                <td>${order.id}</td>
                                                <td>Mesa ${order.table_number}</td>
                                                <td>
                                                    <span class="badge bg-warning text-dark">
                                                        ${order.status.charAt(0).toUpperCase() + order.status.slice(1)}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="/restaurants/orders/${order.id}/items" class="btn btn-info btn-sm me-2">
                                                        <i class="bi bi-eye"></i> Ver Platos
                                                    </a>
                                                </td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;
                }
            });
    }, 5000);
</script>
@endsection
