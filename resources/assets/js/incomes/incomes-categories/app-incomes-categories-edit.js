$(document).ready(function () {
  // Abrir modal para editar categoría de ingreso
  $('.datatables-income-categories tbody').on('click', '.edit-record', function () {
    var recordId = $(this).data('id');
    prepareEditModal(recordId);
  });

  // Manejar el evento submit del formulario para evitar el comportamiento predeterminado
  $('#editIncomeCategoryForm').on('submit', function (e) {
    e.preventDefault();
    var recordId = $('#updateIncomeCategoryBtn').data('id');
    submitEditIncomeCategory(recordId);
  });

  // Enviar formulario de edición al hacer clic en el botón de guardar cambios
  $('#editIncomeCategoryModal').on('click', '#updateIncomeCategoryBtn', function (e) {
    e.preventDefault();
    $('#editIncomeCategoryForm').submit();
  });

  function prepareEditModal(recordId) {
    // Función para preparar el modal de edición
    $.ajax({
      url: `income-categories/${recordId}/edit`,
      type: 'GET',
      success: function (data) {
        // Rellenar los campos del formulario con los datos obtenidos
        $('#income_name_edit').val(data.income_name);
        $('#income_description_edit').val(data.income_description);

        // Mostrar el modal
        $('#editIncomeCategoryModal').modal('show');
        $('#updateIncomeCategoryBtn').data('id', recordId); // Asigna el ID del registro al botón de actualización
      },
      error: function () {
        Swal.fire('Error', 'No se pudo cargar el formulario de edición. Por favor, intenta de nuevo.', 'error');
      }
    });
  }

  function submitEditIncomeCategory(recordId) {
    var formData = {
      income_name: $('#income_name_edit').val(),
      income_description: $('#income_description_edit').val(),
      '_token': $('meta[name="csrf-token"]').attr('content')
    };

    $.ajax({
      url: `income-categories/${recordId}`,
      type: 'PUT',
      data: formData,
      success: function () {
        $('#editIncomeCategoryModal').modal('hide');
        // $('.datatables-income-categories').DataTable().ajax.reload();
        location.reload();
        Swal.fire('¡Actualizado!', 'La categoría de ingreso ha sido actualizada con éxito.', 'success');
      },
      error: function (xhr) {
        $('#editIncomeCategoryModal').modal('hide');

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
          $('#editIncomeCategoryModal').modal('show');
        });
      }
    });
  }
});
