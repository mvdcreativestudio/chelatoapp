document.addEventListener('DOMContentLoaded', function () {
    let id = $('#purchaseOrderId').data('id');

    let rawMaterialsMap = {};
    var table;

    $.ajax({
        url: 'purchase-orders-items-raw-materials',
        method: 'GET',
        success: function (response) {
            var select = $('#raw_material_id');
            select.empty();
            select.html('<option value="" selected disabled>Seleccione una opción</option>');
            response.forEach(function (material) {
                select.append(`<option value="${material.id}"  data-unit-measure="${material.unit_of_measure}">${material.name}</option>`);
                rawMaterialsMap[material.id] = material.name;
            });

            initDataTable();
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar las materias primas:', error);
            console.error('Detalles:', xhr.responseText);
        }
    });


    function initDataTable() {
        table = $('.datatables-purchase-order-items').DataTable({
            "order": [[0, "desc"]],
            data: purchaseOrderItems.map(item => {
                return {
                    ...item,
                    raw_material_name: rawMaterialsMap[item.raw_material_id] || 'Desconocido'
                };
            }),
            columns: [
                { data: 'id' },
                { data: 'raw_material_name' },
                { data: 'quantity' },
                { data: 'currency' },
                { data: 'unit_price' },
                {
                    data: null,
                    render: function (data, type, row) {
                        return (row.quantity * row.unit_price).toFixed(2);
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
            },
            drawCallback: function () {
                updateOrderValue();
                updateUniqueMaterialsCount();
            }
        });
    }

    function updateOrderValue() {
        let totalUSD = 0;
        let totalUYU = 0;

        table.rows().every(function () {
            let data = this.data();
            let total = data.quantity * data.unit_price;

            if (data.currency === 'USD') {
                totalUSD += total;
            } else if (data.currency === 'UYU') {
                totalUYU += total;
            }
        });

        // Actualizar el valor de las tarjetas
        $('.total-usd').text('$' + totalUSD.toFixed(2));
        $('.total-uyu').text('UYU ' + totalUYU.toFixed(2));
    }


    // Manejo de la visibilidad de columnas
    $('.toggle-column').on('change', function (e) {
        var column = table.column($(this).attr('data-column'));
        column.visible(!column.visible());
    });

    function updateUniqueMaterialsCount() {
        let uniqueMaterials = new Set();
        table.rows().every(function () {
            let data = this.data();
            uniqueMaterials.add(data.raw_material_id);
        });
        $('.different-raw-materials').text(uniqueMaterials.size);
    }

    $('#addItemForm').on('submit', function (event) {
        event.preventDefault();

        let rawMaterialId = $('#raw_material_id').val();
        let unitPrice = parseFloat($('#unit_price').val()).toFixed(2);
        let currency = $('#currency').val();


        // Verificar si no se ha seleccionado una materia prima
        if (!rawMaterialId) {
            Swal.fire({
                title: 'Error',
                text: 'Debe seleccionar una materia prima antes de enviar el formulario.',
                icon: 'error'
            });
            return;  // No se envía el formulario
        }
        // Realizar la llamada para obtener el último precio de la materia prima
        $.ajax({
            url: `raw-material-prices/${rawMaterialId}`,
            method: 'GET',
            success: function (response) {
                let lastPrice = response.price ? parseFloat(response.price).toFixed(2) : null;
                let lastCurrency = response.currency ? response.currency : null;
                $('#addItemOffCanvas').offcanvas('hide');
                // Verificar si no existe un precio anterior
                if (lastPrice === null || lastPrice === undefined) {
                    Swal.fire({
                        title: 'Nuevo precio',
                        text: `No se encontró un precio anterior. Se agregará el precio actual de $${unitPrice}.`,
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonText: 'Aceptar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            submitFormWithNewPrice(rawMaterialId, currency, unitPrice);
                        }
                    });
                } else if (lastPrice !== unitPrice || lastCurrency !== currency) {
                    $('#addItemOffCanvas').offcanvas('hide');
                    // Mostrar la alerta si el precio es diferente
                    Swal.fire({
                        title: 'Precio diferente detectado',
                        text: `El último precio fue ${lastCurrency}${lastPrice}, estás ingresando ${currency} ${unitPrice}. ¿Deseas continuar y registrar el nuevo precio?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Aceptar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            submitFormWithNewPrice(rawMaterialId, currency, unitPrice);
                        }
                    });
                } else {
                    // Si el precio es igual, simplemente proceder sin mostrar la alerta
                    submitForm();
                }
            },
            error: function (xhr, status, error) {
                console.error('Error al verificar el precio anterior:', error);
            }
        });
    });

    // Función para hacer el POST de nuevos precios y luego agregar el item a la orden
    function submitFormWithNewPrice(rawMaterialId, currency, unitPrice) {
        $.ajax({
            url: 'raw-material-prices',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.csrfToken
            },
            data: {
                raw_material_id: rawMaterialId,
                currency: currency,
                price: unitPrice
            },
            success: function () {
                // Proceder con el envío del formulario
                submitForm();
            },
            error: function (xhr, status, error) {
                console.error('Error al registrar el nuevo precio:', error);
            }
        });
    }

    // Función para proceder con el envío del formulario de items de orden de compra
    function submitForm() {
        console.log(id);
        if ($('#addItemForm input[name="purchase_orders_id"]').length === 0) {
            $('<input>').attr({
                type: 'hidden',
                name: 'purchase_orders_id',
                value: id
            }).prependTo('#addItemForm');
        }

        $.ajax({
            url: 'purchase-order-items',
            method: 'POST',
            data: $('#addItemForm').serialize(),
            headers: {
                'X-CSRF-TOKEN': window.csrfToken
            },
            success: function (response) {
                if (response.success) {
                    let newItem = response.purchaseOrderItem;
                    newItem.raw_material_name = rawMaterialsMap[newItem.raw_material_id] || 'Desconocido';
                    location.reload();  // Refrescar la página después de la inserción
                    $('#addItemOffCanvas').offcanvas('hide');  // Ocultar el offcanvas
                    $('#addItemForm')[0].reset();  // Resetear el formulario
                }
            },
            error: function (xhr, status, error) {
                console.error('Error:', error);
                console.error('Detalles:', xhr.responseText);
            }
        });
    }



    $(document).on('click', '.btn-delete', function () {
        var itemId = $(this).data('id');
        console.log(itemId);

        eliminarItemDeCompra(itemId);
    });

    function eliminarItemDeCompra(itemId) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "No podrás revertir esto",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `purchase-order-items/${itemId}`,
                    type: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        Swal.fire(
                            'Eliminado!',
                            'El item ha sido eliminada de la orden.',
                            'success'
                        ).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function (error) {
                        Swal.fire(
                            'Error!',
                            'Ocurrió un problema al intentar eliminar el item de la orden de compra.',
                            'error'
                        );
                    }
                });
            }
        });
    }

    $('#raw_material_id').on('change', function () {
        let rawMaterialId = $(this).val();

        const selectedRawMaterial = this.options[this.selectedIndex];
        const unitOfMeasure = selectedRawMaterial.getAttribute('data-unit-measure');
        const measurementUnit = (unitOfMeasure == "Unidades") ? "Un" : unitOfMeasure;
        document.getElementById('unit_of_measure').value = measurementUnit;
        // Hacer la solicitud para obtener el último precio del material seleccionado
        $.ajax({
            url: `raw-material-prices/${rawMaterialId}`,
            method: 'GET',
            success: function (response) {
                // Rellenar el campo de precio y moneda con el último valor registrado
                if (response.price && response.currency) {
                    $('#unit_price').val(parseFloat(response.price).toFixed(2));
                    $('#currency').val(response.currency);
                } else {
                    $('#unit_price').val(undefined);
                }
            },
            error: function (xhr, status, error) {
                console.error('Error al obtener el último precio:', error);
            }
        });
    });

});
