$(document).ready(function () {
  // Eliminar un producto compuesto
  $('.datatables-composite-products tbody').on('click', '.delete-record', function () {
    var recordId = $(this).data('id');
    deleteCompositeProduct(recordId);
  });

  // Eliminar múltiples productos compuestos
  $('#deleteSelected').on('click', function () {
    var selectedIds = [];

    $('.datatables-composite-products tbody input[type="checkbox"]:checked').each(function () {
      selectedIds.push($(this).data('id'));
    });

    if (selectedIds.length === 0) {
      Swal.fire({
        icon: 'warning',
        title: 'Atención',
        text: 'Por favor, seleccione al menos un producto compuesto para eliminar.'
      });
      return;
    }

    confirmMultipleDeletionCompositeProduct(selectedIds);
  });

  function deleteCompositeProduct(recordId) {
    // Función para eliminar un producto compuesto
    Swal.fire({
      title: '¿Estás seguro de eliminar este producto compuesto?',
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
          url: 'composite-products/' + recordId,
          type: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function (result) {
            if (result.success) {
              Swal.fire('Eliminado!', 'El producto compuesto ha sido eliminado.', 'success');
              $('.datatables-composite-products').DataTable().ajax.reload();
            } else {
              Swal.fire('Error!', 'No se pudo eliminar el producto compuesto. Intente de nuevo.', 'error');
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            if (xhr.status === 403) {
              Swal.fire('Permiso denegado!', xhr.responseJSON.message, 'error');
            } else {
              Swal.fire('Error!', 'No se pudo eliminar el producto compuesto: ' + (xhr.responseJSON.message || thrownError), 'error');
            }
          }
        });
      }
    });
  }

  function confirmMultipleDeletionCompositeProduct(selectedIds) {
    // Muestra un modal de confirmación para eliminar múltiples productos compuestos
    Swal.fire({
      title: '¿Estás seguro de eliminar los productos compuestos seleccionados?',
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
          url: 'composite-products/delete-multiple',
          type: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          data: {
            ids: selectedIds
          },
          success: function (result) {
            if (result.success) {
              Swal.fire('Eliminado!', 'Los productos compuestos seleccionados han sido eliminados.', 'success');
              // dt_compositeProducts.ajax.reload(null, false);
              $('.datatables-composite-products').DataTable().ajax.reload();
            } else {
              Swal.fire('Error!', 'No se pudieron eliminar los productos compuestos seleccionados. Intente de nuevo.', 'error');
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            if (xhr.status === 403) {
              Swal.fire('Permiso denegado!', xhr.responseJSON.message, 'error');
            } else {
              Swal.fire('Error!', 'No se pudo eliminar el producto compuesto: ' + (xhr.responseJSON.message || thrownError), 'error');
            }
          }
        });
      }
    });
  }
});
