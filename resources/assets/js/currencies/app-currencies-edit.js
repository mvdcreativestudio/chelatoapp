$(document).ready(function () {
  // Abrir modal para editar moneda
  $('.datatables-currencies tbody').on('click', '.edit-record', function () {
    var recordId = $(this).data('id');
    prepareEditModal(recordId);
  });

  // Manejar el evento submit del formulario para evitar el comportamiento predeterminado
  $('#editCurrencyForm').on('submit', function (e) {
    e.preventDefault();
    var recordId = $('#updateCurrencyBtn').data('id');
    submitEditCurrency(recordId);
  });

  // Enviar formulario de edición al hacer clic en el botón de guardar cambios
  $('#editCurrencyModal').on('click', '#updateCurrencyBtn', function (e) {
    e.preventDefault();
    $('#editCurrencyForm').submit();
  });

  function prepareEditModal(recordId) {
    // Función para preparar el modal de edición
    $.ajax({
      url: `currencies/${recordId}/edit`,
      type: 'GET',
      success: function (data) {
        // Rellenar los campos del formulario con los datos obtenidos
        $('#currency_code_edit').val(data.code);
        $('#currency_symbol_edit').val(data.symbol);
        $('#currency_name_edit').val(data.name);
        $('#exchange_rate_edit').val(data.exchange_rate);

        // Mostrar el modal
        $('#editCurrencyModal').modal('show');
        $('#updateCurrencyBtn').data('id', recordId); // Asigna el ID del registro al botón de actualización
      },
      error: function () {
        Swal.fire('Error', 'No se pudo cargar el formulario de edición. Por favor, intenta de nuevo.', 'error');
      }
    });
  }

  function submitEditCurrency(recordId) {
    var formData = {
      code: $('#currency_code_edit').val(),
      symbol: $('#currency_symbol_edit').val(),
      name: $('#currency_name_edit').val(),
      exchange_rate: $('#exchange_rate_edit').val(),
      '_token': $('meta[name="csrf-token"]').attr('content')
    };

    $.ajax({
      url: `currencies/${recordId}`,
      type: 'PUT',
      data: formData,
      success: function () {
        $('#editCurrencyModal').modal('hide');
        $('.datatables-currencies').DataTable().ajax.reload();
        Swal.fire('¡Actualizado!', 'La moneda ha sido actualizada con éxito.', 'success');
      },
      error: function (xhr) {
        $('#editCurrencyModal').modal('hide');

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
          $('#editCurrencyModal').modal('show');
        });
      }
    });
  }
});
