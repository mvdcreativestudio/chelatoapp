'use strict';

$(function () {
    // Inicializar Select2
    $('.select2').select2();

    // Asegurar que un cliente o lead sea seleccionado, no ambos
    $('#client_id').on('change', function () {
        if ($(this).val()) $('#lead_id').val(null).trigger('change');
    });

    $('#lead_id').on('change', function () {
        if ($(this).val()) $('#client_id').val(null).trigger('change');
    });

    // Obtener productos y items del presupuesto desde el HTML
    const productsData = JSON.parse($('.app-ecommerce').attr('data-products') || '[]');
    const budgetItems = JSON.parse($('.app-ecommerce').attr('data-budget-items') || '[]');

    console.log('Productos:', productsData);
    console.log('Items del presupuesto:', budgetItems);

    // Función unificada para calcular el total de una fila
    function calculateRowTotal($row) {
        const quantity = parseInt($row.find('.product-quantity').val()) || 0;
        const price = parseFloat($row.find('.product-price').data('price')) || 0;
        const discountType = $row.find('.item-discount-type').val();
        const discount = parseFloat($row.find('.item-discount').val()) || 0;
        
        let rowTotal = quantity * price;
        
        if (discountType === 'Percentage' && discount > 0) {
            rowTotal = rowTotal * (1 - (discount / 100));
        } else if (discountType === 'Fixed' && discount > 0) {
            rowTotal = rowTotal - (quantity * discount);
        }
        
        $row.find('.subtotal').text('$' + rowTotal.toFixed(2));
        return rowTotal;
    }

    // Modificar la función calculateTotal
    function calculateTotal() {
        let subtotal = 0;
        
        $('#selectedProductsTable tbody tr').each(function() {
            subtotal += calculateRowTotal($(this));
        });
        
        let total = subtotal;
        const generalDiscountType = $('#discount_type').val();
        const generalDiscount = parseFloat($('#discount').val()) || 0;
        
        if (generalDiscountType === 'Percentage' && generalDiscount > 0) {
            total = subtotal * (1 - (generalDiscount / 100));
        } else if (generalDiscountType === 'Fixed' && generalDiscount > 0) {
            total = subtotal - generalDiscount;
        }
        
        // Actualizar tanto el campo visible como el hidden
        $('#total_display').val(total.toFixed(2));
        $('#total').val(total.toFixed(2));
        
        console.log('Subtotal calculado:', subtotal);
        console.log('Total calculado:', total);
    }

    // Asegurarse de que todos los event listeners estén correctamente configurados
    function attachEventListeners() {
        // Cambios en cantidades y descuentos de productos
        $(document).on('input change', '.product-quantity, .item-discount, .item-discount-type', function() {
            calculateTotal();
        });

        // Cambios en descuento general
        $('#discount_type, #discount').on('input change', function() {
            calculateTotal();
        });

        // Manejo de eliminación de productos
        $(document).on('click', '.remove-product', function() {
            $(this).closest('tr').remove();
            calculateTotal();
        });
    }

    // Asegurarse de que los items se carguen correctamente al inicio
    $(document).ready(function() {
        loadExistingBudgetItems();
        attachEventListeners();
        calculateTotal();
    });

    // Agregar productos al presupuesto
    function addProductToTable(product, budgetItem = null) {
        const buildPrice = parseFloat(product.build_price) || 0;
        const stock = product.stock || 0;
        let rowClass = '';
        const disabled = buildPrice === 0 ? 'disabled' : '';
        const quantity = budgetItem ? budgetItem.quantity : 1;
        const discountType = budgetItem ? budgetItem.discount_type : '';
        const discountValue = budgetItem ? budgetItem.discount_price : '';

        if (buildPrice === 0) {
            rowClass = 'table-danger';
        }

        if (!$('#selectedProductsTable tbody').find(`tr[data-product-id="${product.id}"]`).length) {
            const row = `
                <tr data-product-id="${product.id}" class="${rowClass}">
                    <td>${product.name}</td>
                    <td>
                        <input type="number" 
                               class="form-control form-control-sm product-quantity" 
                               value="${quantity}" 
                               min="1"
                               max="${stock}" 
                               name="items[${product.id}][quantity]"
                               data-product-id="${product.id}" 
                               data-build-price="${buildPrice}" 
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
                                <option value="Percentage" ${discountType === 'Percentage' ? 'selected' : ''}>%</option>
                                <option value="Fixed" ${discountType === 'Fixed' ? 'selected' : ''}>$</option>
                            </select>
                            <input type="number" 
                                class="form-control form-control-sm item-discount w-60" 
                                name="items[${product.id}][discount]"
                                value="${discountValue}"
                                step="0.01" 
                                placeholder="0.00">
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
                if (value > stock) {
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

            // Calcular total después de agregar el producto
            calculateTotal();
        }
    }

    // Cargar los productos del presupuesto en la tabla
    function loadExistingBudgetItems() {
        console.log('Cargando items existentes del presupuesto');
        if (!budgetItems.length) {
            console.warn('No hay productos en el presupuesto.');
            return;
        }

        budgetItems.forEach(item => {
            const product = productsData.find(p => p.id == item.product_id);
            if (product) {
                addProductToTable(product, item);
            } else {
                console.warn(`⚠ Producto con ID ${item.product_id} no encontrado en productos disponibles.`);
            }
        });
        
        // Calcular total inicial
        calculateTotal();
    }
    

    // Manejar el botón "Descartar"
    $('#discardButton').on('click', function () {
        window.location.href = $(this).data('url');
    });

    // Cargar productos al iniciar
    $(document).ready(function () {
        loadExistingBudgetItems();
        calculateTotal();
        $('.select2').select2();
    });

    // Manejar la eliminación de productos
    $('#selectedProductsTable').on('click', '.remove-product', function () {
        const $row = $(this).closest('tr');
        const productId = $row.data('product-id');

        $row.remove();
        const $select = $('#products');
        const newValues = ($select.val() || []).filter(value => value != productId);
        $select.val(newValues).trigger('change');

        calculateTotal();
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
                $('#editBudgetForm').submit();
            }
        });
    });

    $('#editBudgetForm').on('submit', function (e) {
        e.preventDefault();

        // Verificar cliente o lead
        if (!$('#client_id').val() && !$('#lead_id').val()) {
            Swal.fire({
                title: 'Error',
                text: 'Debe seleccionar un cliente o un lead',
                icon: 'error'
            });
            return false;
        }

        // Validaciones básicas
        const selectedProducts = $('#products').val();
        if (!selectedProducts || selectedProducts.length === 0) {
            Swal.fire({
                title: 'Error',
                text: 'Debe seleccionar al menos un producto',
                icon: 'error'
            });
            return;
        }

        // Crear FormData
        const formData = new FormData(this);

        // Obtener productos y sus detalles
        const items = {};
        $('#selectedProductsTable tbody tr').each(function() {
            const productId = $(this).data('product-id');
            items[productId] = {
                quantity: $(this).find('.product-quantity').val(),
                discount_type: $(this).find('.item-discount-type').val(),
                discount: $(this).find('.item-discount').val() || '0'
            };
        });

        // Remover campos anteriores si existen
        formData.delete('items');
        formData.delete('products[]');
        
        // Agregar items y productos
        formData.append('items', JSON.stringify(items));
        selectedProducts.forEach(productId => {
            formData.append('products[]', productId);
        });

        // Asegurar que el total se envíe correctamente
        const total = parseFloat($('#total').val());
        formData.set('total', total.toString());

        // Debug log
        const formDataObj = {};
        for (let [key, value] of formData.entries()) {
            formDataObj[key] = value;
        }
        console.log('Enviando datos:', formDataObj);

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Éxito',
                        text: response.message || 'Presupuesto actualizado correctamente',
                        icon: 'success'
                    }).then(() => {
                        window.location.href = response.redirect || '/admin/budgets';
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: response.message || 'Error al actualizar el presupuesto',
                        icon: 'error'
                    });
                }
            },
            error: function(xhr) {
                console.error('Error en la petición:', xhr);
                let errorMessage = 'Error al actualizar el presupuesto';
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                        errorMessage = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    } else if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                }

                Swal.fire({
                    title: 'Error',
                    text: errorMessage,
                    icon: 'error'
                });
            }
        });
    });

    // Modificar el evento change del select de productos
    $('#products').on('change', function () {
        const selectedProductIds = $(this).val() || [];
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
            const budgetItem = budgetItems.find(item => item.product_id == productId);
            if (product && !$tbody.find(`tr[data-product-id="${productId}"]`).length) {
                addProductToTable(product, budgetItem);
            }
        });

        calculateTotal();
    });
});
