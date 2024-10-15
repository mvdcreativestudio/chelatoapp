$(document).ready(function () {
  let editDetailIndex = 1;

  // Manejar el evento submit del formulario para evitar el comportamiento predeterminado
  $('#editEntryForm').on('submit', function (e) {
    e.preventDefault();
    submitEditEntry();
  });

  // Enviar formulario de edición al hacer clic en el botón de guardar cambios
  $('#updateEntryBtn').on('click', function (e) {
    e.preventDefault();
    $('#editEntryForm').submit();
  });

  // Añadir nuevo detalle de asiento
  $('#addEditEntryDetail').on('click', function () {
    addEditEntryDetailRow();  // Llama a la función para agregar una nueva fila
  });

  // Eliminar detalle de asiento
  $('#editEntryDetails').on('click', '.remove-entry-detail', function () {
    $(this).closest('.entry-detail').remove();  // Elimina la fila del detalle
  });

  function addEditEntryDetailRow(detail = {}) {
    let entryDetails = $('#editEntryDetails');
    let accountOptions = '';

    window.accounts.forEach(function(account) {
      accountOptions += `<option value="${account.id}" ${detail.entry_account_id == account.id ? 'selected' : ''}>${account.name}</option>`;
    });

    let newDetail = `
      <div class="entry-detail">
        <input type="hidden" name="edit_details[${editDetailIndex}][entry_detail_id]" value="${detail.id || ''}">
        <div class="row g-2 mb-2">
          <div class="col-md-5">
            <label for="edit_entry_account_id" class="form-label">Cuenta Contable</label>
            <select class="form-select" name="edit_details[${editDetailIndex}][entry_account_id]" required>
              <option value="" selected disabled>Seleccione una cuenta contable</option>
              ${accountOptions}
            </select>
          </div>
          <div class="col-md-3">
            <label for="edit_amount_debit" class="form-label">Debe</label>
            <input type="number" class="form-control" name="edit_details[${editDetailIndex}][amount_debit]" value="${detail.amount_debit || ''}" placeholder="0.00">
          </div>
          <div class="col-md-3">
            <label for="edit_amount_credit" class="form-label">Haber</label>
            <input type="number" class="form-control" name="edit_details[${editDetailIndex}][amount_credit]" value="${detail.amount_credit || ''}" placeholder="0.00">
          </div>
          <div class="col-md-1 d-flex align-items-end">
            <button type="button" class="btn btn-danger btn-sm remove-entry-detail"><i class="bx bx-trash"></i></button>
          </div>
        </div>
      </div>`;
      
    entryDetails.append(newDetail);
    editDetailIndex++;
  }

  function submitEditEntry() {
    // var route = $('#updateEntryBtn').data('route').replace(':id', recordId);
    // console.log({route, recordId});
    // get route form
    var route = $('#editEntryForm').attr('action');
    console.log(route);
    // Recopilar los datos del formulario
    var formData = {
      entry_date: $('#edit_entry_date').val(),
      entry_type_id: $('#edit_entry_type_id').val(),
      concept: $('#edit_concept').val(),
      currency_id: $('#edit_currency_id').val(),
      details: []
    };

    // Recopilar los detalles del asiento
    $('#editEntryDetails .entry-detail').each(function(index, element) {
      var entry_detail_id = $(element).find('[name^="edit_details["][name$="[entry_detail_id]"]').val();
      var entry_account_id = $(element).find('[name^="edit_details["][name$="[entry_account_id]"]').val();
      var amount_debit = $(element).find('[name^="edit_details["][name$="[amount_debit]"]').val();
      var amount_credit = $(element).find('[name^="edit_details["][name$="[amount_credit]"]').val();
      console.log(entry_detail_id);
      formData.details.push({
        id: entry_detail_id,
        entry_account_id: entry_account_id,
        amount_debit: amount_debit || 0,
        amount_credit: amount_credit || 0
      });
    });

    // Enviar la solicitud AJAX
    $.ajax({
      url: route,
      type: 'PUT', // Cambia a PUT para editar
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      data: formData,
      success: function (response) {
        Swal.fire({
          icon: 'success',
          title: 'Asiento Actualizado',
          text: response.message
        }).then(result => {
          window.location.href = `${baseUrl}admin/entry-details/${response.id}`;
        });
      },
      error: function (xhr) {

        var errorMessage = '';

        if (xhr.responseJSON && xhr.responseJSON.errors) {
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
        })
      }
    });
}
});
