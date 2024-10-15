$(document).ready(function () {
  // Función para abrir el formulario de edición con los valores preseleccionados
  function prepareEditModal(currentAccountId) {
    $.ajax({
      url: `/current-accounts/${currentAccountId}/edit`, // Llamada para obtener los datos de la cuenta
      type: 'GET',
      success: function (data) {
        // Rellenar los campos con los datos obtenidos
        $('#current_account_id').val(data.id);
        $('#total_debit').val(data.total_debit);
        $('#currency_id_current_account').val(data.currency_id_current_account);
        
        // Preseleccionar el cliente o proveedor según el tipo de entidad
        if (data.client_id) {
          $('#entity_type').val('client');
          $('#clientSelectWrapper').removeClass('d-none');
          $('#supplierSelectWrapper').addClass('d-none');
          $('#client_id').val(data.client_id);
        } else if (data.supplier_id) {
          $('#entity_type').val('supplier');
          $('#supplierSelectWrapper').removeClass('d-none');
          $('#clientSelectWrapper').addClass('d-none');
          $('#supplier_id').val(data.supplier_id);
        }

        // Filtrar y preseleccionar el tipo de crédito basado en el tipo de entidad
        filterCreditTypeOptions($('#entity_type').val());
        $('#current_account_settings_id').val(data.current_account_settings_id);
        
        // Mostrar el modal
        $('#editCurrentAccountModal').modal('show');
      },
      error: function () {
        Swal.fire('Error', 'No se pudo cargar la cuenta corriente. Por favor, intenta de nuevo.', 'error');
      }
    });
  }

  // Lógica para abrir el modal con los valores preseleccionados
  $('.datatables-current-accounts tbody').on('click', '.edit-record', function () {
    var currentAccountId = $(this).data('id');
    prepareEditModal(currentAccountId);
  });

  // Enviar formulario de edición
  $('#editCurrentAccountForm').on('submit', function (e) {
    e.preventDefault();
    submitEditCurrentAccount();
  });

  // Función para enviar los datos editados
  function submitEditCurrentAccount() {
    var route = $('#updateCurrentAccountBtn').data('route');
    var formData = {
      current_account_id: $('#current_account_id').val(),
      total_debit: $('#total_debit').val(),
      currency_id_current_account: $('#currency_id_current_account').val(),
      client_id: $('#client_id').val(),
      supplier_id: $('#supplier_id').val(),
      current_account_settings_id: $('#current_account_settings_id').val(),
      '_token': $('meta[name="csrf-token"]').attr('content')
    };

    $.ajax({
      url: route,
      type: 'PUT',
      data: formData,
      success: function () {
        Swal.fire('¡Actualizado!', 'La cuenta corriente ha sido actualizada con éxito.', 'success').then(result => {
          window.location.href = `${baseUrl}admin/current-accounts/${formData.current_account_id}/edit`;
        });
      },
      error: function (xhr) {
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
        });
      }
    });
  }

  // Filtrar tipos de crédito según el tipo de entidad
  function filterCreditTypeOptions(entityType) {
    let filteredOptions = '';

    // Filtrar según transaction_type: 'Sale' para clientes, 'Purchase' para proveedores
    currentAccountSettings.forEach(function(setting) {
      if ((entityType === 'client' && setting.transaction_type === 'Sale') ||
          (entityType === 'supplier' && setting.transaction_type === 'Purchase')) {
        filteredOptions += `<option value="${setting.id}">${setting.payment_terms}</option>`;
      }
    });

    // Actualizar las opciones del select de tipo de crédito
    $('#current_account_settings_id').html(filteredOptions).prop('disabled', false);
  }

  // Lógica de selección de tipo de entidad (cliente o proveedor) en el modal de edición
  $('#entity_type').on('change', function() {
    let selectedType = $(this).val();
    filterCreditTypeOptions(selectedType);

    if (selectedType === 'client') {
      $('#clientSelectWrapper').removeClass('d-none');
      $('#supplierSelectWrapper').addClass('d-none');
    } else if (selectedType === 'supplier') {
      $('#supplierSelectWrapper').removeClass('d-none');
      $('#clientSelectWrapper').addClass('d-none');
    } else {
      $('#clientSelectWrapper, #supplierSelectWrapper').addClass('d-none');
    }
  });
});
