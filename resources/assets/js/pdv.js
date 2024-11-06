'use strict';

    

$(document).ready(function () {
    const cashRegisterId = window.cashRegisterId;
    console.log('cashRegisterId:', cashRegisterId);
    const baseUrl = window.baseUrl;
    const url = `${baseUrl}products/${cashRegisterId}`;
    let currencySymbol = window.currencySymbol;
    let products = [];
    let cart = [];
    let isListView = true;
    let sessionStoreId = null;
    let categories = [];
    let flavors = [];
    let productCategory = [];
    let client = [];
    let priceListProducts = []; // Almacenará los productos de la lista de precios
    $('#client-info').hide();

    $('#manual_price_list_id').on('change', function () {
        const selectedPriceListId = $(this).val();
        loadManualPriceListProducts(selectedPriceListId); // Llamar a la función para cargar los productos de la lista seleccionada
    });

    loadClientFromSession(); // Ejecuta esta función al cargar
    
    if (client && client.id) {
        $('#seleccionar-cliente-btn').hide(); // Ocultar el botón si el cliente existe
    }
    

    // Inicializar Select2 en elementos con clase .select2
    $(function () {
        var select2 = $('.select2');
        if (select2.length) {
            select2.each(function () {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    dropdownParent: $this.parent(),
                    placeholder: $this.data('placeholder')
                });
            });
        }
    });

    // Configuración Toastr

    toastr.options = {
        closeButton: true,               // Mostrar botón de cerrar
        progressBar: true,               // Mostrar barra de progreso
        newestOnTop: true,               // Mostrar el toast más nuevo en la parte superior
        positionClass: 'toast-top-right', // Posición en la esquina superior derecha
        showEasing: 'swing',             // Efecto de entrada
        hideEasing: 'linear',            // Efecto de salida
        showMethod: 'fadeIn',            // Método de entrada (desvanecimiento)
        hideMethod: 'fadeOut',           // Método de salida (desvanecimiento)
        showDuration: 300,               // Duración de la animación de entrada
        hideDuration: 1000,              // Duración de la animación de salida
        timeOut: 2000,                   // Tiempo que permanece visible el toast
        extendedTimeOut: 1000            // Tiempo adicional antes de que desaparezca al hacer hover
    };

    // Cargar el cliente de la sesión
    function loadClientFromSession() {
        console.log('Ejecutando loadClientFromSession...'); // Marca el inicio de la ejecución de la función
        
        $.ajax({
            url: `client-session`,
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                console.log('Respuesta recibida de client-session:', response); // Registro de la respuesta completa

                client = response.client;

                if (client && client.id) {
                    console.log('Cliente encontrado en la sesión:', client); // Cliente encontrado en la sesión

                    showClientInfo(client);
                    $('#client-selection-container').hide();

                    // Si el cliente tiene una lista de precios, cargar los precios desde la lista
                    if (client.price_list_id) {
                        console.log('Cargando lista de precios para client.price_list_id:', client.price_list_id);
                        loadClientPriceList(client.price_list_id);
                    } else {
                        console.log('Cliente sin lista de precios asignada');
                        $('#client-price-list').text('Sin lista de precios');
                    }

                } else {
                    console.log('No se encontró ningún cliente en la sesión');
                }
            },
            error: function (xhr) {
                console.error('Error al cargar el cliente desde la sesión:', xhr.responseText);
                mostrarError('Error al cargar el cliente desde la sesión: ' + xhr.responseText);
            }
        });
    }

    // Cargar el carrito desde el servidor
    function loadCart() {
        $.ajax({
            url: `cart`,
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                // Asegúrate de que 'cart' es un array
                cart = Array.isArray(response.cart) ? response.cart : [];
                updateCart();
            },
            error: function (xhr, status, error) {
                console.error('Error al obtener el carrito:', error);
            }
        });
    }


    // Guardar el carrito en el servidor
    function saveCart() {
        $.ajax({
            url: `cart`,
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ cart: cart }),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        });
    }

    // Cargar las categorías y sus relaciones con los productos desde el backend
    function cargarCategorias() {
        $.ajax({
            url: `categories`,
            type: 'GET',
            success: function (response) {
                if (response && response.categories) {
                    categories = response.categories;
                    cargarCategoriaProducto();
                } else {
                    alert('No se encontraron categorías.');
                }
            },
            error: function (xhr, status, error) {
                alert('Error al cargar las categorías: ' + xhr.responseText);
            }
        });
    }

    // Cargar las categorías de los productos desde el backend
    function cargarCategoriaProducto() {
        $.ajax({
            url: `product-categories`,
            type: 'GET',
            success: function (response) {
                if (response) {
                    productCategory = response;
                    actualizarCategoriasEnVista();
                } else {
                    alert('No se encontraron categorías.');
                }
            },
            error: function (xhr, status, error) {
                alert('Error al cargar las categorías: ' + xhr.responseText);
            }
        });
    }

    // Evento de entrada para el campo de búsqueda de categorías
    $('#category-search-input').on('input', function () {
        const query = $(this).val();
        searchCategories(query);
    });

    // Función para actualizar el menú desplegable de categorías en la vista
    function actualizarCategoriasEnVista(categoriesToDisplay = productCategory) {
        let categoryHtml = '';
        categoriesToDisplay.forEach(category => {
            categoryHtml += `
                <div class="form-check form-check-primary mt-1">
                    <input class="form-check-input" type="checkbox" value="${category.id}" id="category-${category.category_id}" checked>
                    <label class="form-check-label" for="category-${category.category_id}">${category.name}</label>
                </div>
            `;
        });
        $('#category-container').html(categoryHtml);
    }

    // Escuchar cambios en los checkboxes de las categorías
    $(document).on('change', '.form-check-input', function () {
        filterProductsByCategory();
    });

    // Función para filtrar productos por categorías seleccionadas
    function filterProductsByCategory() {
        const selectedCategories = [];
        $('.form-check-input:checked').each(function () {
            selectedCategories.push(parseInt($(this).val()));
        });


        let filteredProducts = [];

        products.forEach(function (product) {
            const productCategories = categories.filter(category => category.product_id === product.id);

            const hasCategory = productCategories.some(category =>
                selectedCategories.includes(category.category_id)
            );

            if (hasCategory) {
                filteredProducts.push(product);
            }
        });

        if (isListView) {
            displayProductsList(filteredProducts);
        } else {
            displayProducts(filteredProducts);
        }
    }

    // Función para cargar productos
    function loadProducts() {
        $.ajax({
            url: `products/${cashRegisterId}`,
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response && response.products) {
                    products = response.products.map(product => {
                        // Asegura que original_price se establezca solo si aún no existe
                        if (typeof product.original_price === 'undefined') {
                            product.original_price = product.price || product.old_price;
                        }
                        return product;
                    });
    
                    if (isListView) {
                        displayProductsList(products); // Mostrar la vista de lista por defecto
                    } else {
                        displayProducts(products);
                    }
                } else {
                    alert('No se encontraron productos.');
                }
            },
            error: function (xhr, status, error) {
                console.error('Error al obtener los productos:', error);
            }
        });
    }
  
    // Cargar variaciones desde el backend
    function cargarVariaciones() {
        $.ajax({
            url: `flavors`,
            type: 'GET',
            success: function (response) {
                if (response && response.flavors) {
                    flavors = response.flavors;
                    // Llenar el select con los variaciones
                    $('#flavorsSelect').empty();
                    flavors.forEach(flavor => {
                        $('#flavorsSelect').append(new Option(flavor.name, flavor.id));
                    });

                    // Inicializar Select2 con formato de tags
                    $('#flavorsSelect').select2({
                        tags: true,
                        placeholder: 'Selecciona variaciones',
                        dropdownParent: $('#flavorModal')
                    });
                } else {
                    alert('No se encontraron variaciones.');
                }
            },
            error: function (xhr, status, error) {
                alert('Error al cargar los variaciones: ' + xhr.responseText);
            }
        });
    }


    // Función para mostrar productos en formato de tarjetas
    function displayProducts(productsToDisplay) {
        // Ordenar productos por disponibilidad: los productos agotados al final
        productsToDisplay.sort((a, b) => {
            if (a.stock === null) return -1;
            if (b.stock === null) return 1;
            return (a.stock > 0 ? -1 : 1) - (b.stock > 0 ? -1 : 1);
        });

        if (productsToDisplay.length === 0) {
            $('#products-container').html('<p class="text-center mt-3">No hay productos disponibles</p>');
            return;
        }

        let productsHtml = '';
        productsToDisplay.forEach(product => {
            const priceToDisplay = product.price ? product.price : product.old_price;
            const inactiveLabel = product.status == 2 ? `<span class="badge bg-warning text-dark position-absolute top-0 start-0 m-1">Inactivo</span>` : '';
            const oldPriceHtml = product.price && product.old_price ? `<span class="text-muted" style="font-size: 0.8em;"><del>${currencySymbol}${product.old_price}</del></span>` : '';

            // Añadir indicador de stock
            const stockIndicator = getStockIndicator(product);

            productsHtml += `
              <!-- Tarjeta de producto -->
              <div class="col-12 col-sm-6 col-xxl-4 mb-3 card-product-pos" data-category="${product.category}">
                  <div class="card h-100 position-relative product-card-hover">
                      ${inactiveLabel}
                      <img src="${baseUrl}${product.image}" class="card-img-top" alt="${product.name}" style="height: 200px; object-fit: cover;">
                      <div class="card-body d-flex flex-column justify-content-between">
                          <div>
                              <h5 class="card-title">${product.name}</h5>
                              <p class="card-text">
                                  ${oldPriceHtml}
                                  <span class="fw-bold">${currencySymbol}${priceToDisplay}</span>
                              </p>
                              ${stockIndicator}
                          </div>
                          <div class="d-flex flex-column align-items-stretch mt-3">
                            <div class="input-group input-group-sm d-flex">
                              <button class="btn btn-outline-secondary decrement-quantity col-2" type="button" data-id="${product.id}">-</button>
                              <input type="number" class="form-control quantity-input selector-cantidad-pdv col-2" min="1" value="1" data-id="${product.id}">
                              <button class="btn btn-outline-secondary increment-quantity col-2" type="button" data-id="${product.id}">+</button>
                            </div>
                            <button class="btn btn-primary btn-sm add-to-cart mb-2 mt-2" data-id="${product.id}" data-type="${product.type}" ${(product.stock !== null && product.stock <= 0) || product.status == 0 ? 'disabled' : ''}>Agregar al carrito</button>
                          </div>
                      </div>
                  </div>
              </div>
          `;
        });
        $('#products-container').html(productsHtml);
    }




    // Función para mostrar productos en formato de lista
    function displayProductsList(productsToDisplay) {
        // Ordenar productos por disponibilidad: los productos agotados al final
        productsToDisplay.sort((a, b) => (a.stock > 0 ? -1 : 1) - (b.stock > 0 ? -1 : 1));
        if (productsToDisplay.length === 0) {
            $('#products-container').html('<p class="text-center mt-3">No hay productos disponibles</p>');
            return;
        }
        let productsHtml = '<ul class="list-group w-100 p-0">';
        productsToDisplay.forEach(product => {
            const priceToDisplay = product.price ? product.price.toLocaleString('es-ES') : product.old_price.toLocaleString('es-ES');
            const oldPriceFormatted = product.old_price ? product.old_price.toLocaleString('es-ES') : '';
            const inactiveText = product.status == 0 ? '<span class="badge bg-danger text-white ms-2">Inactivo</span>' : '';
            const oldPriceHtml = product.price && product.old_price ? `<small class="text-muted"><del>${currencySymbol}${oldPriceFormatted}</del></small>` : '';

            // Añadir indicador de stock
            const stockIndicator = getStockIndicator(product);

            productsHtml += `
              <li class="list-group-item d-flex justify-content-between align-items-center py-3 border-bottom">
                  <div class="d-flex align-items-center">
                      <img src="${baseUrl}${product.image}" class="me-3" alt="${product.name}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                      <div>
                          <h6 class="mb-0 fw-bold">${product.name}</h6>
                          <div class="d-flex align-items-center mt-1">
                              ${oldPriceHtml ? `<small class="text-muted me-2"><del>${currencySymbol}${oldPriceFormatted}</del></small>` : ''}
                              <span class="text-primary fw-semibold">${currencySymbol}${priceToDisplay}</span>
                              ${inactiveText}
                          </div>
                          ${stockIndicator}
                      </div>
                  </div>
                  <div class="d-flex align-items-center">
                      <div class="input-group me-2">
                          <button class="btn btn-outline-secondary decrement-quantity" type="button" data-id="${product.id}">-</button>
                          <input type="number" class="form-control quantity-input selector-cantidad-pdv" min="1" value="1" data-id="${product.id}">
                          <button class="btn btn-outline-secondary increment-quantity" type="button" data-id="${product.id}">+</button>
                      </div>
                      <button class="btn btn-primary btn-sm add-to-cart" data-id="${product.id}" data-type="${product.type}" ${product.stock !== null && product.stock <= 0 || product.status == 0 ? 'disabled' : ''}>
                          <i class="bx bx-cart-add"></i>
                      </button>
                  </div>
              </li>
          `;
        });
        productsHtml += '</ul>';
        $('#products-container').html(productsHtml);
    }


    // Función para agregar un producto al carrito
    function addToCart(productId, productType) {
        const product = products.find(p => p.id === productId);

        // Determinar el precio a usar
        const priceToUse = product.price ? product.price : product.old_price;

        // Verificar si el producto tiene stock suficiente antes de agregar
        if (product.stock !== null && product.stock <= 0) {
            mostrarError('No hay suficiente stock de este producto.');
            return;
        }

        // Obtener la cantidad deseada del input
        const quantityInput = $(`.quantity-input[data-id="${productId}"]`);
        const quantity = parseInt(quantityInput.val());

        if (productType === 'configurable') {
            // Mostrar el modal para seleccionar variaciones
            $('#flavorModal').modal('show');

            // Guardar el producto temporalmente hasta que se seleccionen los variaciones
            $('#saveFlavors').off('click').on('click', function () {
                const selectedFlavors = $('#flavorsSelect').val();
                if (selectedFlavors.length === 0) {
                    alert('Debe seleccionar al menos un sabor.');
                    return;
                }

                var category = categories.find(category => category.product_id == product.id);
                var category_id = category ? category.category_id : null;
                // Agregar el producto como nuevo ítem en el carrito
                cart.push({
                    id: product.id,
                    name: product.name,
                    image: product.image,
                    price: priceToUse,
                    original_price: product.original_price, // Almacena el precio original
                    original_price: priceToUse, // Guardar el precio original
                    flavors: selectedFlavors,
                    quantity: quantity, // Usar la cantidad deseada
                    category_id: category_id,
                });

                updateCart();
                $('#flavorModal').modal('hide');
                toastr.success(`<strong>${product.name}</strong> agregado correctamente`);
            });
        } else {
            const cartItem = cart.find(item => item.id === productId && item.flavors.length === 0);
            var category = categories.find(category => category.product_id == product.id);
            var category_id = category ? category.category_id : null;

            if (cartItem) {
                // Verificar si hay stock suficiente para incrementar la cantidad
                if (product.stock !== null && cartItem.quantity + quantity > product.stock) {
                    mostrarError('No hay suficiente stock para agregar más unidades de este producto.');
                    return;
                }
                cartItem.quantity += quantity; // Incrementar por la cantidad deseada
            } else {
                // Verificar si hay stock suficiente para agregar el producto por primera vez
                if (product.stock !== null && product.stock < quantity) {
                    mostrarError('No hay suficiente stock de este producto.');
                    return;
                }
                cart.push({
                    id: product.id,
                    name: product.name,
                    image: product.image,
                    price: priceToUse,
                    original_price: priceToUse, // Guardar el precio original
                    flavors: [],
                    quantity: quantity, // Usar la cantidad deseada
                    category_id: category_id,
                    isComposite: product.is_composite ? 1 : 0
                });
            }

            // Restablecer el contador de cantidad a 1 después de agregar al carrito
            $(`.quantity-input[data-id="${productId}"]`).val(1);

            updateCart();
            toastr.success(`<strong>${product.name}</strong> agregado correctamente`);
        }
    }

    function addCompositeProductToCart(productId) {
        // Obtener los productos internos que componen el paquete
        $.ajax({
            url: `${baseUrl}api/composite-products/${productId}`,
            type: 'GET',
            success: function (response) {
                // Agregar cada producto interno al carrito
                response.items.forEach(item => {
                    const cartItem = {
                        id: item.id,
                        name: item.name,
                        price: item.price,
                        quantity: item.quantity,
                        is_composite: product.is_composite ? 1 : 0 // Convertir true/false a 1/0
                    };
                    cart.push(cartItem);
                });
                updateCart();
            },
            error: function (xhr) {
                mostrarError('Error al agregar producto compuesto: ' + xhr.responseText);
            }
        });
    }


    // Función para mostrar errores
    function mostrarError(mensaje) {
        $('#errorContainer').text(mensaje).removeClass('d-none'); // Mostrar mensaje de error
        setTimeout(() => {
            $('#errorContainer').addClass('d-none'); // Ocultar el mensaje después de 5 segundos
        }, 5000);
    }

    // Función para actualizar el carrito en el DOM
    function updateCart() {
        let cartHtml = '';
        let subtotal = 0;
        let totalItems = 0;  // Contador de productos

        cartHtml = `
        <div class="row gy-3 overflow-auto" style="max-height: 400px;">
      `;
      
      cart.forEach(item => {
          const itemTotal = item.price * item.quantity;
          subtotal += itemTotal;
          totalItems += item.quantity;
      
          cartHtml += `
            <div class="col-12">
              <div class="product-cart-card">
                <div class="col-4 d-flex align-items-center">
                  <img src="${baseUrl + item.image}" class="img-fluid product-cart-card-img" alt="${item.name}">
                </div>
                <div class="col-8">
                  <div class="product-cart-card-body">
                    <div class="d-flex justify-content-between">
                      <h5 class="product-cart-title">${item.name}</h5>
                      <div class="product-cart-actions">
                        <span class="product-cart-remove" data-id="${item.id}"><i class="bx bx-trash"></i></span>
                      </div>
                    </div>
                    <p class="product-cart-price">${currencySymbol}${item.price.toLocaleString('es-ES')}</p>
                    <p class="product-cart-quantity">Cantidad: ${item.quantity}</p>
                    <p><strong>Total: ${currencySymbol}${itemTotal.toLocaleString('es-ES')}</strong></p>
                  </div>
                </div>
              </div>
            </div>
          `;
      });
      
      cartHtml += `</div>`; // Cerrar el contenedor de desplazamiento
      

        // Actualiza el contenido del carrito
        $('#cart-items').html(cartHtml);
        $('.subtotal').text(`${currencySymbol}${subtotal.toLocaleString('es-ES', { minimumFractionDigits: 0 })}`);
        $('.total').text(`${currencySymbol}${subtotal.toLocaleString('es-ES', { minimumFractionDigits: 0 })}`);

        // Actualiza el contador de productos en el botón "Ver Carrito"
        $('#cart-count').text(totalItems);

        // Habilitar o deshabilitar el botón "Finalizar Venta" según si hay productos en el carrito
        if (cart.length === 0) {
            $('#finalizarVentaBtn').addClass('disabled').attr('aria-disabled', 'true');
        } else {
            $('#finalizarVentaBtn').removeClass('disabled').attr('aria-disabled', 'false');
        }

        // Guardar el carrito en el servidor
        saveCart();
    }

    // Manejar el clic en el botón "Agregar al carrito"
    $(document).on('click', '.add-to-cart', function () {
        const productId = $(this).data('id');
        const isComposite = $(this).data('composite'); // Cambiar "type" por "composite"

        if (isComposite) {
            // Lógica para productos compuestos
            addCompositeProductToCart(productId);
        } else {
            addToCart(productId);
        }
    });



    // Manejar el clic en el botón "Eliminar del carrito"
    $(document).on('click', '.product-cart-remove', function () {
        const productId = $(this).data('id');
        cart = cart.filter(item => item.id !== productId);
        updateCart();
        toastr.error(`Producto eliminado correctamente`);
    });



    // Manejar el cambio de vista de productos (tarjeta/lista)
    $('#toggle-view-btn').on('click', function () {
        isListView = !isListView;
        $(this).find('i').toggleClass('bx-list-ul bx-grid-alt');
        if (isListView) {
            displayProductsList(products);
        } else {
            displayProducts(products);
        }
    });

    // Filtrar productos por búsqueda
    function searchProducts(query) {
        const filteredProducts = products.filter(product => {
            const productName = product.name ? product.name.toLowerCase() : '';
            const productSku = product.sku ? product.sku.toLowerCase() : '';
            const productBarCode = product.bar_code ? product.bar_code.toLowerCase() : '';
            return productName.includes(query.toLowerCase()) || productSku.includes(query.toLowerCase()) || productBarCode.includes(query.toLowerCase());
        });

        // Si el código de barras coincide exactamente, agregar al carrito automáticamente
        const exactBarCodeMatch = products.find(product => product.bar_code && product.bar_code.toLowerCase() === query.toLowerCase());
        if (exactBarCodeMatch) {
            addToCart(exactBarCodeMatch);
            return;
        }

        if (isListView) {
            displayProductsList(filteredProducts);
        } else {
            displayProducts(filteredProducts);
        }
    }

    // Manejar cambios en la barra de búsqueda
    $('#html5-search-input').on('input', function () {
        const query = $(this).val();
        searchProducts(query);
    });

    // Mostrar el modal de cerrar caja al hacer clic en el botón correspondiente
    $('#btn-cerrar-caja').click(function () {
        var cashRegisterId = $(this).data('id');
        $('#cash_register_id_close').val(cashRegisterId);
        $('#cerrarCajaModal').modal('show');
    });

    // Enviar la solicitud para cerrar la caja registradora
    $('#submit-cerrar-caja').click(function () {
        var csrfToken = $('meta[name="csrf-token"]').attr('content');

        $.ajax({
            url: 'close/' + cashRegisterId,
            type: 'POST',
            data: {
                _token: csrfToken
            },
            success: function (response) {
                $('#cerrarCajaModal').modal('hide');
                location.reload(); // Recargar la página para reflejar los cambios
            },
            error: function (xhr, status, error) {
                alert('Error al cerrar la caja registradora: ' + xhr.responseText);
            }
        });
    });

    // Manejar eventos de clic para incrementar y decrementar la cantidad
    $(document).on('click', '.increment-quantity', function () {
        const productId = $(this).data('id');
        const input = $(`.quantity-input[data-id="${productId}"]`);
        let currentValue = parseInt(input.val());
        input.val(currentValue + 1);
    });

    $(document).on('click', '.decrement-quantity', function () {
        const productId = $(this).data('id');
        const input = $(`.quantity-input[data-id="${productId}"]`);
        let currentValue = parseInt(input.val());
        if (currentValue > 1) {
            input.val(currentValue - 1);
        }
    });

    // Función para generar el indicador de stock
    function getStockIndicator(product) {
        let stockClass, stockText;
        const stock = product.stock;
        const safetyMargin = product.safety_margin || 5;

        if (stock === null) {
            stockClass = 'bg-success';
            stockText = 'En stock';
        } else if (stock <= 0) {
            stockClass = 'bg-danger';
            stockText = 'Sin stock';
        } else if (stock <= safetyMargin) {
            stockClass = 'bg-warning';
            stockText = 'Stock bajo';
        } else {
            stockClass = 'bg-success';
            stockText = 'En stock';
        }
        return `<div class="mt-2">
                  <span class="badge ${stockClass}">${stockText}</span>
                  ${stock !== null ? `<small class="text-muted ms-1">(${stock} disponibles)</small>` : ''}
              </div>`;
    }


    // Cargar la lista de precios del cliente si está asignada
    function loadClientPriceList(priceListId) {
        // Solo carga la lista de precios del cliente si no hay una lista de precios manual seleccionada
        if (!$('#manual_price_list_id').val()) {
            return $.ajax({
                url: `${baseUrl}admin/price-list/${priceListId}/products`,
                type: 'GET',
                success: function (response) {
                    priceListProducts = response.products;
                    updateProductPrices(); // Llamamos a la función para actualizar los precios en la vista
                },
                error: function (xhr) {
                    console.error('Error al cargar los precios de la lista:', xhr.responseText);
                }
            });
        }
    }
    



    // Actualizar los precios de los productos en la vista si están en la lista de precios del cliente
    function updateProductPrices() {
        products = products.map(product => {
            // Si hay una lista de precios seleccionada manualmente, se usa esa.
            const productInPriceList = priceListProducts.find(p => p.id === product.id);
            product.price = productInPriceList ? productInPriceList.price : product.original_price;
            return product;
        });
        displayProductsList(products); // Renderizar con los precios actualizados
    }
    

    function loadStoreIdFromSession() {
        $.ajax({
            url: `storeid-session`,
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                sessionStoreId = response.id;
            },
            error: function (xhr) {
                mostrarError('Error al cargar el cliente desde la sesión: ' + xhr.responseText);
            }
        });
    }



    function showClientInfo(client) {
        const clientType = client.type === 'company' ? 'Empresa' : 'Persona';
        const clientDocLabel = client.type === 'company' ? 'RUT' : 'CI';
        const clientDoc = client.type === 'company' ? client.rut : client.ci;
        const fullName = `${client.name || '-'} ${client.lastname || ''}`.trim();
        const clientPriceList = client.price_list_name;

        $('#client-id').text(client.id || '-');
        $('#client-name').text(fullName);
        $('#client-type').text(clientType);
        $('#client-doc-label').text(clientDocLabel);
        $('#client-doc').text(clientDoc || 'No disponible');
        $('#client-price-list').text(clientPriceList);

        if (client.type === 'company') {
            $('#client-company').html(`<strong class="text-muted">Razón Social:</strong> <span class="text-body fw-bold">${client.company_name || '-'}</span>`);
            $('#client-company').show();
        } else {
            $('#client-company').hide();
        }

        $('#client-info').show();
        $('#client-selection-container').hide();
    }

    function loadClients() {
        $.ajax({
            url: 'clients/json',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                const clients = response.clients;
                const clientCount = response.count;
                if (clientCount > 0) {
                    $('#search-client-container').show();
                } else {
                    $('#search-client-container').hide();
                }
                displayClients(clients);
            },
            error: function (xhr) {
                mostrarError('Error al obtener los clientes: ' + xhr.responseText);
            }
        });
    }

    function displayClients(clients) {
        const clientList = $('#client-list');
        clientList.empty(); // Limpiar la lista existente

        clients.forEach(client => {
            const clientType = client.type === 'company' ? 'Empresa' : 'Persona';
            const clientDoc = client.type === 'company' ? client.rut : client.ci;
            const clientDocLabel = client.type === 'company' ? 'RUT' : 'CI';

            // Si es una empresa, mostrar company_name, si es una persona, mostrar name y lastname
            const displayName = client.type === 'company'
                ? client.company_name || '-'
                : `${client.name || '-'} ${client.lastname || '-'}`;

            const razonSocial =
                client.type === 'company' ? `<p class="client-info"><strong>Razón Social:</strong> ${client.company_name || '-'}</p>` : '';

            const clientCard = `
            <div class="client-card card mb-2" style="border: none; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);">
                <div class="card-body d-flex justify-content-between align-items-center p-2">
                    <div class="client-details">
                        <h6 class="card-title mb-1">${displayName}</h6>
                        ${razonSocial}
                        <p class="client-info"><strong>Tipo de Cliente:</strong> ${clientType}</p>
                        <p class="client-info"><strong>${clientDocLabel}:</strong> ${clientDoc ? clientDoc : 'No disponible'}</p>
                    </div>
                    <button class="btn btn-primary btn-sm btn-select-client" data-client='${JSON.stringify(client)}'>Seleccionar</button>
                </div>
            </div>
        `;

            clientList.append(clientCard);
        });

        // Selección de cliente y carga de lista de precios (si tiene)
        $('.btn-select-client').on('click', function () {
            const selectedClient = $(this).data('client');
            client = selectedClient;
            saveClientToSession(client).then(() => {
                showClientInfo(client);
                console.log('Cliente guardado en la sesión:', client);
            
                $('#seleccionar-cliente-btn').addClass('d-none'); // Oculta el botón
            
                if (client.price_list_id) {
                    loadClientPriceList(client.price_list_id).then(updateCartPrices);
                } else {
                    updateProductPrices();
                    updateCartPrices();
                }
            
                // Cerrar el offcanvas
                let offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('offcanvasEnd'));
                offcanvas.hide();
            });
            

            if (client.price_list_id) {
                loadClientPriceList(client.price_list_id).then(updateCartPrices);
            } else {
                updateProductPrices();
                updateCartPrices();
            }
        });
    }


    function saveClientToSession(client) {
        return $.ajax({
            url: 'client-session',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                client: client
            }
        })
            .fail(function (xhr) {
                mostrarError('Error al guardar el cliente en la sesión: ' + xhr.responseText);
            });
    }

    $('#offcanvasEnd').on('show.bs.offcanvas', function () {
        loadClients();
    });

    $('#deselect-client').on('click', function () {
        deselectClient();
    });

    // Mostrar/Ocultar campos según el tipo de cliente seleccionado
  document.getElementById('tipoCliente').addEventListener('change', function () {
    let tipo = this.value;
    if (tipo === 'individual') {
      document.getElementById('ciField').style.display = 'block';
      document.getElementById('rutField').style.display = 'none';
      document.getElementById('razonSocialField').style.display = 'none';

      // Mostrar los asteriscos en nombre y apellido
      document.querySelector('label[for="nombreCliente"] .text-danger').style.display = 'inline';
      document.querySelector('label[for="apellidoCliente"] .text-danger').style.display = 'inline';

    } else if (tipo === 'company') {
      document.getElementById('ciField').style.display = 'none';
      document.getElementById('rutField').style.display = 'block';
      document.getElementById('razonSocialField').style.display = 'block';

      // Ocultar los asteriscos en nombre y apellido
      document.querySelector('label[for="nombreCliente"] .text-danger').style.display = 'none';
      document.querySelector('label[for="apellidoCliente"] .text-danger').style.display = 'none';
    }
  });

  // Guardar cliente con validaciones
  document.getElementById('guardarCliente').addEventListener('click', function () {
    const nombre = document.getElementById('nombreCliente');
    const apellido = document.getElementById('apellidoCliente');
    const tipo = document.getElementById('tipoCliente');
    const email = document.getElementById('emailCliente');
    const ci = document.getElementById('ciCliente');
    const rut = document.getElementById('rutCliente');
    const direccion = document.getElementById('direccionCliente');
    const razonSocial = document.getElementById('razonSocialCliente');
    const priceList = document.getElementById('price_list_id');

    let hasError = false;
    clearErrors();

    // Validación básica...
    if (tipo.value.trim() === '') {
      showError(tipo, 'Este campo es obligatorio');
      hasError = true;
    }

    // Si el tipo de cliente es "individual", validar nombre y apellido
    if (tipo.value === 'individual') {
      if (nombre.value.trim() === '') {
          showError(nombre, 'El nombre es obligatorio para clientes individuales');
          hasError = true;
      }

      if (apellido.value.trim() === '') {
          showError(apellido, 'El apellido es obligatorio para clientes individuales');
          hasError = true;
      }

      if (ci.value.trim() === '') {
          showError(ci, 'El documento de identidad es obligatorio para clientes individuales');
          hasError = true;
      }
    }

    // Validar que el campo "email" no esté vacío
    if (email.value.trim() === '') {
      showError(email, 'Este campo es obligatorio');
      hasError = true;
    }

    // Validar que "dirección" no esté vacía (si es aplicable a ambos tipos de cliente)
    if (direccion.value.trim() === '') {
      showError(direccion, 'Este campo es obligatorio');
      hasError = true;
    }

    if (tipo.value === 'company') {
        if (rut.value.trim() === '') {
            showError(rut, 'Este campo es obligatorio');
            hasError = true;
        }

        if (razonSocial.value.trim() === '') {
            showError(razonSocial, 'Este campo es obligatorio');
            hasError = true;
        }
    }

    // Si hubo errores, detener la ejecución.
    if (hasError) {
        return;
    }

    // Crear el objeto con los datos a enviar
    let data = {
        store_id: sessionStoreId,
        name: nombre.value.trim(),
        lastname: apellido.value.trim(),
        type: tipo.value,
        email: email.value.trim(),
        address: direccion.value.trim(),
        price_list_id: priceList.value
    };

    if (tipo.value === 'individual') {
        data.ci = ci.value.trim();
    } else if (tipo.value === 'company') {
        data.rut = rut.value.trim();
        data.company_name = razonSocial.value.trim();
    }

    // Realizar la petición para crear el cliente
    fetch('client', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
                let offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('crearClienteOffcanvas'));
                offcanvas.hide();

                // Limpiar el formulario de creación de cliente
                document.getElementById('formCrearCliente').reset();

    })
    .catch(error => {
        mostrarError('Error al guardar el cliente: ' + error);
    });
  });


  // Función para mostrar el mensaje de error
  function showError(input, message) {
    const errorElement = document.createElement('small');
    errorElement.className = 'text-danger';
    errorElement.innerText = message;
    input.parentElement.appendChild(errorElement);
  }

  // Función para limpiar los mensajes de error anteriores
  function clearErrors() {
    const errorMessages = document.querySelectorAll('.text-danger');
    errorMessages.forEach(function (error) {
      error.remove();
    });
  }


    // Función para deseleccionar al cliente
    function deselectClient() {
        client = [];  // Limpiar los datos del cliente
        priceListProducts = []; // Vaciar lista de precios para usar precios originales
    
        saveClientToSession(client)
            .done(function () {
                // Restaurar precios de productos y carrito a los originales
                loadNormalPrices();
                updateCartPrices();
    
                // Actualizar la UI para deseleccionar al cliente
                $('#client-id').text('');
                $('#client-name').text('');
                $('#client-type').text('');
                $('#client-doc').text('');
                $('#client-company').hide();
                $('#client-info').hide();
                $('#client-selection-container').show();
                $('#seleccionar-cliente-btn').removeClass('d-none'); // Remueve la clase 'd-none' para mostrar el botón
            })
            .fail(function (xhr) {
                mostrarError('Error al guardar el cliente en la sesión: ' + xhr.responseText);
            });
    }


    //Función para cargar los precios originales luego de desseleccionar un cliente
    function loadNormalPrices() {
        products = products.map(product => {
            product.price = product.original_price;
            return product;
        });
        displayProductsList(products); // Mostrar productos con precios originales
    }

    // Función para actualizar los precios de los productos en el carrito según la lista de precios del cliente seleccionado
    function updateCartPrices() {
        cart = cart.map(item => {
            // Usar el precio de lista si existe en `priceListProducts`, o el precio original
            const productInPriceList = priceListProducts.find(p => p.id === item.id);
            item.price = productInPriceList ? productInPriceList.price : item.original_price;
            return item;
        });
        updateCart();
    }

    function loadManualPriceListProducts(priceListId) {
        if (priceListId === "0") { 
            loadNormalPrices(); // Restaurar precios originales en la vista
    
            // Restaurar precios originales en el carrito
            cart = cart.map(item => {
                const originalProduct = products.find(p => p.id === item.id);
                item.price = originalProduct ? originalProduct.original_price : item.price;
                return item;
            });
    
            updateCart(); // Actualizar precios en el carrito
            return;
        } else if (priceListId === "") { // Si selecciona "Lista de precios manual"
            loadClientPriceList(client.price_list_id).then(updateCartPrices);
        }
    
        $.ajax({
            url: `${baseUrl}admin/price-list/${priceListId}/products`,
            type: 'GET',
            success: function (response) {
                priceListProducts = response.products;
                updateProductPrices(); // Actualizar precios en la vista
                updateCartPrices(); // Actualizar precios en el carrito
            },
            error: function (xhr) {
                console.error('Error al cargar la lista de precios manual:', xhr.responseText);
            }
        });
    }
    
    

    // Inicializar funciones
    loadProducts();
    loadClientFromSession();
    cargarCategorias();
    cargarVariaciones();
    cargarCategoriaProducto();
    loadCart();
    loadStoreIdFromSession();
});
