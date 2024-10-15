$(document).ready(function () {
  // Eliminar registro
  $('.datatables-expenses-payments-methods tbody').on('click', '.delete-record', function () {
    var recordId = $(this).data('id');
    deleteExpense(recordId);
  });

  // Eliminar múltiples registros

  $('#deleteSelected').on('click', function () {
    var selectedIds = [];

    $('.datatables-expenses-payments-methods tbody input[type="checkbox"]:checked').each(function () {
      selectedIds.push($(this).data('id'));
    });

    if (selectedIds.length === 0) {
      Swal.fire({
        icon: 'warning',
        title: 'Atención',
        text: 'Por favor, seleccione al menos un detalle de gasto para eliminar.'
      });
      return;
    }

    confirmMultipleDeletionExpense(selectedIds);
  });

  function deleteExpense(recordId) {
    // Función para eliminar un gasto
    Swal.fire({
      title: '¿Estás seguro de eliminar este detalle de gasto?',
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
          url: `${baseUrl}admin/expense-payment-methods/${recordId}`,
          type: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function (result) {
            if (result.success) {
              Swal.fire('Eliminado!', 'El detalle de gasto ha sido eliminado.', 'success');
              // $('.datatables-expenses-payments-methods').DataTable().ajax.reload();
              window.location.reload();
            } else {
              Swal.fire('Error!', 'No se pudo eliminar el detalle de gasto. Intente de nuevo.', 'error');
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            Swal.fire('Error!', 'No se pudo eliminar el detalle de gasto: ' + xhr.responseJSON.message, 'error');
          }
        });
      }
    });
  }

  function confirmMultipleDeletionExpense(selectedIds) {
    // Muestra un modal de confirmación para eliminar múltiples registros
    Swal.fire({
      title: '¿Estás seguro de eliminar los detalles de gastos seleccionados?',
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
          url: `${baseUrl}admin/expense-payment-methods/delete-multiple`,
          type: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          data: {
            ids: selectedIds
          },
          success: function (result) {
            if (result.success) {
              Swal.fire('Eliminado!', 'Los detalles de gastos seleccionados han sido eliminados.', 'success');
              // dt_expenses.ajax.reload(null, false);
              // $('.datatables-expenses-payments-methods').DataTable().ajax.reload();
              window.location.reload();
            } else {
              Swal.fire('Error!', 'No se pudieron eliminar los detalles de gastos seleccionados. Intente de nuevo.', 'error');
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            Swal.fire(
              'Error!',
              'No se pudieron eliminar los detalles de gastos seleccionados: ' + xhr.responseJSON.message,
              'error'
            );
          }
        });
      }
    });
  }
});
