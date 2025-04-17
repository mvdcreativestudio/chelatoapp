$(document).ready(function() {
  const entityType = $('#entity_type');
  const clientField = $('#client_field');
  const supplierField = $('#supplier_field');

  // Inicialmente, ocultar ambos campos
  clientField.hide();
  supplierField.hide();



    $('#currency').on('change', function() {
      const selectedCurrency = $(this).val();
      const exchangeRateField = $('#exchange_rate_field');
      
      if (selectedCurrency === 'Dólar') {
          exchangeRateField.show();
          $('#exchange_rate').prop('required', true);
          
          // Hacer la llamada AJAX para obtener la cotización
          $.ajax({
              url: window.baseUrl + 'admin/dollar-rate',
              type: 'GET',
              success: function(response) {
                  if (response.success && response.rate > 0) {
                      $('#exchange_rate').val(response.rate);
                  }
              },
              error: function(xhr) {
                  console.error('Error al obtener la cotización del dólar:', xhr);
              }
          });
      } else {
          exchangeRateField.hide();
          $('#exchange_rate').prop('required', false);
          $('#exchange_rate').val(0);
      }
  });


  // Manejar el cambio de la selección del tipo de entidad
  entityType.on('change', function() {
    if (this.value === 'client') {
      clientField.show(); // Mostrar el combo de clientes
      supplierField.hide(); // Ocultar el combo de proveedores
      $('#client_id').prop('required', true); 
      $('#supplier_id').prop('required', false);
    } else if (this.value === 'supplier') {
      supplierField.show(); // Mostrar el combo de proveedores
      clientField.hide();  // Ocultar el combo de clientes
      $('#supplier_id').prop('required', true);
      $('#client_id').prop('required', false);
    } else {
      // Si se selecciona 'Ninguno'
      clientField.hide();
      supplierField.hide();
      $('#client_id').prop('required', false);
      $('#supplier_id').prop('required', false);
    }
  });

  // Agregar nueva fila de items
  $('#addItemBtn').on('click', function() {
      let newRow = `
          <tr>
              <td><input type="text" class="form-control" name="item_name[]" value=""></td>
              <td><input type="number" class="form-control" name="item_price[]" value="0"></td>
              <td><input type="number" class="form-control" name="item_quantity[]" value="1"></td>
              <td class="text-center">
                  <button type="button" class="btn btn-danger btn-sm remove-item-btn">
                      <i class="fas fa-trash-alt"></i>
                  </button>
              </td>
          </tr>
      `;
      $('#itemsTable tbody').append(newRow);
  });

  // Eliminar fila de items (usamos "event delegation" porque las filas se agregan dinámicamente)
  $('#itemsTable').on('click', '.remove-item-btn', function() {
    $(this).closest('tr').remove();
  });

  // Lógica para enviar el formulario
  $('#addIncomeModal').on('click', '#submitIncomeBtn', function(e) {
    e.preventDefault();
    submitNewIncome();
  });

  function submitNewIncome() {
    var route = $('#submitIncomeBtn').data('route');
    var entityTypeValue = $('#entity_type').val(); // Captura el tipo de entidad

    // Recorremos las filas de la tabla Items para construir el array
    var items = [];
    $('#itemsTable tbody tr').each(function() {
      let row = $(this);
      let name = row.find('input[name="item_name[]"]').val();
      let price = row.find('input[name="item_price[]"]').val();
      let quantity = row.find('input[name="item_quantity[]"]').val();

      // Si deseas validar vacíos, puedes hacerlo aquí
      items.push({
        name: name || '',
        price: price || 0,
        quantity: quantity || 1
      });
    });

    var formData = {
      income_name: $('#income_name').val(),
      income_description: $('#income_description').val(),
      income_date: $('#income_date').val(),
      // income_amount: $('#income_amount').val(),
      payment_method_id: $('#payment_method_id').val(),
      income_category_id: $('#income_category_id').val() || null,
      currency: $('#currency').val(),
      currency_rate: $('#currency').val() === 'Dólar' ? 
                      $('#exchange_rate').val() : 0,
      tax_rate_id: $('#tax_rate_id').val() || null,
    };

    // Agregar client_id o supplier_id según el tipo de entidad seleccionado
    if (entityTypeValue === 'client') {
      formData.client_id = $('#client_id').val();
      formData.supplier_id = null; 
    } else if (entityTypeValue === 'supplier') {
      formData.supplier_id = $('#supplier_id').val();
      formData.client_id = null; 
    } else {
      formData.client_id = null; 
      formData.supplier_id = null;
    }

    // Adjuntamos el array de items
    // IMPORTANTE: se suele enviar como JSON string
    formData.items = items;

    $.ajax({
      url: route,
      type: 'POST',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      data: formData,
      success: function(response) {
        $('#addIncomeModal').modal('hide');
        Swal.fire({
          icon: 'success',
          title: 'Ingreso Agregado',
          text: response.message
        }).then(result => {
          location.reload();
        });
      },
      error: function(xhr) {
        $('#addIncomeModal').modal('hide');

        var errorMessage =
          xhr.responseJSON && xhr.responseJSON.errors
            ? Object.values(xhr.responseJSON.errors).flat().join('\n')
            : 'Error desconocido al guardar.';
        var messageFormatted = '';
        if (xhr.responseJSON.message) {
          messageFormatted = xhr.responseJSON.message;
        } else {
          errorMessage.split('\n').forEach(function(message) {
            messageFormatted += '<div class="text-danger">' + message + '</div>';
          });
        }
        Swal.fire({
          icon: 'error',
          title: 'Error al guardar',
          html: messageFormatted
        }).then(result => {
          $('#addIncomeModal').modal('show');
        });
      }
    });
  }
});