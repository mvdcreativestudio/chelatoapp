<div class="modal fade" id="handyModal" tabindex="-1" aria-labelledby="handyModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title" id="handyModalLabel">Terminales de Handy</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <table class="table table-striped" id="handyTerminalsTable">
                  <thead>
                      <tr>
                          <th>Nombre</th>
                          <th>Identificador</th>
                          <th>Usuario</th>
                          <th>Caja</th>
                          <th>Acciones</th>
                      </tr>
                  </thead>
                  <tbody>
                      @foreach($devices as $device)
                      <tr data-id="{{ $device->id }}">
                          <td><input type="text" class="form-control handy-terminal-name" value="{{ $device->name }}" /></td>
                          <td><input type="text" class="form-control handy-terminal-identifier" value="{{ $device->identifier }}" /></td>
                          <td><input type="text" class="form-control handy-terminal-user" value="{{ $device->user }}" /></td>
                          <td><input type="text" class="form-control handy-terminal-cash-register" value="{{ $device->cash_register }}" /></td>
                          <td class="text-center">
                              <button type="button" class="btn btn-outline-danger btn-sm handy-remove-terminal"><i class="bx bx-trash"></i></button>
                          </td>
                      </tr>
                      @endforeach
                  </tbody>
              </table>
              <button type="button" class="btn btn-success mt-3" id="handyAddTerminalRow">Agregar Nueva Terminal</button>
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
              <button type="button" class="btn btn-primary" id="handySaveTerminals">Guardar Cambios</button>
          </div>
      </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const handyTerminalsTable = document.querySelector('#handyTerminalsTable tbody');
    const handyAddTerminalRowButton = document.getElementById('handyAddTerminalRow');
    const handySaveTerminalsButton = document.getElementById('handySaveTerminals');

    // Agregar nueva fila
    handyAddTerminalRowButton.addEventListener('click', function () {
        const newRow = `
            <tr>
                <td><input type="text" class="form-control handy-terminal-name" placeholder="Nombre" /></td>
                <td><input type="text" class="form-control handy-terminal-identifier" placeholder="Identificador" /></td>
                <td><input type="text" class="form-control handy-terminal-user" placeholder="Usuario" /></td>
                <td><input type="text" class="form-control handy-terminal-cash-register" placeholder="Caja" /></td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm handy-remove-terminal"><i class="bx bx-trash"></i></button>
                </td>
            </tr>`;
        handyTerminalsTable.insertAdjacentHTML('beforeend', newRow);
    });

    // Eliminar una fila
    handyTerminalsTable.addEventListener('click', function (event) {
        if (event.target.classList.contains('handy-remove-terminal') || event.target.closest('.handy-remove-terminal')) {
            const row = event.target.closest('tr');
            const terminalId = row.getAttribute('data-id');

            Swal.fire({
                title: '¿Estás seguro?',
                text: '¡Esta acción no se puede deshacer!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
            }).then((result) => {
                if (result.isConfirmed) {
                    if (terminalId) {
                        // Si el dispositivo tiene un ID, realizar solicitud para eliminar
                        fetch(`/api/pos/devices/${terminalId}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire(
                                    'Eliminado',
                                    'El terminal ha sido eliminado correctamente.',
                                    'success'
                                );
                                row.remove();
                            } else {
                                Swal.fire(
                                    'Error',
                                    'No se pudo eliminar el terminal.',
                                    'error'
                                );
                                console.error('Error del servidor:', data.message);
                            }
                        })
                        .catch(error => {
                            Swal.fire(
                                'Error',
                                'Ocurrió un error al eliminar el terminal.',
                                'error'
                            );
                            console.error('Error:', error);
                        });
                    } else {
                        // Si no tiene ID, simplemente eliminar la fila del DOM
                        row.remove();
                        Swal.fire(
                            'Eliminado',
                            'El terminal ha sido eliminado correctamente.',
                            'success'
                        );
                    }
                }
            });
        }
    });

    // Guardar terminales
    handySaveTerminalsButton.addEventListener('click', function () {
        const terminalRows = handyTerminalsTable.querySelectorAll('tr');
        const terminals = [];
        const posProviderId = 3; // ID para Handy

        terminalRows.forEach(row => {
            const id = row.getAttribute('data-id') || null; // ID si existe
            const name = row.querySelector('.handy-terminal-name').value.trim();
            const identifier = row.querySelector('.handy-terminal-identifier').value.trim();
            const user = row.querySelector('.handy-terminal-user').value.trim();
            const cashRegister = row.querySelector('.handy-terminal-cash-register').value.trim();

            // Validar campos
            if (!name || !identifier) {
                Swal.fire(
                    'Campos requeridos',
                    'El nombre y el identificador son obligatorios.',
                    'warning'
                );
                throw new Error('Validación fallida.');
            }

            // Agregar el terminal al array con el campo pos_provider_id
            terminals.push({ id, name, identifier, user, cash_register: cashRegister, pos_provider_id: posProviderId });
        });

        // Enviar datos al servidor
        fetch(`/api/pos/devices/sync`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ terminals })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire(
                    'Guardado',
                    'Terminales guardadas con éxito.',
                    'success'
                ).then(() => location.reload()); // Recargar para reflejar los cambios
            } else {
                Swal.fire(
                    'Error',
                    'Ocurrió un error al guardar los terminales.',
                    'error'
                );
                console.error('Error del servidor:', data.message, data.errors);
            }
        })
        .catch(error => {
            Swal.fire(
                'Error',
                'Ocurrió un error inesperado.',
                'error'
            );
            console.error('Error:', error);
        });
    });
});
</script>
