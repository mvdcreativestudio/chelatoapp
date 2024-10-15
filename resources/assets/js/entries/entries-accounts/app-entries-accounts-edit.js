$(document).ready(function () {
  // Abrir modal para editar cuenta contable
  $('.datatables-entry-accounts tbody').on('click', '.edit-record', function () {
    var recordId = $(this).data('id');
    prepareEditModal(recordId);
  });

  // Manejar el evento submit del formulario para evitar el comportamiento predeterminado
  $('#editEntryAccountForm').on('submit', function (e) {
    e.preventDefault();
    var recordId = $('#updateEntryAccountBtn').data('id');
    if (validateEditCode()) {
      submitEditEntryAccount(recordId);
    }
  });

  // Enviar formulario de edición al hacer clic en el botón de guardar cambios
  $('#editEntryAccountModal').on('click', '#updateEntryAccountBtn', function (e) {
    e.preventDefault();
    $('#editEntryAccountForm').submit();
  });

  // Validar que el campo 'code' en el formulario de edición solo acepte números
  $('#edit_code').on('input', function (e) {
    this.value = this.value.replace(/[^0-9]/g, ''); // Reemplaza todo lo que no sea un número
  });

  // Función para preparar el modal de edición de cuenta contable
  function prepareEditModal(recordId) {
    $.ajax({
      url: `${baseUrl}admin/entry-accounts/${recordId}/edit`,
      type: 'GET',
      success: function (data) {
        // Rellenar los campos del formulario con los datos obtenidos
        $('#edit_code').val(data.code);
        $('#edit_name').val(data.name);
        $('#edit_description').val(data.description);

        // Mostrar el modal
        $('#editEntryAccountModal').modal('show');
        $('#updateEntryAccountBtn').data('id', recordId); // Asigna el ID del registro al botón de actualización
      },
      error: function () {
        Swal.fire('Error', 'No se pudo cargar el formulario de edición. Por favor, intenta de nuevo.', 'error');
      }
    });
  }

  function validateEditCode() {
    var code = $('#edit_code').val();
    if (!code || isNaN(code)) {
      Swal.fire({
        icon: 'error',
        title: 'Error de validación',
        text: 'El código de la cuenta contable debe ser un número válido.'
      });
      return false;
    }
    return true;
  }

  function submitEditEntryAccount(recordId) {
    var formData = {
      'code': $('#edit_code').val(),
      'name': $('#edit_name').val(),
      'description': $('#edit_description').val(),
      '_token': $('meta[name="csrf-token"]').attr('content')
    };

    $.ajax({
      url: `${baseUrl}admin/entry-accounts/${recordId}`,
      type: 'PUT',
      data: formData,
      success: function () {
        $('#editEntryAccountModal').modal('hide');
        Swal.fire('¡Actualizado!', 'La cuenta contable ha sido actualizada con éxito.', 'success').then(result => {
          location.reload();
        });
      },
      error: function (xhr) {
        $('#editEntryAccountModal').modal('hide');

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
          $('#editEntryAccountModal').modal('show');
        });
      }
    });
  }
});
