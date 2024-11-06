$(document).ready(function () {
  // Abrir modal para editar configuración de cuenta corriente
  $('.datatables-current-account-settings tbody').on('click', '.edit-record', function () {
    var recordId = $(this).data('id');
    prepareEditModal(recordId);
  });

  // Manejar el evento submit del formulario para evitar el comportamiento predeterminado
  $('#editCurrentAccountSettingForm').on('submit', function (e) {
    e.preventDefault();
    var recordId = $('#updateCurrentAccountSettingBtn').data('id');
    submitEditCurrentAccountSetting(recordId);
  });

  // Enviar formulario de edición al hacer clic en el botón de guardar cambios
  $('#editCurrentAccountSettingModal').on('click', '#updateCurrentAccountSettingBtn', function (e) {
    e.preventDefault();
    $('#editCurrentAccountSettingForm').submit();
  });

  function prepareEditModal(recordId) {
    // Función para preparar el modal de edición
    $.ajax({
      url: `current-account-settings/${recordId}/edit`,
      type: 'GET',
      success: function (data) {
        // Rellenar los campos del formulario con los datos obtenidos
        $('#transaction_type_edit').val(data.transaction_type);
        $('#late_fee_edit').val(data.late_fee);
        $('#payment_terms_edit').val(data.payment_terms);

        // Mostrar el modal
        $('#editCurrentAccountSettingModal').modal('show');
        $('#updateCurrentAccountSettingBtn').data('id', recordId); // Asigna el ID del registro al botón de actualización
      },
      error: function () {
        Swal.fire('Error', 'No se pudo cargar el formulario de edición. Por favor, intenta de nuevo.', 'error');
      }
    });
  }

  function submitEditCurrentAccountSetting(recordId) {
    var formData = {
      transaction_type: $('#transaction_type_edit').val(),
      late_fee: $('#late_fee_edit').val(),
      payment_terms: $('#payment_terms_edit').val(),
      '_token': $('meta[name="csrf-token"]').attr('content')
    };

    $.ajax({
      url: `current-account-settings/${recordId}`,
      type: 'PUT',
      data: formData,
      success: function () {
        $('#editCurrentAccountSettingModal').modal('hide');
        $('.datatables-current-account-settings').DataTable().ajax.reload();
        Swal.fire('¡Actualizado!', 'La configuración de cuenta corriente ha sido actualizada con éxito.', 'success');
      },
      error: function (xhr) {
        $('#editCurrentAccountSettingModal').modal('hide');

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
          $('#editCurrentAccountSettingModal').modal('show');
        });
      }
    });
  }
});
