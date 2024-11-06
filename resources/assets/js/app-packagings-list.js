document.addEventListener('DOMContentLoaded', function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': window.csrfToken
        }
    });

    $('#loadPackagesButton').click(function () {
        window.location.href = "packages"; // Redirige a la URL del método index
    });

    $('#openProductionModal').click(function () {
        // Mostrar el modal usando Bootstrap
        $('#productionModal').modal('show');

        // Hacer la llamada AJAX para obtener las bulk productions
        $.ajax({
            url: 'start-production', // Ruta al controlador
            type: 'GET',
            success: function (data) {
                $('#bulk_production').empty();
                $('#bulk_production').append('<option value="" disabled selected>Seleccione una producción</option>');

                $.each(data, function (index, bulkProduction) {

                    var formulaQuantity = bulkProduction.formula_quantity || 1;

                    var totalQuantity = bulkProduction.quantity_available * formulaQuantity;

                    $('#bulk_production').append(
                        $('<option>', {
                            value: bulkProduction.bulk_production_id,
                            text: bulkProduction.formula_name + ' - ' + totalQuantity + ' ' + bulkProduction.formula_unit_of_measure +' - ' + bulkProduction.batch_number,
                            'data-quantity-produced': totalQuantity,
                            'data-unit-of-measure': bulkProduction.formula_unit_of_measure,
                            'data-formula-quantity': formulaQuantity
                        })
                    );
                });
            },
            error: function () {
                alert('Error al obtener las producciones a granel.');
            }
        });

        // Hacer la llamada AJAX para obtener los packages
        $.ajax({
            url: 'packages-all', // Ruta para obtener los paquetes
            type: 'GET',
            success: function (data) {
                $('#package_id').empty();
                $('#package_id').append('<option value="" disabled selected>Seleccione un envase</option>');

                $.each(data[0], function (index, pck) {
                    $('#package_id').append(
                        $('<option>', {
                            value: pck.id,
                            text: pck.name + '-' + pck.size + ' ' + pck.unit_of_measure,
                            'data-size': pck.size, // Guardar el tamaño del paquete
                            'data-unit-of-measure': pck.unit_of_measure, // Guardar la unidad de medida del paquete
                            'data-stock': pck.stock // Guardar el stock del paquete
                        })
                    );
                });
            },
            error: function () {
                alert('Error al obtener los paquetes.');
            }
        });

        $.ajax({
            url: 'package-components-select', // Ruta para obtener los paquetes
            type: 'GET',
            success: function (data) {
                // Limpiar los selects antes de llenarlos
                $('#tap_id').empty();
                $('#label_id').empty();

                $('#tap_id').append(
                    $('<option>', {
                        value: '',
                        text: 'Seleccionar una tapa (Opcional)'
                    })
                );

                $('#label_id').append(
                    $('<option>', {
                        value: '',
                        text: 'Seleccionar una etiqueta (Opcional)'
                    })
                );
                // Rellenar el select de tapas
                $.each(data.taps, function (index, tap) {
                    $('#tap_id').append(
                        $('<option>', {
                            value: tap.id,
                            text: 'ID: ' + tap.id + ' - ' + tap.name
                        })
                    );
                });

                // Rellenar el select de etiquetas
                $.each(data.labels, function (index, label) {
                    $('#label_id').append(
                        $('<option>', {
                            value: label.id,
                            text: 'ID: ' + label.id + ' - ' + label.name
                        })
                    );
                });
            },
            error: function () {
                alert('Error al obtener los paquetes.');
            }
        });
    });

    // Manejar el submit del formulario
    $('#submitProductionForm').click(function (e) {
        e.preventDefault();

        const bulkProductionId = $('#bulk_production').val();
        const quantityToPackage = parseFloat($('#quantity_packaged').val()); // Quantity of packages
        const packageId = $('#package_id').val();
        const labelId = $('#label_id').val() ? $('#label_id').val() : null;
        const tapId = $('#tap_id').val() ? $('#tap_id').val() : null;

        const packageSize = parseFloat($('#package_id option:selected').data('size')); // Package size
        const packageUnitOfMeasure = $('#package_id option:selected').data('unit-of-measure').toLowerCase(); // Unit of measure of the package
        const packageStock = parseFloat($('#package_id option:selected').data('stock')); // Package stock
        const bulkQuantityProduced = parseFloat($('#bulk_production option:selected').data('quantity-produced')); // Bulk quantity produced
        const bulkUnitOfMeasure = $('#bulk_production option:selected').data('unit-of-measure').toLowerCase(); // Bulk unit of measure
        const formulaInfo = parseFloat($('#bulk_production option:selected').data('formula-quantity'));

        function convertToMl(value, unit) {
            if (unit === 'l') {
                return value * 1000;
            }
            return value;
        }

        const bulkQuantityInMl = convertToMl(bulkQuantityProduced, bulkUnitOfMeasure);
        const packageSizeInMl = convertToMl(packageSize, packageUnitOfMeasure);
        const totalToPackageInMl = quantityToPackage * packageSizeInMl;

        if (totalToPackageInMl > bulkQuantityInMl) {
            $('#productionModal').modal('hide');
            swal.fire('Error', 'No hay suficiente cantidad en la producción a granel.', 'error');
            return;
        }

        if (quantityToPackage > packageStock) {
            $('#productionModal').modal('hide');
            swal.fire('Error', 'No hay suficiente stock del paquete seleccionado.', 'error');
            return;
        }


        let quantityUsedInMl = totalToPackageInMl;
        let quantityUsed;

        if (bulkUnitOfMeasure === 'l' || bulkUnitOfMeasure === 'L') {
            if (packageUnitOfMeasure === 'l' || packageUnitOfMeasure === 'L') {
                quantityUsed = (quantityToPackage * packageSize) / formulaInfo;
            } else {
                quantityUsed = ((quantityToPackage * packageSize) / 1000) / formulaInfo
            }
        } else {
            quantityUsed = (quantityToPackage * packageSizeInMl) / formulaInfo; // In terms of package size
        }

        quantityUsed = parseFloat(quantityUsed.toFixed(2));
        console.log(quantityUsed);

        $.ajax({
            url: 'packagings',
            type: 'POST',
            data: {
                bulk_production_id: bulkProductionId,
                quantity_packaged: quantityToPackage,
                quantity_used: quantityUsed,
                package_id: packageId,
                label_id: labelId,
                tap_id: tapId,
                packaging_date: new Date().toISOString().slice(0, 10)
            },
            success: function (response) {
                $('#productionModal').modal('hide');
                swal.fire('Éxito', 'Empaque iniciado con éxito.', 'success').then(() => {
                    window.location.reload();
                });
            },
            error: function () {
                $('#productionModal').modal('hide');
                swal.fire('Error', 'Error al iniciar el empaque.', 'error');
            }
        });
    });

    $(document).on('click', '.btn-delete-packaging', function () {
        var packagingId = $(this).data('id');

        eliminarPackaging(packagingId);
    });

    function eliminarPackaging(packagingId) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "No podrás revertir esta acción. El envasado será eliminado y el stock agregado al producto se mantendrá igual.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `packagings/${packagingId}`,  // Cambia esta ruta por la correcta
                    type: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        Swal.fire(
                            'Eliminado!',
                            'El envasado ha sido eliminado.',
                            'success'
                        ).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function (error) {
                        Swal.fire(
                            'Error!',
                            'Ocurrió un problema al intentar eliminar el envasado.',
                            'error'
                        );
                    }
                });
            }
        });
    }
});
