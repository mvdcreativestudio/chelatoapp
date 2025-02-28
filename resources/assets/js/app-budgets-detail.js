'use strict';

$(function () {
    // Inicializar DataTable
    var dt_budget_details = $('.datatables-budget-details').DataTable({
        dom: 't',
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        }
    });

    // Manejar la eliminación del presupuesto
    $(document).on('click', '.delete-budget', function() {
        const budgetId = $(this).data('id');

        Swal.fire({
            title: '¿Estás seguro?',
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
                    url: `/admin/budgets/${budgetId}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: '¡Eliminado!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: 'Aceptar'
                            }).then(() => {
                                window.location.href = '/admin/budgets';
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: response.message,
                                icon: 'error',
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error',
                            text: 'Ocurrió un error al eliminar el presupuesto',
                            icon: 'error',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                });
            }
        });
    });

    // Modificar el handler del botón convert-to-order
    $(document).on('click', '.convert-to-order', function(e) {
        e.preventDefault();
        const budgetId = $(this).data('id');
        const url = $(this).data('url');
        
        Swal.fire({
            title: '¿Convertir a orden?',
            text: "¿Desea generar una venta a partir de este presupuesto?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, generar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            window.location.href = response.redirect;
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: response.message || 'Error al procesar la solicitud',
                                icon: 'error'
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Ocurrió un error al procesar la solicitud',
                            icon: 'error'
                        });
                    }
                });
            }
        });
    });

    // Actualizar estado del presupuesto
    $('#updateStatusForm').on('submit', function(e) {
        e.preventDefault();
        const budgetId = $(this).data('budget-id'); // Obtener el ID del data attribute
        const status = $('#status').val();

        $.ajax({
            url: `/admin/budgets/${budgetId}/status`,
            type: 'PUT',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                status: status
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: '¡Actualizado!',
                        text: 'El estado del presupuesto ha sido actualizado',
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: response.message,
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Ocurrió un error al actualizar el estado',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            }
        });
    });

    // Inicializar tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Email notification handler
    $('.notify-by-email').on('click', function(e) {
        e.preventDefault();
        $('#sendEmailModal').modal('show');
    });

    $('#sendEmailForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        
        // Cerrar el modal
        $('#sendEmailModal').modal('hide');

        // Mostrar Swal de "Enviando..."
        Swal.fire({
            title: 'Enviando...',
            text: 'Por favor, espera mientras enviamos el presupuesto.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                Swal.close(); // Cerrar Swal de "Enviando..."

                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 2000
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Ocurrió un error al enviar el correo',
                        showConfirmButton: true
                    });
                }
            },
            error: function(xhr) {
                Swal.close(); // Cerrar Swal de "Enviando..." en caso de error

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Ha ocurrido un error al enviar el correo',
                    showConfirmButton: true
                });
            }
        });
    });
});