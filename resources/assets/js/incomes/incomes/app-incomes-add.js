$(document).ready(function() {
  const entityType = $('#entity_type');
  const clientField = $('#client_field');
  const supplierField = $('#supplier_field');

  // Inicialmente, ocultar ambos campos
  clientField.hide();
  supplierField.hide();

  // Manejar el cambio de la selección del tipo de entidad
  entityType.on('change', function () {
    if (this.value === 'client') {
      clientField.show(); // Mostrar el combo de clientes
      supplierField.hide(); // Ocultar el combo de proveedores
      $('#client_id').prop('required', true); // Hacer el cliente requerido
      $('#supplier_id').prop('required', false); // No requerido para proveedores
    } else if (this.value === 'supplier') {
      supplierField.show(); // Mostrar el combo de proveedores
      clientField.hide(); // Ocultar el combo de clientes
      $('#supplier_id').prop('required', true); // Hacer el proveedor requerido
      $('#client_id').prop('required', false); // No requerido para clientes
    } else {
      // Si se selecciona 'Ninguno'
      clientField.hide();
      supplierField.hide();
      $('#client_id').prop('required', false); // No requerido
      $('#supplier_id').prop('required', false); // No requerido
    }
  });

  // Lógica para enviar el formulario
  $('#addIncomeModal').on('click', '#submitIncomeBtn', function (e) {
    e.preventDefault();
    submitNewIncome();
  });

  function submitNewIncome() {
    var route = $('#submitIncomeBtn').data('route');
    var entityType = $('#entity_type').val(); // Captura el tipo de entidad (Cliente, Proveedor o Ninguno)

    var formData = {
      income_name: $('#income_name').val(),
      income_description: $('#income_description').val(),
      income_date: $('#income_date').val(),
      income_amount: $('#income_amount').val(),
      payment_method_id: $('#payment_method_id').val(),
      income_category_id: $('#income_category_id').val(),
      currency_id: $('#currency_id').val(),
    };

    // Agregar client_id o supplier_id según el tipo de entidad seleccionado
    if (entityType === 'client') {
      formData.client_id = $('#client_id').val();
      formData.supplier_id = null; // Limpiar supplier_id
    } else if (entityType === 'supplier') {
      formData.supplier_id = $('#supplier_id').val();
      formData.client_id = null; // Limpiar client_id
    } else {
      formData.client_id = null; // Limpiar ambos si es Ninguno
      formData.supplier_id = null;
    }

    $.ajax({
      url: route,
      type: 'POST',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      data: formData,
      success: function (response) {
        $('#addIncomeModal').modal('hide');
        Swal.fire({
          icon: 'success',
          title: 'Ingreso Agregado',
          text: response.message
        }).then(result => {
          location.reload();
        });
      },
      error: function (xhr) {
        $('#addIncomeModal').modal('hide');

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
          $('#addIncomeModal').modal('show');
        });
      }
    });
  }
});
