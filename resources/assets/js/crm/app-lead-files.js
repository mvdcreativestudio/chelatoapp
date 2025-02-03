document.addEventListener('DOMContentLoaded', function() {
    // Manejador para el botón "Ver multimedia"
    $(document).on('click', '.view-lead-files', function() {
        const leadItem = $(this).closest('.kanban-item');
        const leadId = leadItem.data('id');
        const leadName = leadItem.find('.card-title').text();
        const leadEmail = leadItem.find('.card-text').first().text();
        const leadPhone = leadItem.find('.card-text').last().text();

        // Actualizar información del lead en el modal
        $('#filesModal .lead-name').text(leadName);
        $('#filesModal .lead-email').text(leadEmail);
        $('#filesModal .lead-phone').text(leadPhone);

        // Limpiar la lista de archivos
        $('#filesList').empty();

        // Cargar archivos existentes
        $.ajax({
            url: `lead-attached-files/${leadId}`,
            method: 'GET',
            success: function(files) {
                if (files.length > 0) {
                    files.forEach(function(file) {
                        appendFileToList(file);
                    });
                } else {
                    $('#filesList').html('<p class="text-muted no-files">No hay archivos adjuntos</p>');
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron cargar los archivos'
                });
            }
        });

        // Configurar el input de archivo para el lead actual
        $('#fileInput').data('lead-id', leadId);

        // Mostrar el modal
        $('#filesModal').modal('show');
    });

    // Manejador para la carga de archivos
    $('#fileInput').on('change', function() {
        const file = this.files[0];
        const leadId = $(this).data('lead-id');
        
        if (!file) return;

        const formData = new FormData();
        formData.append('file', file);
        formData.append('lead_id', leadId);

        $.ajax({
            url: 'lead-attached-files',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': window.csrfToken
            },
            success: function(response) {
                if (response.success) {
                    // Eliminar el mensaje de "No hay archivos adjuntos" si existe
                    $('#filesList .no-files').remove();
                    appendFileToList(response.file);
                    // Limpiar el input
                    $('#fileInput').val('');
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cargar el archivo'
                });
            }
        });
    });

    // Función para agregar un archivo a la lista
    function appendFileToList(file) {
        const isImage = /\.(jpg|jpeg|png|gif)$/i.test(file.file);
        const fileName = file.file.split('/').pop();
        const fileUrl = `/storage/${file.file}`;

        let fileHtml = `
            <div class="col-md-6 mb-3">
                <div class="file-item h-100 border rounded">
                    <div class="p-2">
                        <div class="file-preview mb-2 d-flex justify-content-center align-items-center" style="height: 150px; background-color: #f8f9fa;">
                            ${isImage ? 
                                `<img src="${fileUrl}" class="img-fluid" style="max-height: 150px; object-fit: contain;">` :
                                `<i class="bx bx-file fs-1 text-secondary"></i>`
                            }
                        </div>
                        <div class="file-info">
                            <div class="btn-group w-100">
                                <a href="${fileUrl}" class="btn btn-outline-primary btn-sm flex-grow-1" target="_blank">
                                    <i class="bx bx-show me-1"></i> Ver
                                </a>
                                <a href="${fileUrl}" class="btn btn-outline-secondary btn-sm flex-grow-1" download>
                                    <i class="bx bx-download me-1"></i> Descargar
                                </a>
                                <button class="btn btn-outline-danger btn-sm flex-grow-1 delete-file" data-id="${file.id}">
                                    <i class="bx bx-trash me-1"></i> Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Si es el primer elemento o si el último row está lleno, crear nuevo row
        if ($('#filesList .row').length === 0 || $('#filesList .row:last .col-md-6').length === 2) {
            $('#filesList').append('<div class="row"></div>');
        }
        
        // Agregar el archivo al último row
        $('#filesList .row:last').append(fileHtml);
    }

    // Manejador para eliminar archivos
    $(document).on('click', '.delete-file', function() {
        const fileId = $(this).data('id');
        const fileItem = $(this).closest('.file-item');

        // Close the modal before showing the Swal alert
        const bsModal = bootstrap.Modal.getInstance(document.getElementById('filesModal'));
        bsModal.hide();

        Swal.fire({
            title: '¿Estás seguro?',
            text: "No podrás revertir esta acción",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `lead-attached-files/${fileId}`,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': window.csrfToken
                    },
                    success: function(response) {
                        fileItem.remove();
                        Swal.fire(
                            'Eliminado!',
                            'El archivo ha sido eliminado.',
                            'success'
                        );
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            'No se pudo eliminar el archivo.',
                            'error'
                        );
                    }
                });
            }
        });
    });
});