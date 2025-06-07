$(document).ready(function () {
  // Eliminar registro de configuración de cuenta corriente
  $('.datatables-current-account-settings tbody').on('click', '.delete-record', function () {
    var recordId = $(this).data('id');
    deleteCurrentAccountSetting(recordId);
  });

  // Eliminar múltiples registros de configuración de cuenta corriente
  $('#deleteSelected').on('click', function () {
    var selectedIds = [];

    $('.datatables-current-account-settings tbody input[type="checkbox"]:checked').each(function () {
      selectedIds.push($(this).data('id'));
    });

    if (selectedIds.length === 0) {
      Swal.fire({
        icon: 'warning',
        title: 'Atención',
        text: 'Por favor, seleccione al menos una configuración para eliminar.'
      });
      return;
    }

    confirmMultipleDeletionCurrentAccountSetting(selectedIds);
  });

  function deleteCurrentAccountSetting(recordId) {
    // Función para eliminar una configuración de cuenta corriente
    Swal.fire({
      title: '¿Estás seguro de eliminar esta configuración?',
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
          url: 'current-account-settings/' + recordId,
          type: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function (result) {
            if (result.success) {
              Swal.fire('Eliminado!', 'La configuración ha sido eliminada.', 'success');
              location.reload();
            } else {
              Swal.fire('Error!', 'No se pudo eliminar la configuración. Intente de nuevo.', 'error');
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            if (xhr.status === 403) {
              Swal.fire('Permiso denegado!', xhr.responseJSON.message, 'error');
            } else {
              Swal.fire('Error!', 'No se pudo eliminar la configuración: ' + (xhr.responseJSON.message || thrownError), 'error');
            }
          }
        });
      }
    });
  }

  function confirmMultipleDeletionCurrentAccountSetting(selectedIds) {
    // Muestra un modal de confirmación para eliminar múltiples registros
    Swal.fire({
      title: '¿Estás seguro de eliminar las configuraciones seleccionadas?',
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
          url: 'current-account-settings/delete-multiple',
          type: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          data: {
            ids: selectedIds
          },
          success: function (result) {
            if (result.success) {
              Swal.fire('Eliminado!', 'Las configuraciones seleccionadas han sido eliminadas.', 'success');
              location.reload();
            } else {
              Swal.fire('Error!', 'No se pudieron eliminar las configuraciones seleccionadas. Intente de nuevo.', 'error');
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            if (xhr.status === 403) {
              Swal.fire('Permiso denegado!', xhr.responseJSON.message, 'error');
            } else {
              Swal.fire('Error!', 'No se pudo eliminar la configuración: ' + (xhr.responseJSON.message || thrownError), 'error');
            }
          }
        });
      }
    });
  }
});
