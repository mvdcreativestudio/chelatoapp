<div class="modal fade" id="scanntechModal" tabindex="-1" aria-labelledby="scanntechModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title" id="scanntechModalLabel">Terminales de Scanntech</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <table class="table table-striped" id="terminalsTable">
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
                          <td><input type="text" class="form-control terminal-name" value="{{ $device->name }}" /></td>
                          <td><input type="text" class="form-control terminal-identifier" value="{{ $device->identifier }}" /></td>
                          <td><input type="text" class="form-control terminal-user" value="{{ $device->user }}" /></td>
                          <td><input type="text" class="form-control terminal-cash-register" value="{{ $device->cash_register }}" /></td>
                          <td class="text-center">
                              <button type="button" class="btn btn-outline-danger btn-sm remove-terminal"><i class="bx bx-trash"></i></button>
                          </td>
                      </tr>
                      @endforeach
                  </tbody>
              </table>
              <button type="button" class="btn btn-success mt-3" id="addTerminalRow">Agregar Nueva Terminal</button>
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
              <button type="button" class="btn btn-primary" id="saveTerminals">Guardar Cambios</button>
          </div>
      </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const terminalsTable = document.querySelector('#terminalsTable tbody');
    const addTerminalRowButton = document.getElementById('addTerminalRow');
    const saveTerminalsButton = document.getElementById('saveTerminals');

    // Agregar nueva fila
    function addRowHandler() {
        const newRow = `
            <tr>
                <td><input type="text" class="form-control terminal-name" placeholder="Nombre" /></td>
                <td><input type="text" class="form-control terminal-identifier" placeholder="Identificador" /></td>
                <td><input type="text" class="form-control terminal-user" placeholder="Usuario" /></td>
                <td><input type="text" class="form-control terminal-cash-register" placeholder="Caja" /></td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-terminal"><i class="bx bx-trash"></i></button>
                </td>
            </tr>`;
        terminalsTable.insertAdjacentHTML('beforeend', newRow);
    }

    // Asociar el evento al botón
    addTerminalRowButton.addEventListener('click', addRowHandler);

    // Guardar terminales (resto del código aquí)
    saveTerminalsButton.addEventListener('click', function () {
        const terminalRows = terminalsTable.querySelectorAll('tr');
        const terminals = [];
        const posProviderId = 1; // Ajusta este ID al que corresponda para Scanntech.

        terminalRows.forEach(row => {
            const id = row.getAttribute('data-id') || null; // ID si existe
            const name = row.querySelector('.terminal-name').value.trim();
            const identifier = row.querySelector('.terminal-identifier').value.trim();
            const user = row.querySelector('.terminal-user').value.trim();
            const cashRegister = row.querySelector('.terminal-cash-register').value.trim();

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

        // URL fija para la API
        const url = `/api/pos/devices/sync`;

        // Enviar datos al servidor
        fetch(url, {
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


    // Guardar terminales
    saveTerminalsButton.addEventListener('click', function () {
        const terminalRows = terminalsTable.querySelectorAll('tr');
        const terminals = [];
        const posProviderId = 1; // Ajusta este ID al que corresponda para Scanntech.

        terminalRows.forEach(row => {
            const id = row.getAttribute('data-id') || null; // ID si existe
            const name = row.querySelector('.terminal-name').value.trim();
            const identifier = row.querySelector('.terminal-identifier').value.trim();
            const user = row.querySelector('.terminal-user').value.trim();
            const cashRegister = row.querySelector('.terminal-cash-register').value.trim();

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

        // URL fija para la API
        const url = `/api/pos/devices/sync`;

        // Enviar datos al servidor
        fetch(url, {
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
