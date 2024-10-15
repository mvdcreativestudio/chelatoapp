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
              {{-- @php
                dd($cajas);
              @endphp --}}
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


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- jQuery -->
<script>
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

        // Mostrar el modal de crear al hacer clic en el botón de crear caja
        $('#crear-caja-btn').click(function() {
            $('#crearCajaModal').modal('show');
        });

        // Obtener los IDs de las tiendas para la caja registradora
        $.ajax({
            url: 'point-of-sale/stores',
            type: 'GET',
            success: function(response) {
                var storeIds = response; // Array con los IDs de las tiendas

                if (storeIds.length === 0) {
                    // Si el array está vacío, ocultar el botón de crear caja
                    $('#crear-caja-btn').hide();
                } else {
                    // Crear un select con las opciones
                    var select = $('<select>', {
                        class: 'form-control',
                        id: 'store_id',
                        name: 'store_id',
                        required: true
                    });

                    // Añadir las opciones de las tiendas
                    $.each(storeIds, function(index, store) {
                        select.append($('<option>', {
                            value: store.id,
                            text: store.name, // Usar el nombre de la tienda para mostrar en el select
                            selected: index === 0 // Seleccionar la primera tienda por defecto
                        }));
                    });

                    $('#crearCajaModal .modal-body .mb-3').html(select);

                    $('#crear-caja-btn').click(function() {
                        $('#crearCajaModal').modal('show');
                    });
                }
            },
            error: function(xhr, status, error) {
              const textToObject = JSON.parse(xhr.responseText);
              showModalError($('#crearCajaModal'), textToObject.message);
            }
        });

        // Mostrar mensaje de error en el modal correspondiente
        function showModalError(modal, message) {
            var errorMessage = $('<div>', {
                class: 'alert alert-danger mt-2',
                text: message
            });
            // Eliminar mensajes de error anteriores
            modal.find('.alert').remove();
            // Añadir nuevo mensaje de error
            modal.find('.modal-body').prepend(errorMessage);
        }

        // Enviar los datos de la nueva caja registradora al servidor
        $('#submit-crear-caja').click(function() {
            var storeId = $('#store_id').val();
            if (!storeId) {
                showModalError($('#crearCajaModal'), 'Por favor, seleccione una tienda.');
                return;
            }

            var csrfToken = $('meta[name="csrf-token"]').attr('content');

            $.ajax({
                url: 'points-of-sales',
                type: 'POST',
                data: {
                    store_id: storeId,
                    user_id: authenticatedUserId,
                    _token: csrfToken
                },
                success: function(response) {
                    $('#crearCajaModal').modal('hide');
                    location.reload(); // Recargar la página para reflejar los cambios
                },
                error: function(xhr, status, error) {
                    const textToObject = JSON.parse(xhr.responseText);
                    showModalError($('#crearCajaModal'), textToObject.message);
                }
            });
        });

        // Mostrar el modal para abrir la caja con el monto inicial
        $('.btn-open').click(function() {
            var cashRegisterId = $(this).data('id');
            $('#cash_register_id').val(cashRegisterId);
            $('#abrirCajaModal').modal('show');
        });

        // Enviar los datos para abrir la caja registradora
        $('#submit-abrir-caja').click(function() {
            var cashRegisterId = $('#cash_register_id').val();
            var initialAmount = $('#initial_amount').val();
            var csrfToken = $('meta[name="csrf-token"]').attr('content');

            if (!initialAmount) {
                showModalError($('#abrirCajaModal'), 'Por favor, ingrese un monto inicial.');
                return;
            }

            $.ajax({
                url: 'pdv/open',
                type: 'POST',
                data: {
                    cash_register_id: cashRegisterId,
                    cash_float: initialAmount,
                    _token: csrfToken
                },
                success: function(response) {
                    $('#abrirCajaModal').modal('hide');
                    window.location.href = '/admin/pdv/front'; // Redirigir a la página de PDV
                },
                error: function(xhr, status, error) {
                    const textToObject = JSON.parse(xhr.responseText);
                    showModalError($('#abrirCajaModal'), textToObject.message);
                }
            });
        });

        // Manejar la eliminación de la caja registradora
        $('.btn-delete').click(function() {
            var id = $(this).data('id');
            var csrfToken = $('meta[name="csrf-token"]').attr('content');

            if (confirm('¿Estás seguro de que deseas eliminar esta caja registradora?')) {
                $.ajax({
                    url: 'points-of-sales/' + id,
                    type: 'DELETE',
                    data: {
                        _token: csrfToken
                    },
                    success: function(response) {
                        location.reload(); // Recargar la página para reflejar los cambios
                    },
                    error: function(xhr, status, error) {
                        showModalError($('#editarCajaModal'), 'Error al eliminar la caja registradora: ' + xhr.responseText);
                    }
                });
            }
        });

        // Mostrar el modal de cierre al hacer clic en el botón "Cerrar"
        $('.btn-closed').click(function() {
            var cashRegisterId = $(this).data('id'); // Obtener el ID de la caja registradora
            $('#cash_register_id_close').val(cashRegisterId); // Asignar el ID al campo oculto en el modal
            $('#cerrarCajaModal').modal('show'); // Mostrar el modal de cierre de caja
        });

        // Enviar la solicitud para cerrar la caja registradora al hacer clic en el botón "Cerrar"
        $('#submit-cerrar-caja').click(function() {
            var cashRegisterId = $('#cash_register_id_close').val(); // Obtener el ID de la caja a cerrar
            var csrfToken = $('meta[name="csrf-token"]').attr('content'); // Obtener el token CSRF

            $.ajax({
                url: 'pdv/close/' + cashRegisterId, // URL de la solicitud para cerrar la caja
                type: 'POST',
                data: {
                    _token: csrfToken // Token CSRF para seguridad
                },
                success: function(response) {
                    $('#cerrarCajaModal').modal('hide'); // Ocultar el modal al cerrar la caja
                    location.reload(); // Recargar la página para reflejar los cambios
                },
                error: function(xhr, status, error) {
                    showModalError($('#cerrarCajaModal'), 'Error al cerrar la caja registradora: ' + xhr.responseText);
                }
            });
        });
    });
</script>
@endsection
