$(document).ready(function() {
  $('#addExpensePaymentMethodModal').on('click', '#submitExpensePaymentMethodBtn', function () {
    submitNewExpense();
  });

  function submitNewExpense() {
    var route = $('#submitExpensePaymentMethodBtn').data('route');
    var formData = {
      amount_paid: $('#amount_paid').val(),
      payment_date: $('#payment_date').val(),
      payment_method_id: $('#payment_method_id').val(),
      expense_id: $('#expense_id').val(),
    };
    $.ajax({
      url: route,
      type: 'POST',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      data: formData,
      success: function (response) {
        $('#addExpensePaymentMethodModal').modal('hide');
        Swal.fire({
          icon: 'success',
          title: 'Detalle de Gasto Agregado',
          text: response.message
        }).then(result => {
          // $('.datatables-expenses-paymentes-methods').DataTable().ajax.reload();
          window.location.reload();
          // window.location.href = `${baseUrl}admin/expense-payment-methods${response.id}/detail`;
        });
      },
      error: function (xhr) {
        $('#addExpensePaymentMethodModal').modal('hide');

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
          $('#addExpensePaymentMethodModal').modal('show');
        });
      }
    });
  }
});