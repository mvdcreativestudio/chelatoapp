'use strict';

$(function () {
    $('.select2').select2();

    $('#client_id').on('change', function () {
        if ($(this).val()) {
            $('#lead_id').val(null).trigger('change');
        }
    });

    $('#lead_id').on('change', function () {
        if ($(this).val()) {
            $('#client_id').val(null).trigger('change');
        }
    });
    
    // Función para mostrar u ocultar la tabla de productos seleccionados
    function toggleSelectedProductsTable() {
        const selectedProductsContainer = $('#selectedProductsContainer');
        const selectedProducts = $('#products').val() || [];
  
        if (selectedProducts.length > 0) {
          selectedProductsContainer.removeClass('d-none'); // Muestra la tabla
        } else {
          selectedProductsContainer.addClass('d-none'); // Oculta la tabla
        }
    }

    // Función para calcular el total de una fila
    function calculateRowTotal($row) {
        const quantity = parseInt($row.find('.product-quantity').val()) || 0;
        const price = parseFloat($row.find('.product-price').data('price')) || 0;
        const discountType = $row.find('.item-discount-type').val();
        const discount = parseFloat($row.find('.item-discount').val()) || 0;
        
        let rowTotal = quantity * price;
        
        if (discountType === 'Percentage' && discount > 0) {
            rowTotal = rowTotal - (rowTotal * (discount / 100));
        } else if (discountType === 'Fixed' && discount > 0) {
            rowTotal = rowTotal - (quantity * discount);
        }
        
        $row.find('.subtotal').text('$' + rowTotal.toFixed(2));
        return rowTotal;
    }

    // Función para calcular el total
    function calculateTotal() {
        let subtotal = 0;
        
        // Calcular subtotal sumando todos los subtotales de las filas
        $('#selectedProductsTable tbody tr').each(function() {
            subtotal += calculateRowTotal($(this));
        });
        
        // Aplicar descuento general del presupuesto
        let total = subtotal;
        const generalDiscountType = $('#discount_type').val();
        const generalDiscount = parseFloat($('#discount').val()) || 0;
        
        if (generalDiscountType === 'Percentage' && generalDiscount > 0) {
            total = subtotal - (subtotal * (generalDiscount / 100));
        } else if (generalDiscountType === 'Fixed' && generalDiscount > 0) {
            total = subtotal - generalDiscount;
        }
        
        $('#total').val(total.toFixed(2));
    }

    // Agregar eventos para recalcular cuando cambian los descuentos
    $('#discount_type, #discount').on('change input', calculateTotal);

    // Función para agregar un producto a la tabla
    function addProductToTable(product) {
        const buildPrice = parseFloat(product.build_price) || 0;
        const stock = product.stock || 0;
        
        // Añadir console.log para depurar
        console.log('Stock setting value:', $('.app-ecommerce').attr('data-allow-no-stock'));
        
        // Verificar estrictamente que sea la cadena "true"
        const allowNoStock = $('.app-ecommerce').attr('data-allow-no-stock') === 'true';
        console.log('Allow no stock interpreted as:', allowNoStock);
        
        let rowClass = '';
        const disabled = buildPrice === 0 ? 'disabled' : '';

        if (buildPrice === 0) {
            rowClass = 'table-danger';
        }

        // Check if product is already in table
        if (!$('#selectedProductsTable tbody').find(`tr[data-product-id="${product.id}"]`).length) {
            const row = `
                <tr data-product-id="${product.id}" class="${rowClass}">
                    <td>${product.name}</td>
                    <td>
                        <input type="number" 
                               class="form-control form-control-sm product-quantity" 
                               value="1" 
                               min="1"
                               ${!allowNoStock ? `max="${stock}"` : ''} 
                               name="items[${product.id}][quantity]"
                               data-product-id="${product.id}" 
                               data-build-price="${buildPrice}"
                               data-stock="${stock}" 
                               ${disabled}>
                    </td>
                    <td class="text-center">${stock}</td>
                    <td class="product-price text-end" data-price="${buildPrice}">
                        ${buildPrice > 0 ? '$' + buildPrice.toFixed(2) : 'N/A'}
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <select class="form-control form-control-sm item-discount-type w-50" 
                                    name="items[${product.id}][discount_type]">
                                <option value="">Sin descuento</option>
                                <option value="Percentage">%</option>
                                <option value="Fixed">$</option>
                            </select>
                            <input type="number" 
                                class="form-control form-control-sm item-discount w-60" 
                                name="items[${product.id}][discount]"
                                step="0.01" 
                                placeholder="0.00"
                                style="appearance: textfield; -moz-appearance: textfield; -webkit-appearance: textfield;">
                        </div>
                    </td>
                    <td class="subtotal text-end fw-bold">
                        ${buildPrice > 0 ? '$' + buildPrice.toFixed(2) : 'N/A'}
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger remove-product">
                            <i class="bx bx-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#selectedProductsTable tbody').append(row);

            // Add event listeners for the new row
            const $row = $(`tr[data-product-id="${product.id}"]`);
            
            // Quantity change handler
            $row.find('.product-quantity').on('input', function() {
                const value = parseInt($(this).val()) || 0;
                // Volver a verificar el valor para cada evento
                const allowNoStock = $('.app-ecommerce').attr('data-allow-no-stock') === 'true';
                
                if (!allowNoStock && value > stock) {
                    $(this).val(stock);
                    Swal.fire({
                        title: 'Stock insuficiente',
                        text: `El stock disponible para ${product.name} es ${stock}`,
                        icon: 'warning',
                        confirmButtonText: 'Entendido'
                    });
                } else if (value < 1) {
                    $(this).val(1);
                }
                calculateRowTotal($row);
                calculateTotal();
            });

            // Discount type and value change handlers
            $row.find('.item-discount-type, .item-discount').on('change input', function() {
                calculateRowTotal($row);
                calculateTotal();
            });
        }
    }

    // Modificar el evento change del select de productos
    $('#products').on('change', function () {
        const selectedProductIds = $(this).val() || [];
        const productsData = JSON.parse($('.app-ecommerce').attr('data-products'));
        const $tbody = $('#selectedProductsTable tbody');

        // Eliminar productos que ya no están seleccionados
        $tbody.find('tr').each(function() {
            const rowProductId = $(this).data('product-id');
            if (!selectedProductIds.includes(rowProductId.toString())) {
                $(this).remove();
            }
        });

        // Agregar nuevos productos seleccionados
        selectedProductIds.forEach(productId => {
            const product = productsData.find(p => p.id == productId);
            if (product && !$tbody.find(`tr[data-product-id="${productId}"]`).length) {
                addProductToTable(product);
            }
        });

        toggleSelectedProductsTable();
        calculateTotal();
    });

    // Recalcular total al cambiar la cantidad
    $('#selectedProductsTable').on('input', '.product-quantity', function () {
        const input = $(this);
        if (input.val() <= 0) {
            input.val(1); // Enforce that quantity must be at least 1
        }
        calculateTotal();
    });

    // Modificar la función de eliminar producto
    $('#selectedProductsTable').on('click', '.remove-product', function () {
        const $row = $(this).closest('tr');
        const productId = $row.data('product-id');

        // Eliminar la fila de la tabla
        $row.remove();

        // Desmarcar el producto en el select2
        const $select = $('#products');
        const currentValues = $select.val() || [];
        const newValues = currentValues.filter(value => value != productId);
        $select.val(newValues).trigger('change');

        calculateTotal();
        toggleSelectedProductsTable();
    });

    // Llama a la función al cargar la página para inicializar el estado de la tabla
    $(document).ready(function () {
        toggleSelectedProductsTable(); // Al inicio, por si ya hay productos seleccionados
    });

    // Manejar el clic en el botón "Descartar"
    $('#discardButton').on('click', function () {
        const url = $(this).data('url');
        window.location.href = url;
    });

    // Modificar el evento de submit del formulario
    $('#addBudgetForm').on('submit', function(e) {
        e.preventDefault();

        // Verificar productos seleccionados
        if (!$('#products').val()?.length) {
            Swal.fire({
                title: 'Error',
                text: 'Debe seleccionar al menos un producto',
                icon: 'error'
            });
            return false;
        }

        // Verificar cliente o lead
        if (!$('#client_id').val() && !$('#lead_id').val()) {
            Swal.fire({
                title: 'Error',
                text: 'Debe seleccionar un cliente o un lead',
                icon: 'error'
            });
            return false;
        }

        // Verificar stock si la configuración no permite presupuestos sin stock
        const allowNoStock = $('.app-ecommerce').attr('data-allow-no-stock') === 'true';
        if (!allowNoStock) {
            let stockError = false;
            let productsWithoutStock = [];

            $('#selectedProductsTable tbody tr').each(function() {
                const quantity = parseInt($(this).find('.product-quantity').val()) || 0;
                const stock = parseInt($(this).find('.product-quantity').data('stock')) || 0;
                const productName = $(this).find('td:first').text();
                
                if (quantity > stock) {
                    stockError = true;
                    productsWithoutStock.push(productName + ' (stock: ' + stock + ', solicitado: ' + quantity + ')');
                }
            });

            if (stockError) {
                Swal.fire({
                    title: 'Stock insuficiente',
                    html: 'Los siguientes productos no tienen stock suficiente:<br><br>' + 
                          productsWithoutStock.join('<br>') +
                          '<br><br>La configuración actual no permite crear presupuestos con productos sin stock.',
                    icon: 'warning',
                    confirmButtonText: 'Entendido'
                });
                return false;
            }
        }

        // Convertir el checkbox is_blocked a valor booleano
        const isBlocked = $('#is_blocked').prop('checked');
        $('<input>').attr({
            type: 'hidden',
            name: 'is_blocked',
            value: isBlocked ? '1' : '0'
        }).appendTo(this);

        // Enviar formulario
        this.submit();
    });

    // Agregar SweetAlert para confirmación de guardado
    $('#saveButton').on('click', function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: '¿Desea guardar el presupuesto?',
            text: "Confirme para continuar",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#addBudgetForm').submit();
            }
        });
    });

    // Handle tab switching
    $('#clientTypeTabs button').on('click', function (e) {
        e.preventDefault();
        
        // Clear both selects when switching tabs
        $('#client_id, #lead_id').val('').trigger('change');
        
        // If switching to client tab
        if ($(this).attr('id') === 'client-tab') {
            $('#lead_id').prop('required', false);
            $('#client_id').prop('required', true);
        }
        // If switching to lead tab
        else if ($(this).attr('id') === 'lead-tab') {
            $('#client_id').prop('required', false);
            $('#lead_id').prop('required', true);
        }
    });

    // Form validation
    $('#addBudgetForm').on('submit', function(e) {
        const activeTab = $('#clientTypeTabs .nav-link.active').attr('id');
        const clientId = $('#client_id').val();
        const leadId = $('#lead_id').val();

        if (activeTab === 'client-tab' && !clientId) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Por favor seleccione un cliente'
            });
        } else if (activeTab === 'lead-tab' && !leadId) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Por favor seleccione un lead'
            });
        }
    });
});
