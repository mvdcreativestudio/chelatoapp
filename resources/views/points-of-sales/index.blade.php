@extends('layouts.layoutMaster')

@section('title', 'Listado de Cajas Registradoras')

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
    'resources/assets/vendor/libs/select2/select2.js'
])
<script>
    window.authenticatedUserId = @json($userId);
</script>
@endsection
@section('page-script')
@vite([
'resources/assets/js/points-of-sales/app-points-of-sales-list.js'
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
                    <th>Terminal POS</th>
                    <th>QR MercadoPago</th> <!-- Nueva columna -->
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
                      {{-- Utiliza el método del modelo para determinar el estado --}}
                      <span class="badge {{ $caja->getEstado()['clase'] }}">{{ $caja->getEstado()['estado'] }}</span>
                  </td>
                  <td class="text-center">
                    @if ($caja->posDevices->isNotEmpty())
                        <div class="d-flex flex-column align-items-center">
                            <!-- Lista de dispositivos POS -->
                            @foreach ($caja->posDevices as $posDevice)
                                <p class="mb-1">
                                    <strong>{{ $posDevice->name }}</strong>
                                    <br>
                                    <span class="text-muted">{{ $posDevice->identifier }}</span>
                                </p>
                            @endforeach

                            <!-- Botón para desvincular -->
                            <button
                                class="btn btn-outline-danger btn-sm mt-2 btn-unlink-pos"
                                data-id="{{ $caja->id }}"
                                data-store_id="{{ $caja->store_id }}"
                                data-pos-id="{{ $caja->posDevices->first()->id }}">
                                <i class="fas fa-unlink mx-1"></i> Desvincular
                            </button>
                        </div>
                    @else
                        <div class="d-flex flex-column align-items-center">
                            <!-- Icono para indicar que no hay dispositivos vinculados -->
                            <i class="fas fa-exclamation-circle text-warning fs-4 mb-2"></i>
                            <span class="text-muted mb-2">Sin terminal asignada</span>

                            <!-- Botón para vincular -->
                            <button
                                class="btn btn-outline-primary btn-sm btn-link-pos"
                                data-id="{{ $caja->id }}"
                                data-store_id="{{ $caja->store_id }}">
                                <i class="fas fa-link mx-1"></i> Vincular
                            </button>
                        </div>
                    @endif
                </td>
                <td class="text-center">
                        @if($caja->cash_register_id)
                            <a href="{{ $caja->template_document }}" target="_blank" class="btn btn-link text-primary" title="Ver QR MercadoPago">
                                <i class="fas fa-qrcode"></i>
                            </a>
                        @else
                            <span class="text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="Debe crear un POS para este punto de venta">-</span>
                        @endif
                  </td>



                  <td>
                    @php
                        // Verifica si hay acciones disponibles para mostrar
                        $accionesDisponibles = (
                            $caja->close_time != null ||
                            auth()->user()->hasRole('Administrador') ||
                            ($caja->open_time == null && $caja->close_time == null) // Permite abrir si no está iniciada
                        );
                    @endphp

                    @if($accionesDisponibles)
                    <!-- Menú desplegable de tres puntos -->
                    <div class="dropdown">
                        <button class="btn btn-link text-muted p-0" type="button" id="dropdownMenuButton{{ $caja->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton{{ $caja->id }}">
                            <!-- Mostrar "Abrir caja" si está cerrada o no iniciada -->
                            @if($caja->close_time != null || ($caja->open_time == null && $caja->close_time == null))
                            <li>
                                <button class="dropdown-item btn-open" data-id="{{ $caja->id }}">Abrir caja</button>
                            </li>
                            @endif
                            <!-- Mostrar "Cerrar caja" si está abierta -->
                            @if($caja->open_time != null && $caja->close_time == null)
                            <li>
                                <button class="dropdown-item btn-closed" data-id="{{ $caja->id }}">Cerrar caja</button>
                            </li>
                            @endif

                            <!-- Mostrar las acciones si el usuario tiene rol de Administrador -->
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

<!-- Modal para ingresar los datos de la caja registradora -->
<div class="modal fade" id="crearCajaModal" tabindex="-1" aria-labelledby="crearCajaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="crearCajaLabel">Crear Caja Registradora</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="store_id" class="form-label">ID de Empresa:</label>
                    <input type="text" id="store_id" name="store_id" class="form-control">
                </div>
                <input type="hidden" id="user_id" name="user_id" value="{{ $userId }}">
            </div>
            <div class="modal-footer">
                <button type="button" id="submit-crear-caja" class="btn btn-primary">Crear</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar la caja registradora -->
<div class="modal fade" id="editarCajaModal" tabindex="-1" aria-labelledby="editarCajaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarCajaLabel">Editar Caja Registradora</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="edit_store_id" class="form-label">ID de Empresa:</label>
                    <input type="text" id="edit_store_id" name="edit_store_id" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="edit_user_id" class="form-label">ID de Usuario:</label>
                    <input type="text" id="edit_user_id" name="edit_user_id" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="submit-editar-caja" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ingresar el monto inicial de la caja registradora -->
<div class="modal fade" id="abrirCajaModal" tabindex="-1" aria-labelledby="abrirCajaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="abrirCajaLabel">Abrir Caja Registradora</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="initial_amount" class="form-label">Monto Inicial:</label>
                    <input type="number" id="initial_amount" name="initial_amount" class="form-control" required>
                </div>
                <input type="hidden" id="cash_register_id" name="cash_register_id">
            </div>
            <div class="modal-footer">
                <button type="button" id="submit-abrir-caja" class="btn btn-primary">Abrir Caja</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para cerrar caja registradora -->
<div class="modal fade" id="cerrarCajaModal" tabindex="-1" aria-labelledby="cerrarCajaLabel" aria-hidden="true">
  <div class="modal-dialog">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title" id="cerrarCajaLabel">Cerrar Caja Registradora</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <p>¿Estás seguro de que deseas cerrar esta caja registradora?</p>
              <input type="hidden" id="cash_register_id_close" name="cash_register_id_close">
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="button" id="submit-cerrar-caja" class="btn btn-primary">Cerrar Caja</button>
          </div>
      </div>
  </div>
</div>
<!-- Modal para editar los datos del POS de MercadoPago -->
<div class="modal fade" id="editarPosModal" tabindex="-1" aria-labelledby="editarPosLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <input type="hidden" id="edit_pos_id" name="edit_pos_id">
            <input type="hidden" id="edit_store_id" name="edit_store_id">
            <div class="modal-header">
                <h5 class="modal-title" id="editarPosLabel">Editar Datos del POS de MercadoPago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="edit_pos_name" class="form-label">Nombre del POS</label>
                    <input type="text" id="edit_pos_name" name="edit_pos_name" class="form-control" required>
                </div>
                {{-- <div class="mb-3">
                    <label for="edit_fixed_amount" class="form-label">¿Monto Fijo?</label>
                    <select id="edit_fixed_amount" name="edit_fixed_amount" class="form-select" required>
                        <option value="false">No</option>
                        <option value="true">Sí</option>
                    </select>
                </div> --}}
                {{-- <div class="mb-3">
                    <label for="edit_store_id" class="form-label">ID de la Tienda (Interno Mercado Pago)</label>
                    <input type="text" id="edit_store_id" name="edit_store_id" class="form-control" readonly>
                </div> --}}
                {{-- <div class="mb-3">
                    <label for="edit_external_store_id" class="form-label">ID Externo de la Tienda</label>
                    <input type="text" id="edit_external_store_id" name="edit_external_store_id" class="form-control" readonly>
                </div> --}}
                {{-- <div class="mb-3">
                    <label for="edit_external_id" class="form-label">ID Externo del POS</label>
                    <input type="text" id="edit_external_id" name="edit_external_id" class="form-control" required>
                </div> --}}
                {{-- <div class="mb-3">
                    <label for="edit_category" class="form-label">Categoría</label>
                    <input type="number" id="edit_category" name="edit_category" class="form-control" required>
                </div> --}}
            </div>
            <div class="modal-footer">
                <button type="button" id="submit-editar-pos" class="btn btn-primary">Guardar Cambios</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para asignar un dispositivo POS a la caja registradora -->
<div class="modal fade" id="managePosModal" tabindex="-1" aria-labelledby="managePosModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="managePosModalLabel">Gestionar POS</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="pos_device" class="form-label">Dispositivo POS:</label>
          <select id="pos_device" class="form-select">
            <!-- Opciones dinámicas se cargarán aquí -->
          </select>
        </div>
        <input type="hidden" id="cash_register_id" name="cash_register_id">
      </div>
      <div class="modal-footer">
        <button type="button" id="submit-manage-pos" class="btn btn-primary">Guardar</button>
      </div>
    </div>
  </div>
</div>




<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- jQuery -->
<script>
    
</script>
<script>

$(document).on('click', '.btn-link-pos, .btn-edit-pos', function () {
    let cashRegisterId = $(this).data('id');
    let storeId = $(this).data('store_id'); // Obtén el store_id del atributo data-store_id
    $('#cash_register_id').val(cashRegisterId);

    // Filtrar los dispositivos POS según el store_id
    $.ajax({
        url: 'get-pos-devices', // Ruta para obtener los dispositivos POS
        type: 'GET',
        data: { store_id: storeId },
        success: function (devices) {
            let options = '';
            devices.forEach(device => {
                options += `<option value="${device.id}">${device.name} - ${device.identifier}</option>`;
            });
            $('#pos_device').html(options);
            $('#managePosModal').modal('show');
        },
        error: function () {
            alert('Error al cargar los dispositivos POS.');
        }
    });
});


$(document).on('click', '.btn-link-pos, .btn-edit-pos', function () {
    let cashRegisterId = $(this).data('id');
    $('#cash_register_id').val(cashRegisterId);
    $('#managePosModal').modal('show');
});

$('#submit-manage-pos').click(function () {
    let cashRegisterId = $('#cash_register_id').val();
    let posDeviceId = $('#pos_device').val();
    let csrfToken = $('meta[name="csrf-token"]').attr('content');

    $.ajax({
        url: 'cash-registers/' + cashRegisterId + '/link-pos',
        type: 'POST',
        data: {
            pos_device_id: posDeviceId,
            _token: csrfToken
        },
        success: function (response) {
            $('#managePosModal').modal('hide');
            location.reload();
        },
        error: function (xhr) {
            alert('Error al vincular el POS.');
        }
    });
});

$(document).on('click', '.btn-unlink-pos', function () {
    let cashRegisterId = $(this).data('id');
    let posDeviceId = $(this).data('pos-id'); // Asegúrate de que este atributo esté definido en el botón
    let csrfToken = $('meta[name="csrf-token"]').attr('content');

    if (confirm('¿Estás seguro de que deseas desvincular el POS?')) {
        $.ajax({
            url: `cash-registers/${cashRegisterId}/unlink-pos/${posDeviceId}`,
            type: 'DELETE',
            data: {
                _token: csrfToken
            },
            success: function () {
                location.reload();
            },
            error: function () {
                alert('Error al desvincular el POS.');
            }
        });
    }
});


</script>
@endsection
