$(document).ready(function () {
  // Eliminar registro
  $('.datatables-incomes tbody').on('click', '.delete-record', function () {
    var recordId = $(this).data('id');
    deleteIncome(recordId);
  });

  // Eliminar múltiples registros
  $('#deleteSelected').on('click', function () {
    var selectedIds = [];

    $('.datatables-incomes tbody input[type="checkbox"]:checked').each(function () {
      selectedIds.push($(this).data('id'));
    });

    if (selectedIds.length === 0) {
      Swal.fire({
        icon: 'warning',
        title: 'Atención',
        text: 'Por favor, seleccione al menos un ingreso para eliminar.'
      });
      return;
    }

    confirmMultipleDeletionIncome(selectedIds);
  });

  function deleteIncome(recordId) {
    // Función para eliminar un ingreso
    Swal.fire({
      title: '¿Estás seguro de eliminar este ingreso?',
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
          url: 'incomes/' + recordId,
          type: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function (result) {
            if (result.success) {
              Swal.fire('Eliminado!', 'El ingreso ha sido eliminado.', 'success');
              $('.datatables-incomes').DataTable().ajax.reload();
            } else {
              Swal.fire('Error!', 'No se pudo eliminar el ingreso. Intente de nuevo.', 'error');
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            if (xhr.status === 403) {
              Swal.fire('Permiso denegado!', xhr.responseJSON.message, 'error');
            } else {
              Swal.fire('Error!', 'No se pudo eliminar el ingreso: ' + (xhr.responseJSON.message || thrownError), 'error');
            }
          }
        });
      }
    });
  }

  function confirmMultipleDeletionIncome(selectedIds) {
    // Muestra un modal de confirmación para eliminar múltiples registros
    Swal.fire({
      title: '¿Estás seguro de eliminar los ingresos seleccionados?',
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
          url: 'incomes/delete-multiple',
          type: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          data: {
            ids: selectedIds
          },
          success: function (result) {
            if (result.success) {
              Swal.fire('Eliminado!', 'Los ingresos seleccionados han sido eliminados.', 'success');
              $('.datatables-incomes').DataTable().ajax.reload();
              $('#dropdownMenuButton').addClass('d-none');

            } else {
              Swal.fire('Error!', 'No se pudieron eliminar los ingresos seleccionados. Intente de nuevo.', 'error');
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            if (xhr.status === 403) {
              Swal.fire('Permiso denegado!', xhr.responseJSON.message, 'error');
            } else {
              Swal.fire('Error!', 'No se pudo eliminar el ingreso: ' + (xhr.responseJSON.message || thrownError), 'error');
            }
          }
        });
      }
    });
  }
});
