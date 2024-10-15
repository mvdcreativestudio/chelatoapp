$(document).ready(function () {
  // Eliminar registro de detalle de asiento
  $('.datatables-entry-details tbody').on('click', '.delete-record', function () {
    var recordId = $(this).data('id');
    deleteEntryDetail(recordId);
  });

  // Eliminar múltiples registros
  $('#deleteSelected').on('click', function () {
    var selectedIds = [];

    $('.datatables-entry-details tbody input[type="checkbox"]:checked').each(function () {
      selectedIds.push($(this).data('id'));
    });

    if (selectedIds.length === 0) {
      Swal.fire({
        icon: 'warning',
        title: 'Atención',
        text: 'Por favor, seleccione al menos un detalle de asiento para eliminar.'
      });
      return;
    }

    confirmMultipleDeletionEntryDetails(selectedIds);
  });

  function deleteEntryDetail(recordId) {
    // Función para eliminar un detalle de asiento
    Swal.fire({
      title: '¿Estás seguro de eliminar este detalle de asiento?',
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
          url: `${baseUrl}admin/entry-details/${recordId}`,
          type: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function (result) {
            if (result.success) {
              Swal.fire('Eliminado!', 'El detalle de asiento ha sido eliminado.', 'success');
              window.location.reload(); // Recargar la página
            } else {
              Swal.fire('Error!', 'No se pudo eliminar el detalle de asiento. Intente de nuevo.', 'error');
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            Swal.fire('Error!', 'No se pudo eliminar el detalle de asiento: ' + xhr.responseJSON.message, 'error');
          }
        });
      }
    });
  }

  function confirmMultipleDeletionEntryDetails(selectedIds) {
    // Muestra un modal de confirmación para eliminar múltiples registros
    Swal.fire({
      title: '¿Estás seguro de eliminar los detalles de asiento seleccionados?',
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
          url: `${baseUrl}admin/entry-details/delete-multiple`,
          type: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          data: {
            ids: selectedIds
          },
          success: function (result) {
            if (result.success) {
              Swal.fire('Eliminado!', 'Los detalles de asiento seleccionados han sido eliminados.', 'success');
              window.location.reload(); // Recargar la página
            } else {
              Swal.fire('Error!', 'No se pudieron eliminar los detalles de asiento seleccionados. Intente de nuevo.', 'error');
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            Swal.fire(
              'Error!',
              'No se pudieron eliminar los detalles de asiento seleccionados: ' + xhr.responseJSON.message,
              'error'
            );
          }
        });
      }
    });
  }
});
