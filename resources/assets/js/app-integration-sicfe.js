'use strict';

document.addEventListener('DOMContentLoaded', function () {
  // Toggle SICFE switch - muestra modal de configuración
  document.querySelectorAll('[id^="sicfeSwitch-"]').forEach(function (switchEl) {
    switchEl.addEventListener('change', function () {
      const storeId = this.dataset.storeId;
      const isChecked = this.checked;

      if (isChecked) {
        // Mostrar modal de configuración
        const modal = new bootstrap.Modal(document.getElementById('sicfeConfigModal-' + storeId));
        modal.show();
      } else {
        // Desactivar SICFE
        saveSicfeConfig(storeId, false);
      }
    });
  });

  // Guardar configuración SICFE
  document.querySelectorAll('.save-sicfe-config').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const storeId = this.dataset.storeId;
      saveSicfeConfig(storeId, true);
    });
  });
});

window.saveSicfeConfig = function(storeId, enabled) {
  const data = {
    invoices_enabled: enabled ? 1 : 0,
    sicfe_tenant: document.getElementById('sicfeTenant-' + storeId)?.value || '',
    sicfe_user: document.getElementById('sicfeUser-' + storeId)?.value || '',
    sicfe_password: document.getElementById('sicfePassword-' + storeId)?.value || '',
    sicfe_branch_office: document.getElementById('sicfeBranchOffice-' + storeId)?.value || '',
    has_special_caes: false,
  };

  fetch('/admin/integrations/' + storeId + '/sicfe', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    },
    body: JSON.stringify(data),
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert(data.message || 'Configuración guardada correctamente.');
        // Cerrar modal si existe
        const modal = bootstrap.Modal.getInstance(document.getElementById('sicfeConfigModal-' + storeId));
        if (modal) modal.hide();
        location.reload();
      } else {
        alert(data.message || 'Error al guardar la configuración.');
      }
    })
    .catch(error => {
      alert('Error en la petición.');
      console.error('Error:', error);
    });
}

window.checkSicfeConnection = function(storeId) {
  const modal = new bootstrap.Modal(document.getElementById('sicfeConnectionModal-' + storeId));
  modal.show();

  const loader = document.getElementById('sicfeConnectionLoader-' + storeId);
  const dataDiv = document.getElementById('sicfeConnectionData-' + storeId);
  const errorDiv = document.getElementById('sicfeConnectionError-' + storeId);

  loader.style.display = 'block';
  dataDiv.style.display = 'none';
  errorDiv.style.display = 'none';

  fetch('/admin/integrations/' + storeId + '/sicfe-connection', {
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    },
  })
    .then(response => response.json())
    .then(result => {
      loader.style.display = 'none';

      if (result.success) {
        const tbody = dataDiv.querySelector('tbody');
        tbody.innerHTML = '';

        const data = result.data;
        for (const [key, value] of Object.entries(data)) {
          const row = document.createElement('tr');
          row.innerHTML = '<td><strong>' + key + '</strong></td><td>' + (value || '-') + '</td>';
          tbody.appendChild(row);
        }

        dataDiv.style.display = 'block';
      } else {
        errorDiv.textContent = result.message || 'Error al obtener datos de conexión.';
        errorDiv.style.display = 'block';
      }
    })
    .catch(error => {
      loader.style.display = 'none';
      errorDiv.textContent = 'Error en la petición.';
      errorDiv.style.display = 'block';
      console.error('Error:', error);
    });
}
