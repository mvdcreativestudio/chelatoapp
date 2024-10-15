@extends('layouts.layoutMaster')

@section('title', 'Detalles de la Caja Registradora')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
@endsection
@section('content')
    <div class="container-fluid my-5">
    @hasrole('Administrador')
        <!-- Sección de tarjetas -->
        <div class="row mb-4">
            <!-- Tarjeta de Ventas realizadas -->
            <div class="col-sm-12 col-lg-4 mb-4">
                <div class="card card-border-shadow-info h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2 pb-1">
                            <div class="avatar me-2">
                                <span class="avatar-initial rounded bg-label-info"><i class='bx bx-time'></i></span>
                            </div>
                            <h4 class="ms-1 mb-0">{{ $details->count() }}</h4>
                        </div>
                        <p class="mb-1 fw-medium me-1">Registros de caja</p>
                    </div>
                </div>
            </div>
            <!-- Tarjeta de logs de caja abiertos -->
            <div class="col-sm-12 col-lg-4 mb-4">
                <div class="card card-border-shadow-info h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2 pb-1">
                            <div class="avatar me-2">
                                 <span class="avatar-initial rounded bg-label-info"><i class='fas fa-cash-register'></i></span>
                            </div>
                            <h4 class="ms-1 mb-0">{{$openCount}}</h4>
                        </div>
                        <p class="mb-1 fw-medium me-1">Registros de cajas abiertas</p>
                    </div>
                </div>
            </div>
            <!-- Tarjeta de logs de cajas cerradas -->
                <div class="col-sm-12 col-lg-4 mb-4">
                    <div class="card card-border-shadow-info h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2 pb-1">
                                <div class="avatar me-2">
                                     <span class="avatar-initial rounded bg-label-info"><i class='fas fa-lock'></i></span>
                                </div>
                                <h4 class="ms-1 mb-0">{{$closedCount}}</h4>
                            </div>
                            <p class="mb-1 fw-medium me-1">Registros de cajas cerradas</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 shadow-sm rounded">

            <!-- Tarjeta de Información de la Caja Registradora -->
            <div class="card mb-4">
              <div class="card-body">
                  <div class="d-flex align-items-center">
                      <div class="avatar avatar-lg me-3">
                          <span class="avatar-initial rounded-circle bg-label-info"><i class="bx bx-user"></i></span>
                      </div>
                      <div>
                          <h4 class="mb-1">Caja Registradora ID: {{ $cashRegister->id }}</h4>
                          <p class="mb-0 text-muted">Operada por: <strong>{{ $cashRegister->user->name }}</strong></p>
                      </div>
                  </div>
              </div>
            </div>
              <div class="d-flex">
                    <p class="text-muted small">
                        <a href="" class="toggle-switches" data-bs-toggle="collapse" data-bs-target="#columnSwitches"
                            aria-expanded="false" aria-controls="columnSwitches">Ver / Ocultar columnas de la tabla</a>
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
                                <span class="switch-label">Hora de Apertura</span>
                            </label>
                        </div>
                        <div class="mx-3">
                            <label class="switch switch-square">
                                <input type="checkbox" class="toggle-column switch-input" data-column="2" checked>
                                <span class="switch-toggle-slider">
                                    <span class="switch-on"><i class="bx bx-check"></i></span>
                                    <span class="switch-off"><i class="bx bx-x"></i></span>
                                </span>
                                <span class="switch-label">Hora de Cierre</span>
                            </label>
                        </div>
                        <div class="mx-3">
                            <label class="switch switch-square">
                                <input type="checkbox" class="toggle-column switch-input" data-column="3" checked>
                                <span class="switch-toggle-slider">
                                    <span class="switch-on"><i class="bx bx-check"></i></span>
                                    <span class="switch-off"><i class="bx bx-x"></i></span>
                                </span>
                                <span class="switch-label">Ventas en Efectivo</span>
                            </label>
                        </div>
                        <div class="mx-3">
                            <label class="switch switch-square">
                                <input type="checkbox" class="toggle-column switch-input" data-column="4" checked>
                                <span class="switch-toggle-slider">
                                    <span class="switch-on"><i class="bx bx-check"></i></span>
                                    <span class="switch-off"><i class="bx bx-x"></i></span>
                                </span>
                                <span class="switch-label">Ventas POS</span>
                            </label>
                        </div>
                        <div class="mx-3">
                            <label class="switch switch-square">
                                <input type="checkbox" class="toggle-column switch-input" data-column="5" checked>
                                <span class="switch-toggle-slider">
                                    <span class="switch-on"><i class="bx bx-check"></i></span>
                                    <span class="switch-off"><i class="bx bx-x"></i></span>
                                </span>
                                <span class="switch-label">Fondo de caja</span>
                            </label>
                        </div>
                        <div class="mx-3">
                            <label class="switch switch-square">
                                <input type="checkbox" class="toggle-column switch-input" data-column="6" checked>
                                <span class="switch-toggle-slider">
                                    <span class="switch-on"><i class="bx bx-check"></i></span>
                                    <span class="switch-off"><i class="bx bx-x"></i></span>
                                </span>
                                <span class="switch-label">Estado</span>
                            </label>
                        </div>
                        <div class="mx-3">
                            <label class="switch switch-square">
                                <input type="checkbox" class="toggle-column switch-input" data-column="7" checked>
                                <span class="switch-toggle-slider">
                                    <span class="switch-on"><i class="bx bx-check"></i></span>
                                    <span class="switch-off"><i class="bx bx-x"></i></span>
                                </span>
                                <span class="switch-label">Total</span>
                            </label>
                        </div>
                        <div class="mx-3">
                            <label class="switch switch-square">
                                <input type="checkbox" class="toggle-column switch-input" data-column="8" checked>
                                <span class="switch-toggle-slider">
                                    <span class="switch-on"><i class="bx bx-check"></i></span>
                                    <span class="switch-off"><i class="bx bx-x"></i></span>
                                </span>
                                <span class="switch-label">Ver Ventas</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card-datatable table-responsive">
                    <table id="cash-register-details" class="table table-bordered table-hover bg-white">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Hora de Apertura</th>
                                <th>Hora de Cierre</th>
                                <th>Ventas en Efectivo</th>
                                <th>Ventas POS</th>
                                <th>Fondo de caja</th>
                                <th>Estado</th>
                                <th>Total</th>
                                <th>Ver Ventas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($details as $detail)
                                <tr>
                                    <td>{{ $detail->id }}</td>
                                    <td class="text-center">
                                        <strong>{{ \Carbon\Carbon::parse($detail->open_time)->translatedFormat('d \d\e F Y') }}</strong><br>
                                        {{ \Carbon\Carbon::parse($detail->open_time)->format('h:i a') }}
                                    </td>
                                    <td class="text-center">
                                        @if($detail->close_time)
                                            <strong>{{ \Carbon\Carbon::parse($detail->close_time)->translatedFormat('d \d\e F Y') }}</strong><br>
                                          {{ \Carbon\Carbon::parse($detail->close_time)->format('h:i a') }}
                                        @else
                                            No ha cerrado.
                                        @endif
                                    </td>
                                    <td>{{ $settings->currency_symbol }}{{ number_format( $detail->cash_sales , 0, ',', '.') }}</td>
                                    <td>{{ $settings->currency_symbol }}{{ number_format( $detail->pos_sales , 0, ',', '.') }}</td>
                                    <td>{{ $settings->currency_symbol }}{{ number_format( $detail->cash_float , 0, ',', '.') }}</td>
                                    <td>
                                        @if (is_null($detail->close_time))
                                            <span class="badge bg-success">ABIERTA</span>
                                        @else
                                            <span class="badge bg-danger">CERRADA</span>
                                        @endif
                                    </td>
                                    <td>{{ $settings->currency_symbol }}{{ number_format($detail->cash_sales + $detail->pos_sales + $detail->cash_float, 0, ',', '.') }}</td>
                                    <td>
                                        <button class="btn btn-outline-primary btn-view-sales"
                                            data-id="{{ $detail->id }}">
                                            &rarr;
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
        </div>
        @else
        <p>No tienes permiso para ver esta sección.</p>
        @endhasrole
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        var baseUrl = "{{ url('/') }}";

        $(document).ready(function() {
            $('.btn-view-sales').click(function() {
                var detailId = $(this).data('id');
                window.location.href = baseUrl + 'admin/point-of-sale/details/sales/' +
                detailId; // Construye la URL completa
            });

            $('#cash-register-details').DataTable({
                // Opciones de DataTables
                responsive: true,
                autoWidth: false,
                order: [
                    [0, 'desc']
                ], // Ordenar por la primera columna (ID)
                language: {
                    search: "Buscar:",
                    lengthMenu: "Mostrar _MENU_ registros por página",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    paginate: {
                        first: "Primero",
                        last: "Último",
                        next: "Siguiente",
                        previous: "Anterior"
                    },
                    zeroRecords: "No se encontraron resultados",
                    infoEmpty: "No hay registros disponibles",
                    infoFiltered: "(filtrado de _MAX_ registros totales)"
                },
                columnDefs: [{
                        targets: [8],
                        orderable: false
                    } // Deshabilitar la ordenación en la columna "Ver Ventas"
                ], // Deshabilitar la ordenación en la columna "Ver Ventas"
                dom: '<"row"<"col-sm-12 col-md-6"f><"col-sm-12 col-md-6 text-end"l>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                initComplete: function() {
                    $('.dataTables_filter input').addClass('form-control'); // Add form-control class to search input
                    $('.dataTables_length select').addClass('form-select'); // Add form-select class to page selector
                    $('.dataTables_filter').addClass('text-start'); // Align search bar to the left
                }
            });

            $('.toggle-column').on('change', function() {
                var column = $(this).data('column'); // Número de la columna
                var table = $('table');

                // Mostrar u ocultar la columna
                table.find('tr').each(function() {
                    if ($(this).find('td, th').eq(column).is(':visible')) {
                        $(this).find('td, th').eq(column).toggle();
                    } else {
                        $(this).find('td, th').eq(column).toggle();
                    }
                });
            });
        });
    </script>
@endsection
