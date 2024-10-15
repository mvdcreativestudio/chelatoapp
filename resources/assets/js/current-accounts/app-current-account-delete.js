$(document).ready(function () {
  // Eliminar registro de cuenta corriente
  $('.datatables-current-accounts tbody').on('click', '.delete-record', function () {
    var recordId = $(this).data('id');
    deleteCurrentAccount(recordId);
  });

  // Eliminar múltiples registros de cuentas corrientes
  $('#deleteSelected').on('click', function () {
    var selectedIds = [];

    $('.datatables-current-accounts tbody input[type="checkbox"]:checked').each(function () {
      selectedIds.push($(this).data('id'));
    });

    if (selectedIds.length === 0) {
      Swal.fire({
        icon: 'warning',
        title: 'Atención',
        text: 'Por favor, seleccione al menos una cuenta corriente para eliminar.'
      });
      return;
    }

    confirmMultipleDeletionCurrentAccounts(selectedIds);
  });

  function deleteCurrentAccount(recordId) {
    // Función para eliminar una cuenta corriente
    Swal.fire({
      title: '¿Estás seguro de eliminar esta cuenta corriente?',
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
          url: 'current-accounts/' + recordId,
          type: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function (result) {
            if (result.success) {
              Swal.fire('Eliminado!', 'La cuenta corriente ha sido eliminada.', 'success');
              $('.datatables-current-accounts').DataTable().ajax.reload();
            } else {
              Swal.fire('Error!', 'No se pudo eliminar la cuenta corriente. Intente de nuevo.', 'error');
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            if (xhr.status === 403) {
              Swal.fire('Permiso denegado!', xhr.responseJSON.message, 'error');
            } else {
              Swal.fire('Error!', 'No se pudo eliminar la cuenta corriente: ' + (xhr.responseJSON.message || thrownError), 'error');
            }
          }
        });
      }
    });
  }

  function confirmMultipleDeletionCurrentAccounts(selectedIds) {
    // Muestra un modal de confirmación para eliminar múltiples cuentas corrientes
    Swal.fire({
      title: '¿Estás seguro de eliminar las cuentas corrientes seleccionadas?',
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
          url: 'current-accounts/delete-multiple',
          type: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          data: {
            ids: selectedIds
          },
          success: function (result) {
            if (result.success) {
              Swal.fire('Eliminado!', 'Las cuentas corrientes seleccionadas han sido eliminadas.', 'success');
              $('.datatables-current-accounts').DataTable().ajax.reload();
            } else {
              Swal.fire('Error!', 'No se pudieron eliminar las cuentas corrientes seleccionadas. Intente de nuevo.', 'error');
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            if (xhr.status === 403) {
              Swal.fire('Permiso denegado!', xhr.responseJSON.message, 'error');
            } else {
              Swal.fire('Error!', 'No se pudo eliminar la cuenta corriente: ' + (xhr.responseJSON.message || thrownError), 'error');
            }
          }
        });
      }
    });
  }
});
