$(document).ready(function () {
  // Eliminar registro
  $('.datatables-income-categories tbody').on('click', '.delete-record', function () {
    var recordId = $(this).data('id');
    deleteIncomeCategory(recordId);
  });

  // Eliminar múltiples registros

  $('#deleteSelected').on('click', function () {
    var selectedIds = [];

    $('.datatables-income-categories tbody input[type="checkbox"]:checked').each(function () {
      selectedIds.push($(this).data('id'));
    });

    if (selectedIds.length === 0) {
      Swal.fire({
        icon: 'warning',
        title: 'Atención',
        text: 'Por favor, seleccione al menos una categoría para eliminar.'
      });
      return;
    }

    confirmMultipleDeletionIncomeCategory(selectedIds);
  });

  function deleteIncomeCategory(recordId) {
    // Función para eliminar una categoría de ingreso
    Swal.fire({
      title: '¿Estás seguro de eliminar esta categoría?',
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
          url: 'income-categories/' + recordId,
          type: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function (result) {
            if (result.success) {
              Swal.fire('Eliminado!', 'La categoría ha sido eliminada.', 'success');
              location.reload();
            } else {
              Swal.fire('Error!', 'No se pudo eliminar la categoría. Intente de nuevo.', 'error');
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            if (xhr.status === 403) {
              Swal.fire('Permiso denegado!', xhr.responseJSON.message, 'error');
            } else {
              Swal.fire('Error!', 'No se pudo eliminar la categoría: ' + (xhr.responseJSON.message || thrownError), 'error');
            }
          }
        });
      }
    });
  }

  function confirmMultipleDeletionIncomeCategory(selectedIds) {
    // Muestra un modal de confirmación para eliminar múltiples registros
    Swal.fire({
      title: '¿Estás seguro de eliminar las categorías seleccionadas?',
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
          url: 'income-categories/delete-multiple',
          type: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          data: {
            ids: selectedIds
          },
          success: function (result) {
            if (result.success) {
              Swal.fire('Eliminado!', 'Las categorías seleccionadas han sido eliminadas.', 'success');
              location.reload();
            } else {
              Swal.fire('Error!', 'No se pudieron eliminar las categorías seleccionadas. Intente de nuevo.', 'error');
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            if (xhr.status === 403) {
              Swal.fire('Permiso denegado!', xhr.responseJSON.message, 'error');
            } else {
              Swal.fire('Error!', 'No se pudo eliminar la categoría: ' + (xhr.responseJSON.message || thrownError), 'error');
            }
          }
        });
      }
    });
  }
});
