@extends('tenant.layouts.app')

@section('content')
<div class="container">
    <h1>Gestión de Mesas</h1>

    @foreach($branches as $branch)
        <h2>{{ $branch->name }}</h2> <!-- Nombre de la sede -->
        <table class="table mt-3">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($branch->tables as $table)
                <tr>
                    <td>{{ $table->number }}</td>
                    <td>
                        <form action="{{ route('tables.delete', $table->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
</div>
@endsection
