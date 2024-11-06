document.addEventListener('DOMContentLoaded', function () {

    // Inicializar el repeater de los productos/materias primas comprados. 
    $('.purchase-entries').repeater({
        initEmpty: false,
        defaultValues: {
            'entry_date': ''
        },
        show: function () {
            $(this).slideDown();
        },
        hide: function (deleteElement) {
            $(this).slideUp(deleteElement);
        },
        isFirstItemUndeletable: false
    });

    // Inicializar el repeater del modal (para los lotes)
    $('#repeater').repeater({
        initEmpty: false,
        defaultValues: {
            'batch_number': ''
        },
        show: function () {
            $(this).slideDown();
        },
        hide: function (deleteElement) {
            $(this).slideUp(deleteElement);
        },
        ready: function (setIndexes) {
            // You can use this callback to perform actions after the repeater is ready
        },
        isFirstItemUndeletable: false
    });



    // Evento para el botón de enviar todas las entradas
    $('#submit-all-entries').on('click', function () {
        let entries = [];
        let hasError = false;

        $('[data-repeater-item]').filter(':visible').each(function (index) {
            console.log("Processing item index: ", index); // Debug index
            let receivedQuantity = $(this).find('input[name*="[received_quantity]"]').val();
            let entryDate = $(this).find('input[name*="[entry_date]"]').val();
            let remainingQuantity = $(this).find('input[name*="[remaining_quantity]"]').val();

            let entry = {
                purchase_order_items_id: $(this).find('select[name*="[purchase_order_items_id]"]').val(),
                remaining_quantity: remainingQuantity,
                received_quantity: receivedQuantity,
                entry_date: entryDate
            };

            if (!entry.received_quantity || !entry.entry_date) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Datos Faltantes',
                    text: 'Por favor, ingresa la cantidad recibida y la fecha de entrada.',
                });
                hasError = true;
                return false; // Detener el bucle si falta algún dato
            }
            
            if (parseInt(entry.received_quantity) > parseInt(entry.remaining_quantity)) {
      
                Swal.fire({
                    icon: 'error',
                    title: 'Cantidad Excedida',
                    text: 'La cantidad recibida excede la cantidad restante.',
                });
                hasError = true;
                return false; // Detener el bucle si la cantidad excede
            }

            entries.push(entry);
        });

        if (!hasError && entries.length > 0) {
            $.ajax({
                url: 'purchase-entries-multiple',
                method: 'POST',
                data: {
                    _token: window.csrfToken,
                    purchase_entries: entries
                },
                success: function (response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: 'Entradas de compra creadas con éxito.',
                        }).then(() => {
                            window.location.reload();
                        });
                    }
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al enviar los datos. Revisa los campos.',
                    });
                    console.log(xhr.responseText);
                }
            });
        }
    });



    $(document).on('click', '.open-lot-modal', function () {
        // Almacena el entryId en una variable global para usarlo más tarde
        var entryId = $(this).data('entry-id');
        var maxQuantity = $(this).data('entry-quantity');

        $('#batchModal').modal('show');

        // Cuando se haga clic en el botón de guardar, usa la variable entryId almacenada
        $('#save-lots').off('click').on('click', function () {
            var totalQuantity = 0;
            $('.lot-quantity').each(function () {
                totalQuantity += parseFloat($(this).val()) || 0;
            });

            if (totalQuantity > maxQuantity) {
                $('#batchModal').modal('hide');

                Swal.fire({
                    icon: 'warning',
                    title: 'Error cantidad ingresada',
                    text: 'Ingresó más elementos que los de la orden. Usted ingresó ' + totalQuantity + ' unidades y la cantidad de elementos para esta orden era ' + maxQuantity + ' unidades'
                });
            } else if (totalQuantity < maxQuantity) {
                $('#batchModal').modal('hide');

                Swal.fire({
                    icon: 'warning',
                    title: 'Error cantidad ingresada',
                    text: 'Ingresó menos elementos que los de la orden. Usted ingresó ' + totalQuantity + ' unidades y la cantidad de elementos para esta orden era ' + maxQuantity + ' unidades'
                });
            } else {
                var formDataArray = $('#batches-form').serializeArray();
                var batchesData = {};

                formDataArray.forEach(item => {
                    let match = item.name.match(/batches\[(\d+)\]\[(.+?)\]/);
                    if (match) {
                        let index = match[1];
                        let key = match[2];
                        if (!batchesData[index]) {
                            batchesData[index] = {};
                        }
                        batchesData[index][key] = item.value;
                    }
                });

                // Usa la variable entryId que almacenaste
                $.ajax({
                    url: 'batches-multiple',
                    type: 'POST',
                    data: JSON.stringify({
                        batches: Object.values(batchesData), // Convierte el objeto de lotes en un array
                        purchase_entries_id: entryId,
                        _token: window.csrfToken
                    }),
                    contentType: 'application/json',
                    success: function (response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: response.success,
                        }).then((result) => {window.location.reload();});;
                        $('#batchModal').modal('hide');
                        
                    },
                    error: function (response) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un error al guardar los lotes.',
                        });
                    }
                });
            }
        });
    });
});