$(document).ready(function () {
  let detailIndex = 1;

  // Añadir nuevo detalle de asiento
  $('#addEntryDetail').on('click', function () {
    addEntryDetail();
  });

  // Manejar el clic en el botón de guardar en el modal
  $('#submitEntryBtn').on('click', function (e) {
    e.preventDefault();
    submitNewEntry();
  });

  // Eliminar un detalle de asiento
  $('#entryDetails').on('click', '.remove-entry-detail', function () {
    $(this).closest('.entry-detail').remove();
  });

  function addEntryDetail() {
    let entryDetails = $('#entryDetails');
    let accountOptions = '';

    window.accounts.forEach(function (account) {
      accountOptions += `<option value="${account.id}">${account.name}</option>`;
    });

    let newDetail = `
      <div class="entry-detail">
        <div class="row g-2 mb-2">
          <div class="col-md-5">
            <label for="entry_account_id" class="form-label">Cuenta Contable</label>
            <select class="form-select" id="entry_account_id" name="details[${detailIndex}][entry_account_id]" required>
              <option value="" selected disabled>Seleccione una cuenta contable</option>
              ${accountOptions}
            </select>
          </div>
          <div class="col-md-3">
            <label for="amount_debit" class="form-label">Debe</label>
            <input type="number" class="form-control" id="amount_debit" name="details[${detailIndex}][amount_debit]" placeholder="0.00">
          </div>
          <div class="col-md-3">
            <label for="amount_credit" class="form-label">Haber</label>
            <input type="number" class="form-control" id="amount_credit" name="details[${detailIndex}][amount_credit]" placeholder="0.00">
          </div>
          <div class="col-md-1 d-flex align-items-end">
            <button type="button" class="btn btn-danger btn-sm remove-entry-detail"><i class="bx bx-trash"></i></button>
          </div>
        </div>
      </div>`;

    entryDetails.append(newDetail);
    detailIndex++;
  }

  function submitNewEntry() {
    var route = $('#submitEntryBtn').data('route');

    // Recopilar los datos del formulario
    var formData = {
      entry_date: $('#entry_date').val(),
      entry_type_id: $('#entry_type_id').val(),
      concept: $('#concept').val(),
      currency_id: $('#currency_id').val(),
      details: []
    };

    // Recopilar los detalles del asiento
    $('#entryDetails .entry-detail').each(function (index, element) {
      var entry_account_id = $(element).find('[name^="details["][name$="[entry_account_id]"]').val();
      var amount_debit = $(element).find('[name^="details["][name$="[amount_debit]"]').val();
      var amount_credit = $(element).find('[name^="details["][name$="[amount_credit]"]').val();

      formData.details.push({
        entry_account_id: entry_account_id,
        amount_debit: amount_debit || 0,
        amount_credit: amount_credit || 0
      });
    });

    // Enviar la solicitud AJAX
    $.ajax({
      url: route,
      type: 'POST',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      data: formData,
      success: function (response) {
        $('#addEntryModal').modal('hide');
        Swal.fire({
          icon: 'success',
          title: 'Asiento Agregado',
          text: response.message
        }).then(result => {
          window.location.href = `${baseUrl}admin/entry-details/${response.id}`;
        });
      },
      error: function (xhr) {
        // $('#addEntryModal').modal('hide');

        var errorMessage = '';

        if (xhr.responseJSON && xhr.responseJSON.errors) {
          // Iterar sobre cada campo y sus mensajes de error
          Object.keys(xhr.responseJSON.errors).forEach(function (key) {
            xhr.responseJSON.errors[key].forEach(function (message) {
              errorMessage += `<div class="text-danger">${message}</div>`;
            });
          });
        } else {
          errorMessage = 'Error desconocido al guardar.';
        }

        Swal.fire({
          icon: 'error',
          title: 'Error al guardar',
          html: errorMessage
        }).then(result => {
          // $('#addEntryModal').modal('show');
        });
      }
    });
  }
});
