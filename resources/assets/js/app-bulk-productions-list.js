document.addEventListener('DOMContentLoaded', function () {

  // Inicializamos la tabla con DataTables
  var table = $('.datatables-bulk-productions').DataTable({
    data: bulkProductions, // Datos de las fórmulas
    columns: [
      { data: 'batch_number' },
      { data: 'formula_name' },
      {
        data: null, // Cantidad producida
        render: function (data, type, row) {
          let quantityProduced = row.quantity_produced * row.formula_quantity;
          return quantityProduced + ' ' + row.formula_unit_of_measure;
        }
      },
      {
        data: null, // Cantidad utilizada
        render: function (data, type, row) {
          let quantityUsed = row.quantity_used * row.formula_quantity;
          return quantityUsed + ' ' + row.formula_unit_of_measure;
        }
      },
      {
        data: 'production_date', // Fecha de elaboración
        render: function (data, type, row) {
          // Convertir la fecha a un objeto Date
          let date = new Date(data);
          let day = String(date.getDate()).padStart(2, '0'); // Formato dd
          let month = String(date.getMonth() + 1).padStart(2, '0'); // Formato mm
          let year = String(date.getFullYear()).slice(-2); // Formato yy
          let hours = String(date.getHours()).padStart(2, '0'); // Hora
          let minutes = String(date.getMinutes()).padStart(2, '0'); // Minutos

          // Devolver el formato: dd-mm-yy hh:mm
          return `<strong>Fecha: </strong>${day}-${month}-${year}<strong> Hora: </strong>${hours}:${minutes}`;
        }
      },
      {
        data: 'user_name'
      },
      {
        data: null,
        className: "text-center",
        orderable: false,
        render: function (data, type, row) {
          return `
                <div class="dropdown">
                    <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right">
                        <li><button class="dropdown-item btn-VerLotes" data-id="${row.id}">Ver lotes</button></li>
                        <li><button class="dropdown-item btn-delete" data-id="${row.id}">Eliminar</button></li>
                    </ul>
                </div>`;
        }
      }
    ],
    responsive: true,
    language: {
      url: '//cdn.datatables.net/plug-ins/1.11.3/i18n/es_es.json'
    }
  });


  // Lógica para mostrar u ocultar columnas
  $('.toggle-column').on('change', function () {
    var column = table.column($(this).attr('data-column'));
    column.visible(!column.visible());
  });


  // Evento para eliminar fórmula
  $(document).on('click', '.btn-delete', function () {
    var itemId = $(this).data('id');

    eliminarProduccionAGranel(itemId);
  });

  function eliminarProduccionAGranel(itemId) {
    Swal.fire({
      title: '¿Estás seguro?',
      text: "No podrás revertir esto. No se volverá a agregar el stock de los lotes utilizados para la producción a granel.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: `bulk-productions/${itemId}`,
          type: 'DELETE',
          data: {
            _token: $('meta[name="csrf-token"]').attr('content')
          },
          success: function (response) {
            Swal.fire(
              'Eliminado!',
              'La producción a granel ha sido eliminada.',
              'success'
            ).then(() => {
              window.location.reload();
            });
          },
          error: function (error) {
            Swal.fire(
              'Error!',
              'Ocurrió un problema al intentar eliminar la producción a granel.',
              'error'
            );
          }
        });
      }
    });
  }

  // Cargar las fórmulas cuando se abre el off-canvas
  $('#offcanvasProduction').on('show.bs.offcanvas', function () {
    $.ajax({
      url: 'formulas-list',
      method: 'GET',
      success: function (response) {
        let formulas = response.formulas;

        if (!formulas || formulas.length === 0) {
          Swal.fire('Advertencia', 'No se encontraron fórmulas disponibles', 'warning');
          return;
        }

        let selectFormula = $('#formula');
        selectFormula.empty(); // Limpiar las opciones previas
        selectFormula.append('<option value="">Seleccione una fórmula</option>');

        formulas.forEach(function (formula) {
          selectFormula.append(`<option value="${formula.id}">${formula.name}</option>`);
        });
      },
      error: function () {
        Swal.fire('Error', 'No se pudieron cargar las fórmulas', 'error');
      }
    });
  });

  // Validación y envío del formulario para iniciar producción
  $('#productionForm').on('submit', function (e) {
    e.preventDefault();

    let batch_number = $('#name').val();
    let formula = $('#formula').val();
    let quantity = $('#quantity').val();

    if (!formula || !quantity || quantity <= 0) {
      Swal.fire('Advertencia', 'Debe completar todos los campos correctamente', 'warning');
      return;
    }
    $('#offcanvasProduction').offcanvas('hide');

    // Mostrar la alerta de confirmación
    Swal.fire({
      title: '¿Estás seguro que deseas continuar?',
      text: "Si le das a 'Aceptar' se restará la materia prima de stock y se creará la producción de la fórmula.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Aceptar',
      cancelButtonText: 'Cancelar',
      reverseButtons: false
    }).then((result) => {
      if (result.isConfirmed) {
        // Enviar la solicitud AJAX para iniciar producción
        $.ajax({
          url: 'start-production',  // Endpoint para iniciar producción
          method: 'POST',
          data: {
            batch_number: batch_number,
            formula_id: formula,
            quantity: quantity,
            _token: csrfToken  // Para seguridad CSRF
          },
          success: function (response) {
            if (response.success) {
              showProductionDetailsModal(response.step_batch_usage);
            } else {
              Swal.fire('Error', response.message, 'error');
            }
          },
          error: function (xhr) {
            Swal.fire('Error', 'Ocurrió un error al iniciar la producción', 'error');
          }
        });
      }
    });
  });


  function showProductionDetailsModal(stepBatchUsage) {
    // Limpiar el contenido del modal si se está reutilizando
    $('#productionDetailsModal .modal-body').html('');

    let currentStep = 0;
    const completedSteps = new Set();

    function renderStep(index) {
      const stepData = stepBatchUsage[index];
      const isCompleted = completedSteps.has(index);
      const isLast = index === stepBatchUsage.length - 1;
      const isCurrent = index === currentStep;

      let stepHtml = `
            <div class="card mb-3" id="step-${index}">
                <div class="card-body d-flex align-items-center" style="min-height: 100px;">
                    <div class="flex-grow-1 ${isCompleted ? 'text-decoration-line-through' : ''}">
                        <h5 class="card-title">Paso ${stepData.step}</h5>
                        <p><strong>Aclaraciones:</strong> ${stepData.clarifications || 'Ninguna aclaración disponible'}</p>
        `;

      if (stepData.batches_used && stepData.batches_used.length > 0) {
        stepHtml += '<p><strong>Lotes utilizados:</strong></p><ul>';
        stepData.batches_used.forEach(function (batch) {
          stepHtml += `<li>Lote ${batch.name}: ${batch.quantity_used} ${batch.unit_of_measure}</li>`;
        });
        stepHtml += '</ul>';
      }

      stepHtml += `
                  </div>
                  ${isCurrent ? `
                      <div class="ms-3">
                          <button class="btn ${isLast ? 'btn-danger finalize-step' : 'btn-success complete-step'}" data-step="${index}">
                              ${isLast ? 'Finalizar' : 'Completado'}
                          </button>
                      </div>
                  ` : ''}
                  </div>
                  </div>
              `;


      return stepHtml;
    }

    // Captura de eventos de botones "Completado" y "Finalizar"
  document.addEventListener('click', function (event) {
    if (event.target.classList.contains('complete-step')) {
        const stepIndex = event.target.getAttribute('data-step');
    }
    
    if (event.target.classList.contains('finalize-step')) {
        location.reload();
    }
  });


    function renderAllSteps() {
      let modalContent = '<div class="container-fluid">';
      stepBatchUsage.forEach((_, index) => {
        modalContent += renderStep(index);
      });
      modalContent += '</div>';
      $('#productionDetailsModal .modal-body').html(modalContent);

      // Apply blur effect to future steps
      stepBatchUsage.forEach((_, index) => {
        if (index > currentStep) {
          $(`#step-${index}`).css({
            'filter': 'blur(5px)',
            'user-select': 'none',
            'pointer-events': 'none'
          });
        } else {
          $(`#step-${index}`).css({
            'filter': 'none',
            'user-select': 'auto',
            'pointer-events': 'auto'
          });
        }
      });
    }

    renderAllSteps();

    // Event listener for step completion
    $('#productionDetailsModal').on('click', '.complete-step', function () {
      const stepIndex = parseInt($(this).data('step'));
      completedSteps.add(stepIndex);

      if (stepIndex < stepBatchUsage.length - 1) {
        currentStep++;
        renderAllSteps();
      } else {
        // Close the modal and the offcanvas if it's the last step
        $('#productionDetailsModal').modal('hide');
        var offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('offcanvasProduction'));
        if (offcanvas) {
          offcanvas.hide();
        }
      }
    });

    // Mostrar el modal
    $('#productionDetailsModal').modal('show');
  }

  $(document).on('click', '.btn-VerLotes', function () {
    const productionId = $(this).data('id');
    $.ajax({
        url: `get-batches/${productionId}`,
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.batches && response.batches.length > 0) {
                let modalBody = '';
                response.batches.forEach(batch => {
                    const batchInfo = `<p><strong>Número de lote:</strong> ${batch.batch_name} <strong>Cantidad utilizada:</strong> ${batch.quantity_used} ${batch.unit_of_measure}`;
                    modalBody += `${batchInfo}`;
                });
                $('#modalBatches .batch-details').html(modalBody);

                $('#modalBatches .modal-qr').html('<div id="qrcode"></div>');
                new QRCode(document.getElementById("qrcode"), {
                    text: response.qr_url,
                    width: 128,
                    height: 128
                });

                $('#modalBatches').modal('show');
            } else {
                $('#modalBatches .batch-details').html('<p>No se encontraron lotes.</p>');
                $('#modalBatches').modal('show');
            }
        },
        error: function (xhr, status, error) {
            $('#modalBatches .modal-body').html('<p>Error al cargar los lotes.</p>');
            $('#modalBatches').modal('show');
        }
    });
});


  // Add event listener for the download button
  $(document).on('click', '#downloadQR', function () {
    const qrCanvas = document.querySelector('#qrcode canvas');
    if (qrCanvas) {
      const link = document.createElement('a');
      link.download = 'qr_code.png';
      link.href = qrCanvas.toDataURL('image/png').replace("image/png", "image/octet-stream");
      link.click();
    }
  });
});
