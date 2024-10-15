
$(document).ready(function () {
  // Abrir modal para editar gasto
  $('.datatables-expenses tbody').on('click', '.edit-record', function () {
    var recordId = $(this).data('id');
    prepareEditModal(recordId);
  });
  // Manejar el evento submit del formulario para evitar el comportamiento predeterminado
  $('#editExpenseForm').on('submit', function (e) {
    e.preventDefault();
    var recordId = $('#updateExpenseBtn').data('id');
    submitEditExpense(recordId);
  });

  // Enviar formulario de edición al hacer clic en el botón de guardar cambios
  $('#editExpenseModal').on('click', '#updateExpenseBtn', function (e) {
    e.preventDefault();
    $('#editExpenseForm').submit();
  });
  function prepareEditModal(recordId) {
    // Función para preparar el modal de edición
    $.ajax({
      url: `expenses/${recordId}/edit`,
      type: 'GET',
      success: function (data) {
        // Rellenar los campos del formulario con los datos obtenidos
        $('#amountEdit').val(data.amount);
        // $('#statusEdit').val(data.status);
        $('#dueDateEdit').val(moment(data.due_date).format('YYYY-MM-DD'));
        $('#supplierIdEdit').val(data.supplier_id);
        $('#expenseCategoryIdEdit').val(data.expense_category_id);
        $('#expenseCurrencyIdEdit').val(data.currency_id);
        $('#storeIdEdit').val(data.store_id);


        // Mostrar el modal
        $('#editExpenseModal').modal('show');
        $('#updateExpenseBtn').data('id', recordId); // Asigna el ID del registro al botón de actualización
      },
      error: function () {
        Swal.fire('Error', 'No se pudo cargar el formulario de edición. Por favor, intenta de nuevo.', 'error');
      }
    });
  }

  function submitEditExpense(recordId) {
    var formData = {
      amount: $('#amountEdit').val(),
      // status: $('#statusEdit').val(),
      due_date: $('#dueDateEdit').val(),
      supplier_id: $('#supplierIdEdit').val(),
      expense_category_id: $('#expenseCategoryIdEdit').val(),
      currency_id: $('#expenseCurrencyIdEdit').val(),
      store_id: $('#storeIdEdit').val(),
      '_token': $('meta[name="csrf-token"]').attr('content')
    }
    // return;
    $.ajax({
      url: `expenses/${recordId}`,
      type: 'PUT',
      data: formData,
      success: function () {
        $('#editExpenseModal').modal('hide');
        $('.datatables-expenses').DataTable().ajax.reload();
        Swal.fire('¡Actualizado!', 'El gasto ha sido actualizado con éxito.', 'success');
      },
      error: function (xhr) {
        $('#editExpenseModal').modal('hide');

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
          $('#editExpenseModal').modal('show');
        });
      }
    });
  }
});