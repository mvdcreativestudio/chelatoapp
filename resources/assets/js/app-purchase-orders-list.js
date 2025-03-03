import 'select2/dist/js/i18n/es';

document.addEventListener('DOMContentLoaded', function () {
    $.ajax({
        url: 'suppliers-all',
        method: 'GET',
        success: function (response) {
            let supplierSelect = $('#supplier_id');
            let editSupplierSelect = $('#edit_supplier_id');
            supplierSelect.empty();
            editSupplierSelect.empty();

            supplierSelect.append('<option value="">Seleccione un proveedor</option>');
            editSupplierSelect.append('<option value="">Seleccione un proveedor</option>');

            response.forEach(function (supplier) {
                const option = `<option value="${supplier.id}">ID: ${supplier.id} - ${supplier.name}</option>`;
                supplierSelect.append(option);
                editSupplierSelect.append(option);
            });

            supplierSelect.select2({
                placeholder: "Seleccione un proveedor",
                allowClear: true,
                width: '100%',
                minimumResultsForSearch: 0,
                language: "es",
                dropdownParent: $('#addOrderOffCanvas')
            });

            editSupplierSelect.select2({
                placeholder: "Seleccione un proveedor",
                allowClear: true,
                width: '100%',
                minimumResultsForSearch: 0,
                language: "es",
                dropdownParent: $('#editPurchaseOrderCanvas') 
            });
        },
        error: function (xhr, status, error) {
            console.error('Error:', error);
            console.error('Detalles:', xhr.responseText);
        }
    });
    
    

    var table = $('.datatables-purchase-orders').DataTable({
        "order": [[0, "desc"]],
        data: purchaseOrders, // Aquí se cargan los datos que pasaste desde el controlador
        columns: [
            { data: 'id' },
            { data: 'supplier_name' }, // Asegúrate de ajustar este nombre de columna al campo correcto
            {
                data: 'created_at', render: function (data, type, row) {
                    const date = new Date(data);
                    return date.toLocaleDateString('es-ES', { timeZone: 'UTC' }); 
                },
            },
            {
                data: 'due_date', render: function (data, type, row) {
                    const date = new Date(data);
                    return date.toLocaleDateString('es-ES', { timeZone: 'UTC' }); 
                }
            },
            {
                data: 'status', render: function (data, type, row) {
                    let statusClass = '';
                    let statusText = '';

                    switch (Number(data)) {
                        case 0:
                            statusClass = 'bg-danger text-white';
                            statusText = 'Cancelada';
                            break;
                        case 1:
                            statusClass = 'bg-secondary text-white';
                            statusText = 'Pendiente';
                            break;
                        case 2:
                            statusClass = 'bg-success text-white';
                            statusText = 'Completada';
                            break;
                        default:
                            statusClass = 'bg-light text-dark';
                            statusText = 'Desconocido';
                    }

                    return `<span class="badge ${statusClass}">${statusText}</span>`;
                }
            },
            {
                data: null,
                className: "text-center",
                orderable: false,
                render: function (data, type, row) {
                    return `
                    <button class="btn btn-primary btn-view-raw-materials" data-id="${row.id}">Ver</button>`;
                }
            },
            {
                data: null,
                className: "text-center",
                orderable: false,
                render: function (data, type, row) {
                    return `
                    <div class="dropdown">
                        <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <li><button class="dropdown-item btn-entries" data-id="${row.id}">Confirmar recepción</button></li>
                            <li><button class="dropdown-item btn-pdf" data-id="${row.id}">Generar PDF</button></li>
                            <li><button class="dropdown-item btn-edit" data-id="${row.id}">Editar</button></li>
                            <li><button class="dropdown-item btn-delete" data-id="${row.id}">Eliminar</button></li>
                        </ul>
                    </div>`;
                }
            }
        ],
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.3/i18n/es_es.json'
        }
    });

    $('.toggle-column').on('change', function (e) {
        var column = table.column($(this).attr('data-column'));
        column.visible(!column.visible());
    });

    // Manejar el envío del formulario de agregar orden
    $('#addOrderForm').on('submit', function (event) {
        event.preventDefault();

        console.log('Enviando formulario...');
        $.ajax({
            url: 'purchase-orders',
            method: 'POST',
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': window.csrfToken
            },
            success: function (response) {
                if (response.success) {
                    $('#addOrderOffCanvas').offcanvas('hide');
                    $('#addOrderForm')[0].reset();
                    Swal.fire(
                        'Agregada!',
                        'La orden de compra ha sido agregada.',
                        'success'
                    ).then(() => {
                        window.location.reload();
                    });

                }
                console.log('Formulario enviado');
            },
            error: function (xhr, status, error) {
                console.error('Error, url utilizada:', this.url);
                console.error('Error:', error);
                console.error('Detalles:', xhr.responseText);
            }
        });
    });

    $(document).on('click', '.btn-view-raw-materials', function () {
        var purchaseOrderId = $(this).data('id');

        $.ajax({
            url: 'store-purchase-order-item-id',
            method: 'POST',
            data: {
                id: purchaseOrderId
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function () {
                window.location.href = 'purchase-orders-items';
            },
            error: function (xhr, status, error) {
                console.error('Error:', error);
                console.error('Detalles:', xhr.responseText);
            }
        });
    });

    $(document).on('click', '.btn-entries', function () {
        var purchaseOrderId = $(this).data('id');
        var row = table.row($(this).parents('tr')).data(); // Obtén la fila correspondiente
        var status = row.status; // Obtener el status de la orden

        // Verifica el estado de la orden
        if (status == 0) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'La orden de compra está cancelada.',
            });
        } else if (status == 2) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'La orden de compra está completada.',
            });
        } else if (status == 1) {
            // Si la orden está pendiente (status 1), realiza la solicitud Ajax
            $.ajax({
                url: 'store-purchase-order-item-id',
                method: 'POST',
                data: {
                    id: purchaseOrderId
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function () {
                    window.location.href = 'purchase-entries';
                },
                error: function (xhr, status, error) {
                    console.error('Error:', error);
                    console.error('Detalles:', xhr.responseText);
                }
            });
        }
    });


    $(document).on('click', '.btn-delete', function () {
        var orderId = $(this).data('id');
        eliminarOrdenDeCompra(orderId);
    });

    function eliminarOrdenDeCompra(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "No podrás revertir esto.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `purchase-orders/${id}`,
                    type: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        Swal.fire(
                            'Eliminado!',
                            'La orden de compra ha sido eliminada.',
                            'success'
                        ).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function (error) {
                        Swal.fire(
                            'Error!',
                            'Ocurrió un problema al intentar eliminar la orden de compra.',
                            'error'
                        );
                    }
                });
            }
        });
    }

    $(document).on('click', '.btn-pdf', function () {
        var orderId = $(this).data('id');
        $.ajax({
            url: 'purchase-orders/pdf',
            type: 'POST',
            data: {
                purchase_order_id: orderId,
            },
            headers: {
                'X-CSRF-TOKEN': window.csrfToken
            },
            xhrFields: {
                responseType: 'blob'
            },
            success: function (blob) {
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'orden_compra_' + orderId + '.pdf';
                link.click();
            },
            error: function (xhr, status, error) {
                console.error(error);
                alert('Ocurrió un error al generar el PDF.');
            }
        });
    });

    // Manejar clic en el botón de editar
    $(document).on('click', '.btn-edit', function () {
        var purchaseOrderId = $(this).data('id');

        // Obtener los datos de la orden de compra
        $.ajax({
            url: `purchase-orders/${purchaseOrderId}`,
            method: 'GET',
            success: function (purchaseOrder) {
                // Llenar el formulario del off-canvas
                $('#edit_order_id').val(purchaseOrder.id);
                $('#edit_supplier_id').val(purchaseOrder.supplier_id);
                $('#edit_due_date').val(purchaseOrder.due_date);

                // Abrir el off-canvas
                var editCanvas = new bootstrap.Offcanvas(document.getElementById('editPurchaseOrderCanvas'));
                editCanvas.show();
            },
            error: function (xhr, status, error) {
                console.error('Error al obtener la orden de compra:', error);
                console.error('Detalles:', xhr.responseText);
            }
        });
    });

    // Manejar el envío del formulario de edición
    $('#editOrderForm').on('submit', function (event) {
        event.preventDefault();

        var orderId = $('#edit_order_id').val();
        var formData = {
            supplier_id: $('#edit_supplier_id').val(),
            due_date: $('#edit_due_date').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        $.ajax({
            url: `purchase-orders/${orderId}`,
            method: 'PUT', 
            data: formData,
            success: function (response) {
                var row = table.row(function (idx, data, node) {
                    return data.id === response.id;
                });

                response.supplier_name = response.supplier.name;

                row.data(response).draw();

                // Cerrar el off-canvas
                var editCanvasEl = document.getElementById('editPurchaseOrderCanvas');
                var editCanvas = bootstrap.Offcanvas.getInstance(editCanvasEl);
                editCanvas.hide();

                Swal.fire('Éxito', 'Orden de compra actualizada correctamente.', 'success');
            },
            error: function (xhr, status, error) {
                console.error('Error al actualizar la orden de compra:', error);
                console.error('Detalles:', xhr.responseText);
                Swal.fire('Error', 'No se pudo actualizar la orden de compra.', 'error');
            }
        });
    });
});
