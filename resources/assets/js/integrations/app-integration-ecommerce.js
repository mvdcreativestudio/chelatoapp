document.addEventListener('DOMContentLoaded', () => {
    const ecommerceSwitches = document.querySelectorAll('[id^="ecommerceSwitch"]');

    ecommerceSwitches.forEach(switchElement => {
        switchElement.addEventListener('change', function () {
            const storeId = this.dataset.storeId;
            const isChecked = this.checked;

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: `${window.baseUrl}admin/integrations/${storeId}/toggle-ecommerce`,
                type: 'POST',
                data: {
                    ecommerce: isChecked ? 1 : 0,
                    _token: window.csrfToken
                },
                success: function (data) {
                    if (data.success) {
                        const card = document.getElementById(`ecommerce-card-${storeId}`).querySelector('.card');
                        const checkIcon = card.querySelector('.status-indicator');

                        if (checkIcon) {
                            checkIcon.classList.toggle('d-none', !isChecked);
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    } else {
                        handleError(data.message);
                    }
                },
                error: function (xhr) {
                    handleError(xhr.responseJSON?.message || 'Ha ocurrido un error al actualizar el estado');
                }
            });

            function handleError(message) {
                console.error('Error:', message);
                switchElement.checked = !isChecked; 

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message,
                    confirmButtonText: 'Aceptar'
                });
            }
        });
    });
});