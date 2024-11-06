$(document).ready(function () {
  const entityType = $('#edit_entity_type');
  const clientField = $('#edit_client_field');
  const supplierField = $('#edit_supplier_field');

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
  $('.datatables-incomes tbody').on('click', '.edit-record', function () {
    var recordId = $(this).data('id');
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

  // Preparar modal de edición con los datos del ingreso
  function prepareEditModal(recordId) {
    $.ajax({
      url: `incomes/${recordId}/edit`,
      type: 'GET',
      success: function (data) {
        // Rellenar los campos del formulario con los datos obtenidos
        $('#edit_income_name').val(data.income_name);
        $('#edit_income_description').val(data.income_description);
        $('#edit_income_date').val(moment(data.income_date).format('YYYY-MM-DD'));
        $('#edit_income_amount').val(data.income_amount);
        $('#edit_payment_method_id').val(data.payment_method_id);
        $('#edit_income_category_id').val(data.income_category_id);
        $('#edit_currency_id').val(data.currency_id);

        // Lógica para mostrar Cliente o Proveedor según el tipo de entidad
        if (data.client_id != null) {
          $('#edit_entity_type').val('client').trigger('change');
          $('#edit_client_id').val(data.client_id);
        } else if (data.supplier_id != null) {
          $('#edit_entity_type').val('supplier').trigger('change');
          $('#edit_supplier_id').val(data.supplier_id);
        } else {
          $('#edit_entity_type').val('none').trigger('change');
        }

        // Mostrar el modal
        $('#editIncomeModal').modal('show');
        $('#submitEditIncomeBtn').data('id', recordId); // Asigna el ID del registro al botón de actualización
      },
      error: function () {
        Swal.fire('Error', 'No se pudo cargar el formulario de edición. Por favor, intenta de nuevo.', 'error');
      }
    });
  }

  // Función para enviar los datos editados
  function submitEditIncome(recordId) {
    var formData = {
      income_name: $('#edit_income_name').val(),
      income_description: $('#edit_income_description').val(),
      income_date: $('#edit_income_date').val(),
      income_amount: $('#edit_income_amount').val(),
      payment_method_id: $('#edit_payment_method_id').val(),
      income_category_id: $('#edit_income_category_id').val(),
      currency_id: $('#edit_currency_id').val(),
      client_id: $('#edit_client_id').val(),
      supplier_id: $('#edit_supplier_id').val(),
      '_token': $('meta[name="csrf-token"]').attr('content')
    };

    $.ajax({
      url: `incomes/${recordId}`,
      type: 'PUT',
      data: formData,
      success: function () {
        $('#editIncomeModal').modal('hide');
        $('.datatables-incomes').DataTable().ajax.reload();
        Swal.fire('¡Actualizado!', 'El ingreso ha sido actualizado con éxito.', 'success');
      },
      error: function (xhr) {
        $('#editIncomeModal').modal('hide');

        var errorMessage =
          xhr.responseJSON && xhr.responseJSON.errors
            ? Object.values(xhr.responseJSON.errors).flat().join('\n')
            : 'Error desconocido al guardar.';

        var messageFormatted = '';
        if (xhr.responseJSON.message) {
          messageFormatted = xhr.responseJSON.message;
        } else {
          errorMessage.split('\n').forEach(function (message) {
            messageFormatted += '<div class="text-danger">' + message + '</div>';
          });
        }
        Swal.fire({
          icon: 'error',
          title: 'Error al guardar',
          html: messageFormatted
        }).then(result => {
          $('#editIncomeModal').modal('show');
        });
      }
    });
  }

  // close modal reset
  $('#editIncomeModal').on('hidden.bs.modal', function () {
    $('#editIncomeForm').trigger('reset');
    entityType.val('none').trigger('change');
    clientField.hide();
    supplierField.hide();
  });
});