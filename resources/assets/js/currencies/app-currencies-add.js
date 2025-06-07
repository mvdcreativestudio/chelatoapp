$(document).ready(function() {
  // Evento para agregar nueva moneda
  $('#addCurrencyModal').on('click', '#submitCurrencyBtn', function () {
    submitNewCurrency();
  });

  // Función para enviar el formulario de nueva moneda
  function submitNewCurrency() {
    var route = $('#submitCurrencyBtn').data('route');
    var formData = {
      code: $('#currency_code').val(),
      symbol: $('#currency_symbol').val(),
      name: $('#currency_name').val(),
      exchange_rate: $('#exchange_rate').val(),
    };
    
    $.ajax({
      url: route,
      type: 'POST',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      data: formData,
      success: function (response) {
        $('#addCurrencyModal').modal('hide');
        Swal.fire({
          icon: 'success',
          title: 'Moneda Agregada',
          text: response.message
        }).then(result => {
          // Recargar la tabla de monedas si es necesario
          $('.datatables-currencies').DataTable().ajax.reload();
        });
      },
      error: function (xhr) {
        $('#addCurrencyModal').modal('hide');

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
          $('#addCurrencyModal').modal('show');
        });
      }
    });
  }
});
