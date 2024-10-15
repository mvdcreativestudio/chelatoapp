$(document).ready(function () {
  // Eliminar registro
  $('.datatables-expenses tbody').on('click', '.delete-record', function () {
    var recordId = $(this).data('id');
    deleteExpense(recordId);
  });

  // Eliminar múltiples registros

  $('#deleteSelected').on('click', function () {
    var selectedIds = [];

    $('.datatables-expenses tbody input[type="checkbox"]:checked').each(function () {
      selectedIds.push($(this).data('id'));
    });

    if (selectedIds.length === 0) {
      Swal.fire({
        icon: 'warning',
        title: 'Atención',
        text: 'Por favor, seleccione al menos un gasto para eliminar.'
      });
      return;
    }

    confirmMultipleDeletionExpense(selectedIds);
  });

  function deleteExpense(recordId) {
    // Función para eliminar un gasto
    Swal.fire({
      title: '¿Estás seguro de eliminar este gasto?',
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
          url: 'expenses/' + recordId,
          type: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function (result) {
            if (result.success) {
              Swal.fire('Eliminado!', 'El cupón ha sido eliminado.', 'success');
              $('.datatables-expenses').DataTable().ajax.reload();
            } else {
              Swal.fire('Error!', 'No se pudo eliminar el cupón. Intente de nuevo.', 'error');
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            if (xhr.status === 403) {
              Swal.fire('Permiso denegado!', xhr.responseJSON.message, 'error');
            } else {
              Swal.fire('Error!', 'No se pudo eliminar el gasto: ' + (xhr.responseJSON.message || thrownError), 'error');
            }
          }
        });
      }
    });
  }

  function confirmMultipleDeletionExpense(selectedIds) {
    // Muestra un modal de confirmación para eliminar múltiples registros
    Swal.fire({
      title: '¿Estás seguro de eliminar los gastos seleccionados?',
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
          url: 'expenses/delete-multiple',
          type: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          data: {
            ids: selectedIds
          },
          success: function (result) {
            if (result.success) {
              Swal.fire('Eliminado!', 'Los gastos seleccionados han sido eliminados.', 'success');
              // dt_expenses.ajax.reload(null, false);
              $('.datatables-expenses').DataTable().ajax.reload();
            } else {
              Swal.fire('Error!', 'No se pudieron eliminar los gastos seleccionados. Intente de nuevo.', 'error');
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            if (xhr.status === 403) {
              Swal.fire('Permiso denegado!', xhr.responseJSON.message, 'error');
            } else {
              Swal.fire('Error!', 'No se pudo eliminar el gasto: ' + (xhr.responseJSON.message || thrownError), 'error');
            }
          }
        });
      }
    });
  }
});
