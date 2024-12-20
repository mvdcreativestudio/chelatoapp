$(document).ready(function() {
    // Seleccionar todos los switches de estado de evento
    $(".toggle-event-status").on("change", function() {
        const isActive = $(this).is(":checked");
        const eventId = $(this).data("event-id");
        const storeId = $(this).data("store-id");
        const $toggleSwitch = $(this); // Guardar referencia para revertir en caso de cancelación

        // Mostrar confirmación de activación/desactivación
        Swal.fire({
            title: isActive ? "¿Activar este evento?" : "¿Desactivar este evento?",
            text: `Está a punto de ${isActive ? "activar" : "desactivar"} el evento. ¿Desea continuar?`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: isActive ? "Activar" : "Desactivar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                // Crear el objeto a enviar
                const dataToSend = {
                    is_active: isActive,
                    event_id: eventId,
                    store_id: storeId
                };

                // Enviar solicitud AJAX para actualizar el estado
                $.ajax({
                    url: window.eventToggleStatus.replace(":id", storeId),
                    type: "POST",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                    },
                    contentType: "application/json",
                    data: JSON.stringify(dataToSend),
                    success: function(data) {
                        if (data.success) {
                            Swal.fire({
                                icon: "success",
                                title: "Estado del evento actualizado",
                                text: data.message,
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error al actualizar el estado del evento",
                                text: data.message,
                            });
                            $toggleSwitch.prop("checked", !isActive); // Revertir el cambio en caso de error
                        }
                    },
                    error: function(error) {
                        Swal.fire({
                            icon: "error",
                            title: "Error al actualizar el estado del evento",
                            text: "Hubo un error al intentar actualizar el estado del evento. Por favor, inténtelo de nuevo.",
                        });
                        $toggleSwitch.prop("checked", !isActive); // Revertir el cambio en caso de error
                    }
                });
            } else {
                // Revertir el cambio si el usuario cancela
                $toggleSwitch.prop("checked", !isActive);
            }
        });
    });
});
