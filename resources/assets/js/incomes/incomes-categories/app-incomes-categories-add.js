$(document).ready(function() {
  // Evento para agregar nueva categoría de ingresos
  $('#addIncomeCategoryModal').on('click', '#submitIncomeCategoryBtn', function () {
    submitNewIncomeCategory();
  });

  // Función para enviar el formulario de nueva categoría de ingresos
  function submitNewIncomeCategory() {
    var route = $('#submitIncomeCategoryBtn').data('route');
    var formData = {
      income_name: $('#income_name').val(),
      income_description: $('#income_description').val(),
    };
    
    $.ajax({
      url: route,
      type: 'POST',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      data: formData,
      success: function (response) {
        $('#addIncomeCategoryModal').modal('hide');
        Swal.fire({
          icon: 'success',
          title: 'Categoría de Ingreso Agregada',
          text: response.message
        }).then(result => {
          // Recargar la tabla de categorías de ingresos si es necesario
          // $('.datatables-income-categories').DataTable().ajax.reload();
          location.reload();
        });
      },
      error: function (xhr) {
        $('#addIncomeCategoryModal').modal('hide');

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
          $('#addIncomeCategoryModal').modal('show');
        });
      }
    });
  }
});
