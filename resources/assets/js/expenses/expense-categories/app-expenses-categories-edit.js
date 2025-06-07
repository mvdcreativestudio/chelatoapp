$(document).ready(function () {
  // Abrir modal para editar categoría de gasto
  $('.datatables-expense-categories tbody').on('click', '.edit-record', function () {
    var recordId = $(this).data('id');
    prepareEditModal(recordId);
  });

  // Manejar el evento submit del formulario para evitar el comportamiento predeterminado
  $('#editExpenseCategoryForm').on('submit', function (e) {
    e.preventDefault();
    var recordId = $('#updateExpenseCategoryBtn').data('id');
    submitEditExpenseCategory(recordId);
  });

  // Enviar formulario de edición al hacer clic en el botón de guardar cambios
  $('#editExpenseCategoryModal').on('click', '#updateExpenseCategoryBtn', function (e) {
    e.preventDefault();
    $('#editExpenseCategoryForm').submit();
  });

  // Función para preparar el modal de edición
  function prepareEditModal(recordId) {
    $.ajax({
      url: `expense-categories/${recordId}/edit`,
      type: 'GET',
      success: function (data) {
        // Rellenar los campos del formulario con los datos obtenidos
        $('#nameEdit').val(data.name);
        $('#descriptionEdit').val(data.description);

        // Mostrar el modal
        $('#editExpenseCategoryModal').modal('show');
        $('#updateExpenseCategoryBtn').data('id', recordId); // Asigna el ID del registro al botón de actualización
      },
      error: function () {
        Swal.fire('Error', 'No se pudo cargar el formulario de edición. Por favor, intenta de nuevo.', 'error');
      }
    });
  }

  // Función para enviar los cambios de edición de la categoría de gasto
  function submitEditExpenseCategory(recordId) {
    var formData = {
      name: $('#nameEdit').val(),
      description: $('#descriptionEdit').val(),
      '_token': $('meta[name="csrf-token"]').attr('content')
    };
    
    $.ajax({
      url: `expense-categories/${recordId}`,
      type: 'PUT',
      data: formData,
      success: function () {
        $('#editExpenseCategoryModal').modal('hide');
        $('.datatables-expense-categories').DataTable().ajax.reload();
        Swal.fire('¡Actualizado!', 'La categoría de gasto ha sido actualizada con éxito.', 'success');
      },
      error: function (xhr) {
        $('#editExpenseCategoryModal').modal('hide');

        var errorMessage =
          xhr.responseJSON && xhr.responseJSON.errors
            ? Object.values(xhr.responseJSON.errors).flat().join('\n')
            : 'Error desconocido al guardar.';

        var messageFormatted = '';
        if (xhr.responseJSON.message) {
          messageFormatted = xhr.responseJSON.message;
        } else {
          errorMessage.split('\n').forEach(function (message) {
            messageFormatted += '<div class="text-danger">' + message + '</div>';
          });
        }
        Swal.fire({
          icon: 'error',
          title: 'Error al guardar',
          html: messageFormatted
        }).then(result => {
          $('#editExpenseCategoryModal').modal('show');
        });
      }
    });
  }
});
