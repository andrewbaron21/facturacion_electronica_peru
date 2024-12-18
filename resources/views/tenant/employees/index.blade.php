@extends('tenant.layouts.app')

@section('content')
<div class="container">
    <h1 class="text-center my-4">Empleados</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('employees.create') }}" class="btn btn-primary">Crear Empleado</a>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Sedes</th>
                    <th>Roles</th>
                    <th>Asignar rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employees as $employee)
                <tr>
                    <td>{{ $employee->name }}</td>
                    <td>{{ $employee->email }}</td>
                    <td>
                        @foreach($employee->branches as $branch)
                            {{ $branch->name }}<br>
                        @endforeach
                    </td>
                    <td>
                        @foreach($employee->roles as $role)
                            {{ $role->name }}<br>
                        @endforeach
                    </td>
                    <td>
                        <div class="d-flex justify-content-between">
                            <div class="btn-group-vertical mr-2">
                                <form action="{{ route('employees.assignRole') }}" method="POST" class="mb-2">
                                    @csrf
                                    <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                                    <div class="form-group">
                                        <label for="branch_id_{{ $employee->id }}">Sucursal:</label>
                                        <select name="branch_id" id="branch_id_{{ $employee->id }}" class="form-control form-control-sm" required>
                                            <option value="" disabled selected>Seleccionar sede</option>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="role_id_{{ $employee->id }}">Rol:</label>
                                        <select name="role_id" id="role_id_{{ $employee->id }}" class="form-control form-control-sm" required>
                                            <option value="" disabled selected>Seleccionar role</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="submit" id="submit_button_{{ $employee->id }}" class="btn btn-secondary btn-sm w-100 mt-2 mb-2" disabled>Asignar Rol</button>
                                </form>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="btn-group-vertical mr-2">
                            <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-info btn-sm mb-2">Editar</a>
                            <form action="{{ route('employees.removeRole', ['employee' => $employee->id]) }}" method="POST" class="mb-2">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-warning btn-sm w-100 mb-2">Eliminar Roles</button>
                            </form>
                            <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este empleado?');" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm w-100">Eliminar Empleado</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const employees = @json($employees); // Pasamos la lista de empleados al JavaScript
        
        employees.forEach(employee => {
            const branchSelect = document.getElementById(`branch_id_${employee.id}`);
            const roleSelect = document.getElementById(`role_id_${employee.id}`);
            const submitButton = document.getElementById(`submit_button_${employee.id}`);

            const validateSelection = () => {
                if (branchSelect.value && roleSelect.value) {
                    submitButton.removeAttribute('disabled');
                } else {
                    submitButton.setAttribute('disabled', 'true');
                }
            };

            branchSelect.addEventListener('change', validateSelection);
            roleSelect.addEventListener('change', validateSelection);
        });
    });
</script>
@endsection
