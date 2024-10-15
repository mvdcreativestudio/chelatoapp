$(document).ready(function () {
  // Evento para el botón de guardar el pago
  $('#submitPaymentBtn').on('click', function (e) {
    e.preventDefault(); // Evita el comportamiento predeterminado del formulario
    submitNewPayment();
  });

  // Función para enviar los datos del nuevo pago
  function submitNewPayment() {
    // Valida si todos los campos requeridos están llenos
    if (
      !$('#payment_amount').val() ||
      !$('#payment_method_id').val() ||
      !$('#payment_date').val()
    ) {
      Swal.fire({
        icon: 'error',
        title: 'Campos requeridos',
        text: 'Por favor, completa todos los campos obligatorios.'
      });
      return;
    }

    // Obtener la ruta de acción del formulario
    var route = $('#addNewPaymentForm').attr('action');

    // Recopilar los datos del formulario, determinando si es cliente o proveedor
    var formData = {
      current_account_id: $('input[name="current_account_id"]').val(),
      payment_amount: $('#payment_amount').val(),
      payment_method_id: $('#payment_method_id').val(),
      payment_date: $('#payment_date').val(),
      _token: $('meta[name="csrf-token"]').attr('content') // Token CSRF
    };

    // Si existe client_id o supplier_id, lo agregamos a formData
    if ($('input[name="client_id"]').length > 0) {
      formData.client_id = $('input[name="client_id"]').val();
    } else if ($('input[name="supplier_id"]').length > 0) {
      formData.supplier_id = $('input[name="supplier_id"]').val();
    }

    // Realizar la petición AJAX
    $.ajax({
      url: route,
      type: 'POST',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      data: formData,
      success: function (response) {
        Swal.fire({
          icon: 'success',
          title: 'Pago Registrado',
          text: 'El pago ha sido agregado correctamente.'
        }).then(result => {
          console.log(`${baseUrl}current-account-payments/${response.current_account_id}`);
          window.location.href = `${baseUrl}admin/current-account-payments/${response.current_account_id}`; // Redirige a la lista de pagos de la cuenta corriente
        });
      },
      error: function (xhr) {
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
        });
      }
    });
  }
});
