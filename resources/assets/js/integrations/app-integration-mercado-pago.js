document.addEventListener('DOMContentLoaded', () => {
    const configureButtons = document.querySelectorAll('.configure-mp-presencial');

    configureButtons.forEach(button => {
        button.addEventListener('click', function () {
            const storeId = this.dataset.storeId;
            const modalId = `mercadoPagoPresencialModal-${storeId}`;
            const modal = document.getElementById(modalId);

            const bootstrapModal = new bootstrap.Modal(modal);
            bootstrapModal.show();
        });
    });

    document.querySelectorAll('[id^="mercadoPagoSwitchPresencial-"]').forEach(checkbox => {
        checkbox.addEventListener('change', function (e) {
            const storeId = this.id.split('-')[1];
            const fieldsContainer = document.getElementById(`mercadoPagoFieldsPresencial-${storeId}`);
            fieldsContainer.style.display = this.checked ? 'block' : 'none';

            if (!this.checked) {
                e.preventDefault();
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "Vas a desactivar MercadoPago Presencial",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, desactivar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `${window.baseUrl}admin/integrations/${storeId}/mercadopago-presencial`,
                            method: 'POST',
                            data: {
                                accepts_mercadopago_presencial: 0
                            },
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (response) {
                                if (response.success) {
                                    const card = document.querySelector(`#store-content-${storeId} .integration-card`);
                                   
                                    fieldsContainer.style.display = 'none';

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
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.message || 'Error al desactivar MercadoPago Presencial'
                                    });
                                    checkbox.checked = true;
                                    fieldsContainer.style.display = 'block';
                                }
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: xhr.responseJSON?.message || 'Error al desactivar MercadoPago Presencial'
                                });
                                checkbox.checked = true;
                                fieldsContainer.style.display = 'block';
                            }
                        });
                    } else {
                        this.checked = true;
                        fieldsContainer.style.display = 'block';
                    }
                });
            } else {
                const modal = new bootstrap.Modal(document.querySelector(`#mercadoPagoPresencialModal-${storeId}`));
                modal.show();
            }
        });
    });

    document.querySelectorAll('.mercadoPagoPresencialForm').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const storeId = this.id.split('-')[1];
            const formData = new FormData(this);
            formData.append('accepts_mercadopago_presencial', '1');

            $.ajax({
                url: `${window.baseUrl}admin/integrations/${storeId}/mercadopago-presencial`,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.success) {
                        const modal = document.getElementById(`mercadoPagoPresencialModal-${storeId}`);
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        bsModal.hide();

                        const card = document.querySelector(`#store-content-${storeId} .integration-card`);
                        let statusIndicator = card.querySelector('.status-indicator');

                        if (formData.get('accepts_mercadopago_presencial')) {
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
                    }
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Error al actualizar la configuración'
                    });
                }
            });
        });
    });

    document.querySelectorAll('[id^="mercadoPagoSwitchOnline-"]').forEach(checkbox => {
        checkbox.addEventListener('change', function (e) {
            const storeId = this.id.split('-')[1];
            const fieldsContainer = document.getElementById(`mercadoPagoFieldsOnline-${storeId}`);
            fieldsContainer.style.display = this.checked ? 'block' : 'none';

            if (!this.checked) {
                e.preventDefault();
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "Vas a desactivar MercadoPago Online",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, desactivar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `${window.baseUrl}admin/integrations/${storeId}/mercadopago-online`,
                            method: 'POST',
                            data: {
                                accepts_mercadopago_online: 0
                            },
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (response) {
                                if (response.success) {
                                    const card = document.querySelector(`#store-content-${storeId} .integration-card`);
                                   
                                    fieldsContainer.style.display = 'none';

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
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.message || 'Error al desactivar MercadoPago Online'
                                    });
                                    checkbox.checked = true;
                                    fieldsContainer.style.display = 'block';
                                }
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: xhr.responseJSON?.message || 'Error al desactivar MercadoPago Online'
                                });
                                checkbox.checked = true;
                                fieldsContainer.style.display = 'block';
                            }
                        });
                    } else {
                        this.checked = true;
                        fieldsContainer.style.display = 'block';
                    }
                });
            } else {
                const modal = new bootstrap.Modal(document.querySelector(`#mercadoPagoOnlineModal-${storeId}`));
                modal.show();
            }
        });
    });

    document.querySelectorAll('.mercadoPagoOnlineForm').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const storeId = this.id.split('-')[1];
            const formData = new FormData(this);

            $.ajax({
                url: `${window.baseUrl}admin/integrations/${storeId}/mercadopago-online`,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.success) {
                        const modal = document.getElementById(`mercadoPagoOnlineModal-${storeId}`);
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        bsModal.hide();

                        const card = document.querySelector(`#store-content-${storeId} .integration-card`);
                        let statusIndicator = card.querySelector('.status-indicator');

                        if (formData.get('accepts_mercadopago_online')) {
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
                    }
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Error al actualizar la configuración'
                    });
                }
            });
        });
    });
});