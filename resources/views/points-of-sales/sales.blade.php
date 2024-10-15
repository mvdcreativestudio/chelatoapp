@extends('layouts.layoutMaster')

@section('title', 'Ventas de la Caja Registradora')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss'])
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])

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
                            <h4 class="ms-1 mb-0">{{ $totalSales }}</h4>
                        </div>
                        <p class="mb-1 fw-medium me-1">Ventas realizadas</p>
                    </div>
                </div>
            </div>
            <!-- Tarjeta de Ventas en efectivo -->
            <div class="col-sm-12 col-lg-4 mb-4">
                <div class="card card-border-shadow-info h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2 pb-1">
                            <div class="avatar me-2">
                                <span class="avatar-initial rounded bg-label-info"><i class='bx bx-money'></i></span>
                            </div>
                            <h4 class="ms-1 mb-0">{{ $settings->currency_symbol }}{{ number_format($cashSales, 0, ',', '.') }}</h4>
                        </div>
                        <p class="mb-1 fw-medium me-1">Ventas en efectivo</p>
                    </div>
                </div>
            </div>
            <!-- Tarjeta de Ventas por POS -->
            <div class="col-sm-12 col-lg-4 mb-4">
                <div class="card card-border-shadow-info h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2 pb-1">
                            <div class="avatar me-2">
                                <span class="avatar-initial rounded bg-label-info"><i class='bx bx-credit-card'></i></span>
                            </div>
                            <h4 class="ms-1 mb-0">{{ $settings->currency_symbol }}{{ number_format($posSales, 0, ',', '.') }}</h4>
                        </div>
                        <p class="mb-1 fw-medium me-1">Ventas por POS</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección de Ventas Realizadas -->
        <div class="bg-white p-4 shadow-sm rounded">
             <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="my-4">Ventas Realizadas</h2>
                <button id="export-pdf-btn" class="btn btn-label-primary" data-id="{{ $id }}">Exportar PDF</button>
             </div>
            <div class="d-flex">
                <p class="text-muted small">
                    <a href="" class="toggle-filter" data-bs-toggle="collapse">Filtrado por hora</a>
                </p>
            </div>
            <div class="d-flex align-items-center mb-4">
                <input type="time" id="start-time" name="start-time" class="form-control me-2" style="max-width: 150px;">
                <input type="time" id="end-time" name="end-time" class="form-control me-2" style="max-width: 150px;">
                <button id="filter-button" class="btn btn-primary">Buscar</button>
            </div>
            <!-- Controles para mostrar/ocultar columnas -->
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
                            <span class="switch-label">Ventas Efectivo</span>
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
                            <span class="switch-label">Descuento</span>
                        </label>
                    </div>
                    <div class="mx-3">
                        <label class="switch switch-square">
                            <input type="checkbox" class="toggle-column switch-input" data-column="6" checked>
                            <span class="switch-toggle-slider">
                                <span class="switch-on"><i class="bx bx-check"></i></span>
                                <span class="switch-off"><i class="bx bx-x"></i></span>
                            </span>
                            <span class="switch-label">ID Cliente</span>
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
                            <span class="switch-label">Notas</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="card-datatable table-responsive">
              <table id="cash-register-sales" class="table table-bordered table-hover bg-white">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Productos</th>
                        <th>Pago</th> <!-- Nueva columna para el tipo de pago -->
                        {{-- <th>Descuento</th> --}}
                        <th>Total</th>
                        <th>ID Cliente</th>
                        <th>Notas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr>
                        <td>{{ $sale->id }}</td>
                        <td>{{ \Carbon\Carbon::parse($sale->date)->translatedFormat('d \d\e F Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($sale->hour)->format('h:i a') }}</td>
                        <td>
                            @if (!empty($sale->products))
                                <ul>
                                    @foreach ($sale->products as $product)
                                        <li>{{ $product['name'] }} - {{ $settings->currency_symbol }}{{ number_format($product['price'], 0, ',', '.') }} x {{ $product['quantity'] }}</li>
                                    @endforeach
                                </ul>
                            @else
                                Sin productos
                            @endif
                        </td>
                        <td>
                            <!-- Mostrar el tipo de pago con un label -->
                            @if ($sale->cash_sales > 0)
                                <span class="badge bg-success">Efectivo</span>
                            @elseif ($sale->pos_sales > 0)
                                <span class="badge bg-primary">POS</span>
                            @else
                                <span class="badge bg-secondary">Otro</span>
                            @endif
                        </td>
                        {{-- <td>{{ $settings->currency_symbol }}{{ number_format($sale->discount, 0, ',', '.') }}</td> --}}
                        <td>{{ $settings->currency_symbol }}{{ number_format($sale->total, 0, ',', '.') }}</td>
                        <td>
                          @if($sale->client_id == null)
                            N/A
                          @else
                            {{ $sale->client_id }}
                          @endif
                        </td>
                        <td>{{ $sale->notes }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">No hay datos disponibles.</td>
                    </tr>
                    @endforelse
                </tbody>
              </table>
            </div>
        </div>
        @else
        <p>No tienes permiso para ver esta sección.</p>
        @endhasrole
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        var baseUrl = "{{ url('/') }}";

        document.addEventListener('DOMContentLoaded', function() {
            const filterButton = document.getElementById('filter-button');
            const table = document.getElementById('cash-register-sales');

            filterButton.addEventListener('click', function() {
                const startTime = document.getElementById('start-time').value;
                const endTime = document.getElementById('end-time').value;

                if (!startTime || !endTime) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ambas horas deben ser ingresadas para realizar el filtrado.',
                        customClass: {
                        confirmButton: 'btn btn-danger'
                        }
                    });
                    return;
                }

                const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

                for (let i = 0; i < rows.length; i++) {
                    const hourCell = rows[i].getElementsByTagName('td')[2];
                    if (hourCell) {
                        const hourValue = hourCell.textContent || hourCell.innerText;
                        if (hourValue >= startTime && hourValue <= endTime) {
                            rows[i].style.display = '';
                        } else {
                            rows[i].style.display = 'none';
                        }
                    }
                }
            });
        });
        $(document).ready(function() {
            // Al hacer clic en un checkbox para mostrar/ocultar columnas
            $('.toggle-column').on('change', function() {
                var column = $(this).data('column'); // Número de la columna
                var table = $('table');

                // Mostrar u ocultar la columna
                table.find('tr').each(function() {
                    $(this).find('td, th').eq(column).toggle();
                });
            });

        $('#export-pdf-btn').on('click', function () {
            var id = $(this).data('id'); // Obtener el ID de la caja registradora

            $.ajax({
                url: baseUrl + 'admin/point-of-sale/details/sales/pdf/' + + id,
                method: 'GET',
                xhrFields: {
                    responseType: 'blob'
                },
                success: function (response) {
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(response);
                    link.download = 'cash_register_sales_log_'+id+'.pdf';

                    document.body.appendChild(link);
                    link.click();

                    document.body.removeChild(link);
                },
                error: function (xhr) {
                    console.error('Error al generar el PDF:', xhr);
                    Swal.fire(
                        'Error!',
                        'No se pudo generar el PDF. Intente de nuevo',
                        'error'
                );}
            });
        });

        });
    </script>
@endsection
