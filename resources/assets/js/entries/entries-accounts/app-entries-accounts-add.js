$(document).ready(function() {
  // Evitar que el campo 'code' permita ingresar letras o caracteres no numéricos
  $('#code').on('input', function (e) {
    // Permitir solo números y evitar la letra 'e' y otros caracteres no numéricos
    this.value = this.value.replace(/[^0-9]/g, '');
  });

  // Escuchar el evento de clic para enviar el formulario
  $('#addEntryAccountModal').on('click', '#submitEntryAccountBtn', function () {
    // Validar si el campo 'code' es numérico
    if (validateCode()) {
      submitNewEntryAccount();
    }
  });

  function validateCode() {
    var code = $('#code').val();
    if (!code || isNaN(code)) {
      Swal.fire({
        icon: 'error',
        title: 'Error de validación',
        text: 'El código de la cuenta contable debe ser un número válido.'
      });
      return false;
    }
    return true;
  }

  function submitNewEntryAccount() {
    // Obtener la ruta desde el botón de envío
    var route = $('#submitEntryAccountBtn').data('route');
    
    // Recopilar los datos del formulario
    var formData = {
      code: $('#code').val(),
      name: $('#name').val(),
      description: $('#description').val()
    };
    
    // Realizar la petición AJAX para guardar la cuenta contable
    $.ajax({
      url: route,
      type: 'POST',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      data: formData,
      success: function (response) {
        // Ocultar el modal tras el éxito
        $('#addEntryAccountModal').modal('hide');
        
        // Mostrar notificación de éxito
        Swal.fire({
          icon: 'success',
          title: 'Cuenta Contable Agregada',
          text: response.message
        }).then(result => {
          // Recargar la página para reflejar el nuevo registro agregado
          window.location.reload();
        });
      },
      error: function (xhr) {
        // Ocultar el modal en caso de error
        $('#addEntryAccountModal').modal('hide');

        // Manejar los mensajes de error
        var errorMessage =
          xhr.responseJSON && xhr.responseJSON.errors
            ? Object.values(xhr.responseJSON.errors).flat().join('\n')
            : 'Error desconocido al guardar.';
        
        var messageFormatted = '';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          messageFormatted = xhr.responseJSON.message;
        } else {
          errorMessage.split('\n').forEach(function (message) {
            messageFormatted += '<div class="text-danger">' + message + '</div>';
          });
        }

        // Mostrar notificación de error
        Swal.fire({
          icon: 'error',
          title: 'Error al guardar',
          html: messageFormatted
        }).then(result => {
          // Reabrir el modal en caso de error
          $('#addEntryAccountModal').modal('show');
        });
      }
    });
  }
});
