$(document).ready(function () {
  const entityType = $('#edit_entity_type');
  const clientField = $('#edit_client_field');
  const supplierField = $('#edit_supplier_field');

  // Campo moneda. Si es dólar, se fija la cotización del día. 
  $('#edit_currency').on('change', function() {
      const selectedCurrency = $(this).val();
      const exchangeRateField = $('#edit_exchange_rate_field');
      
      if (selectedCurrency === 'Dólar') {
          exchangeRateField.show();
          $('#edit_exchange_rate').prop('required', true);
          
          // Hacer la llamada AJAX para obtener la cotización
          $.ajax({
              url: window.baseUrl + 'admin/dollar-rate',
              type: 'GET',
              success: function(response) {
                  if (response.success && response.rate > 0) {
                      $('#edit_exchange_rate').val(response.rate);
                  }
              },
              error: function(xhr) {
                  console.error('Error al obtener la cotización del dólar:', xhr);
              }
          });
      } else {
          exchangeRateField.hide();
          $('#edit_exchange_rate').prop('required', false);
          $('#edit_exchange_rate').val(0);
      }
  });

  // Manejar el cambio en el select de Tipo de Entidad (Cliente/Proveedor/Ninguno)
  entityType.on('change', function () {
    if (this.value === 'client') {
      clientField.show();
      supplierField.hide();
      $('#edit_client_id').prop('required', true).val('');
      $('#edit_supplier_id').prop('required', false).val('');
    } else if (this.value === 'supplier') {
      supplierField.show();
      clientField.hide();
      $('#edit_supplier_id').prop('required', true).val('');
      $('#edit_client_id').prop('required', false).val('');
    } else {
      clientField.hide();
      supplierField.hide();
      $('#edit_client_id').prop('required', false).val('');
      $('#edit_supplier_id').prop('required', false).val('');
    }
  });

  // Abrir modal para editar ingreso
  $('.datatables-incomes tbody').on('click', '.edit-record', function (e) {
    e.preventDefault();
    var recordId = $(this).data('id');
    
    $('#editIncomeForm').trigger('reset');
    $('#editItemsTable tbody').empty();
    
    // Preparamos y abrimos el modal
    prepareEditModal(recordId);
  });

  // Manejar el evento submit del formulario para evitar el comportamiento predeterminado
  $('#editIncomeForm').on('submit', function (e) {
    e.preventDefault();
    var recordId = $('#submitEditIncomeBtn').data('id');
    submitEditIncome(recordId);
  });

  // Enviar formulario de edición al hacer clic en el botón de guardar cambios
  $('#editIncomeModal').on('click', '#submitEditIncomeBtn', function (e) {
    e.preventDefault();
    $('#editIncomeForm').submit();
  });

  // Agregar nueva fila de items
  $('#editAddItemBtn').on('click', function () {
    const newRow = `
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
      `;;
    $('#editItemsTable tbody').append(newRow);
  });

  // Eliminar fila de items
  $('#editItemsTable').on('click', '.remove-item-btn', function () {
    $(this).closest('tr').remove();
  });

  // Preparar modal de edición con los datos del ingreso
  function prepareEditModal(recordId) {
    $.ajax({
      url: window.baseUrl + `admin/incomes/${recordId}/edit`,  
      type: 'GET',
      success: function (data) {
        // Rellenamos los campos
        $('#edit_income_name').val(data.income_name);
        $('#edit_income_description').val(data.income_description);
        $('#edit_income_date').val(moment(data.income_date).format('YYYY-MM-DD'));
        $('#edit_payment_method_id').val(data.payment_method_id);
        $('#edit_income_category_id').val(data.income_category_id);
        $('#edit_currency').val(data.currency);
        $('#edit_tax_rate_id').val(data.tax_rate_id);
        $('#submitEditIncomeBtn').data('id', recordId);

        // Si es dólar, mostramos el campo de cotización
        if (data.currency === 'Dólar') {
            $('#edit_exchange_rate_field').show();
            $('#edit_exchange_rate').prop('required', true);
            $('#edit_exchange_rate').val(data.currency_rate);
        } else {
            $('#edit_exchange_rate_field').hide();
            $('#edit_exchange_rate').prop('required', false);
            $('#edit_exchange_rate').val(0);
        }

        // Manejo de entidad (cliente/proveedor)
        if (data.client_id) {
          $('#edit_entity_type').val('client');
          $('#edit_client_field').show();
          $('#edit_client_id').val(data.client_id);
        } else if (data.supplier_id) {
          $('#edit_entity_type').val('supplier');
          $('#edit_supplier_field').show();
          $('#edit_supplier_id').val(data.supplier_id);
        } else {
          $('#edit_entity_type').val('none');
        }

        // Cargar items
        if (data.items && data.items.length > 0) {
          data.items.forEach(item => {
            const row = `
              <tr>
                <td><input type="text" class="form-control" name="item_name[]" value="${item.name}" required></td>
                <td><input type="number" class="form-control" name="item_price[]" value="${item.price}" required></td>
                <td><input type="number" class="form-control" name="item_quantity[]" value="${item.quantity}" required></td>
                <td class="text-center">
                  <button type="button" class="btn btn-danger btn-sm remove-item-btn">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </td>
              </tr>`;
            $('#editItemsTable tbody').append(row);
          });
        }

        // Abrimos el modal usando Bootstrap 5
        const modalElement = document.getElementById('updateIncomeModal');
          if (modalElement) {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
              const editModal = new bootstrap.Modal(modalElement);
              editModal.show();
            } else {
              // Fallback to jQuery if Bootstrap's native JS isn't available
              $(modalElement).modal('show');
            }
          } else {
            console.error('Modal element #updateIncomeModal not found');
          }
      },
      error: function (xhr) {
        console.error('Error cargando datos:', xhr);
        Swal.fire('Error', 'No se pudieron cargar los datos del ingreso', 'error');
      }
    });
  }

  // Función para enviar los datos editados
  function submitEditIncome(recordId) {
    const items = [];
    $('#editItemsTable tbody tr').each(function () {
        const row = $(this);
        const name = row.find('input[name="item_name[]"]').val();
        const price = row.find('input[name="item_price[]"]').val();
        const quantity = row.find('input[name="item_quantity[]"]').val();

        items.push({ name, price, quantity });
    });

    const formData = {
        income_name: $('#edit_income_name').val(),
        income_description: $('#edit_income_description').val(),
        income_date: $('#edit_income_date').val(),
        payment_method_id: $('#edit_payment_method_id').val(),
        income_category_id: $('#edit_income_category_id').val(),
        currency: $('#edit_currency').val(), 
        exchange_rate: $('#edit_exchange_rate').val(),
        tax_rate_id: $('#edit_tax_rate_id').val(),
        client_id: $('#edit_client_id').val() || null,
        supplier_id: $('#edit_supplier_id').val() || null,
        items: items,
        '_token': $('meta[name="csrf-token"]').attr('content')
    };

    $.ajax({
        url: window.baseUrl + `admin/incomes/${recordId}`,  
        type: 'PUT',
        data: formData,
        success: function () {
            $('#updateIncomeModal').modal('hide');
            $('.datatables-incomes').DataTable().ajax.reload();
            Swal.fire('¡Actualizado!', 'El ingreso ha sido actualizado con éxito.', 'success');
        },
        error: function (xhr) {
          console.error('Error en la actualización:', xhr);
          $('#updateIncomeModal').modal('hide');
          
          const errorMessage = xhr.responseJSON && xhr.responseJSON.errors
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

  $('#updateIncomeModal').on('click', '#submitEditIncomeBtn', function(e) {
    e.preventDefault();
    const recordId = $(this).data('id');
    if (!recordId) {
        console.error('No se encontró el ID del registro');
        return;
    }
    submitEditIncome(recordId);
});
});