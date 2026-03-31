'use strict';

$(document).ready(function() {
    const cashRegisterId = window.cashRegisterId;
    const baseUrl = window.baseUrl;
    const url = `${baseUrl}products/${cashRegisterId}`;
    let currencySymbol = window.currencySymbol;
    let products = [];
    let cart = [];
    let isListView = true;
    let categories = [];
    let flavors = [];
    let productCategory = [];
    let clients = [];


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

    // Cargar el carrito desde el servidor
    function loadCart() {
        $.ajax({
            url: `cart`,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                // Asegúrate de que 'cart' es un array
                cart = Array.isArray(response.cart) ? response.cart : [];
                updateCart();
            },
            error: function(xhr, status, error) {
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
            success: function(response) {
                if (response && response.categories) {
                    categories = response.categories;
                    cargarCategoriaProducto();
                } else {
                    alert('No se encontraron categorías.');
                }
            },
            error: function(xhr, status, error) {
                alert('Error al cargar las categorías: ' + xhr.responseText);
            }
        });
    }

    // Cargar las categorías de los productos desde el backend
    function cargarCategoriaProducto() {
        $.ajax({
            url: `product-categories`,
            type: 'GET',
            success: function(response) {
                if (response) {
                    productCategory = response;
                    actualizarCategoriasEnVista();
                } else {
                    alert('No se encontraron categorías.');
                }
            },
            error: function(xhr, status, error) {
                alert('Error al cargar las categorías: ' + xhr.responseText);
            }
        });
    }

    // Evento de entrada para el campo de búsqueda de categorías
    $('#category-search-input').on('input', function() {
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
    $(document).on('change', '.form-check-input', function() {
        filterProductsByCategory();
    });

    // Función para filtrar productos por categorías seleccionadas
    function filterProductsByCategory() {
        const selectedCategories = [];
        $('.form-check-input:checked').each(function() {
            selectedCategories.push(parseInt($(this).val()));
        });


        let filteredProducts = [];

        products.forEach(function(product) {
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
          success: function(response) {
              if (response && response.products) {
                  products = response.products;
                  if (isListView) {
                      displayProductsList(products); // Mostrar la vista de lista por defecto
                  } else {
                      displayProducts(products);
                  }
              } else {
                  alert('No se encontraron productos.');
              }
          },
          error: function(xhr, status, error) {
              console.error('Error al obtener los productos:', error);
          }
      });
    }

    // Cargar variaciones desde el backend
  function cargarVariaciones() {
    $.ajax({
        url: `flavors`,
        type: 'GET',
        success: function(response) {
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
        error: function(xhr, status, error) {
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
      let productsHtml = '<ul class="list-group w-100">';
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
          $('#saveFlavors').off('click').on('click', function() {
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
          success: function(response) {
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
          error: function(xhr) {
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
    $(document).on('click', '.add-to-cart', function() {
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
  $(document).on('click', '.product-cart-remove', function() {
    const productId = $(this).data('id');
    cart = cart.filter(item => item.id !== productId);
    updateCart();
    toastr.error(`Producto eliminado correctamente`);
  });


  // Función para cargar clientes
  function loadClients() {
      $.ajax({
          url: 'clients/json',
          type: 'GET',
          dataType: 'json',
          success: function(response) {
              clients = response.clients;
              const clientCount = response.count;
              if (clientCount > 0) {
                  $('#search-client-container').show();
              } else {
                  $('#search-client-container').hide();
              }
              displayClients(clients);
          },
          error: function(xhr, status, error) {
              console.error('Error al obtener los clientes:', error);
          }
      });
  }

  // Función para mostrar los clientes
  function displayClients(clients) {
      const clientList = $('#client-list');
      clientList.empty();
      clients.forEach(client => {
          const fullName = `${client.name} ${client.lastname}`;
          const clientItem = `
              <li class="list-group-item d-flex justify-content-between align-items-center">
                  <div>
                      <strong>${fullName}</strong><br>
                      <small>CI: ${client.ci}</small><br>
                      <small>RUT: ${client.rut}</small>
                  </div>
                  <button class="btn btn-primary btn-sm add-client" data-client='${JSON.stringify(client)}'>
                      <i class="bx bx-plus"></i>
                  </button>
              </li>
          `;
          clientList.append(clientItem);
      });

      // Manejar el clic en el botón "+"
      $('.add-client').on('click', function() {
          const client = $(this).data('client');
          saveClientToSession(client);

          // Actualizar el botón "Seleccionar cliente" con el nombre completo del cliente
          const buttonSelector = document.querySelector('[data-bs-target="#offcanvasEnd"]');
          if (buttonSelector) {
              const fullName = `${client.name} ${client.lastname}`;
              buttonSelector.textContent = fullName;
          }

          // Cerrar el modal
          const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('offcanvasEnd'));
          offcanvas.hide();
      });
  }

  function saveClientToSession(client) {
      $.ajax({
          url: 'client-session',
          type: 'POST',
          data: {
              _token: $('meta[name="csrf-token"]').attr('content'),
              client: client
          },
      });
  }

  // Filtrar clientes por búsqueda
  $('#search-client').on('input', function() {
      const query = $(this).val().toLowerCase();
      searchClients(query);
  });

  // Cargar clientes al abrir el offcanvas
  $('#offcanvasEnd').on('show.bs.offcanvas', function() {
      loadClients();
  });

  function searchClients(query) {
      const filteredClients = clients.filter(client => {
          const clientName = client.name ? client.name.toLowerCase() : '';
          const clientCI = client.ci ? client.ci.toLowerCase() : '';
          const clientRUT = client.rut ? client.rut.toLowerCase() : '';
          return clientName.includes(query.toLowerCase()) ||
                 clientCI.includes(query.toLowerCase()) ||
                 clientRUT.includes(query.toLowerCase());
      });

      displayClients(filteredClients); // Muestra los clientes filtrados
  }

  // Guardar cliente en base de datos
  document.getElementById('guardarCliente').addEventListener('click', function () {
      let nombre = document.getElementById('nombreCliente').value;
      let apellido = document.getElementById('apellidoCliente').value;
      let tipo = document.getElementById('tipoCliente').value;
      let email = document.getElementById('emailCliente').value;
      let ci = document.getElementById('ciCliente').value;
      let rut = document.getElementById('rutCliente').value;

      let data = {
          name: nombre,
          lastname: apellido,
          type: tipo,
          email: email
      };

      if (tipo === 'individual') {
          data.ci = ci;
      } else if (tipo === 'company') {
          data.rut = rut;
      }

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
          document.getElementById('formCrearCliente').reset();
      })
      .catch((error) => {
          console.error('Error:', error);
      });
  });

  document.getElementById('tipoCliente').addEventListener('change', function() {
      let tipo = this.value;
      if (tipo === 'individual') {
          document.getElementById('ciField').style.display = 'block';
          document.getElementById('rutField').style.display = 'none';
      } else if (tipo === 'company') {
          document.getElementById('ciField').style.display = 'none';
          document.getElementById('rutField').style.display = 'block';
      }
  });

  // Manejar el cambio de vista de productos (tarjeta/lista)
  $('#toggle-view-btn').on('click', function() {
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
  $('#html5-search-input').on('input', function() {
      const query = $(this).val();
      searchProducts(query);
  });

  // ==========================================
  // FLUJO DE CIERRE DE CAJA CON VERIFICACION
  // ==========================================

  // Obtener el cashRegisterLogId al cargar
  var cashRegisterLogId = null;
  $.ajax({
      url: 'log/' + cashRegisterId,
      type: 'GET',
      async: false,
      success: function(data) {
          if (data && data.cash_register_log_id) {
              cashRegisterLogId = data.cash_register_log_id;
              window.cashRegisterLogId = cashRegisterLogId;
          }
      }
  });

  // Abrir modal de cierre y cargar detalles
  $('#btn-show-close-modal').click(function () {
      if (!cashRegisterLogId) {
          toastr.error('No se encontro un registro de caja abierto.');
          return;
      }

      $('#closeCashRegisterModal').modal('show');

      $.ajax({
          url: `${window.baseUrl}admin/cash-register-logs/${cashRegisterLogId}/details`,
          type: 'GET',
          success: function (response) {
              if (response.success) {
                  const details = response.details;
                  const expectedCash = parseFloat(details.final_cash_balance || 0);
                  const cs = window.currencySymbol;
                  const fmt = (v) => parseFloat(v || 0).toLocaleString('es-UY', {minimumFractionDigits: 0, maximumFractionDigits: 0});

                  $('#cash-register-details').html(`
                      <div class="card border-0">
                          <div class="card-header"><h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>${details.store_name} - ${details.name}</h6></div>
                          <div class="card-body">
                              <div class="row g-3">
                                  <div class="col-6"><div class="d-flex align-items-center"><i class="bx bx-calendar text-success me-2"></i><div><small class="text-muted">Apertura</small><div class="fw-semibold">${details.open_time}</div></div></div></div>
                                  <div class="col-6"><div class="d-flex align-items-center"><i class="bx bx-calculator text-info me-2"></i><div><small class="text-muted">Fondo de Caja</small><div class="fw-semibold">${cs}${fmt(details.cash_float)}</div></div></div></div>
                                  <div class="col-6"><div class="d-flex align-items-center"><i class="bx bx-money text-success me-2"></i><div><small class="text-muted">Efectivo</small><div class="fw-semibold">${cs}${fmt(details.cash_sales)}</div></div></div></div>
                                  <div class="col-6"><div class="d-flex align-items-center"><i class="bx bx-credit-card text-warning me-2"></i><div><small class="text-muted">POS (Cred/Deb)</small><div class="fw-semibold">${cs}${fmt(details.pos_sales)}</div></div></div></div>
                                  <div class="col-6"><div class="d-flex align-items-center"><i class="bx bxl-paypal text-primary me-2"></i><div><small class="text-muted">Mercadopago</small><div class="fw-semibold">${cs}${fmt(details.mercadopago_sales)}</div></div></div></div>
                                  <div class="col-6"><div class="d-flex align-items-center"><i class="bx bx-transfer text-info me-2"></i><div><small class="text-muted">Transferencias</small><div class="fw-semibold">${cs}${fmt(details.bank_transfer_sales)}</div></div></div></div>
                                  <div class="col-6"><div class="d-flex align-items-center"><i class="bx bx-notepad text-secondary me-2"></i><div><small class="text-muted">Cuenta Corriente</small><div class="fw-semibold">${cs}${fmt(details.internal_credit_sales)}</div></div></div></div>
                                  <div class="col-6"><div class="d-flex align-items-center"><i class="bx bx-wallet text-danger me-2"></i><div><small class="text-muted">Gastos</small><div class="fw-semibold">${cs}${fmt(details.total_expenses)}</div></div></div></div>
                              </div>
                              <hr class="my-3">
                              <div class="d-flex justify-content-between align-items-center">
                                  <h5 class="mb-0">Total de Ventas:</h5>
                                  <h4 class="mb-0 text-success">${cs}${fmt(details.total_sales)}</h4>
                              </div>
                          </div>
                      </div>
                  `);

                  $('#expected_cash').val(expectedCash.toFixed(2));
                  $('#actual_cash').val('');
                  $('#cash-confirmation-section').show();
                  $('#verification-result').hide();
                  $('#verify-cash-btn').show();
                  $('#submit-cerrar-caja').hide().css('display', 'none');
                  $('#force-close-btn').hide().css('display', 'none');
                  $('#cash-difference-alert').hide();
              } else {
                  $('#cash-register-details').html('<div class="alert alert-danger">Error al cargar los detalles.</div>');
              }
          },
          error: function () {
              $('#cash-register-details').html('<div class="alert alert-danger">Error al cargar los detalles de la caja.</div>');
          }
      });
  });

  // Verificar efectivo ingresado
  $(document).off('click', '#verify-cash-btn').on('click', '#verify-cash-btn', function () {
      const expectedCash = parseFloat($('#expected_cash').val()) || 0;
      const actualCash = parseFloat($('#actual_cash').val());

      if (isNaN(actualCash)) {
          toastr.error('Por favor ingrese el monto de efectivo real.');
          return;
      }

      const difference = actualCash - expectedCash;
      const differenceAbs = Math.abs(difference);
      const cs = window.currencySymbol;

      $('#expected-cash-display').text(`${cs}${expectedCash.toFixed(2)}`);
      $('#actual-cash-display').text(`${cs}${actualCash.toFixed(2)}`);
      $('#verification-result').show();

      if (differenceAbs < 0.01) {
          $('#cash-difference-alert')
              .removeClass('alert-warning alert-danger')
              .addClass('alert-success')
              .html('<i class="bx bx-check-circle me-2"></i><strong>Perfecto!</strong> El efectivo coincide con lo esperado.')
              .show();
          $('#verify-cash-btn').hide();
          $('#submit-cerrar-caja').show().css('display', 'inline-flex');
          $('#force-close-btn').hide().css('display', 'none');
          toastr.success('Efectivo verificado correctamente!');
      } else {
          const differenceText = difference > 0 ?
              `<strong>Sobrante:</strong> ${cs}${difference.toFixed(2)}` :
              `<strong>Faltante:</strong> ${cs}${differenceAbs.toFixed(2)}`;

          $('#cash-difference-message').html(differenceText);
          $('#cash-difference-alert')
              .removeClass('alert-success alert-warning alert-danger')
              .addClass(difference > 0 ? 'alert-warning' : 'alert-danger')
              .show();
          $('#verify-cash-btn').hide();
          $('#submit-cerrar-caja').hide().css('display', 'none');
          $('#force-close-btn').show().css('display', 'inline-block');
          toastr[difference > 0 ? 'warning' : 'error'](`Diferencia detectada: ${cs}${differenceAbs.toFixed(2)}`);
      }
  });

  // Reintentar verificación al modificar monto
  $(document).off('input', '#actual_cash').on('input', '#actual_cash', function () {
      $('#verification-result').hide();
      $('#verify-cash-btn').show();
      $('#submit-cerrar-caja').hide().css('display', 'none');
      $('#force-close-btn').hide().css('display', 'none');
  });

  function showPrintCloseModal() {
      const printUrl = `${window.baseUrl}admin/cash-register-logs/${cashRegisterLogId}/print80mm`;
      Swal.fire({
          icon: 'success',
          title: 'Caja cerrada correctamente',
          text: 'Desea imprimir el resumen del cierre?',
          showCancelButton: true,
          confirmButtonText: 'Imprimir',
          cancelButtonText: 'No, cerrar',
          allowOutsideClick: false,
      }).then((result) => {
          if (result.isConfirmed) {
              const printWindow = window.open(printUrl, 'print_cierre', 'width=350,height=600,left=100,top=100');
              if (printWindow) {
                  printWindow.onload = function () { printWindow.print(); };
              }
          }
          location.reload();
      });
  }

  // Cerrar caja sin diferencia
  $('#submit-cerrar-caja').click(function () {
      const actualCash = parseFloat($('#actual_cash').val());
      var csrfToken = $('meta[name="csrf-token"]').attr('content');

      $.ajax({
          url: 'close/' + cashRegisterId,
          type: 'POST',
          data: {
              _token: csrfToken,
              actual_cash: actualCash,
              cash_difference: 0
          },
          success: function (response) {
              $('#closeCashRegisterModal').modal('hide');
              showPrintCloseModal();
          },
          error: function (xhr) {
              alert('Error al cerrar la caja registradora: ' + xhr.responseText);
          }
      });
  });

  // Cerrar caja con diferencia (force close)
  $(document).off('click', '#force-close-btn').on('click', '#force-close-btn', function () {
      const expectedCash = parseFloat($('#expected_cash').val());
      const actualCash = parseFloat($('#actual_cash').val());
      const difference = actualCash - expectedCash;
      const differenceAbs = Math.abs(difference);
      const cs = window.currencySymbol;
      const differenceText = difference > 0 ?
          `Sobrante de ${cs}${difference.toFixed(2)}` :
          `Faltante de ${cs}${differenceAbs.toFixed(2)}`;

      Swal.fire({
          title: 'Confirmar cierre con diferencia?',
          html: `
              <div class="alert alert-warning mb-3">
                  <strong>Diferencia detectada:</strong> ${differenceText}
              </div>
              <p>Esta accion quedara registrada en el sistema.</p>
          `,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#ffc107',
          cancelButtonColor: '#6c757d',
          confirmButtonText: 'Si, cerrar con diferencia',
          cancelButtonText: 'Cancelar'
      }).then((result) => {
          if (result.isConfirmed) {
              const csrfToken = $('meta[name="csrf-token"]').attr('content');

              Swal.fire({ title: 'Cerrando caja...', text: 'Por favor, espere.', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

              $.ajax({
                  url: 'close/' + cashRegisterId,
                  type: 'POST',
                  data: {
                      _token: csrfToken,
                      actual_cash: actualCash,
                      cash_difference: difference
                  },
                  success: function (response) {
                      Swal.close();
                      $('#closeCashRegisterModal').modal('hide');
                      showPrintCloseModal();
                  },
                  error: function (xhr) {
                      Swal.close();
                      Swal.fire({ icon: 'error', title: 'Error al cerrar la caja', text: 'Intente nuevamente.' });
                  }
              });
          }
      });
  });

  // ==========================================
  // FLUJO DE EGRESOS DE CAJA
  // ==========================================

  $(document).on('click', '#registrar-egreso-btn', function () {
      if (!cashRegisterLogId) {
          Swal.fire({ title: 'Error', text: 'No hay cajas abiertas para registrar egresos', icon: 'error' });
          return;
      }
      $('#registrarEgresoForm')[0].reset();
      $('#egreso_currency_rate_field').hide();
      $('#egreso_cash_register_log_id').val(cashRegisterLogId);
      $('#registrarEgresoModal').modal('show');
  });

  $('#egreso_currency').on('change', function() {
      if ($(this).val() === 'Dolar') {
          $('#egreso_currency_rate_field').show();
          $('#egreso_currency_rate').prop('required', true);
      } else {
          $('#egreso_currency_rate_field').hide();
          $('#egreso_currency_rate').prop('required', false).val(0);
      }
  });

  $('#submit-registrar-egreso').click(function () {
      var monto = $('#egreso_monto').val();
      var concepto = $('#egreso_concepto').val();
      var currency = $('#egreso_currency').val();
      var currencyRate = $('#egreso_currency_rate').val();
      var logId = $('#egreso_cash_register_log_id').val();
      var categoria = $('#egreso_categoria').val();
      var observaciones = $('#egreso_observaciones').val();
      var csrfToken = $('meta[name="csrf-token"]').attr('content');

      if (!monto || !concepto || !currency || !logId) {
          Swal.fire({ title: 'Error', text: 'Complete todos los campos requeridos (Concepto, Monto, Moneda)', icon: 'error' });
          return;
      }

      if (currency === 'Dolar' && (!currencyRate || currencyRate <= 0)) {
          Swal.fire({ title: 'Error', text: 'Ingrese una cotizacion valida para el dolar', icon: 'error' });
          return;
      }

      var requestData = {
          _token: csrfToken,
          amount: monto,
          concept: concepto,
          currency: currency,
          expense_category_id: categoria || null,
          observations: observaciones || null,
          cash_register_log_id: logId
      };

      if (currency === 'Dolar') {
          requestData.currency_rate = currencyRate;
      }

      $.ajax({
          url: `${window.baseUrl}admin/cash-register/expense`,
          type: 'POST',
          data: requestData,
          success: function (response) {
              $('#registrarEgresoModal').modal('hide');
              Swal.fire({ title: 'Exito', text: 'Egreso registrado correctamente', icon: 'success' });
              $('#registrarEgresoForm')[0].reset();
              $('#egreso_currency_rate_field').hide();
          },
          error: function (xhr) {
              let errorMessage = 'Error al registrar el egreso';
              if (xhr.responseJSON && xhr.responseJSON.message) {
                  errorMessage = xhr.responseJSON.message;
              }
              Swal.fire({ title: 'Error', text: errorMessage, icon: 'error' });
          }
      });
  });

  // Manejar eventos de clic para incrementar y decrementar la cantidad
  $(document).on('click', '.increment-quantity', function() {
      const productId = $(this).data('id');
      const input = $(`.quantity-input[data-id="${productId}"]`);
      let currentValue = parseInt(input.val());
      input.val(currentValue + 1);
  });

  $(document).on('click', '.decrement-quantity', function() {
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

  // Inicializar funciones
  loadProducts();
  cargarCategorias();
  cargarVariaciones();
  cargarCategoriaProducto();
  loadCart();
  });
