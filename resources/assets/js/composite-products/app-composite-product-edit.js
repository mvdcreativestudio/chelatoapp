'use strict';

(function () {
  document.addEventListener('DOMContentLoaded', function () {
    // Quill Editor para la descripción del producto compuesto
    const descriptionElement = document.querySelector('.comment-editor');

    if (descriptionElement) {
      const quill = new Quill(descriptionElement, {
        modules: {
          toolbar: '.comment-toolbar'
        },
        placeholder: 'Descripción del producto compuesto',
        theme: 'snow'
      });

      const form = descriptionElement.closest('form');

      if (form) {
        form.addEventListener('submit', function () {
          const hiddenInput = document.getElementById('hiddenDescription');
          if (hiddenInput) {
            hiddenInput.value = quill.root.innerHTML;
          }
        });
      }
    }

    // Control de la imagen subida usando Dropzone
    const dropzoneElement = document.querySelector('#dropzone');
    const hiddenImageInput = document.getElementById('compositeProductImage');

    if (dropzoneElement) {
      const myDropzone = new Dropzone(dropzoneElement, {
        url: '#',
        autoProcessQueue: false,
        maxFiles: 1,
        previewsContainer: '#existingImage',
        clickable: '#btnBrowse, #dropzone',
        maxFilesize: 2,
        acceptedFiles: '.jpg,.jpeg,.png,.gif',
        init: function () {
          const dz = this;

          dz.on('addedfile', function (file) {
            const reader = new FileReader();

            reader.onload = function (event) {
              const arrayBuffer = event.target.result;
              const blob = new Blob([arrayBuffer], { type: file.type });
              const newFile = new File([blob], file.name, { type: file.type });

              const dataTransfer = new DataTransfer();
              dataTransfer.items.add(newFile);

              hiddenImageInput.files = dataTransfer.files;
            };

            reader.readAsArrayBuffer(file);
          });

          dz.on('removedfile', function () {
            hiddenImageInput.value = '';
            if (dz.files.length === 0) {
              dropzoneElement.querySelector('.dz-message').style.display = 'block';
            }
          });

          dz.on('thumbnail', function (file, dataUrl) {
            document.querySelector('#existingImage').innerHTML =
              `<img src="${dataUrl}" alt="Imagen del producto compuesto" class="img-fluid" id="productImagePreview">`;
          });

          const form = dropzoneElement.closest('form');
          form.addEventListener('submit', function (event) {
            if (dz.getAcceptedFiles().length) {
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

    // Inicializar select2
    $(function () {
      const select2 = $('.select2');
      if (select2.length) {
        select2.each(function () {
          const $this = $(this);
          $this.wrap('<div class="position-relative"></div>').select2({
            dropdownParent: $this.parent(),
            placeholder: $this.data('placeholder')
          });
        });
      }

      // Botón "Descartar"
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

    // Evento para manejar la selección de todos los productos en el producto compuesto
    $('#selectAllProductsButton').on('click', function () {
      const productsSelect = $('#product_ids');
      productsSelect.find('option').prop('selected', true).trigger('change');
    });

    // Calcular el costo total basado en los productos seleccionados y cantidades
    const selectedProductsTable = $('#selectedProductsTable tbody');
    const priceAlert = $('#priceAlert');
    const recommendedPriceInput = $('#recommended_price');
    const saveButton = $('#saveButton');
    const productsSelect = $('#product_ids');

    function toggleSaveButton(disable) {
      if (disable) {
        saveButton.prop('disabled', true);
      } else {
        saveButton.prop('disabled', false);
      }
    }

    function calculateTotalRecommendedPrice() {
      let totalRecommendedPrice = 0;
      let hasMissingPrices = false;

      selectedProductsTable.find('tr').each(function () {
        const quantity = $(this).find('.product-quantity').val();
        const buildPrice = $(this).find('.product-quantity').data('build-price');
        // Verificar si algún producto no tiene precio
        if (buildPrice === 0) {
          return (hasMissingPrices = true);
        }
        const subtotal = parseFloat(buildPrice) * parseFloat(quantity);

        $(this)
          .find('.subtotal')
          .text('$' + subtotal.toFixed(2));
        totalRecommendedPrice += subtotal;
      });

      recommendedPriceInput.val(totalRecommendedPrice.toFixed(2));
      // Deshabilitar el botón de guardar si hay productos sin precio
      if (hasMissingPrices) {
        toggleSaveButton(true);
        priceAlert.removeClass('d-none');
      } else {
        toggleSaveButton(false);
        priceAlert.addClass('d-none');
      }
    }

    function addProductToTable(product, quantity = 1) {
      const buildPrice = parseFloat(product.build_price) || 0;
      let rowClass = '';
      const disabled = buildPrice === 0 ? 'disabled' : ''; // Disable input if no build_price

      if (buildPrice === 0) {
        rowClass = 'table-danger'; // Fila con borde rojo si no tiene build_price
      }

      // Comprobar si el producto ya está en la tabla
      if (!selectedProductsTable.find(`tr[data-product-id="${product.id}"]`).length) {
        const row = `
          <tr data-product-id="${product.id}" class="${rowClass}">
            <td>${product.name}</td>
            <td>
              <input type="number" class="form-control product-quantity" value="${quantity}" min="1" data-product-id="${product.id}" data-build-price="${buildPrice}" ${disabled}>
            </td>
            <td>${buildPrice > 0 ? '$' + buildPrice.toFixed(2) : 'N/A'}</td>
            <td class="subtotal">${buildPrice > 0 ? '$' + (buildPrice * quantity).toFixed(2) : 'N/A'}</td>
            <td><button type="button" class="btn btn-danger remove-product" data-product-id="${product.id}">Eliminar</button></td>
          </tr>
        `;
        selectedProductsTable.append(row);
      }
    }

    $('#product_ids').on('change', function () {
      const selectedProductIds = $(this).val() || [];
      const productsData = JSON.parse($('.app-ecommerce').attr('data-products'));

      selectedProductIds.forEach(productId => {
        const product = productsData.find(p => p.id == productId);
        if (product) {
          addProductToTable(product);
        }
      });

      calculateTotalRecommendedPrice(); // Calcular costo total al seleccionar productos
    });

    // Recalcular costo total al cambiar la cantidad
    selectedProductsTable.on('input', '.product-quantity', function (event) {
      const input = $(this);
      if (input.val() <= 0) {
        input.val(1); // Enforce that quantity must be at least 1
      }
      calculateTotalRecommendedPrice();
    });

    // Eliminar producto de la tabla y del select
    selectedProductsTable.on('click', '.remove-product', function () {
      const productId = $(this).data('product-id');

      // Eliminar la fila de la tabla
      $(this).closest('tr').remove();

      // Desmarcar el producto en el select
      const option = productsSelect.find(`option[value="${productId}"]`);
      option.prop('selected', false).trigger('change'); // Desmarca y dispara el evento change

      calculateTotalRecommendedPrice(); // Recalcular costo total después de eliminar
    });

    // Capturar los datos y enviarlos por AJAX
    function submitEditCompositeProduct() {
      // Validar si todos los campos obligatorios están llenos
      if (!$('#composite-product-name').val() || !$('#recommended_price').val()) {
        Swal.fire({
          icon: 'error',
          title: 'Campos requeridos',
          text: 'Por favor, completa todos los campos obligatorios.'
        });
        return;
      }

      // Obtener la ruta de la acción del formulario
      const route = $('#editCompositeProductForm').attr('action');

      const formData = {
        name: $('#composite-product-name').val(),
        description: $('#description').val(),
        price: $('#price').val(),
        recommended_price: $('#recommended_price').val(),
        store_id: $('#store_id').val(),
        products: []
      };

      // Capturar los productos seleccionados y sus cantidades
      selectedProductsTable.find('tr').each(function () {
        const productId = $(this).data('product-id');
        const quantity = $(this).find('.product-quantity').val();
        formData.products.push({
          product_id: productId,
          quantity: quantity
        });
      });

      // Enviar la solicitud AJAX
      $.ajax({
        url: route,
        type: 'PUT',
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        success: function (response) {
          Swal.fire({
            icon: 'success',
            title: 'Producto Compuesto Actualizado',
            text: response.message
          }).then(result => {
            window.location.href = `${baseUrl}admin/composite-products/${response.id}`;
          });
        },
        error: function (xhr) {
          var errorMessage =
            xhr.responseJSON && xhr.responseJSON.errors
              ? Object.values(xhr.responseJSON.errors).flat().join('\n')
              : 'Error desconocido al guardar.';
          Swal.fire({
            icon: 'error',
            title: 'Error al guardar',
            text: errorMessage
          });
        }
      });
    }

    // Asignar la función de envío al botón
    $('#saveButton').on('click', function (e) {
      e.preventDefault();
      submitEditCompositeProduct(); // Llamar a la función al hacer clic en el botón
    });
  });
})();
