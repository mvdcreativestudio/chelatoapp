'use strict';

(function () {
  document.addEventListener('DOMContentLoaded', function () {

    function limitTwoDecimals(event) {
      const input = event.target;
      let value = input.value;

      // Expresión regular que permite números con hasta dos decimales
      const regex = /^\d+(\.\d{0,2})?$/;

      if (!regex.test(value)) {
        // Si el valor no coincide con la expresión regular, recortamos a dos decimales
        input.value = parseFloat(value).toFixed(2);
      }
    }

    // Seleccionamos los campos de precio
    const priceInput = document.getElementById('ecommerce-product-price');
    const discountPriceInput = document.getElementById('ecommerce-product-discount-price');

    // Si los inputs existen, añadimos el evento input para limitar a dos decimales
    if (priceInput) {
      priceInput.addEventListener('input', limitTwoDecimals);
    }

    if (discountPriceInput) {
      discountPriceInput.addEventListener('input', limitTwoDecimals);
    }

    const commentEditorElement = document.querySelector('.comment-editor');

    if (commentEditorElement) {
      const quill = new Quill(commentEditorElement, {
        modules: {
          toolbar: '.comment-toolbar'
        },
        placeholder: 'Descripción del producto',
        theme: 'snow'
      });

      const form = commentEditorElement.closest('form');

      if (form) {
        form.addEventListener('submit', function () {
          const hiddenInput = document.getElementById('hiddenDescription');
          if (hiddenInput) {
            hiddenInput.value = quill.root.innerHTML;
          }
        });
      }
    }

    const productTypeSelect = document.getElementById('productType');
    const flavorsQuantityContainer = document.getElementById('flavorsQuantityContainer');
    const flavorsContainer = document.getElementById('flavorsContainer');
    const recipeCard = document.getElementById('recipeCard');
    const stockContainer = document.getElementById('stockContainer');

    function toggleFields() {
      // Oculta del todo los campos de cantidad de sabores. Si trabaja con productos variables, descomentar la primer linea y eliminar la segunda debajo de este comentario
      if (productTypeSelect.value === 'configurable') {
      // if (productTypeSelect.value === 'configurable' || productTypeSelect.value === 'simple') {
        flavorsQuantityContainer.style.display = 'block';
        flavorsContainer.style.display = 'block';
        recipeCard.style.display = 'none';
        stockContainer.style.display = 'block';
        console.log('Producto configurable');
      } else {
        flavorsQuantityContainer.style.display = 'none';
        flavorsContainer.style.display = 'none';
        recipeCard.style.display = 'block';
        stockContainer.style.display = 'block';
        console.log('Producto simple');

      }
    }

    productTypeSelect.addEventListener('change', () => {
      toggleFields();
    });

    toggleFields();

    const dropzoneElement = document.querySelector('#dropzone');
    const hiddenImageInput = document.getElementById('productImage');

    if (dropzoneElement) {
      const myDropzone = new Dropzone(dropzoneElement, {
        url: '#', // No se necesita URL aquí, el formulario manejará el envío
        autoProcessQueue: false,
        maxFiles: 1,
        previewsContainer: '#existingImage', // Muestra la vista previa en el contenedor existente
        clickable: '#btnBrowse, #dropzone',
        maxFilesize: 2, // Limite de 2MB
        acceptedFiles: '.jpg,.jpeg,.png,.gif',
        init: function () {
          const dz = this;

          dz.on('addedfile', function (file) {
            // Leer el archivo y actualizar el campo oculto
            const reader = new FileReader();

            reader.onload = function (event) {
              // Crear un objeto File a partir del ArrayBuffer resultante
              const arrayBuffer = event.target.result;
              const blob = new Blob([arrayBuffer], { type: file.type });
              const newFile = new File([blob], file.name, { type: file.type });

              // Crear un objeto DataTransfer para manejar los archivos
              const dataTransfer = new DataTransfer();
              dataTransfer.items.add(newFile);

              // Asignar el archivo al input oculto
              hiddenImageInput.files = dataTransfer.files;
            };

            reader.readAsArrayBuffer(file);
          });

          dz.on('removedfile', function () {
            // Vaciar el campo oculto
            hiddenImageInput.value = '';
            // Mostrar el mensaje de Dropzone si no hay archivos
            if (dz.files.length === 0) {
              dropzoneElement.querySelector('.dz-message').style.display = 'block';
            }
          });

          dz.on('thumbnail', function (file, dataUrl) {
            document.querySelector('#existingImage').innerHTML =
              `<img src="${dataUrl}" alt="Imagen del producto" class="img-fluid" id="productImagePreview">`;
          });

          const form = dropzoneElement.closest('form');
          form.addEventListener('submit', function (event) {
            if (dz.getAcceptedFiles().length) {
              // Si hay archivos en Dropzone, evita el envío automático
              event.preventDefault();
              dz.processQueue();
              dz.on('success', function () {
                form.submit();
              });
            } else {
              form.submit();
            }
          });
        }
      });
    }

    const previewTemplate = `<div class="dz-preview dz-file-preview">
      <div class="dz-details">
        <div class="dz-thumbnail">
          <img data-dz-thumbnail>
          <span class="dz-nopreview">No preview</span>
          <div class="dz-success-mark"></div>
          <div class="dz-error-mark"></div>
          <div class="dz-error-message"><span data-dz-errormessage></span></div>
          <div class="progress">
            <div class="progress-bar progress-bar-primary" role="progressbar" aria-valuemin="0" aria-valuemax="100" data-dz-uploadprogress></div>
          </div>
        </div>
        <div class="dz-filename" data-dz-name></div>
        <div class="dz-size" data-dz-size></div>
      </div>
    </div>`;

    $(function () {
      var select2 = $('.select2');
      if (select2.length) {
        select2.each(function () {
          var $this = $(this);
          $this.wrap('<div class="position-relative"></div>').select2({
            dropdownParent: $this.parent(),
            placeholder: $this.data('placeholder')
          });
        });
      }



      toggleFields();

      $('#productType').change(function () {
        toggleFields();
      });

      function updateRawMaterialOptions() {
        var rawMaterialSelects = $('.raw-material-select');
        var selectedRawMaterials = rawMaterialSelects
          .map(function () {
            return $(this).val();
          })
          .get();

        rawMaterialSelects.each(function () {
          $(this)
            .find('option')
            .each(function () {
              var $option = $(this);
              if (selectedRawMaterials.includes($option.val()) && !$option.is(':selected')) {
                $option.prop('disabled', true);
              } else {
                $option.prop('disabled', false);
              }
            });
        });
      }

      function updateFlavorOptions() {
        var flavorSelects = $('.used-flavor-select');
        var selectedFlavors = flavorSelects
          .map(function () {
            return $(this).val();
          })
          .get();

        flavorSelects.each(function () {
          $(this)
            .find('option')
            .each(function () {
              var $option = $(this);
              if (selectedFlavors.includes($option.val()) && !$option.is(':selected')) {
                $option.prop('disabled', true);
              } else {
                $option.prop('disabled', false);
              }
            });
        });
      }

      function canAddMoreRawMaterials() {
        var lastQuantityInput = $('.recipe-card input[name^="recipes["][name$="[quantity]"]').last();
        return lastQuantityInput.val() !== '';
      }

      function getRawMaterialOptions() {
        var rawMaterials = JSON.parse(document.querySelector('.app-ecommerce').dataset.rawMaterials);
        var options = '<option value="" disabled selected>Selecciona una materia prima</option>';
        rawMaterials.forEach(function (rawMaterial) {
          options += `<option value="${rawMaterial.id}" data-unit="${rawMaterial.unit_of_measure}">${rawMaterial.name}</option>`;
        });
        return options;
      }

      function getFlavorOptions() {
        var flavors = JSON.parse(document.querySelector('.app-ecommerce').dataset.flavors);
        var options = '<option value="" disabled selected>Selecciona un sabor</option>';
        flavors.forEach(function (flavor) {
          options += `<option value="${flavor.id}">Balde de ${flavor.name}</option>`;
        });
        return options;
      }

      function updateRecipeIndices() {
        $('[data-repeater-list="recipes"]')
          .children()
          .each(function (index) {
            $(this).find('.raw-material-select').attr('name', `recipes[${index}][raw_material_id]`);
            $(this).find('.used-flavor-select').attr('name', `recipes[${index}][used_flavor_id]`);
            $(this).find('input[name$="[quantity]"]').attr('name', `recipes[${index}][quantity]`);
            $(this).find('input[name$="[units_per_bucket]"]').attr('name', `recipes[${index}][units_per_bucket]`);
          });
      }

      $(document).on('click', '#addRawMaterial', function () {
        if (canAddMoreRawMaterials()) {
          var repeaterList = $('[data-repeater-list="recipes"]');
          var rawMaterialOptions = getRawMaterialOptions();
          var repeaterItem = `
            <div class="row mb-3" data-repeater-item>
              <div class="col-4">
                <label class="form-label" for="raw-material">Materia Prima</label>
                <select class="form-select raw-material-select" name="recipes[][raw_material_id]">
                  ${rawMaterialOptions}
                </select>
              </div>
              <div class="col-3">
                <label class="form-label" for="quantity">Cantidad</label>
                <input type="number" class="form-control" name="recipes[][quantity]" placeholder="Cantidad" aria-label="Cantidad" disabled>
              </div>
              <div class="col-3 d-flex align-items-end">
                <input type="text" class="form-control unit-of-measure" placeholder="Unidad de medida" readonly>
              </div>
              <div class="col-2 d-flex align-items-end">
                <button type="button" class="btn btn-danger" data-repeater-delete>Eliminar</button>
              </div>
            </div>`;
          repeaterList.append(repeaterItem);
          updateRecipeIndices();
          updateRawMaterialOptions();
        } else {
          alert('Por favor, ingrese la cantidad antes de agregar una nueva materia prima.');
        }
      });

      $(document).on('click', '#addUsedFlavor', function () {
        if (canAddMoreRawMaterials()) {
          var repeaterList = $('[data-repeater-list="recipes"]');
          var flavorOptions = getFlavorOptions();
          var repeaterItem = `
            <div class="row mb-3" data-repeater-item>
              <div class="col-4">
                <label class="form-label" for="used-flavor">Sabor Usado</label>
                <select class="form-select used-flavor-select" name="recipes[][used_flavor_id]">
                  ${flavorOptions}
                </select>
              </div>
              <div class="col-3">
                <label class="form-label" for="units-per-bucket">Unidades por Balde</label>
                <input type="number" class="form-control units-per-bucket" name="recipes[][units_per_bucket]" placeholder="Unidades por balde" aria-label="Unidades por balde" disabled>
              </div>
              <div class="col-3">
                <label class="form-label" for="quantity-individual">Cantidad Individual</label>
                <input type="number" class="form-control quantity-individual" name="recipes[][quantity]" placeholder="Cantidad Individual" aria-label="Cantidad Individual" readonly>
              </div>
              <div class="col-2 d-flex align-items-end">
                <button type="button" class="btn btn-danger" data-repeater-delete>Eliminar</button>
              </div>
            </div>`;
          repeaterList.append(repeaterItem);
          updateRecipeIndices();
          updateFlavorOptions();
        } else {
          alert('Por favor, ingrese la cantidad antes de agregar un nuevo sabor.');
        }
      });

      $(document).on('click', '[data-repeater-delete]', function () {
        $(this).closest('.row').remove();
        updateRecipeIndices();
        updateRawMaterialOptions();
        updateFlavorOptions();
      });

      $(document).on('change', '.raw-material-select', function () {
        var unitOfMeasure = $(this).find('option:selected').data('unit');
        $(this).closest('.row').find('.unit-of-measure').val(unitOfMeasure);
        var quantityInput = $(this).closest('.row').find('input[name$="[quantity]"]');
        quantityInput.prop('disabled', !$(this).val());
        quantityInput.val($(this).val() ? quantityInput.val() : '');
        updateRawMaterialOptions();
      });

      $(document).on('change', '.used-flavor-select', function () {
        var unitsPerBucketInput = $(this).closest('.row').find('.units-per-bucket');
        unitsPerBucketInput.prop('disabled', !$(this).val());
        unitsPerBucketInput.val($(this).val() ? unitsPerBucketInput.val() : '');
        updateFlavorOptions();
      });

      $(document).on('input', '.units-per-bucket', function () {
        var unitsPerBucket = parseFloat($(this).val());
        var quantityIndividualInput = $(this).closest('.row').find('.quantity-individual');
        if (unitsPerBucket > 0) {
          var individualQuantity = 1 / unitsPerBucket;
          quantityIndividualInput.val(individualQuantity.toFixed(4));
        } else {
          quantityIndividualInput.val('');
        }
      });

      // Agregar evento para el botón "Seleccionar todos"
      $('#selectAllFlavorsButton').on('click', function () {
        var flavorsSelect = $('.variationOptions');
        flavorsSelect.find('option').prop('selected', true).trigger('change');
      });
    });

    document.addEventListener('DOMContentLoaded', function () {
      var statusSwitch = document.getElementById('statusSwitch');
      statusSwitch.value = statusSwitch.checked ? '1' : '2';

      statusSwitch.addEventListener('change', function () {
        this.value = this.checked ? '1' : '2';
      });
    });

    $(document).ready(function () {
      $('#category-org').select2({
        placeholder: 'Seleccione la(s) categoría(s)',
        allowClear: true
      });

      const discardButton = document.getElementById('discardButton');

      discardButton.addEventListener('click', function () {
        let isFormFilled = Array.from(document.querySelector('form').elements).some(input => {
          if (input.type !== 'submit' && input.type !== 'button' && input.value !== '') {
            return true;
          }
        });

        if (isFormFilled) {
          Swal.fire({
            title: '¿Estás seguro?',
            text: 'Si continúas, perderás los datos no guardados.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, salir',
            cancelButtonText: 'Cancelar'
          }).then(result => {
            if (result.isConfirmed) {
              history.back();
            }
          });
        } else {
          history.back();
        }
      });
    });
  });

  document.getElementById('bar_code').addEventListener('keypress', function(event) {
    // Si el código de la tecla presionada es 13 (Enter)
    if (event.keyCode === 13 || event.which === 13) {
        // Evita que el formulario se envíe
        event.preventDefault();
        // Opcional: Puedes hacer algo más aquí, como mover el enfoque a otro campo
        console.log('Enter presionado en el campo de código de barras.');
    }
  });

})();
