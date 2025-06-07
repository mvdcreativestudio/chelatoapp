<!-- Modal para gestionar dispositivos de Scanntech -->
<div class="modal fade" id="scanntechDevicesModal" tabindex="-1" aria-labelledby="scanntechDevicesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scanntechDevicesModalLabel">Gestionar Dispositivos Scanntech</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Aquí puedes gestionar los dispositivos vinculados a Scanntech para esta tienda.</p>

                <!-- Botón para agregar un nuevo dispositivo -->
                <div class="mb-4">
                    <button type="button" class="btn btn-primary" id="addDeviceButton">Agregar nuevo dispositivo</button>
                </div>

                <!-- Verificar si hay dispositivos o no -->
                @if ($devices->isEmpty())
                    <div id="no-devices-message">
                        <p class="text-muted">No hay dispositivos vinculados a esta tienda.</p>
                    </div>
                @else
                    <div id="devices-list">
                        <div class="row">
                            @foreach ($devices as $device)
                                <div class="col-md-6">
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <h5 class="card-title">Dispositivo {{ $loop->iteration }}</h5>
                                            <p><strong>ID del Dispositivo:</strong> {{ $device->identifier }}</p>
                                            <p><strong>Usuario:</strong> {{ $device->user }}</p>
                                            <p><strong>Caja:</strong> {{ $device->cash_register }}</p>

                                            <!-- Botón para editar -->
                                            <button type="button" class="btn btn-sm btn-secondary editDeviceButton">Editar</button>

                                            <!-- Formulario de edición (oculto inicialmente) -->
                                            <div class="editDeviceForm" style="display: none; margin-top: 15px;">
                                                <form method="POST" action="{{ route('pos.devices.update', ['store' => $store->id, 'id' => $device->id]) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <!-- Campo oculto para el pos_provider_id -->
                                                    <input type="hidden" name="pos_provider_id" value="1">

                                                    <div class="mb-3">
                                                        <label for="deviceIdentifier{{ $device->id }}" class="form-label">ID del Dispositivo</label>
                                                        <input type="text" class="form-control" id="deviceIdentifier{{ $device->id }}" name="identifier" value="{{ $device->identifier }}" required>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="deviceUser{{ $device->id }}" class="form-label">Usuario</label>
                                                        <input type="text" class="form-control" id="deviceUser{{ $device->id }}" name="user" value="{{ $device->user }}" required>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="deviceCashRegister{{ $device->cash_register }}" class="form-label">Caja</label>
                                                        <input type="text" class="form-control" id="deviceCashRegister{{ $device->id }}" name="cash_register" value="{{ $device->cash_register }}" required>
                                                    </div>

                                                    <button type="submit" class="btn btn-primary btn-sm">Guardar Cambios</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Formulario para agregar un nuevo dispositivo -->
                <div id="addDeviceForm" style="display: none; margin-top: 20px;">
                    <form id="newDeviceForm" method="POST" action="{{ route('pos.devices.store', ['store' => $store->id]) }}">
                        @csrf
                        <input type="hidden" name="pos_provider_id" value="1"> <!-- Asignamos Scanntech como proveedor -->

                        <div class="mb-3">
                            <label for="deviceIdentifierNew" class="form-label">ID del Dispositivo</label>
                            <input type="text" class="form-control" id="deviceIdentifierNew" name="identifier" required>
                        </div>

                        <div class="mb-3">
                            <label for="deviceUserNew" class="form-label">Usuario</label>
                            <input type="text" class="form-control" id="deviceUserNew" name="user" required>
                        </div>

                        <div class="mb-3">
                            <label for="deviceCashRegisterNew" class="form-label">Caja</label>
                            <input type="text" class="form-control" id="deviceCashRegisterNew" name="cash_register" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Guardar Dispositivo</button>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const addDeviceButton = document.getElementById('addDeviceButton');
        const addDeviceForm = document.getElementById('addDeviceForm');
        const noDevicesMessage = document.getElementById('no-devices-message');

        if (addDeviceButton) {
            addDeviceButton.addEventListener('click', function () {
                // Mostrar el formulario de nuevo dispositivo
                addDeviceForm.style.display = 'block';
            });
        }

        // Mostrar el formulario de edición al hacer clic en "Editar"
        const editButtons = document.querySelectorAll('.editDeviceButton');
        editButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const editForm = this.nextElementSibling;
                editForm.style.display = editForm.style.display === 'none' ? 'block' : 'none';
            });
        });
    });
</script>
