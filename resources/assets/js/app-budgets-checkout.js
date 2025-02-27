$(function() {
    const form = $('#checkoutForm');
    const cashFields = $('#cashFields');
    const changeAmount = $('#changeAmount');
    const total = parseFloat($('#totalAmount').data('total'));

    // Mostrar/ocultar campos de efectivo según método de pago
    $('select[name="payment_method"]').on('change', function() {
        if ($(this).val() === 'cash') {
            cashFields.show();
        } else {
            cashFields.hide();
        }
    }).trigger('change'); // Trigger inicial

    // Calcular cambio en pagos en efectivo
    $('input[name="amount_received"]').on('input', function() {
        const received = parseFloat($(this).val()) || 0;
        const change = received - total;
        
        if (change >= 0) {
            changeAmount.html(`Cambio: $${change.toFixed(2)}`).removeClass('text-danger');
        } else {
            changeAmount.html(`Falta: $${Math.abs(change).toFixed(2)}`).addClass('text-danger');
        }
    });

    // Manejar envío del formulario
    form.on('submit', function(e) {
        e.preventDefault();

        // Validar monto recibido si es pago en efectivo
        if ($('select[name="payment_method"]').val() === 'cash') {
            const received = parseFloat($('input[name="amount_received"]').val()) || 0;
            if (received < total) {
                Swal.fire({
                    title: 'Error',
                    text: 'El monto recibido debe ser igual o mayor al total',
                    icon: 'error'
                });
                return;
            }
        }

        Swal.fire({
            title: '¿Confirmar venta?',
            text: "Se procesará la venta con los datos ingresados",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, procesar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData(this);
                
                // Agregar las notas al FormData
                const notes = $('textarea[name="notes"]').val();
                if (notes) {
                    formData.append('notes', notes);
                }

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: '¡Venta realizada!',
                                text: response.message,
                                icon: 'success'
                            }).then(() => {
                                window.location.href = response.redirect;
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: response.message || 'Error al procesar la venta',
                                icon: 'error'
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Error al procesar la venta',
                            icon: 'error'
                        });
                    }
                });
            }
        });
    });
});