$(document).ready(function() {
  // Mostrar/Ocultar campos de pago parcial según el checkbox "¿Pago Parcial?"
  $('#partial_payment').on('change', function() {
    if ($(this).is(':checked')) {
      $('#partialPaymentFields').removeClass('d-none');
      $('#amount_paid').attr('required', true);
      $('#payment_method_id').attr('required', true);
      $('#currency_id_current_account').attr('required', true);
    } else {
      $('#partialPaymentFields').addClass('d-none');
      $('#amount_paid').attr('required', false);
      $('#payment_method_id').attr('required', false);
      $('#currency_id_current_account').attr('required', false);
      $('#amount_paid').val('');
      $('#payment_method_id').val('');
      $('#currency_id_current_account').val('');
    }
  });

  // Evento para el botón de guardar la cuenta corriente
  $('#submitCurrentAccountBtn').on('click', function (e) {
    e.preventDefault();
    submitNewCurrentAccount();
  });

  // Mostrar/Ocultar campos de pago completo según el checkbox "¿Está Pagado?"
  $('#is_paid').on('change', function () {
    if ($(this).is(':checked')) {
      $('#paymentFields').removeClass('d-none');
      $('#amount_paid').val($('#total_debit').val());
    } else {
      $('#paymentFields').addClass('d-none');
      $('#amount_paid').val('');
    }
  });

  // Mantener el monto pagado igual al monto total si está marcado como pagado
  $('#total_debit').on('keyup', function () {
    if ($('#partial_payment').is(':checked')) {
      $('#amount_paid').val($(this).val());
    }
  });

  // Lógica de selección de tipo de entidad (cliente o proveedor)
  $('#entity_type').on('change', function() {
    let selectedType = $(this).val();
    filterCreditTypeOptions(selectedType); // Filtrar tipos de crédito según la selección de entidad

    if (selectedType === 'client') {
      // Mostrar select de clientes
      $('#clientSelectWrapper').removeClass('d-none');
      $('#supplierSelectWrapper').addClass('d-none');
    } else if (selectedType === 'supplier') {
      // Mostrar select de proveedores
      $('#supplierSelectWrapper').removeClass('d-none');
      $('#clientSelectWrapper').addClass('d-none');
    } else {
      // Ocultar ambos selects si no hay selección
      $('#clientSelectWrapper, #supplierSelectWrapper').addClass('d-none');
    }
  });

  // Filtrar tipos de crédito según el tipo de entidad
  function filterCreditTypeOptions(entityType) {
    let filteredOptions = '';


    filteredOptions += '<option value="" selected disabled>Seleccione el tipo de crédito</option>';

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

  // Función para enviar los datos de la nueva cuenta corriente
  function submitNewCurrentAccount() {
    let entityType = $('#entity_type').val();
    let entityId = entityType === 'client' ? $('#client_id').val() : $('#supplier_id').val();

    // Valida si todos los campos requeridos están llenos
    if (!entityId || !$('#current_account_settings_id').val() || !$('#currency_id_current_account').val()) {
      Swal.fire({
        icon: 'error',
        title: 'Campos requeridos',
        text: 'Por favor, completa todos los campos obligatorios.'
      });
      return;
    }

    var route = $('#submitCurrentAccountBtn').data('route');
    var formData = {
      total_debit: $('#total_debit').val(),
      client_id: $('#client_id').val(),
      supplier_id: $('#supplier_id').val(),
      current_account_settings_id: $('#current_account_settings_id').val(),
      partial_payment: $('#partial_payment').is(':checked'),
      amount_paid: $('#amount_paid').val(),
      description: $('#description').val(),
      payment_method_id: $('#payment_method_id').val(),
      currency_id_current_account: $('#currency_id_current_account').val(),
    };

    $.ajax({
      url: route,
      type: 'POST',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      data: formData,
      success: function (response) {
        Swal.fire({
          icon: 'success',
          title: 'Cuenta Corriente Agregada',
          text: response.message
        }).then(result => {
          window.location.href = `${baseUrl}admin/current-account-payments/${response.id}`;
        });
      },
      error: function (xhr) {
        var errorMessage =
          xhr.responseJSON && xhr.responseJSON.errors
            ? Object.values(xhr.responseJSON.errors).flat().join('\n')
            : 'Error desconocido al guardar.';
        Swal.fire({
          icon: 'error',
          title: 'Error al guardar',
          text: errorMessage
        });
      }
    });
  }
});
