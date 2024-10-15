$(document).ready(function() {
  $('#addEntryTypeModal').on('click', '#submitEntryTypeBtn', function () {
    submitNewEntryType();
  });

  function submitNewEntryType() {
    var route = $('#submitEntryTypeBtn').data('route');
    var formData = {
      name: $('#name').val(),
      description: $('#description').val()
    };
    
    $.ajax({
      url: route,
      type: 'POST',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      data: formData,
      success: function (response) {
        $('#addEntryTypeModal').modal('hide');
        Swal.fire({
          icon: 'success',
          title: 'Tipo de Asiento Agregado',
          text: response.message
        }).then(result => {
          // Recargar la tabla para reflejar el nuevo tipo de asiento agregado
          window.location.reload();
        });
      },
      error: function (xhr) {
        $('#addEntryTypeModal').modal('hide');

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
          $('#addEntryTypeModal').modal('show');
        });
      }
    });
  }
});
