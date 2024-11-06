$(document).ready(function() {
  // Enviar el formulario al hacer clic en el botón "Guardar Categoría"
  $('#addExpenseCategoryModal').on('click', '#submitExpenseCategoryBtn', function () {
    submitNewExpenseCategory();
  });

  // Función para enviar la nueva categoría de gasto
  function submitNewExpenseCategory() {
    var route = $('#submitExpenseCategoryBtn').data('route');
    var formData = {
      name: $('#name').val(),
      description: $('#description').val(),
    };
    
    $.ajax({
      url: route,
      type: 'POST',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      data: formData,
      success: function (response) {
        $('#addExpenseCategoryModal').modal('hide');
        Swal.fire({
          icon: 'success',
          title: 'Categoría Agregada',
          text: response.message
        }).then(result => {
          // Recargar la DataTable o redirigir después de guardar
          // window.location.href = `${baseUrl}admin/expense-categories/${response.id}`;
          location.reload();
        });
      },
      error: function (xhr) {
        $('#addExpenseCategoryModal').modal('hide');

        var errorMessage =
          xhr.responseJSON && xhr.responseJSON.errors
            ? Object.values(xhr.responseJSON.errors).flat().join('\n')
            : 'Error desconocido al guardar.';
        var messageFormatted = '';
        if (xhr.responseJSON.message) {
          messageFormatted = xhr.responseJSON.message;
        }else{
          errorMessage.split('\n').forEach(function (message) {
            messageFormatted += '<div class="text-danger">' + message + '</div>';
          });
        }
        Swal.fire({
          icon: 'error',
          title: 'Error al guardar',
          html: messageFormatted
        }).then(result => {
          $('#addExpenseCategoryModal').modal('show');
        });
      }
    });
  }
});
