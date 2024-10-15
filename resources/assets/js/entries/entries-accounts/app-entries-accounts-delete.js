$(document).ready(function () {
  // Eliminar una cuenta contable
  $('.datatables-entry-accounts tbody').on('click', '.delete-record', function () {
    var recordId = $(this).data('id');
    deleteEntryAccount(recordId);
  });

  // Eliminar múltiples registros de cuentas contables
  $('#deleteSelected').on('click', function () {
    var selectedIds = [];

    $('.datatables-entry-accounts tbody input[type="checkbox"]:checked').each(function () {
      selectedIds.push($(this).data('id'));
    });

    if (selectedIds.length === 0) {
      Swal.fire({
        icon: 'warning',
        title: 'Atención',
        text: 'Por favor, seleccione al menos una cuenta contable para eliminar.'
      });
      return;
    }

    confirmMultipleDeletionEntryAccounts(selectedIds);
  });

  function deleteEntryAccount(recordId) {
    // Función para eliminar una cuenta contable
    Swal.fire({
      title: '¿Estás seguro de eliminar esta cuenta contable?',
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
          url: `${baseUrl}admin/entry-accounts/${recordId}`,
          type: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function (result) {
            if (result.success) {
              Swal.fire('Eliminado!', 'La cuenta contable ha sido eliminada.', 'success');
              window.location.reload(); // Recargar la página
            } else {
              Swal.fire('Error!', 'No se pudo eliminar la cuenta contable. Intente de nuevo.', 'error');
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            Swal.fire('Error!', 'No se pudo eliminar la cuenta contable: ' + xhr.responseJSON.message, 'error');
          }
        });
      }
    });
  }

  function confirmMultipleDeletionEntryAccounts(selectedIds) {
    // Muestra un modal de confirmación para eliminar múltiples cuentas contables
    Swal.fire({
      title: '¿Estás seguro de eliminar las cuentas contables seleccionadas?',
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
          url: `${baseUrl}admin/entry-accounts/delete-multiple`,
          type: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          data: {
            ids: selectedIds
          },
          success: function (result) {
            if (result.success) {
              Swal.fire('Eliminado!', 'Las cuentas contables seleccionadas han sido eliminadas.', 'success');
              window.location.reload(); // Recargar la página
            } else {
              Swal.fire('Error!', 'No se pudieron eliminar las cuentas contables seleccionadas. Intente de nuevo.', 'error');
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            Swal.fire(
              'Error!',
              'No se pudieron eliminar las cuentas contables seleccionadas: ' + xhr.responseJSON.message,
              'error'
            );
          }
        });
      }
    });
  }
});
