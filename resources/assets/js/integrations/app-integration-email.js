document.addEventListener('DOMContentLoaded', () => {
    // Manejo de los botones de guardar configuración de correo
    const saveEmailConfigBtns = document.querySelectorAll('[id^="saveEmailConfig"]');

    saveEmailConfigBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const storeId = this.id.split('-')[1];
            const modalElement = document.getElementById(`emailConfigModal-${storeId}`);
            const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
            const form = document.getElementById(`emailConfigForm-${storeId}`);

            const formData = new FormData(form);

            $.ajax({
                url: `${window.baseUrl}admin/integrations/${storeId}/email-config`,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    console.log('Success:', response);
                    if (response.success) {
                        modal.hide();

                        const card = document.querySelector(`#store-content-${storeId} .card`);
                        const checkIcon = card.querySelector('.status-indicator');
                        if (checkIcon) {
                            checkIcon.classList.remove('d-none');
                        } else {
                            // Si no existe el icono, lo agregamos
                            const statusIndicator = document.createElement('span');
                            statusIndicator.classList.add('status-indicator');
                            statusIndicator.innerHTML = '<i class="bx bx-check text-white"></i>';
                            card.querySelector('.card-header').appendChild(statusIndicator);
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
                        handleError(response.message, modal);
                    }
                },
                error: function (xhr) {
                    console.error('Error:', xhr);
                    handleError(xhr.responseJSON?.message || 'Error al guardar la configuración', modal);

                    // Revertir el estado del switch si hay error
                    const switchEl = document.getElementById(`emailConfigSwitch-${storeId}`);
                    if (switchEl) {
                        switchEl.checked = false;
                    }
                }
            });
        });
    });

    // Manejo de los switches de configuración de correo
    const emailConfigSwitches = document.querySelectorAll('[id^="emailConfigSwitch-"]');

    emailConfigSwitches.forEach(switchEl => {
        switchEl.addEventListener('change', function() {
            const storeId = this.id.split('-')[1];
            const modalElement = document.getElementById(`emailConfigModal-${storeId}`);
            const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);

            if (!this.checked) {
                // Confirmar desactivación
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "Se eliminará la configuración de correo actual",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, desactivar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        deactivateEmailConfig(storeId, modal);
                    } else {
                        this.checked = true;
                    }
                });
            } else {
                // Mostrar modal solo al activar
                modal.show();
            }
        });
    });

    function deactivateEmailConfig(storeId, modal) {
        const formData = new FormData();
        formData.append('stores_email_config', '0');

        $.ajax({
            url: `${window.baseUrl}admin/integrations/${storeId}/email-config`,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('Deactivate Success:', response);
                if (response.success) {
                    modal.hide();

                    const card = document.querySelector(`#store-content-${storeId} .card`);
   
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
                    handleError(response.message, modal);
                }
            },
            error: function(xhr) {
                console.error('Deactivate Error:', xhr);
                handleError(xhr.responseJSON?.message || 'Error al desactivar la configuración', modal);

                // Revertir el estado del switch si hay error
                const switchEl = document.getElementById(`emailConfigSwitch-${storeId}`);
                if (switchEl) {
                    switchEl.checked = true;
                }
            }
        });
    }

    function handleError(message, modal) {
        if (modal) {
            modal.hide();
        }

        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
            confirmButtonText: 'Aceptar'
        });
    }
});