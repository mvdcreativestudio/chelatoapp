@extends('layouts.layoutMaster')

@section('title', 'Listado de Órdenes POS')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/select2/select2.scss',
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/assets/vendor/libs/select2/select2.js',
])
@endsection

@section('content')
<h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Gestión /</span> Listado de Órdenes POS
</h4>

@if (session('success'))
<div class="alert alert-success mt-3 mb-3">
    {{ session('success') }}
</div>
@endif

@if (session('error'))
<div class="alert alert-danger mt-3 mb-3">
    {{ session('error') }}
</div>
@endif

@if ($errors->any())
@foreach ($errors->all() as $error)
<div class="alert alert-danger">
    {{ $error }}
</div>
@endforeach
@endif

<!-- Contenedor para la tabla -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Órdenes POS</h5>
    </div>
    <div class="card-header">
        <div class="d-flex">
            <p class="text-muted small">
                <a href="" class="toggle-switches" data-bs-toggle="collapse" data-bs-target="#columnSwitches" aria-expanded="false" aria-controls="columnSwitches">Ver / Ocultar columnas de la tabla</a>
            </p>
        </div>
        <div class="collapse" id="columnSwitches">
            <div class="mt-0 d-flex flex-wrap">
                <div class="mx-0">
                    <label class="switch switch-square">
                        <input type="checkbox" class="toggle-column switch-input" data-column="0" checked>
                        <span class="switch-toggle-slider">
                            <span class="switch-on"><i class="bx bx-check"></i></span>
                            <span class="switch-off"><i class="bx bx-x"></i></span>
                        </span>
                        <span class="switch-label">ID</span>
                    </label>
                </div>
                <div class="mx-3">
                    <label class="switch switch-square">
                        <input type="checkbox" class="toggle-column switch-input" data-column="1" checked>
                        <span class="switch-toggle-slider">
                            <span class="switch-on"><i class="bx bx-check"></i></span>
                            <span class="switch-off"><i class="bx bx-x"></i></span>
                        </span>
                        <span class="switch-label">Fecha</span>
                    </label>
                </div>
                <div class="mx-3">
                    <label class="switch switch-square">
                        <input type="checkbox" class="toggle-column switch-input" data-column="2" checked>
                        <span class="switch-toggle-slider">
                            <span class="switch-on"><i class="bx bx-check"></i></span>
                            <span class="switch-off"><i class="bx bx-x"></i></span>
                        </span>
                        <span class="switch-label">Hora</span>
                    </label>
                </div>
                <div class="mx-3">
                    <label class="switch switch-square">
                        <input type="checkbox" class="toggle-column switch-input" data-column="3" checked>
                        <span class="switch-toggle-slider">
                            <span class="switch-on"><i class="bx bx-check"></i></span>
                            <span class="switch-off"><i class="bx bx-x"></i></span>
                        </span>
                        <span class="switch-label">Log de Caja</span>
                    </label>
                </div>
                <div class="mx-3">
                    <label class="switch switch-square">
                        <input type="checkbox" class="toggle-column switch-input" data-column="4" checked>
                        <span class="switch-toggle-slider">
                            <span class="switch-on"><i class="bx bx-check"></i></span>
                            <span class="switch-off"><i class="bx bx-x"></i></span>
                        </span>
                        <span class="switch-label">Ventas en Efectivo</span>
                    </label>
                </div>
                <div class="mx-3">
                    <label class="switch switch-square">
                        <input type="checkbox" class="toggle-column switch-input" data-column="5" checked>
                        <span class="switch-toggle-slider">
                            <span class="switch-on"><i class="bx bx-check"></i></span>
                            <span class="switch-off"><i class="bx bx-x"></i></span>
                        </span>
                        <span class="switch-label">Ventas POS</span>
                    </label>
                </div>
                <div class="mx-3">
                    <label class="switch switch-square">
                        <input type="checkbox" class="toggle-column switch-input" data-column="6" checked>
                        <span class="switch-toggle-slider">
                            <span class="switch-on"><i class="bx bx-check"></i></span>
                            <span class="switch-off"><i class="bx bx-x"></i></span>
                        </span>
                        <span class="switch-label">Descuento</span>
                    </label>
                </div>
                <div class="mx-3">
                    <label class="switch switch-square">
                        <input type="checkbox" class="toggle-column switch-input" data-column="7" checked>
                        <span class="switch-toggle-slider">
                            <span class="switch-on"><i class="bx bx-check"></i></span>
                            <span class="switch-off"><i class="bx bx-x"></i></span>
                        </span>
                        <span class="switch-label">Tipo de Cliente</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de órdenes POS -->
    <div class="card-datatable table-responsive p-3">
        <table class="table datatables-pos-orders border-top">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Log de Caja</th>
                    <th>Ventas en Efectivo</th>
                    <th>Ventas POS</th>
                    <th>Descuento</th>
                    <th>Tipo de Cliente</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($posOrders as $order)
                <tr>
                    <td>{{ $order->id }}</td>
                    <td>{{ $order->date }}</td>
                    <td>{{ $order->hour }}</td>
                    <td>{{ $order->cash_register_log_id }}</td>
                    <td>{{ $order->cash_sales }}</td>
                    <td>{{ $order->pos_sales }}</td>
                    <td>{{ $order->discount }}</td>
                    <td>{{ $order->client_type }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('page-script')
<script>
    $(document).ready(function() {
        var table = $('.datatables-pos-orders').DataTable({
            "order": [[ 0, "desc" ]] // Ordenar por ID de manera descendente
        });

        $('.toggle-column').on('change', function (e) {
            e.preventDefault();
            var column = table.column($(this).attr('data-column'));
            column.visible(!column.visible());
        });
    });
</script>
@endsection
