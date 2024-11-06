document.addEventListener('DOMContentLoaded', function () {
    var table;
  
    function initDataTable() {
      table = $('.datatables-formula-steps').DataTable({
        order: [[5, 'asc']],
        data: purchaseOrderItems,
        columns: [
          { data: 'id' },
          { data: 'formula_id' },
          { data: 'name' },
          { data: 'quantity_required' },
          { data: 'clarification' },
          { data: 'step' },
          {
            data: null,
            className: 'text-center',
            orderable: false,
            render: function (data, type, row) {
              return `
                          <div class="dropdown">
                              <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                  <i class="fas fa-ellipsis-v"></i>
                              </button>
                              <ul class="dropdown-menu dropdown-menu-right">
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
    }
  
    // Actualizar la card de total de pasos
    function updateTotalSteps() {
      const totalSteps = purchaseOrderItems.length;
      document.querySelector('.total-steps').textContent = totalSteps;
    }
  
    // Actualizar la card de materias primas diferentes
    function updateTotalRawMaterials() {
      const rawMaterials = purchaseOrderItems.map(item => item.raw_material_id);
      const uniqueRawMaterials = new Set(rawMaterials);
      document.querySelector('.total-raw-material').textContent = uniqueRawMaterials.size;
    }
  
    initDataTable();
    updateTotalSteps();
    updateTotalRawMaterials();
  
    // Manejo de la visibilidad de columnas
    $('.toggle-column').on('change', function (e) {
      var column = table.column($(this).attr('data-column'));
      column.visible(!column.visible());
    });
  
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('csvFile');
    const uploadButton = document.getElementById('uploadButton');
    const form = document.getElementById('csvUploadForm');
    const fileNameDisplay = document.createElement('p');
    fileNameDisplay.classList.add('mt-3');
    fileNameDisplay.style.display = 'none';
    dropzone.appendChild(fileNameDisplay);
    const removeFileButton = document.createElement('button');
    removeFileButton.classList.add('btn', 'btn-outline-danger', 'mt-2');
    removeFileButton.textContent = 'Quitar archivo seleccionado';
    removeFileButton.style.display = 'none';
    dropzone.appendChild(removeFileButton);
  
    uploadButton.addEventListener('click', () => {
      fileInput.click();
    });
  
    dropzone.addEventListener('dragover', e => {
      e.preventDefault();
      dropzone.classList.add('border-primary');
    });
  
    dropzone.addEventListener('dragleave', () => {
      dropzone.classList.remove('border-primary');
    });
  
    fileInput.addEventListener('change', () => {
      if (fileInput.files.length > 0) {
        fileNameDisplay.textContent = `Archivo seleccionado: ${fileInput.files[0].name}`;
        fileNameDisplay.style.display = 'block';
        uploadButton.style.display = 'none';
        removeFileButton.style.display = 'block';
      }
    });
  
    dropzone.addEventListener('drop', e => {
      e.preventDefault();
      dropzone.classList.remove('border-primary');
      fileInput.files = e.dataTransfer.files;
      if (fileInput.files.length > 0) {
        fileNameDisplay.textContent = `Archivo seleccionado: ${fileInput.files[0].name}`;
        fileNameDisplay.style.display = 'block';
        uploadButton.style.display = 'none';
        removeFileButton.style.display = 'block';
      }
    });
  
    removeFileButton.addEventListener('click', () => {
      fileInput.value = ''; // Reinicia el valor del input de archivo
      fileNameDisplay.style.display = 'none'; // Oculta el nombre del archivo
      uploadButton.style.display = 'block'; // Muestra el botón "Subir archivo"
      removeFileButton.style.display = 'none'; // Oculta el botón "Quitar archivo"
    });
  
    $(form).on('submit', function (e) {
      e.preventDefault(); // Previene el comportamiento por defecto del formulario
  
      var formData = new FormData(this);
  
      $.ajax({
        url: 'formula-steps-csv',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
          'X-CSRF-TOKEN': window.csrfToken // Token CSRF para la protección
        },
        success: function (data) {
          if (data.success) {
            window.location.reload(); // Recargar la página si el archivo se carga correctamente
          } else {
            alert('Error al cargar el archivo: ' + data.error);
          }
        },
        error: function (jqXHR, textStatus, errorThrown) {
          console.error('Error:', textStatus, errorThrown);
        }
      });
    });
  
    $('#repeater').repeater({
      initEmpty: false,
      show: function () {
        $(this).slideDown();
        populateRawMaterialsSelect($(this).find('select'));
      },
      hide: function (deleteElement) {
        $(this).slideUp(deleteElement);
      },
      isFirstItemUndeletable: false
    });
  
    function populateRawMaterialsSelect(selectElement) {
      selectElement.empty();
      selectElement.append('<option value="" disabled selected>Seleccione una materia prima</option>');
      selectElement.append('<option value="">Ninguna</option>');

      for (var id in rawMaterialsData) {
        selectElement.append(`<option value="${id}">${rawMaterialsData[id].name}</option>`);
      }
    }
  
    $(document).on('click', '.addStepsModal', function () {
      $('#stepsModal').modal('show');
    });
  
    let rawMaterialsData = {}; // Para almacenar los materiales y sus unidades de medida
  
    // Cargar materias primas cuando se abre el modal
    $('#stepsModal').on('show.bs.modal', function () {
      $.ajax({
        url: 'purchase-orders-items-raw-materials',
        method: 'GET',
        success: function (response) {
          var rawMaterialSelect = $('.raw-material').first();
          response.forEach(function (item) {
            rawMaterialsData[item.id] = {
              name: item.name,
              unit_of_measure: item.unit_of_measure
            };
          });
          populateRawMaterialsSelect(rawMaterialSelect);
        },
        error: function () {
          alert('Error al cargar las materias primas.');
        }
      });
    });
  
    // Detectar cambio en el selector de materias primas
    $(document).on('change', '.raw-material', function () {
      var selectedRawMaterial = $(this).val();
      var $row = $(this).closest('[data-repeater-item]'); // Encuentra la fila actual donde ocurre el cambio
      var quantityInput = $row.find('.lot-quantity'); // Encuentra el input de cantidad en la misma fila
      var unitOfMeasureSpan = $row.find('#unit-of-measure'); // Encuentra el span de unidad de medida en la misma fila
  
  
      if (selectedRawMaterial) {
        // Habilitar el campo de cantidad si hay una materia prima seleccionada
        quantityInput.prop('disabled', false);
  
        // Mostrar la unidad de medida correspondiente
        var unitOfMeasure = rawMaterialsData[selectedRawMaterial].unit_of_measure;
        unitOfMeasure = (unitOfMeasure == "Unidades") ? "Un" : unitOfMeasure;

        unitOfMeasureSpan.text(unitOfMeasure);
      } else {
        // Deshabilitar el campo de cantidad si no se selecciona materia prima
        quantityInput.prop('disabled', true);
        unitOfMeasureSpan.text('--'); // Resetear la unidad de medida
      }
    });
  
    $(document).on('click', '#save-lots', function () {
      // Recoger los datos del formulario
      var stepsData = $('#steps-form').serializeArray();
  
      // Transformar los datos para que se ajusten a la estructura esperada por el servidor
      var formattedData = {};
      stepsData.forEach(function (item) {
        if (!formattedData[item.name]) {
          formattedData[item.name] = [];
        }
        formattedData[item.name].push(item.value);
      });
  
      // Enviar los datos a la ruta storeMultiple
      $.ajax({
        url: 'formula-steps-multiple',
        method: 'POST',
        data: {
          steps: formattedData, // Datos del formulario
          _token: window.csrfToken
        },
        success: function (response) {
          if (response.success) {
            $('#stepsModal').modal('hide'); // Cierra el modal
            location.reload();
          } else {
            alert('Hubo un error al guardar los pasos.'); // Manejo de errores
          }
        },
        error: function (xhr, status, error) {
          console.error('Error al enviar datos:', error);
          alert('Error al enviar datos. Por favor, intenta de nuevo.');
        }
      });
    });
  

    $('.datatables-formula-steps').on('click', '.btn-delete', function (e) {
        e.preventDefault();
        const id = $(this).data('id');

        Swal.fire({
            title: '¿Estás seguro?',
            text: "¿Deseas eliminar este elemento?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `formula-steps/${id}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (data) {
                        if (data.success) {
                            Swal.fire(
                                'Eliminado',
                                'El elemento ha sido eliminado.',
                                'success'
                            ).then(result => {
                                if (result.isConfirmed) {
                                    location.reload();
                                }
                            });;

                        } else {
                            Swal.fire(
                                'Error',
                                'No se pudo eliminar el elemento.',
                                'error'
                            );
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error:', error);
                        Swal.fire(
                            'Error',
                            'Ocurrió un error al intentar eliminar el elemento.',
                            'error'
                        );
                    }
                });
            }
        });
    });

    const downloadBtn = document.getElementById('downloadTemplateBtn');
    
    if (downloadBtn) {
        downloadBtn.addEventListener('click', function() {
            downloadTemplate();
        });
    }
    
    function downloadTemplate() {
        const csvContent = "raw_material_id,quantity_required,step,clarification\n";
        
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement("a");
        
        if (window.navigator.msSaveBlob) { 
            window.navigator.msSaveBlob(blob, "Pasos_formula.csv");
        } else {
            link.href = window.URL.createObjectURL(blob);
            link.download = "Pasos_formula.csv";
            link.style.display = "none";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }
});
