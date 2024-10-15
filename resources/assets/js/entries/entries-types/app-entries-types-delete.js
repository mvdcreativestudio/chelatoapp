$(document).ready(function () {
  // Eliminar un tipo de asiento contable
  $('.datatables-entry-types tbody').on('click', '.delete-record', function () {
    var recordId = $(this).data('id');
    deleteEntryType(recordId);
  });

  // Eliminar múltiples registros de tipos de asientos
  $('#deleteSelected').on('click', function () {
    var selectedIds = [];

    $('.datatables-entry-types tbody input[type="checkbox"]:checked').each(function () {
      selectedIds.push($(this).data('id'));
    });

    if (selectedIds.length === 0) {
      Swal.fire({
        icon: 'warning',
        title: 'Atención',
        text: 'Por favor, seleccione al menos un tipo de asiento para eliminar.'
      });
      return;
    }

    confirmMultipleDeletionEntryTypes(selectedIds);
  });

  function deleteEntryType(recordId) {
    // Función para eliminar un tipo de asiento contable
    Swal.fire({
      title: '¿Estás seguro de eliminar este tipo de asiento?',
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
          url: `${baseUrl}admin/entry-types/${recordId}`,
          type: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function (result) {
            if (result.success) {
              Swal.fire('Eliminado!', 'El tipo de asiento ha sido eliminado.', 'success');
              window.location.reload(); // Recargar la página
            } else {
              Swal.fire('Error!', 'No se pudo eliminar el tipo de asiento. Intente de nuevo.', 'error');
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            Swal.fire('Error!', 'No se pudo eliminar el tipo de asiento: ' + xhr.responseJSON.message, 'error');
          }
        });
      }
    });
  }

  function confirmMultipleDeletionEntryTypes(selectedIds) {
    // Muestra un modal de confirmación para eliminar múltiples tipos de asientos
    Swal.fire({
      title: '¿Estás seguro de eliminar los tipos de asiento seleccionados?',
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
          url: `${baseUrl}admin/entry-types/delete-multiple`,
          type: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          data: {
            ids: selectedIds
          },
          success: function (result) {
            if (result.success) {
              Swal.fire('Eliminado!', 'Los tipos de asiento seleccionados han sido eliminados.', 'success');
              window.location.reload(); // Recargar la página
            } else {
              Swal.fire('Error!', 'No se pudieron eliminar los tipos de asiento seleccionados. Intente de nuevo.', 'error');
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            Swal.fire(
              'Error!',
              'No se pudieron eliminar los tipos de asiento seleccionados: ' + xhr.responseJSON.message,
              'error'
            );
          }
        });
      }
    });
  }
});
