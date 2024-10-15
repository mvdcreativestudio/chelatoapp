
$(document).ready(function () {
  // Abrir modal para editar gasto
  $('.datatables-expenses-payments-methods tbody').on('click', '.edit-record', function () {
    var recordId = $(this).data('id');
    prepareEditModal(recordId);
  });
  // Manejar el evento submit del formulario para evitar el comportamiento predeterminado
  $('#editExpensePaymentMethodForm').on('submit', function (e) {
    e.preventDefault();
    var recordId = $('#updateExpensePaymentMethodBtn').data('id');
    submitEditExpense(recordId);
  });

  // Enviar formulario de edición al hacer clic en el botón de guardar cambios
  $('#editExpensePaymentMethodModal').on('click', '#updateExpensePaymentMethodBtn', function (e) {
    e.preventDefault();
    $('#editExpensePaymentMethodForm').submit();
  });
  function prepareEditModal(recordId) {
    // Función para preparar el modal de edición
    $.ajax({
      url: `${baseUrl}admin/expense-payment-methods/${recordId}/edit`,
      type: 'GET',
      success: function (data) {
        // Rellenar los campos del formulario con los datos obtenidos
        $('#amount_paid_edit').val(data.amount_paid);
        $('#payment_date_edit').val(data.payment_date);
        $('#payment_method_id_edit').val(data.payment_method_id);


        // Mostrar el modal
        $('#editExpensePaymentMethodModal').modal('show');
        $('#updateExpensePaymentMethodBtn').data('id', recordId); // Asigna el ID del registro al botón de actualización
      },
      error: function () {
        Swal.fire('Error', 'No se pudo cargar el formulario de edición. Por favor, intenta de nuevo.', 'error');
      }
    });
  }

  function submitEditExpense(recordId) {
    var formData = {
      'amount_paid': $('#amount_paid_edit').val(),
      'payment_date': $('#payment_date_edit').val(),
      'payment_method_id': $('#payment_method_id_edit').val(),
      'expense_id': $('#expense_id_edit').val(),
      '_token': $('meta[name="csrf-token"]').attr('content')
    }
    // return;
    $.ajax({
      url: `${baseUrl}admin/expense-payment-methods/${recordId}`,
      type: 'PUT',
      data: formData,
      success: function () {
        $('#editExpensePaymentMethodModal').modal('hide');
        // $('.datatables-expenses-payments-methods').DataTable().ajax.reload();
        Swal.fire('¡Actualizado!', 'El detalle del gasto ha sido actualizado con éxito.', 'success').then(result => {
          location.reload();
        });
      },
      error: function (xhr) {
        $('#editExpensePaymentMethodModal').modal('hide');

        var errorMessage =
          xhr.responseJSON && xhr.responseJSON.errors
            ? Object.values(xhr.responseJSON.errors).flat().join('\n')
            : 'Error desconocido al guardar.';

        var messageFormatted = '';
        if (xhr.responseJSON.message) {
          messageFormatted = xhr.responseJSON.message;
        }else{
          errorMessage.split('\n').forEach(function (message) {
            messageFormatted += '<div class="text-danger">' + message + '</div>';
          });
        }
        Swal.fire({
          icon: 'error',
          title: 'Error al guardar',
          html: messageFormatted
        }).then(result => {
          $('#editExpensePaymentMethodModal').modal('show');
        });
      }
    });
  }
});