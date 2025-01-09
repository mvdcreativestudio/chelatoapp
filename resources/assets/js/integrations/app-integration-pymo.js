document.addEventListener('DOMContentLoaded', () => {

    function checkPymoConnection(storeId) {
        const modal = new bootstrap.Modal(document.getElementById(`pymoConnectionModal-${storeId}`));
        modal.show();
        
        const loader = document.getElementById(`pymoConnectionLoader-${storeId}`);
        const dataDiv = document.getElementById(`pymoConnectionData-${storeId}`);
        const errorDiv = document.getElementById(`pymoConnectionError-${storeId}`);
        
        loader.style.display = 'block';
        dataDiv.style.display = 'none';
        errorDiv.style.display = 'none';
    
        fetch(`${window.baseUrl}admin/integrations/pymo-connection/${storeId}`, {
            headers: {
                'X-CSRF-TOKEN': window.csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            loader.style.display = 'none';
            if (data.success) {
                const companyInfo = data.data;
                dataDiv.querySelector('.company-name').textContent = companyInfo.name || 'N/A';
                dataDiv.querySelector('.company-rut').textContent = companyInfo.rut || 'N/A';
                dataDiv.querySelector('.company-email').textContent = companyInfo.email || 'N/A';
                dataDiv.querySelector('.company-branch').textContent = 
                    companyInfo.branchOffices.find(office => 
                        office.number === (companyInfo.selectedBranch ? companyInfo.selectedBranch.number : ''))?.fiscalAddress || 'N/A';
                
                dataDiv.style.display = 'block';
            } else {
                errorDiv.textContent = data.message || 'Error desconocido';
                errorDiv.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error al verificar la conexión:', error);
            loader.style.display = 'none';
            errorDiv.textContent = 'Error al verificar la conexión';
            errorDiv.style.display = 'block';
        });
    }


    const savePymoConfigBtns = document.querySelectorAll('.btn.save-pymo-config');

    savePymoConfigBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const storeId = this.dataset.storeId;
            const modal = this.closest('.modal');
            const pymoUser = modal.querySelector('#pymoUser').value;
            const pymoPassword = modal.querySelector('#pymoPassword').value;
            const pymoBranchOffice = modal.querySelector('#pymoBranchOffice')?.value;
            const isEnabled = document.querySelector(`#pymoSwitch-${storeId}`).checked;

            // Validación de campos
            if (!pymoUser || !pymoPassword || (isEnabled && !pymoBranchOffice)) {
                handleError('Todos los campos son obligatorios.');
                return;
            }

            $.ajax({
                url: `${window.baseUrl}admin/integrations/${storeId}/pymo`,
                type: 'POST',
                data: {
                    invoices_enabled: isEnabled ? 1 : 0,
                    pymo_user: pymoUser,
                    pymo_password: pymoPassword,
                    pymo_branch_office: pymoBranchOffice,
                    _token: window.csrfToken
                },
                success: function(response) {
                    if (response.success) {
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        bsModal.hide();

                        const card = document.querySelector(`#store-content-${storeId} .integration-card`);
                        let statusIndicator = card.querySelector('.status-indicator');

                        if (isEnabled) {
                            if (!statusIndicator) {
                                statusIndicator = document.createElement('span');
                                statusIndicator.className = 'status-indicator';
                                statusIndicator.innerHTML = '<i class="bx bx-check text-white"></i>';
                                card.querySelector('.card-header').appendChild(statusIndicator);
                            }
                        } else if (statusIndicator) {
                            statusIndicator.remove();
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        handleError(response.message || 'Error al guardar la configuración de Pymo');
                    }
                },
                error: function(xhr) {
                    handleError(xhr.responseJSON?.message || 'Error al guardar la configuración de Pymo');
                }
            });
        });
    });

    // Añadir listeners para los switches de Pymo
    const pymoSwitches = document.querySelectorAll('.form-check-input[name="invoices_enabled"]');
    pymoSwitches.forEach(switchElement => {
        switchElement.addEventListener('change', function(e) {
            const storeId = e.target.getAttribute('data-store-id');
            if (e.target.checked) {
                // Abrir el modal de configuración al habilitar
                const configModal = new bootstrap.Modal(document.getElementById(`pymoConfigModal-${storeId}`));
                configModal.show();
            } else {
                // Manejar la desactivación sin abrir el modal
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "Deseas desactivar la integración de Pymo.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, desactivar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`${window.baseUrl}admin/integrations/${storeId}/pymo`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': window.csrfToken,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ store_id: storeId })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                             

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Desactivado',
                                    text: data.message || 'Pymo ha sido desactivado correctamente.',
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                e.target.checked = true;
                                handleError(data.message || 'Error al desactivar Pymo');
                            }
                        })
                        .catch(error => {
                            console.error('Error al desactivar Pymo:', error);
                            e.target.checked = true;
                            handleError('Error al desactivar Pymo');
                        });
                    } else {
                        e.target.checked = true;
                    }
                });
            }
        });
    });

    window.checkPymoConnection = checkPymoConnection;
});

function handleError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: message,
        confirmButtonText: 'Aceptar'
    });
}