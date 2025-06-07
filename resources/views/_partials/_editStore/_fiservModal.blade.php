<div class="modal fade" id="fiservModal" tabindex="-1" aria-labelledby="fiservModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title" id="fiservModalLabel">Terminales de Fiserv</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <table class="table table-striped" id="fiservTerminalsTable">
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
                          <td><input type="text" class="form-control fiserv-terminal-name" value="{{ $device->name }}" /></td>
                          <td><input type="text" class="form-control fiserv-terminal-identifier" value="{{ $device->identifier }}" /></td>
                          <td><input type="text" class="form-control fiserv-terminal-user" value="{{ $device->user }}" /></td>
                          <td><input type="text" class="form-control fiserv-terminal-cash-register" value="{{ $device->cash_register }}" /></td>
                          <td class="text-center">
                              <button type="button" class="btn btn-outline-danger btn-sm fiserv-remove-terminal"><i class="bx bx-trash"></i></button>
                          </td>
                      </tr>
                      @endforeach
                  </tbody>
              </table>
              <button type="button" class="btn btn-success mt-3" id="fiservAddTerminalRow">Agregar Nueva Terminal</button>
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
              <button type="button" class="btn btn-primary" id="fiservSaveTerminals">Guardar Cambios</button>
          </div>
      </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const fiservTerminalsTable = document.querySelector('#fiservTerminalsTable tbody');
    const fiservAddTerminalRowButton = document.getElementById('fiservAddTerminalRow');
    const fiservSaveTerminalsButton = document.getElementById('fiservSaveTerminals');

    // Agregar nueva fila
    fiservAddTerminalRowButton.addEventListener('click', function () {
        const newRow = `
            <tr>
                <td><input type="text" class="form-control fiserv-terminal-name" placeholder="Nombre" /></td>
                <td><input type="text" class="form-control fiserv-terminal-identifier" placeholder="Identificador" /></td>
                <td><input type="text" class="form-control fiserv-terminal-user" placeholder="Usuario" /></td>
                <td><input type="text" class="form-control fiserv-terminal-cash-register" placeholder="Caja" /></td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm fiserv-remove-terminal"><i class="bx bx-trash"></i></button>
                </td>
            </tr>`;
        fiservTerminalsTable.insertAdjacentHTML('beforeend', newRow);
    });

    // Eliminar una fila
    fiservTerminalsTable.addEventListener('click', function (event) {
        if (event.target.classList.contains('fiserv-remove-terminal') || event.target.closest('.fiserv-remove-terminal')) {
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
    fiservSaveTerminalsButton.addEventListener('click', function () {
        const terminalRows = fiservTerminalsTable.querySelectorAll('tr');
        const terminals = [];
        const posProviderId = 2; // ID para Fiserv

        terminalRows.forEach(row => {
            const id = row.getAttribute('data-id') || null; // ID si existe
            const name = row.querySelector('.fiserv-terminal-name').value.trim();
            const identifier = row.querySelector('.fiserv-terminal-identifier').value.trim();
            const user = row.querySelector('.fiserv-terminal-user').value.trim();
            const cashRegister = row.querySelector('.fiserv-terminal-cash-register').value.trim();

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
