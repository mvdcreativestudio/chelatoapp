$(document).ready(function () {
  // Eliminar registro
  $('.datatables-currencies tbody').on('click', '.delete-record', function () {
    var recordId = $(this).data('id');
    deleteCurrency(recordId);
  });

  // Eliminar múltiples registros
  $('#deleteSelected').on('click', function () {
    var selectedIds = [];

    $('.datatables-currencies tbody input[type="checkbox"]:checked').each(function () {
      selectedIds.push($(this).data('id'));
    });

    if (selectedIds.length === 0) {
      Swal.fire({
        icon: 'warning',
        title: 'Atención',
        text: 'Por favor, seleccione al menos una moneda para eliminar.'
      });
      return;
    }

    confirmMultipleDeletionCurrency(selectedIds);
  });

  function deleteCurrency(recordId) {
    // Función para eliminar una moneda
    Swal.fire({
      title: '¿Estás seguro de eliminar esta moneda?',
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
          url: 'currencies/' + recordId,
          type: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function (result) {
            if (result.success) {
              Swal.fire('Eliminado!', 'La moneda ha sido eliminada.', 'success');
              location.reload();
            } else {
              Swal.fire('Error!', 'No se pudo eliminar la moneda. Intente de nuevo.', 'error');
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            if (xhr.status === 403) {
              Swal.fire('Permiso denegado!', xhr.responseJSON.message, 'error');
            } else {
              Swal.fire('Error!', 'No se pudo eliminar la moneda: ' + (xhr.responseJSON.message || thrownError), 'error');
            }
          }
        });
      }
    });
  }

  function confirmMultipleDeletionCurrency(selectedIds) {
    // Muestra un modal de confirmación para eliminar múltiples registros
    Swal.fire({
      title: '¿Estás seguro de eliminar las monedas seleccionadas?',
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
          url: 'currencies/delete-multiple',
          type: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          data: {
            ids: selectedIds
          },
          success: function (result) {
            if (result.success) {
              Swal.fire('Eliminado!', 'Las monedas seleccionadas han sido eliminadas.', 'success');
              location.reload();
            } else {
              Swal.fire('Error!', 'No se pudieron eliminar las monedas seleccionadas. Intente de nuevo.', 'error');
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            if (xhr.status === 403) {
              Swal.fire('Permiso denegado!', xhr.responseJSON.message, 'error');
            } else {
              Swal.fire('Error!', 'No se pudo eliminar la moneda: ' + (xhr.responseJSON.message || thrownError), 'error');
            }
          }
        });
      }
    });
  }
});
