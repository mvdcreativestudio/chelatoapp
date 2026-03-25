@extends('layouts.layoutMaster')

@section('title', 'Listado de Cajas Registradoras')

@section('vendor-style')
@vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
    'resources/assets/vendor/libs/select2/select2.scss',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
])
@endsection

@section('vendor-script')
@vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
    'resources/assets/vendor/libs/select2/select2.js',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
])
@endsection

@section('content')
<h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Gestión /</span> Listado de Cajas Registradoras
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

<!-- Contenedor para el botón y la tabla -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <button id="crear-caja-btn" class="btn btn-primary">Nueva Caja</button>
            <a href="{{ route('pos-orders.index') }}" class="btn btn-secondary">Movimientos</a>
        </div>
    </div>

    <!-- Tabla de cajas registradoras -->
    <div class="card-datatable table-responsive p-3">
        <table id="cash-registers-table" class="table table-bordered table-hover bg-white">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Empresa</th>
                    <th>Usuario</th>
                    <th>Ultima Apertura</th>
                    <th>Ultimo Cierre</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
              @foreach ($cajas as $caja)
              <tr>
                  <td>{{ $caja->id }}</td>
                  <td>{{ $caja->store_name }}</td>
                  <td>{{ $caja->user_name }}</td>
                  <td class="text-center">
                      @if($caja->open_time)
                          {{ \Carbon\Carbon::parse($caja->open_time)->translatedFormat('d \d\e F Y') }}<br>
                          {{ \Carbon\Carbon::parse($caja->open_time)->format('h:i a') }}
                      @else
                          <span class="text-muted">N/A</span>
                      @endif
                  </td>
                  <td class="text-center">
                      @if($caja->close_time)
                          {{ \Carbon\Carbon::parse($caja->close_time)->translatedFormat('d \d\e F Y') }}<br>
                          {{ \Carbon\Carbon::parse($caja->close_time)->format('h:i a') }}
                      @else
                          <span class="text-muted">N/A</span>
                      @endif
                  </td>
                  <td>
                      <span class="badge {{ $caja->getEstado()['clase'] }}">{{ $caja->getEstado()['estado'] }}</span>
                  </td>
                  <td>
                    @php
                        $accionesDisponibles = (
                            $caja->close_time != null ||
                            auth()->user()->hasRole('Administrador') ||
                            ($caja->open_time == null && $caja->close_time == null)
                        );
                    @endphp

                    @if($accionesDisponibles)
                    <div class="dropdown">
                        <button class="btn btn-link text-muted p-0" type="button" id="dropdownMenuButton{{ $caja->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton{{ $caja->id }}">
                            @if($caja->close_time != null || ($caja->open_time == null && $caja->close_time == null))
                            <li>
                                <button class="dropdown-item btn-open" data-id="{{ $caja->id }}">Abrir caja</button>
                            </li>
                            @endif
                            @if($caja->open_time != null && $caja->close_time == null)
                            <li>
                                <button class="dropdown-item btn-closed" data-id="{{ $caja->id }}">Cerrar caja</button>
                            </li>
                            @endif

                            @hasrole('Administrador')
                            <li>
                                <button class="dropdown-item btn-view" data-id="{{ $caja->id }}" data-store="{{ $caja->store_id }}" data-user="{{ $caja->user_id }}">Ver Detalles</button>
                            </li>
                            <li>
                                <button class="dropdown-item btn-delete" data-id="{{ $caja->id }}">Eliminar</button>
                            </li>
                            @endhasrole
                        </ul>
                    </div>
                    @endif
                </td>
              </tr>
              @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para crear caja registradora -->
<div class="modal fade" id="crearCajaModal" tabindex="-1" aria-labelledby="crearCajaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="crearCajaLabel">Crear Caja Registradora</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="store_id" class="form-label">Empresa:</label>
                    <div id="store-select-container">
                        <select class="form-control" id="store_id" name="store_id" required>
                            <option value="">Cargando empresas...</option>
                        </select>
                    </div>
                </div>
                <input type="hidden" id="user_id" name="user_id" value="{{ $userId }}">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="submit-crear-caja" class="btn btn-primary">Crear</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para abrir caja registradora (con nombre) -->
<div class="modal fade" id="abrirCajaModal" tabindex="-1" aria-labelledby="abrirCajaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="abrirCajaLabel">Abrir Caja Registradora</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="cash_register_name" class="form-label">Nombre de la apertura:</label>
                    <input type="text" id="cash_register_name" name="name" class="form-control" placeholder="Ej: Turno Mañana, Caja Principal..." required>
                    <small class="text-muted">Un nombre descriptivo para identificar esta sesión de caja</small>
                </div>
                <div class="mb-3">
                    <label for="initial_amount" class="form-label">Monto Inicial (Fondo de caja):</label>
                    <input type="number" id="initial_amount" name="initial_amount" class="form-control" step="0.01" min="0" required>
                </div>
                <input type="hidden" id="cash_register_id" name="cash_register_id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="submit-abrir-caja" class="btn btn-primary">Abrir Caja</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para cerrar caja registradora (con verificación) -->
<div class="modal fade" id="cerrarCajaModal" tabindex="-1" aria-labelledby="cerrarCajaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="cerrarCajaLabel"><i class="bx bx-lock me-2"></i>Cerrar Caja Registradora</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Resumen de la caja -->
                <div id="cash-register-summary">
                    <div class="text-center py-3" id="summary-loading">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Cargando resumen de caja...</p>
                    </div>
                    <div id="summary-content" style="display: none;">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body p-3">
                                        <h6 class="text-muted mb-2">Tienda</h6>
                                        <p class="mb-0 fw-semibold" id="summary-store">-</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body p-3">
                                        <h6 class="text-muted mb-2">Apertura</h6>
                                        <p class="mb-0 fw-semibold" id="summary-open-time">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h6 class="mb-3 text-primary">Resumen de Ventas</h6>
                        <div class="table-responsive mb-3">
                            <table class="table table-sm">
                                <tbody>
                                    <tr><td>Fondo de Caja</td><td class="text-end fw-semibold" id="summary-cash-float">$0</td></tr>
                                    <tr><td><i class="bx bx-money text-success me-1"></i> Efectivo</td><td class="text-end fw-semibold" id="summary-cash-sales">$0</td></tr>
                                    <tr><td><i class="bx bx-credit-card text-info me-1"></i> POS (Créd/Déb)</td><td class="text-end fw-semibold" id="summary-pos-sales">$0</td></tr>
                                    <tr><td><i class="bx bxl-paypal text-primary me-1"></i> Mercadopago</td><td class="text-end fw-semibold" id="summary-mp-sales">$0</td></tr>
                                    <tr><td><i class="bx bx-transfer text-warning me-1"></i> Transferencias</td><td class="text-end fw-semibold" id="summary-transfer-sales">$0</td></tr>
                                    <tr><td><i class="bx bx-notepad text-secondary me-1"></i> Cuenta Corriente</td><td class="text-end fw-semibold" id="summary-credit-sales">$0</td></tr>
                                    <tr class="table-primary"><td><strong>Total Ventas</strong></td><td class="text-end fw-bold" id="summary-total-sales">$0</td></tr>
                                    <tr class="table-danger"><td><i class="bx bx-wallet text-danger me-1"></i> <strong>Gastos</strong></td><td class="text-end fw-bold text-danger" id="summary-expenses">$0</td></tr>
                                    <tr class="table-success"><td><strong>Efectivo esperado en caja</strong></td><td class="text-end fw-bold text-success" id="summary-expected-cash">$0</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <hr>
                        <h6 class="mb-3">Verificación de Efectivo</h6>
                        <div class="mb-3">
                            <label for="actual_cash_input" class="form-label">Efectivo real contado en caja:</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $settings->currency_symbol ?? '$' }}</span>
                                <input type="number" id="actual_cash_input" class="form-control" step="0.01" min="0" placeholder="Ingrese el monto contado">
                            </div>
                        </div>
                        <div id="cash-difference-display" class="alert d-none" role="alert">
                            <span id="cash-difference-text"></span>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="cash_register_id_close" name="cash_register_id_close">
                <input type="hidden" id="cash_register_log_id_close">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="submit-cerrar-caja" class="btn btn-danger">
                    <i class="bx bx-lock me-1"></i> Confirmar Cierre
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('page-script')
<script>
    var currencySymbol = '{{ $settings->currency_symbol ?? "$" }}';

    $(document).ready(function() {
        $('#cash-registers-table').DataTable({
            "order": [[ 0, "desc" ]],
            "language": {
                "processing": "Procesando...",
                "search": "Buscar:",
                "lengthMenu": "Mostrar _MENU_ registros",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                "infoFiltered": "(filtrado de _MAX_ registros totales)",
                "loadingRecords": "Cargando...",
                "zeroRecords": "No se encontraron registros coincidentes",
                "emptyTable": "No hay datos disponibles en la tabla",
                "paginate": {
                    "first": "Primero",
                    "previous": "Anterior",
                    "next": "Siguiente",
                    "last": "Último"
                },
            }
        });

        var authenticatedUserId = @json($userId);
        var csrfToken = $('meta[name="csrf-token"]').attr('content');

        function showModalError(modal, message) {
            var errorMessage = $('<div>', { class: 'alert alert-danger mt-2', text: message });
            modal.find('.alert').remove();
            modal.find('.modal-body').prepend(errorMessage);
        }

        function numberFormat(number) {
            return new Intl.NumberFormat('es-ES', { minimumFractionDigits: 0 }).format(number || 0);
        }

        // Crear caja - cargar stores
        $('#crear-caja-btn').click(function() {
            $('#crearCajaModal').modal('show');
        });

        // Cargar stores en el select del modal de crear caja
        $.ajax({
            url: 'point-of-sale/stores',
            type: 'GET',
            success: function(response) {
                var storeIds = response;
                if (storeIds.length === 0) {
                    $('#crear-caja-btn').hide();
                } else {
                    var select = $('<select>', { class: 'form-control', id: 'store_id', name: 'store_id', required: true });
                    $.each(storeIds, function(index, store) {
                        select.append($('<option>', { value: store.id, text: store.name, selected: index === 0 }));
                    });
                    $('#store-select-container').html(select);
                }
            },
            error: function() {
                $('#store-select-container').html('<p class="text-danger mb-0">Error al cargar empresas.</p>');
            }
        });

        // Enviar crear caja
        $('#submit-crear-caja').click(function() {
            var storeId = $('#store_id').val();
            if (!storeId) {
                showModalError($('#crearCajaModal'), 'Por favor, seleccione una tienda.');
                return;
            }

            $.ajax({
                url: 'points-of-sales',
                type: 'POST',
                data: { store_id: storeId, user_id: authenticatedUserId, _token: csrfToken },
                success: function(response) {
                    $('#crearCajaModal').modal('hide');
                    location.reload();
                },
                error: function(xhr) {
                    const textToObject = JSON.parse(xhr.responseText);
                    showModalError($('#crearCajaModal'), textToObject.message);
                }
            });
        });

        // Abrir caja
        $(document).on('click', '.btn-open', function() {
            var cashRegisterId = $(this).data('id');
            $('#cash_register_id').val(cashRegisterId);
            $('#cash_register_name').val('');
            $('#initial_amount').val('');
            $('#abrirCajaModal .alert').remove();
            $('#abrirCajaModal').modal('show');
        });

        // Enviar abrir caja
        $('#submit-abrir-caja').click(function() {
            var cashRegisterId = $('#cash_register_id').val();
            var initialAmount = $('#initial_amount').val();
            var name = $('#cash_register_name').val();

            if (!name || name.trim() === '') {
                showModalError($('#abrirCajaModal'), 'Por favor, ingrese un nombre para la apertura de caja.');
                return;
            }
            if (!initialAmount && initialAmount !== '0') {
                showModalError($('#abrirCajaModal'), 'Por favor, ingrese un monto inicial.');
                return;
            }

            $.ajax({
                url: 'pdv/open',
                type: 'POST',
                data: {
                    cash_register_id: cashRegisterId,
                    cash_float: initialAmount,
                    name: name,
                    _token: csrfToken
                },
                success: function(response) {
                    $('#abrirCajaModal').modal('hide');
                    window.location.href = '/admin/pdv/front';
                },
                error: function(xhr) {
                    try {
                        const textToObject = JSON.parse(xhr.responseText);
                        showModalError($('#abrirCajaModal'), textToObject.message);
                    } catch(e) {
                        showModalError($('#abrirCajaModal'), 'Error al abrir la caja registradora.');
                    }
                }
            });
        });

        // Cerrar caja - cargar resumen
        $(document).on('click', '.btn-closed', function() {
            var cashRegisterId = $(this).data('id');
            $('#cash_register_id_close').val(cashRegisterId);
            $('#actual_cash_input').val('');
            $('#cash-difference-display').addClass('d-none');
            $('#summary-loading').show();
            $('#summary-content').hide();
            $('#cerrarCajaModal').modal('show');

            // Cargar resumen desde el endpoint
            $.ajax({
                url: 'pdv/log/' + cashRegisterId,
                type: 'GET',
                success: function(logData) {
                    if (logData && logData.cash_register_log_id) {
                        $('#cash_register_log_id_close').val(logData.cash_register_log_id);

                        // Cargar detalles completos
                        $.ajax({
                            url: 'cash-register-logs/' + logData.cash_register_log_id + '/details',
                            type: 'GET',
                            success: function(response) {
                                if (response.success) {
                                    var d = response.details;
                                    $('#summary-store').text(d.store_name);
                                    $('#summary-open-time').text(d.open_time);
                                    $('#summary-cash-float').text(currencySymbol + numberFormat(d.cash_float));
                                    $('#summary-cash-sales').text(currencySymbol + numberFormat(d.cash_sales));
                                    $('#summary-pos-sales').text(currencySymbol + numberFormat(d.pos_sales));
                                    $('#summary-mp-sales').text(currencySymbol + numberFormat(d.mercadopago_sales));
                                    $('#summary-transfer-sales').text(currencySymbol + numberFormat(d.bank_transfer_sales));
                                    $('#summary-credit-sales').text(currencySymbol + numberFormat(d.internal_credit_sales));
                                    $('#summary-total-sales').text(currencySymbol + numberFormat(d.total_sales));
                                    $('#summary-expenses').text('-' + currencySymbol + numberFormat(d.total_expenses));
                                    $('#summary-expected-cash').text(currencySymbol + numberFormat(d.final_cash_balance));

                                    // Guardar datos para cálculo de diferencia
                                    $('#cerrarCajaModal').data('expected-cash', d.final_cash_balance);

                                    $('#summary-loading').hide();
                                    $('#summary-content').show();
                                }
                            },
                            error: function() {
                                $('#summary-loading').html('<p class="text-danger">Error al cargar el resumen.</p>');
                            }
                        });
                    }
                },
                error: function() {
                    $('#summary-loading').html('<p class="text-danger">No se encontró una caja abierta.</p>');
                }
            });
        });

        // Calcular diferencia cuando se ingresa efectivo real
        $('#actual_cash_input').on('input', function() {
            var actualCash = parseFloat($(this).val()) || 0;
            var expectedCash = parseFloat($('#cerrarCajaModal').data('expected-cash')) || 0;
            var difference = actualCash - expectedCash;

            var diffDisplay = $('#cash-difference-display');
            var diffText = $('#cash-difference-text');

            diffDisplay.removeClass('d-none alert-success alert-danger alert-info');

            if (difference === 0) {
                diffDisplay.addClass('alert-info');
                diffText.html('<i class="bx bx-check-circle me-1"></i> <strong>Cuadra perfecto.</strong> No hay diferencia.');
            } else if (difference > 0) {
                diffDisplay.addClass('alert-success');
                diffText.html('<i class="bx bx-up-arrow-alt me-1"></i> <strong>Sobrante:</strong> ' + currencySymbol + numberFormat(Math.abs(difference)));
            } else {
                diffDisplay.addClass('alert-danger');
                diffText.html('<i class="bx bx-down-arrow-alt me-1"></i> <strong>Faltante:</strong> ' + currencySymbol + numberFormat(Math.abs(difference)));
            }
        });

        // Enviar cierre
        $('#submit-cerrar-caja').click(function() {
            var cashRegisterId = $('#cash_register_id_close').val();
            var actualCash = $('#actual_cash_input').val();
            var expectedCash = parseFloat($('#cerrarCajaModal').data('expected-cash')) || 0;
            var cashDifference = actualCash ? (parseFloat(actualCash) - expectedCash) : 0;

            $.ajax({
                url: 'pdv/close/' + cashRegisterId,
                type: 'POST',
                data: {
                    _token: csrfToken,
                    actual_cash: actualCash || null,
                    cash_difference: cashDifference
                },
                success: function(response) {
                    $('#cerrarCajaModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Caja cerrada',
                        text: response.message,
                        confirmButtonClass: 'btn btn-primary'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    try {
                        const textToObject = JSON.parse(xhr.responseText);
                        showModalError($('#cerrarCajaModal'), textToObject.message);
                    } catch(e) {
                        showModalError($('#cerrarCajaModal'), 'Error al cerrar la caja registradora.');
                    }
                }
            });
        });

        // Ver detalles
        $(document).on('click', '.btn-view', function() {
            var id = $(this).data('id');
            window.location.href = 'point-of-sale/details/' + id;
        });

        // Eliminar caja
        $(document).on('click', '.btn-delete', function() {
            var id = $(this).data('id');
            Swal.fire({
                title: 'Eliminar caja',
                text: '¿Estás seguro de que deseas eliminar esta caja registradora?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonClass: 'btn btn-danger',
                cancelButtonClass: 'btn btn-secondary',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'points-of-sales/' + id,
                        type: 'DELETE',
                        data: { _token: csrfToken },
                        success: function() { location.reload(); },
                        error: function(xhr) {
                            Swal.fire('Error', 'No se pudo eliminar la caja registradora.', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
