document.addEventListener('DOMContentLoaded', () => {
  const uploadCaesModal = new bootstrap.Modal(document.getElementById('uploadCaesModal'));

  // Abrir modal y configurar RUT
  document.querySelectorAll('.open-upload-caes-modal').forEach(button => {
      button.addEventListener('click', () => {
          const storeRut = button.dataset.storeRut;
          const storeId = button.dataset.storeId;
          document.getElementById('storeRut').textContent = storeRut;
          document.getElementById('uploadCaesForm').dataset.storeId = storeId;
          uploadCaesModal.show();
      });
  });

  // Subir archivos
  document.getElementById('uploadCaesBtn').addEventListener('click', () => {
      const form = document.getElementById('uploadCaesForm');
      const storeId = form.dataset.storeId;
      const storeRut = document.getElementById('storeRut').textContent;

      const files = Array.from(form.querySelectorAll('.cae-file')).filter(input => input.files.length > 0);

      if (files.length === 0) {
          Swal.fire('Error', 'Por favor, selecciona al menos un archivo.', 'error');
          return;
      }

      // Cerrar el modal
      uploadCaesModal.hide();

      // Mostrar loader de SweetAlert
      Swal.fire({
          title: 'Subiendo CAEs...',
          text: 'Por favor, espera mientras procesamos los archivos.',
          icon: 'info',
          allowOutsideClick: false,
          showConfirmButton: false,
          willOpen: () => {
              Swal.showLoading();
          },
      });

      let successCount = 0;
      let errorCount = 0;

      files.forEach(fileInput => {
          const caeType = fileInput.dataset.type;
          const file = fileInput.files[0];
          const formData = new FormData();
          formData.append('CfesNewNumbers', file);

          const url = `${window.baseUrl}admin/accounting/pymo-connection/${storeRut}/caes/${caeType}/upload`;

          console.log('Iniciando subida:', { caeType, fileName: file.name });

          fetch(url, {
              method: 'POST',
              body: formData,
              headers: {
                  'X-CSRF-TOKEN': window.csrfToken,
              },
          })
              .then(response => response.json())
              .then(data => {
                  console.log('Respuesta de la API:', data);

                  const statusCell = document.querySelector(`.status-${caeType}`);

                  if (data.status === 'SUCCESS') {
                      // Actualizar la celda de estado con éxito
                      statusCell.textContent = '¡Cargado con éxito!';
                      statusCell.classList.remove('text-warning');
                      statusCell.classList.add('text-success');
                      successCount++;
                  } else {
                      // Mostrar error en la celda de estado
                      statusCell.textContent = `Error: ${data.message}`;
                      statusCell.classList.remove('text-warning');
                      statusCell.classList.add('text-danger');
                      errorCount++;
                  }
              })
              .catch(error => {
                  console.error('Error al realizar el fetch:', error);

                  const statusCell = document.querySelector(`.status-${caeType}`);
                  statusCell.textContent = 'Error en la subida';
                  statusCell.classList.remove('text-warning');
                  statusCell.classList.add('text-danger');
                  errorCount++;
              })
              .finally(() => {
                  // Revisar si todos los archivos han sido procesados
                  if (successCount + errorCount === files.length) {
                      Swal.close(); // Cerrar el loader
                      if (successCount > 0 && errorCount === 0) {
                          Swal.fire('¡Éxito!', 'Todos los CAEs fueron cargados correctamente.', 'success');
                      } else if (successCount > 0 && errorCount > 0) {
                          Swal.fire(
                              'Atención',
                              `Algunos CAEs no se cargaron correctamente.
                              \nÉxitos: ${successCount}
                              \nErrores: ${errorCount}`,
                              'warning'
                          );
                      } else {
                          Swal.fire('Error', 'Ningún CAE pudo ser cargado.', 'error');
                      }
                  }
              });
      });
  });
});
