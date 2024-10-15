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
            <h5 class="card-title mb-0">Cajas Registradoras</h5>
        </div>
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
                    <th>Tienda</th>
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
                      {{ \Carbon\Carbon::parse($caja->open_time)->translatedFormat('d \d\e F Y') }}<br>
                      {{ \Carbon\Carbon::parse($caja->open_time)->format('h:i a') }}
                    </td>
                    <td class="text-center">
                      @if($caja->close_time == null)
                        <button class="btn btn-primary btn-closed" data-id="{{ $caja->id }}">Cerrar</button>
                        @else
                        {{ \Carbon\Carbon::parse($caja->close_time)->translatedFormat('d \d\e F Y') }}<br>
                        {{ \Carbon\Carbon::parse($caja->close_time)->format('h:i a') }}
                        @endif
                    </td>
                    <td>
                        @if($caja->close_time == null)
                            <span class="badge bg-success">Abierta</span>
                        @else
                            <span class="badge bg-danger">Cerrada</span>
                        @endif
                    </td>
                    <td>
                        <!-- Menú desplegable de tres puntos -->
                        <div class="dropdown">
                            <button class="btn btn-link text-muted p-0" type="button" id="dropdownMenuButton{{ $caja->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton{{ $caja->id }}">
                                <li>
                                    <button class="dropdown-item btn-open" data-id="{{ $caja->id }}">Abrir caja</button>
                                </li>
                                @hasrole('Administrador')
                                <li>
                                    <button class="dropdown-item btn-view" data-id="{{ $caja->id }}" data-store="{{ $caja->store_id }}" data-user="{{ $caja->user_id }}">Ver Detalles</button>
                                </li>
                                @endhasrole
                                <li>
                                    <button class="dropdown-item btn-edit" data-id="{{ $caja->id }}" data-store="{{ $caja->store_id }}" data-user="{{ $caja->user_id }}">Editar</button>
                                </li>
                                <li>
                                    <button class="dropdown-item btn-delete" data-id="{{ $caja->id }}">Eliminar</button>
                                </li>
                            </ul>
                        </div>
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
                    <label for="edit_store_id" class="form-label">ID de Tienda:</label>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- jQuery -->
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
                "infoPostFix": "",
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

        $('#crear-caja-btn').click(function() {
            $('#crearCajaModal').modal('show');
        });

        $.ajax({
            url: 'point-of-sale/stores',
            type: 'GET',
            success: function(response) {
                var storeIds = response;

                if (storeIds.length === 0) {
                    $('#crear-caja-btn').hide();
                } else {
                    var select = $('<select>', {
                        class: 'form-control',
                        id: 'store_id',
                        name: 'store_id',
                        required: true
                    });

                    $.each(storeIds, function(index, store) {
                        select.append($('<option>', {
                            value: store.id,
                            text: store.name,
                            selected: index === 0
                        }));
                    });

                    $('#crearCajaModal .modal-body .mb-3').html(select);

                    $('#crear-caja-btn').click(function() {
                        $('#crearCajaModal').modal('show');
                    });
                }
            },
            error: function(xhr, status, error) {
                alert('Error al obtener las Empresas: ' + xhr.responseText);
            }
        });

        $('#submit-crear-caja').click(function() {
            var storeId = $('#store_id').val();
            if (!storeId) {
                showError('Por favor, seleccione una tienda.');
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
                    location.reload();
                },
                error: function(xhr, status, error) {
                  showError('Error al crear la caja registradora: ' + xhr.responseText);
                }
            });
        });

        $('.btn-open').click(function() {
            var cashRegisterId = $(this).data('id');
            $('#cash_register_id').val(cashRegisterId);
            $('#abrirCajaModal').modal('show');
        });

        // Remover mensajes de error previos y mostrar nuevos mensajes
        function showError(message) {
            var errorMessage = $('<div>', {
                class: 'text-danger mt-2',
                text: message
            });
            $('#initial_amount').after(errorMessage);
        }

        // Limpiar mensajes de error antes de cada envío
        function clearErrors() {
            $('.text-danger').remove();
        }

        $('#submit-abrir-caja').click(function() {
            clearErrors();  // Limpiar mensajes de error previos

            var cashRegisterId = $('#cash_register_id').val();
            var initialAmount = $('#initial_amount').val();
            var csrfToken = $('meta[name="csrf-token"]').attr('content');

            if (!initialAmount || initialAmount <= 0) {
                showError('Por favor, ingrese un monto inicial válido.');
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
                    location.reload();
                },
                error: function(xhr, status, error) {
                    showError('Error al abrir la caja registradora: ' + xhr.responseText);
                }
            });
        });

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
                        location.reload();
                    },
                    error: function(xhr, status, error) {
                      showError('Error al eliminar la caja registradora: ' + xhr.responseText);
                    }
                });
            }
        });

        $('.btn-view').click(function() {
            var cashRegisterId = $(this).data('id');
            var baseUrl = "{{ url('/admin/point-of-sale/details') }}";
            window.location.href = baseUrl + '/' + cashRegisterId;
        });

        $('.btn-edit').click(function() {
            var id = $(this).data('id');
            var storeId = $(this).data('store');
            var userId = $(this).data('user');

            $('#edit_store_id').val(storeId);
            $('#edit_user_id').val(userId);
            $('#editarCajaModal').modal('show');

            $('#submit-editar-caja').click(function() {
                var updatedStoreId = $('#edit_store_id').val();
                var updatedUserId = $('#edit_user_id').val();
                var csrfToken = $('meta[name="csrf-token"]').attr('content');

                $.ajax({
                    url: 'points-of-sales/' + id,
                    type: 'PUT',
                    data: {
                        store_id: updatedStoreId,
                        user_id: updatedUserId,
                        _token: csrfToken
                    },
                    success: function(response) {
                        $('#editarCajaModal').modal('hide');
                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        showError('Error al actualizar la caja registradora: ' + xhr.responseText);
                    }
                });
            });
        });

        $('.btn-closed').click(function() {
            var cashRegisterId = $(this).data('id');
            $('#cash_register_id_close').val(cashRegisterId);
            $('#cerrarCajaModal').modal('show');
        });
    });
</script>
@endsection
