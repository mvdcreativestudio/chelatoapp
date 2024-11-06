$(document).ready(function () {
  // Eliminar registro de pago
  $('.datatables-current-account-payments tbody').on('click', '.delete-record', function () {
    var recordId = $(this).data('id');
    deletePayment(recordId); // Cambiar a la función deletePayment
  });

  function deletePayment(recordId) {
    // Función para eliminar un pago de cuenta corriente
    Swal.fire({
      title: '¿Estás seguro de eliminar este pago?',
      text: 'Esta acción no se puede deshacer',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Sí, eliminar!',
      cancelButtonText: 'Cancelar'
    }).then(result => {
      if (result.isConfirmed) {
        $.ajax({
          // url: 'current-account-client-payments/' + recordId,
          url : `${baseUrl}admin/current-account-payments/${recordId}`,
          type: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function (result) {
            if (result.success) {
              Swal.fire('Eliminado!', 'El pago ha sido eliminado.', 'success');
            } else {
              Swal.fire('Error!', 'No se pudo eliminar el pago. Intente de nuevo.', 'error');
            }
            // Recargar la tabla de pagos
            location.reload();
          },
          error: function (xhr, ajaxOptions, thrownError) {
            if (xhr.status === 403) {
              Swal.fire('Permiso denegado!', xhr.responseJSON.message, 'error');
            } else {
              Swal.fire('Error!', 'No se pudo eliminar el pago: ' + (xhr.responseJSON.message || thrownError), 'error');
            }
          }
        });
      }
    });
  }
});
