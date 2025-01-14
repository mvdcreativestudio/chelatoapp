document.addEventListener('DOMContentLoaded', () => {
  // Manejo de los switches para Scanntech
  const scanntechSwitches = document.querySelectorAll('[id^="scanntechSwitch-"]');
  scanntechSwitches.forEach(switchEl => {
    switchEl.addEventListener('change', function () {
      const storeId = this.dataset.storeId;
      const card = document.getElementById(`scanntech-card-${storeId}`);
      if (!card) {
        console.error(`No se encontró la tarjeta Scanntech con ID: scanntech-card-${storeId}`);
        return;
      }

      if (this.checked) {
        Swal.fire({
          title: 'Activar Scanntech',
          html: `
            <label for="company" class="form-label mt-3">Empresa</label>
            <input type="text" id="company" class="form-control" placeholder="Ingrese el número/nombre de empresa">
            <label for="branchId" class="form-label mt-3">Sucursal</label>
            <input type="text" id="branchId" class="form-control" placeholder="Ingrese el código de la sucursal">
          `,
          focusConfirm: false,
          preConfirm: () => {
            const branchId = document.getElementById('branchId').value.trim();
            const company = document.getElementById('company').value.trim();
            if (!branchId || !company) {
              Swal.showValidationMessage('Todos los campos son obligatorios');
              return null;
            }
            return {branchId, company };
          },
          showCancelButton: true,
          confirmButtonText: 'Activar',
          cancelButtonText: 'Cancelar'
        }).then((result) => {
          if (result.isConfirmed) {
            const { branchId, company } = result.value;
            card.classList.add('active-integration');
            activateScanntech(storeId, company, branchId, this); // Enviar datos al servidor
          } else {
            this.checked = false;
          }
        });
      } else {
        card.classList.remove('active-integration');
        deactivateScanntech(storeId, this);
      }
    });
  });

  // Delegación de eventos para agregar nuevas filas en el modal Scanntech
  document.querySelectorAll('.modal[data-provider="scanntech"]').forEach((modal, modalIndex) => {
    modal.addEventListener('click', function (event) {
      if (event.target.classList.contains('add-terminal-row') || event.target.closest('.add-terminal-row')) {
        const button = event.target.closest('.add-terminal-row');
        const storeId = button.dataset.storeId;

        const terminalsTable = modal.querySelector(
          `.terminals-table[data-provider="scanntech"][data-store-id="${storeId}"]`
        );

        if (!terminalsTable) {
          console.error(`No se encontró la tabla de terminales para el proveedor: scanntech, tienda: ${storeId}`);
          return;
        }

        // Agregar una nueva fila a la tabla correspondiente
        const newRow = `
          <tr>
            <td><input type="text" class="form-control terminal-name" placeholder="Nombre" /></td>
            <td><input type="text" class="form-control terminal-identifier" placeholder="Identificador" /></td>
            <td><input type="text" class="form-control terminal-user" placeholder="Usuario" /></td>
            <td><input type="text" class="form-control terminal-cash-register" placeholder="Caja" /></td>
            <td class="text-center">
              <button type="button" class="btn btn-outline-danger btn-sm remove-terminal">
                <i class="bx bx-trash"></i>
              </button>
            </td>
          </tr>`;
        terminalsTable.insertAdjacentHTML('beforeend', newRow);
      }
    });
  });

  // Delegación de eventos para eliminar terminales
  document.body.addEventListener('click', function (event) {
    if (event.target.classList.contains('remove-terminal') || event.target.closest('.remove-terminal')) {
      const row = event.target.closest('tr');
      const terminalId = row.getAttribute('data-id');

      const originalRowContent = row.innerHTML;
      row.innerHTML = `
        <td colspan="5" class="text-center">
          <p>¿Confirmar eliminación?</p>
          <button type="button" class="btn btn-danger btn-sm confirm-remove-terminal">Sí, eliminar</button>
          <button type="button" class="btn btn-secondary btn-sm cancel-remove-terminal">Cancelar</button>
        </td>
      `;

      row.querySelector('.confirm-remove-terminal').addEventListener('click', () => {
        if (terminalId) {
          fetch(`/api/pos/devices/${terminalId}`, {
            method: 'DELETE',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': window.csrfToken
            }
          })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                row.remove();
              } else {
                console.error('Error al eliminar terminal:', data.message);
                row.innerHTML = originalRowContent;
                Swal.fire('Error', 'No se pudo eliminar el terminal.', 'error');
              }
            })
            .catch(error => {
              console.error('Error al eliminar terminal:', error);
              row.innerHTML = originalRowContent;
              Swal.fire('Error', 'Ocurrió un error al eliminar el terminal.', 'error');
            });
        } else {
          row.remove();
        }
      });

      row.querySelector('.cancel-remove-terminal').addEventListener('click', () => {
        row.innerHTML = originalRowContent;
      });
    }
  });

  // Botón de guardar cambios para Scanntech
  document.querySelectorAll('.save-terminals[data-provider="scanntech"]').forEach(button => {
    button.addEventListener('click', function () {
      const storeId = this.dataset.storeId;

      // Seleccionar la tabla específica usando data-provider y data-store-id
      const terminalsTable = document.querySelector(
        `.terminals-table[data-provider="scanntech"][data-store-id="${storeId}"]`
      );

      if (!terminalsTable) {
        console.error(`No se encontró la tabla de terminales para el Store ID: ${storeId}`);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se pudo encontrar la tabla de terminales.',
          confirmButtonText: 'Aceptar'
        });
        return;
      }


      // Recolectar datos de las terminales
      const terminals = [];
      terminalsTable.querySelectorAll('tr').forEach((row, index) => {

        // Intentamos obtener cada campo de la fila
        const nameField = row.querySelector('.terminal-name');
        const identifierField = row.querySelector('.terminal-identifier');
        const userField = row.querySelector('.terminal-user');
        const cashRegisterField = row.querySelector('.terminal-cash-register');

        // Validamos que los campos existan antes de intentar obtener valores
        if (!nameField || !identifierField) {
          console.warn(`Campos faltantes en la fila ${index + 1}. Omite esta fila.`);
          return;
        }

        const name = nameField.value.trim();
        const identifier = identifierField.value.trim();
        const user = userField ? userField.value.trim() : null;
        const cashRegister = cashRegisterField ? cashRegisterField.value.trim() : null;

        // Validación: solo agregar si name e identifier no están vacíos
        if (name && identifier) {
          terminals.push({
            id: row.getAttribute('data-id') || null, // ID opcional (puede ser nulo si es una nueva terminal)
            name,
            identifier,
            user,
            cash_register: cashRegister,
            pos_provider_id: 1 // Identificador del proveedor (Scanntech)
          });
        } else {
          console.warn(`Fila ${index + 1} inválida. Falta Nombre o Identificador.`);
        }
      });

      if (terminals.length === 0) {
        // Cierra cualquier popup activo antes de mostrar el nuevo
        bootstrap.Modal.getInstance(document.querySelector(`#scanntechModal-${storeId}`)).hide();


        Swal.fire({
          icon: 'warning',
          title: 'Advertencia',
          html: `
            <p>Se guardaron los cambios sin ninguna terminal para esta integración.</p>
          `,
          showConfirmButton: false, // Ocultar botón "Aceptar"
          timer: 3000, // Cerrar automáticamente después de 1.5 segundos
          didOpen: () => {
            console.log("Advertencia mostrada: No hay terminales válidas.");
          }
        });

        return;
      }



      // Mostrar animación de guardado
      const modalContent = document.querySelector(`#scanntechModal-${storeId} .modal-content`);
      modalContent.classList.add('saving-animation');
      modalContent.innerHTML = `
        <div class="text-center p-5">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="mt-3">Guardando cambios...</p>
        </div>
      `;

      // Realizar la solicitud para guardar terminales
      fetch(`/api/pos/devices/sync`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': window.csrfToken
        },
        body: JSON.stringify({ terminals, storeId })
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            modalContent.innerHTML = `
              <div class="text-center p-5">
                <i class="bx bx-check-circle text-success" style="font-size: 3rem;"></i>
                <p class="mt-3">¡Cambios guardados con éxito!</p>
              </div>
            `;
            setTimeout(() => {
              bootstrap.Modal.getInstance(document.querySelector(`#scanntechModal-${storeId}`)).hide();
              setTimeout(() => location.reload()); // Recargar la página después de cerrar el modal
            }, 1500);
          } else {
            console.error('Error al guardar cambios:', data.message);
            modalContent.innerHTML = `
              <div class="text-center p-5">
                <i class="bx bx-x-circle text-danger" style="font-size: 3rem;"></i>
                <p class="mt-3">Error: ${data.message || 'Ocurrió un error.'}</p>
              </div>
            `;
          }
        })
        .catch(error => {
          console.error('Error inesperado:', error);
          modalContent.innerHTML = `
            <div class="text-center p-5">
              <i class="bx bx-x-circle text-danger" style="font-size: 3rem;"></i>
              <p class="mt-3">Ocurrió un error inesperado.</p>
            </div>
          `;
        });
    });
  });

  // Función para activar Scanntech
  function activateScanntech(storeId, company, branchId, switchEl) {
    $.ajax({
      url: `${window.baseUrl}admin/integrations/${storeId}/scanntech`,
      type: 'POST',
      data: {
        accepts_scanntech: 1,
        branch: branchId,
        company: company,
        _token: window.csrfToken
      },
      success: function (response) {
        if (response.success) {
          Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: response.message,
            showConfirmButton: false,
            timer: 1000 // El mensaje se cierra automáticamente después de 1.5 segundos
          }).then(() => {
            location.reload(); // Recargar la página
          });
        } else {
          console.error('Error al activar Scanntech:', response.message);
          handleError(response.message, switchEl, false);
        }
      },
      error: function (xhr) {
        console.error('Error en el servidor al activar Scanntech:', xhr.responseJSON?.message);
        handleError(xhr.responseJSON?.message || 'Error al activar Scanntech', switchEl, false);
      }
    });
  }

  // Función para desactivar Scanntech
  function deactivateScanntech(storeId, switchEl) {
    $.ajax({
      url: `${window.baseUrl}admin/integrations/${storeId}/scanntech`,
      type: 'POST',
      data: {
        accepts_scanntech: 0,
        _token: window.csrfToken
      },
      success: function (response) {
        if (response.success) {
          Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: response.message,
            showConfirmButton: false,
            timer: 1000 // El mensaje se cierra automáticamente después de 1.5 segundos
          }).then(() => {
            location.reload(); // Recargar la página
          });
        } else {
          console.error('Error al desactivar Scanntech:', response.message);
          handleError(response.message, switchEl, false);
        }
      },
      error: function (xhr) {
        console.error('Error en el servidor al desactivar Scanntech:', xhr.responseJSON?.message);
        handleError(xhr.responseJSON?.message || 'Error al desactivar Scanntech', switchEl, true);
      }
    });
  }

  // Función genérica de manejo de errores
  function handleError(message, switchEl, fallbackState) {
    console.error('Error detectado:', message);
    Swal.fire('Error', message, 'error');
    switchEl.checked = fallbackState;
  }
});
