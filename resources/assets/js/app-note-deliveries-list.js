$(function () {
    const noteDeliveryListContainer = $('.note-delivery-list-container');

    function formatDateTime(dateString) {
        return dateString ? new Date(dateString).toLocaleString() : 'No disponible';
    }

    function displayNoteDeliveries() {
        noteDeliveryListContainer.html('');

        if (!noteDeliveries || noteDeliveries.length === 0) {
            noteDeliveryListContainer.html(`
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="bx bx-info-circle"></i> No hay notas de entrega disponibles.
                    </div>
                </div>
            `);
            return;
        }

        noteDeliveries.forEach(function (delivery) {
            const isCubaComplete = delivery.dispatch_note.quantity >= 5;
            const cubaStatus = `
                <div class="mb-2">
                    <span class="badge ${isCubaComplete ? 'bg-success' : 'bg-warning'}">
                        Cuba: ${isCubaComplete ? 'Completa' : 'Incompleta'}
                    </span>
                </div>
            `;
            const card = `
                <div class="col-md-6 col-lg-4 col-12 note-delivery-card-wrapper">
                    <div class="clients-card-container">
                        <div class="clients-card position-relative">
                            <div class="clients-card-header d-flex justify-content-between align-items-center">
                                <h5 class="clients-name mb-0">
                                    Envío #${delivery.id}
                                </h5>
                                <div class="d-flex align-items-center">
                                    <div class="clients-card-toggle">
                                        <i class="bx bx-chevron-down fs-3"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="clients-card-body" style="display: none;">
                                <div class="d-flex flex-column h-100">
                                    <div>
                                        ${cubaStatus}
                                        <p class="mb-2">
                                            <i class="bx bx-user me-2"></i> Chofer: ${delivery.driver ? delivery.driver.name + ' ' + delivery.driver.last_name : 'No asignado'}
                                        </p>
                                        <p class="mb-2">
                                            <i class="bx bx-car me-2"></i> Vehículo: ${delivery.vehicle ? delivery.vehicle.number + ' - ' + delivery.vehicle.plate : 'No asignado'}
                                        </p>
                                        <p class="mb-2">
                                            <i class="bx bx-store me-2"></i> Origen: ${delivery.store ? delivery.store.name : 'No asignado'}
                                        </p>
                                        <hr>
                                        <h6 class="mb-2"><i class="bx bx-time"></i> Tiempos de Entrega:</h6>
                                        <p class="mb-2 ms-3">
                                            <i class="bx bx-right-arrow-alt"></i> Salida: ${formatDateTime(delivery.departuring)}
                                        </p>
                                        <p class="mb-2 ms-3">
                                            <i class="bx bx-right-arrow-alt"></i> Llegada: ${formatDateTime(delivery.arriving)}
                                        </p>
                                        <p class="mb-2 ms-3">
                                            <i class="bx bx-right-arrow-alt"></i> Inicio Descarga: ${formatDateTime(delivery.unload_starting)}
                                        </p>
                                        <p class="mb-2 ms-3">
                                            <i class="bx bx-right-arrow-alt"></i> Fin Descarga: ${formatDateTime(delivery.unload_finishing)}
                                        </p>
                                        <p class="mb-2 ms-3">
                                            <i class="bx bx-right-arrow-alt"></i> Salida del Sitio: ${formatDateTime(delivery.departure_from_site)}
                                        </p>
                                        <p class="mb-2 ms-3">
                                            <i class="bx bx-right-arrow-alt"></i> Retorno a Planta: ${formatDateTime(delivery.return_to_plant)}
                                        </p>
                                        <div class="d-inline-flex justify-content-end mt-auto mb-2 gap-1">
                                            <a class="btn delete-delivery p-1" data-delivery-id="${delivery.id}">
                                                <i class="bx bx-trash"></i>
                                            </a>                       
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
            noteDeliveryListContainer.append(card);
        });

        $('.clients-card').on('click', function (e) {
            if (!$(e.target).closest('button').length) {
                e.preventDefault();
                const $this = $(this);
                const $icon = $this.find('.clients-card-toggle i');
                const $body = $this.find('.clients-card-body');

                $icon.toggleClass('bx-chevron-down bx-chevron-up');
                $body.slideToggle();
            }
        });
    }

    displayNoteDeliveries();

    $('#searchNoteDelivery').on('input', function () {
        const searchTerm = $(this).val().toLowerCase();
        $('.note-delivery-card-wrapper').each(function () {
            const deliveryInfo = $(this).text().toLowerCase();
            $(this).toggle(deliveryInfo.includes(searchTerm));
        });
    });

    $(document).on('click', '.delete-delivery', function(e) {
        e.preventDefault();
        const deliveryId = $(this).data('delivery-id');
        const card = $(this).closest('.note-delivery-card-wrapper');
    
        Swal.fire({
            title: '¿Está seguro?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${window.baseUrl}admin/note-deliveries/${deliveryId}`, 
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') 
                    },
                    success: function(response) {
                        if (response.success) {
                            card.fadeOut(300, function() {
                                $(this).remove();
                                if ($('.note-delivery-card-wrapper').length === 0) {
                                    displayNoteDeliveries();
                                }
                            });
                            
                            Swal.fire(
                                '¡Eliminado!',
                                'El envío ha sido eliminado.',
                                'success'
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        Swal.fire(
                            'Error',
                            'No se pudo eliminar el envío',
                            'error'
                        );
                    }
                });
            }
        });
    });

});
