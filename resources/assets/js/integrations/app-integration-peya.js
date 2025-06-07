document.addEventListener('DOMContentLoaded', () => {
    // Manejo de los botones de guardar configuración de Pedidos Ya
    const savePeyaConfigBtns = document.querySelectorAll('.save-peya-config');

    savePeyaConfigBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const storeId = this.dataset.storeId;
            const modalElement = document.getElementById(`peyaConfigModal-${storeId}`);
            const modalInstance = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
            const peyaEnviosKeyInput = modalElement.querySelector(`#peyaEnviosKey-${storeId}`);
            const peyaEnviosKey = peyaEnviosKeyInput ? peyaEnviosKeyInput.value : '';
            const isEnabled = document.querySelector(`#peyaEnviosSwitch-${storeId}`).checked;

            $.ajax({
                url: `${window.baseUrl}admin/integrations/${storeId}/pedidosya`,
                type: 'POST',
                data: {
                    accepts_peya_envios: isEnabled ? 1 : 0,
                    peya_envios_key: peyaEnviosKey,
                    _token: window.csrfToken
                },
                success: function (response) {
                    console.log('Success:', response);
                    if (response.success) {
                        modalInstance.hide();

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
                        handleError(response.message, modalInstance);
                    }
                },
                error: function (xhr) {
                    console.error('Error:', xhr);
                    handleError(xhr.responseJSON?.message || 'Error al guardar la configuración', null);

                    // Revertir el estado del switch si hay error
                    const switchEl = document.getElementById(`peyaEnviosSwitch-${storeId}`);
                    if (switchEl) {
                        switchEl.checked = false;
                    }
                }
            });
        });
    });

    // Manejo de los switches de Pedidos Ya Envíos
    const peyaEnviosSwitches = document.querySelectorAll('[id^="peyaEnviosSwitch-"]');

    peyaEnviosSwitches.forEach(switchEl => {
        switchEl.addEventListener('change', function() {
            const storeId = this.dataset.storeId;
            const isChecked = this.checked;

            if (isChecked) {
                // Mostrar el modal solo al activar
                const modalElement = document.getElementById(`peyaConfigModal-${storeId}`);
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            } else {
                // Confirmar desactivación
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "Se desactivará Pedidos Ya Envíos para esta tienda.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, desactivar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        deactivatePeyaConfig(storeId);
                    } else {
                        // Revertir el estado del switch
                        this.checked = true;
                    }
                });
            }
        });
    });

    function deactivatePeyaConfig(storeId) {
        $.ajax({
            url: `${window.baseUrl}admin/integrations/${storeId}/pedidosya`,
            type: 'POST',
            data: {
                accepts_peya_envios: 0,
                _token: window.csrfToken
            },
            success: function (response) {
                if (response.success) {
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
                    handleError(response.message, null);
                }
            },
            error: function (xhr) {
                console.error('Error:', xhr);
                handleError(xhr.responseJSON?.message || 'Error al desactivar Pedidos Ya Envíos', null);

                // Revertir el estado del switch si hay error
                const switchEl = document.getElementById(`peyaEnviosSwitch-${storeId}`);
                if (switchEl) {
                    switchEl.checked = true;
                }
            }
        });
    }

    function handleError(message, modalInstance = null) {
        if (modalInstance) {
            modalInstance.hide();
        }

        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
            confirmButtonText: 'Aceptar'
        });
    }
});