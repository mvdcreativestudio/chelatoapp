$(document).ready(function() {
  // Evento para agregar nueva configuración de cuenta corriente
  $('#addCurrentAccountSettingModal').on('click', '#submitCurrentAccountSettingBtn', function () {
    submitNewCurrentAccountSetting();
  });

  // Función para enviar el formulario de nueva configuración de cuenta corriente
  function submitNewCurrentAccountSetting() {
    var route = $('#submitCurrentAccountSettingBtn').data('route');
    var formData = {
      transaction_type: $('#transaction_type').val(),
      late_fee: $('#late_fee').val(),
      payment_terms: $('#payment_terms').val(),
    };
    
    $.ajax({
      url: route,
      type: 'POST',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      data: formData,
      success: function (response) {
        $('#addCurrentAccountSettingModal').modal('hide');
        Swal.fire({
          icon: 'success',
          title: 'Configuración de Cuenta Corriente Agregada',
          text: response.message
        }).then(result => {
          // Recargar la tabla de configuraciones de cuentas corrientes si es necesario
          $('.datatables-current-account-settings').DataTable().ajax.reload();
        });
      },
      error: function (xhr) {
        $('#addCurrentAccountSettingModal').modal('hide');

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
          $('#addCurrentAccountSettingModal').modal('show');
        });
      }
    });
  }
});
