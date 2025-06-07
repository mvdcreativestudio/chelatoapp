$(document).ready(function () {
  // Eliminar una categoría de gasto
  $('.datatables-expense-categories tbody').on('click', '.delete-record', function () {
    var recordId = $(this).data('id');
    deleteExpenseCategory(recordId);
  });

  // Eliminar múltiples categorías de gastos
  $('#deleteSelected').on('click', function () {
    var selectedIds = [];

    $('.datatables-expense-categories tbody input[type="checkbox"]:checked').each(function () {
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

    confirmMultipleDeletionExpenseCategory(selectedIds);
  });

  // Función para eliminar una categoría de gasto
  function deleteExpenseCategory(recordId) {
    Swal.fire({
      title: '¿Estás seguro de eliminar esta categoría de gasto?',
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
          url: 'expense-categories/' + recordId,
          type: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function (result) {
            if (result.success) {
              Swal.fire('Eliminado!', 'La categoría de gasto ha sido eliminada.', 'success');
              location.reload();
            } else {
              Swal.fire('Error!', 'No se pudo eliminar la categoría de gasto. Intente de nuevo.', 'error');
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            if (xhr.status === 403) {
              Swal.fire('Permiso denegado!', xhr.responseJSON.message, 'error');
            } else {
              Swal.fire('Error!', 'No se pudo eliminar la categoría de gasto: ' + (xhr.responseJSON.message || thrownError), 'error');
            }
          }
        });
      }
    });
  }

  // Función para confirmar la eliminación de múltiples categorías de gastos
  function confirmMultipleDeletionExpenseCategory(selectedIds) {
    Swal.fire({
      title: '¿Estás seguro de eliminar las categorías de gastos seleccionadas?',
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
          url: 'expense-categories/delete-multiple',
          type: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          data: {
            ids: selectedIds
          },
          success: function (result) {
            if (result.success) {
              Swal.fire('Eliminado!', 'Las categorías de gastos seleccionadas han sido eliminadas.', 'success');
              location.reload();
            } else {
              Swal.fire('Error!', 'No se pudieron eliminar las categorías de gastos seleccionadas. Intente de nuevo.', 'error');
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            if (xhr.status === 403) {
              Swal.fire('Permiso denegado!', xhr.responseJSON.message, 'error');
            } else {
              Swal.fire('Error!', 'No se pudo eliminar la categoría de gasto: ' + (xhr.responseJSON.message || thrownError), 'error');
            }
          }
        });
      }
    });
  }
});
