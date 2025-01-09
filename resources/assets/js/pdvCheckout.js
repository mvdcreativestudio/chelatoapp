$(document).ready(function () {
  let cart = [];
  const baseUrl = window.baseUrl || '';
  const frontRoute = window.frontRoute || '';
  let client = [];
  const cashRegisterId = window.cashRegisterId;
  let cashRegisterLogId = null;
  let sessionStoreId = null;
  let discount = 0;
  let coupon = null;
  let currencySymbol = window.currencySymbol;
  let posResponsesConfig = {};
  $('#client-info').hide();

  document.querySelectorAll('.card-header').forEach(header => {
    header.addEventListener('click', function () {
      const icon = this.querySelector('i');
      icon.classList.toggle('bx-chevron-down');
      icon.classList.toggle('bx-chevron-up');
    });
  });

  function limitTwoDecimals(event) {
    const input = event.target;
    let value = input.value;

    // Guardar la posición actual del cursor
    const cursorPosition = input.selectionStart;

    // Expresión regular que permite números con hasta dos decimales
    const regex = /^\d+(\.\d{0,2})?$/;

    // Si el valor no coincide con la expresión regular, recortamos a dos decimales
    if (!regex.test(value)) {
      value = parseFloat(value).toFixed(2);
      if (!isNaN(value)) {
        input.value = value;
      }
    }

    // Restaurar la posición del cursor después de modificar el valor
    setTimeout(() => {
      input.selectionStart = cursorPosition;
      input.selectionEnd = cursorPosition;
    }, 0);
  }

  // Asociar la función limitTwoDecimals a los campos específicos
  $('#fixed-discount').on('input', limitTwoDecimals);
  $('#valorRecibido').on('input', limitTwoDecimals);
  $('#valorRecibido').on('input', calcularVuelto);

  // INTEGRACIÓN POS

  // Cargar la configuración de respuestas POS desde el backend
  function loadPosResponses() {
    $.ajax({
      url: `${baseUrl}api/pos/responses`,
      type: 'GET',
      dataType: 'json',
      success: function (response) {
        // Almacenar la configuración en la variable global
        posResponsesConfig = response;
      },
      error: function (xhr, status, error) {
        console.error('Error al cargar la configuración de respuestas:', error);
      }
    });
  }

  // Llama a la función para cargar la configuración al inicio
  loadPosResponses();

  function obtenerTokenPos() {
    return $.ajax({
      url: `${baseUrl}api/pos/token`,
      type: 'GET',
      data: {
        store_id: sessionStoreId
      },
      success: function (response) {
        if (response.access_token) {
          return response.access_token;
        } else {
          console.error('Error: no se recibió un token válido');
        }
      },
      error: function (xhr, status, error) {
        console.error('Error al obtener el token del POS:', error);
      }
    });
  }

  function enviarTransaccionPos(token) {
    // Obtener el ID del dispositivo POS desde el cashRegisterId
    $.ajax({
      url: `${baseUrl}api/pos/get-device-info/${cashRegisterId}`,
      type: 'GET',
      success: function (device) {
        const posID = device.data.identifier;
        const empresa = device.data.company;
        const local = device.data.branch;
        const caja = device.data.cash_register;
        const userId = device.data.user;

        const now = new Date();
        const transactionDateTime =
          now.getFullYear().toString() +
          String(now.getMonth() + 1).padStart(2, '0') +
          String(now.getDate()).padStart(2, '0') +
          String(now.getHours()).padStart(2, '0') +
          String(now.getMinutes()).padStart(2, '0') +
          String(now.getSeconds()).padStart(2, '0');

        const amount = parseFloat($('.total').text().replace('$', ''));
        const quotas = 1.5;
        const plan = 1;
        const currency = '858';
        const taxableAmount = amount;
        const invoiceAmount = amount;
        const taxAmount = amount * 2;
        const ivaAmount = amount * 2;
        const needToReadCard = false;

        const transactionData = {
          store_id: sessionStoreId,
          PosID: posID,
          Empresa: empresa,
          Local: local,
          Caja: caja,
          UserId: userId,
          TransactionDateTimeyyyyMMddHHmmssSSS: transactionDateTime,
          Amount: amount.toString() + '00',
          Quotas: quotas,
          Plan: plan,
          Currency: currency,
          TaxableAmount: taxableAmount.toString() + '00',
          InvoiceAmount: invoiceAmount.toString() + '00',
          TaxAmount: taxAmount.toString() + '00',
          IVAAmount: ivaAmount.toString() + '00',
          NeedToReadCard: needToReadCard
        };

        showTransactionStatus(10, false, true); // Mostrar mensaje de transacción en progreso

        $.ajax({
          url: `${baseUrl}api/pos/process-transaction`,
          type: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${token}`
          },
          data: JSON.stringify(transactionData),
          success: function (response) {
            const transactionId = response.TransactionId;
            const sTransactionId = response.STransactionId;

            if (transactionId && sTransactionId) {
              sessionStorage.setItem('TransactionId', transactionId);
              sessionStorage.setItem('STransactionId', sTransactionId);
              consultarEstadoTransaccion(transactionId, sTransactionId, transactionDateTime, token);
            } else {
              console.warn('No se recibieron IDs válidos en la respuesta:', response); // Advertencia si no hay IDs válidos
              showTransactionStatus(999); // Error desconocido
            }
          },
          error: function (xhr, status, error) {
            console.error('Error en la transacción POS:', error); // Error detallado del servidor
            showTransactionStatus(999); // Mostrar error
          }
        });
      },
      error: function (xhr, status, error) {
        console.error('Error al obtener la información del dispositivo POS:', error); // Log en caso de error al obtener el dispositivo
      }
    });
  }

  // Función para consultar el estado de la transacción
  function consultarEstadoTransaccion(transactionId, sTransactionId, transactionDateTime, token) {
    let attempts = 0;
    const maxAttempts = 30; // Número máximo de intentos
    let isTransactionComplete = false; // Variable de control para detener la consulta si la transacción ha sido completada

    function poll() {
      if (attempts >= maxAttempts) {
        showTransactionStatus('Tiempo de espera excedido al consultar el estado de la transacción.', true);
        return;
      }

      if (isTransactionComplete) {
        return;
      }

      setTimeout(function () {
        attempts++;

        // Asegurarse de incluir store_id en los datos enviados
        const dataToSend = {
          PosID: $('#posID').val() || '7',
          Empresa: $('#empresa').val() || '2024',
          Local: $('#local').val() || '1',
          Caja: $('#caja').val() || '7',
          UserId: $('#userId').val() || 'Usuario1',
          TransactionDateTimeyyyyMMddHHmmssSSS: transactionDateTime,
          TransactionId: transactionId,
          STransactionId: sTransactionId,
          store_id: sessionStoreId // Asegúrate de pasar el store_id
        };

        if (attempts === 1) {
          showTransactionStatus('Transacción en progreso...', false, true);
        }

        $.ajax({
          url: `${baseUrl}api/pos/check-transaction-status`,
          type: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${token}`
          },
          data: JSON.stringify(dataToSend), // Asegúrate de que store_id esté en los datos
          success: function (response) {
            const responseCode = response.responseCode;
            showTransactionStatus(responseCode, false, false);

            // Manejo de diferentes códigos de respuesta
            if (responseCode === 10 || responseCode === 113 || responseCode === 12 || responseCode === 0) {
              // Continuar consultando mientras se reciba uno de estos códigos
              poll();
            } else if (responseCode === 111) {
              if (swalInstance) {
                swalInstance.close(); // Cerrar swal si está abierto
              }
              isTransactionComplete = true; // Marcar la transacción como completa para detener consultas adicionales
              postOrder(); // Llamar a la función postOrder() para procesar la orden
            } else {
              console.error('Código de respuesta no esperado:', responseCode);
              showTransactionStatus(`Error inesperado: Código de respuesta ${responseCode}`, true);
            }
          },
          error: function (xhr) {
            console.error('Error al consultar el estado de la transacción:', xhr);
            showTransactionStatus(`Error al consultar el estado: ${xhr.status} - ${xhr.responseText}`, true);
          }
        });
      }, 2000); // Intervalo de 2 segundos entre cada consulta
    }

    poll(); // Iniciar el ciclo de consultas
  }

  let swalInstance;

  // Función para mostrar el mensaje de estado de la transacción
  function showTransactionStatus(code, isError = false, isInitial = false) {
    if (!posResponsesConfig || !posResponsesConfig[code]) {
      // Default message if no response config is found for the given code
      const defaultConfig = {
        message: 'Error desconocido.',
        icon: 'error',
        showCloseButton: true
      };
      showSweetAlert(defaultConfig, isInitial);
      return;
    }

    const responseConfig = posResponsesConfig[code];

    showSweetAlert(responseConfig, isInitial);
  }

  // Función para mostrar el mensaje de respuesta POS en SweetAlert
  function showSweetAlert(responseConfig, isInitial) {
    if (isInitial) {
      swalInstance = Swal.fire({
        icon: responseConfig.icon || 'question',
        title: 'Estado de Transacción',
        html: responseConfig.message,
        showConfirmButton: false,
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });
    } else {
      if (swalInstance) {
        swalInstance.update({
          icon: responseConfig.icon,
          html: responseConfig.message,
          showConfirmButton: responseConfig.showCloseButton,
          confirmButtonText: 'Cerrar',
          allowOutsideClick: responseConfig.showCloseButton
        });

        if (
          !responseConfig.message.includes('en progreso') &&
          !responseConfig.message.includes('Esperando por operación en el PINPad')
        ) {
          Swal.hideLoading();
        }

        if (responseConfig.showCloseButton) {
          swalInstance.then(() => {
            Swal.close();
          });
        }
      }
    }
  }

  // Función para verificar si el usuario tiene permiso para ver las ordenes
  function userHasPermission(permission) {
    // Chequear si la lista de permisos contiene el permiso buscado
    return window.userPermissions && window.userPermissions.includes(permission);
  }

  function mostrarError(mensaje) {
    $('#errorContainer').text(mensaje).removeClass('d-none'); // Mostrar mensaje de error
  }

  function ocultarError() {
    $('#errorContainer').addClass('d-none'); // Ocultar el contenedor de errores
  }

  function obtenerCashRegisterLogId() {
    if (cashRegisterId) {
      $.ajax({
        url: `log/${cashRegisterId}`,
        type: 'GET',
        success: function (response) {
          cashRegisterLogId = response.cash_register_log_id;
          sessionStoreId = response.store_id; // Ahora obtenemos el store_id directamente
        },
        error: function (xhr) {
          mostrarError('Error al obtener el ID de cash register log: ' + xhr.responseText);
        }
      });
    } else {
      console.error('ID de caja registradora no definido');
    }
  }

  function loadCartFromSession() {
    $.ajax({
      url: `cart`,
      type: 'GET',
      dataType: 'json',
      success: function (response) {
        if (Array.isArray(response.cart)) {
          cart = response.cart;
        } else {
          cart = [];
        }
        updateCheckoutCart();
      },
      error: function (xhr) {
        mostrarError('Error al cargar el carrito desde la sesión: ' + xhr.responseText);
      }
    });
  }

  function loadClientFromSession() {
    $.ajax({
      url: `client-session`,
      type: 'GET',
      dataType: 'json',
      success: function (response) {
        client = response.client;

        if (client && client.id) {
          showClientInfo(client);
          $('#client-selection-container').hide();
          console.log('Cliente en loadClientFromSession:', client);

          // Si el cliente tiene una lista de precios, cargar los precios desde la lista
          if (client.price_list_id) {
            updateCartPricesWithPriceList(client.price_list_id);
          } else {
            $('#client-price-list').text('Sin lista de precios');
          }
        }
      },
      error: function (xhr) {
        mostrarError('Error al cargar el cliente desde la sesión: ' + xhr.responseText);
      }
    });
  }

  function updateCartPrices() {
    const selectedPriceListId = $('#manual_price_list_id').val();
    const clientPriceListId = client && client.price_list_id;

    // Usa la lista de precios seleccionada manualmente si existe, de lo contrario la del cliente
    const priceListIdToUse = selectedPriceListId || clientPriceListId;

    if (priceListIdToUse) {
      updateCartPricesWithPriceList(priceListIdToUse);
    } else {
      loadCartFromSessionWithNormalPrices(); // Si no hay lista de precios seleccionada, usar precios normales
    }
  }

  // Escucha cambios en el selector manual de listas de precios
  $('#manual_price_list_id').on('change', updateCartPrices);

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
      $('#client-company').html(
        `<strong class="text-muted">Razón Social:</strong> <span class="text-body fw-bold">${client.company_name || '-'}</span>`
      );
      $('#client-company').show();
    } else {
      $('#client-company').hide();
    }

    $('#client-info').show();
    $('#client-selection-container').hide();
  }

  function saveCartToSession() {
    return $.ajax({
      url: 'cart',
      type: 'POST',
      data: {
        _token: $('meta[name="csrf-token"]').attr('content'),
        cart: cart
      }
    }).fail(function (xhr) {
      mostrarError('Error al guardar el carrito en la sesión: ' + xhr.responseText);
    });
  }

  function calcularTotal() {
    let subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
    let total = subtotal - discount;
    if (total < 0) total = 0;

    // Redondear subtotal, descuento y total a dos decimales
    subtotal = Math.round(subtotal * 100) / 100;
    total = Math.round(total * 100) / 100;
    discount = Math.round(discount * 100) / 100;

    // Mostrar los valores redondeados con dos decimales y separadores de miles
    $('.subtotal').text(`${currencySymbol}${subtotal.toFixed(2).toLocaleString('es-ES')}`);
    $('.total').text(`${currencySymbol}${total.toFixed(2).toLocaleString('es-ES')}`);
    $('.discount-amount').text(`${currencySymbol}${discount.toFixed(2).toLocaleString('es-ES')}`);
  }

  function aplicarDescuento() {
    const couponCode = $('#coupon-code').val();

    // Si no hay ningún cupón o descuento, no realizar validación
    if (!couponCode && !$('#fixed-discount').val()) {
      removeDiscount();
      return;
    }

    if (couponCode) {
      $.ajax({
        url: `${baseUrl}admin/get-coupon/${couponCode}`,
        type: 'GET',
        success: function (response) {
          if (response) {
            aplicarDescuentoPorCupon(response);
          } else {
            mostrarError('Cupón no válido o no encontrado.');
          }
        },
        error: function () {
          mostrarError('Error al aplicar el cupón.');
        }
      });
    } else {
      aplicarDescuentoFijo();
    }
  }

  function aplicarDescuentoPorCupon(couponResponse) {
    coupon = couponResponse;
    let subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);

    if (coupon.coupon.type === 'percentage') {
      discount = (coupon.coupon.amount / 100) * subtotal;
    } else if (coupon.coupon.type === 'fixed') {
      discount = coupon.coupon.amount;
    }

    if (discount > subtotal) {
      discount = subtotal;
    }

    discount = Math.round(discount);
    $('.discount-amount').text(`${currencySymbol}${discount.toFixed(0)}`);

    calcularTotal();
    $('#quitarDescuento').show(); // Mostrar el botón de eliminar descuento
  }

  function aplicarDescuentoFijo() {
    const discountType = $('input[name="discount-type"]:checked').val();
    const discountValue = parseFloat($('#fixed-discount').val());

    if (!discountValue || isNaN(discountValue) || discountValue <= 0) {
      mostrarError('Por favor, ingrese un valor de descuento válido.');
      return;
    }

    let subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);

    if (discountType === 'percentage') {
      discount = (discountValue / 100) * subtotal;
    } else if (discountType === 'fixed') {
      discount = discountValue;
    }

    if (discount > subtotal) {
      discount = subtotal;
    }

    // Redondear el descuento a dos decimales
    discount = Math.round(discount * 100) / 100;

    $('.discount-amount').text(`${currencySymbol}${discount.toFixed(2)}`);

    calcularTotal();
    $('#quitarDescuento').show(); // Mostrar el botón de eliminar descuento
  }

  function removeDiscount() {
    // Reiniciar variables de descuento
    discount = 0;
    coupon = null;

    // Limpiar campos de entrada relacionados con descuentos
    $('#coupon-code').val(''); // Limpiar el código de cupón
    $('#fixed-discount').val(''); // Limpiar el valor del descuento fijo

    // Actualizar la visualización del descuento a 0
    $('.discount-amount').text(`${currencySymbol}0`);

    // Recalcular el total sin descuento
    calcularTotal();

    // Ocultar el botón de eliminar descuento
    $('#quitarDescuento').hide();

    // Ocultar el mensaje de error (si hay alguno mostrado)
    ocultarError();
  }

  // Evento para el botón de "Eliminar descuento"
  $('#quitarDescuento').on('click', function () {
    removeDiscount(); // Llamar a la función para eliminar el descuento
  });

  function updateCheckoutCart() {
    let cartHtml = '';
    let subtotal = 0;

    cart.forEach(item => {
      const itemPrice = item.price && !isNaN(item.price) ? item.price : 0; // Asegurar que el precio es válido
      const itemTotal = itemPrice * item.quantity;
      subtotal += itemTotal;

      // Redondear el precio del producto y el total del ítem a dos decimales
      const formattedItemPrice = (Math.round(itemPrice * 100) / 100).toLocaleString('es-ES', {
        minimumFractionDigits: 2
      });
      const formattedItemTotal = (Math.round(itemTotal * 100) / 100).toLocaleString('es-ES', {
        minimumFractionDigits: 2
      });

      cartHtml += `
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <img src="${baseUrl}${item.image}" alt="${item.name}" class="img-thumbnail me-2" style="width: 50px;">
                <div>
                    <h6 class="mb-0">${item.name}</h6>
                    <small class="text-muted">Cantidad: ${item.quantity} x ${currencySymbol}${formattedItemPrice}</small>
                </div>
            </div>
            <span>${currencySymbol}${formattedItemTotal}</span>
        </li>
        `;
    });

    let total = subtotal - discount;
    if (total < 0) total = 0;

    // Redondear subtotal y total a dos decimales
    subtotal = Math.round(subtotal * 100) / 100;
    total = Math.round(total * 100) / 100;

    const formattedSubtotal = subtotal.toLocaleString('es-ES', { minimumFractionDigits: 2 });
    const formattedTotal = total.toLocaleString('es-ES', { minimumFractionDigits: 2 });

    $('.list-group-flush').html(cartHtml);
    $('.subtotal').text(`${currencySymbol}${formattedSubtotal}`);
    $('.total').text(`${currencySymbol}${formattedTotal}`);

    calcularTotal();
  }

  $('.discount-section button').on('click', function () {
    aplicarDescuento();
  });

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
      const displayName =
        client.type === 'company' ? client.company_name || '-' : `${client.name || '-'} ${client.lastname || '-'}`;

      const razonSocial =
        client.type === 'company'
          ? `<p class="client-info"><strong>Razón Social:</strong> ${client.company_name || '-'}</p>`
          : '';

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

    // Event listener para el botón "Seleccionar"
    $('.btn-select-client').on('click', function () {
      const selectedClient = $(this).data('client');

      showClientInfo(selectedClient);

      // Cerrar el offcanvas después de seleccionar el cliente
      let offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('offcanvasEnd'));
      offcanvas.hide();

      saveClientToSession(selectedClient)
        .done(function () {
          client = selectedClient; // Actualizamos la variable global 'client' al nuevo cliente
          if (client.price_list_id) {
            updateCartPricesWithPriceList(client.price_list_id); // Actualizar precios con la lista del nuevo cliente seleccionado
          } else {
            loadCartFromSessionWithNormalPrices(); // Si no hay lista de precios, cargar precios normales
          }
          console.log('Cliente seleccionado y lista de precios aplicada:', client);
        })
        .fail(function (xhr) {
          mostrarError('Error al guardar el cliente en la sesión: ' + xhr.responseText);
        });
    });
  }

  function loadClientAndPriceList(clientId) {
    $.ajax({
      url: `${baseUrl}admin/client-price-list/${clientId}`, // Aquí se usa el ID del cliente
      type: 'GET',
      success: function (response) {
        client = response.client;

        if (client && client.id) {
          // Actualizamos la vista con la información del cliente
          showClientInfo(client);
          $('#client-selection-container').hide();

          // Si el cliente tiene una lista de precios, actualizamos los precios del carrito
          if (client.price_list_id) {
            updateCartPricesWithPriceList(client.price_list_id); // Esto debería funcionar si el ID es correcto
            $('#client-price-list').text(
              client.price_list_name || 'No se pudo obtener el nombre de la lista de precios'
            );
          } else {
            loadCartFromSessionWithNormalPrices();
          }
        }
      },
      error: function (xhr) {
        mostrarError('Error al cargar el cliente y su lista de precios: ' + xhr.responseText);
      }
    });
  }

  function updateCartPricesWithPriceList(priceListId) {
    $.ajax({
      url: `${baseUrl}admin/price-list/${priceListId}/products`,
      type: 'GET',
      success: function (response) {
        const priceListProducts = response.products;
        let cartUpdated = false;

        // Itera sobre los productos del carrito y actualiza sus precios
        cart.forEach(item => {
          const productInPriceList = priceListProducts.find(p => p.id === item.id);

          if (productInPriceList) {
            // Si el producto está en la lista, aplica el precio de la lista
            item.price = productInPriceList.price;
          } else {
            // Si el producto no está en la lista, restaura el precio original
            item.price = item.original_price;
          }

          cartUpdated = true;
        });

        // Actualiza la vista del carrito si se ha modificado
        if (cartUpdated) {
          updateCheckoutCart();
        }
      },
      error: function (xhr) {
        mostrarError('Error al actualizar los precios con la lista de precios: ' + xhr.responseText);
      }
    });
  }

  $('#search-client').on('input', function () {
    const searchText = $(this).val().toLowerCase();

    // Seleccionar las tarjetas de cliente correctas
    $('#client-list .client-card').each(function () {
      const name = $(this).find('.card-title').text().toLowerCase(); // Obtener el nombre del cliente desde la tarjeta
      const ci = $(this).find('.client-info:contains("CI")').text().toLowerCase(); // Obtener CI
      const rut = $(this).find('.client-info:contains("RUT")').text().toLowerCase(); // Obtener RUT
      const company_name = $(this).find('.client-info:contains("Razón Social")').text().toLowerCase(); // Obtener Razón Social

      // Comprobar si el texto de búsqueda coincide con nombre, CI o RUT
      if (
        name.includes(searchText) ||
        ci.includes(searchText) ||
        rut.includes(searchText) ||
        company_name.includes(searchText)
      ) {
        $(this).removeClass('d-none'); // Mostrar tarjeta
      } else {
        $(this).addClass('d-none'); // Ocultar tarjeta
      }
    });
  });

  function saveClientToSession(client) {
    return $.ajax({
      url: 'client-session',
      type: 'POST',
      data: {
        _token: $('meta[name="csrf-token"]').attr('content'),
        client: client
      }
    }).fail(function (xhr) {
      mostrarError('Error al guardar el cliente en la sesión: ' + xhr.responseText);
    });
  }

  $('#offcanvasEnd').on('show.bs.offcanvas', function () {
    loadClients();
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

  // Crear un nuevo cliente
  document.getElementById('guardarCliente').addEventListener('click', function (e) {
    e.preventDefault();

    const form = document.getElementById('formCrearCliente');
    const formData = new FormData(form);
    const clientType = document.getElementById('tipoCliente').value;

    let requiredFields = {
      nombreCliente: 'Nombre',
      apellidoCliente: 'Apellido',
      emailCliente: 'Correo electrónico',
      direccionCliente: 'Dirección'
    };

    if (clientType === 'individual') {
      requiredFields.ciCliente = 'Cédula de Identidad';
    } else if (clientType === 'company') {
      requiredFields.razonSocialCliente = 'Razón Social';
      requiredFields.rutCliente = 'RUT';
    }

    // Check required fields
    let missingFields = [];
    Object.keys(requiredFields).forEach(field => {
      const element = document.getElementById(field);
      if (!element.value || element.value.trim() === '') {
        missingFields.push(requiredFields[field]);
      }
    });

    if (missingFields.length > 0) {
      const offcanvas = document.getElementById('crearClienteOffcanvas');
      const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvas);
      bsOffcanvas.hide();

      Swal.fire({
        icon: 'error',
        title: 'Campos requeridos',
        html: `Por favor complete los siguientes campos:<br><br>${missingFields.join('<br>')}`,
        confirmButtonText: 'Entendido'
      });
      return;
    }

    let data = {
      name: document.getElementById('nombreCliente').value.trim(),
      lastname: document.getElementById('apellidoCliente').value.trim(),
      type: clientType,
      email: document.getElementById('emailCliente').value.trim(),
      address: document.getElementById('direccionCliente').value.trim(),
      price_list_id: document.getElementById('price_list_id').value
    };

    if (clientType === 'individual') {
      data.ci = document.getElementById('ciCliente').value.trim();
    } else if (clientType === 'company') {
      data.rut = document.getElementById('rutCliente').value.trim();
      data.company_name = document.getElementById('razonSocialCliente').value.trim();
    }

    fetch(`${baseUrl}admin/clients`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': window.csrfToken,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(data)
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const offcanvas = document.getElementById('crearClienteOffcanvas');
          const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvas);
          bsOffcanvas.hide();

          Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: 'Cliente creado correctamente',
            showConfirmButton: false,
            timer: 1500
          });

          form.reset();
          if (typeof updateClientList === 'function') {
            updateClientList();
          }
        }
      })
      .catch(error => {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Error al crear el cliente: ' + error,
          confirmButtonText: 'Aceptar'
        });
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

  document.getElementById('tipoCliente').addEventListener('change', function () {
    let tipo = this.value;
    if (tipo === 'individual') {
      document.getElementById('ciField').style.display = 'block';
      document.getElementById('rutField').style.display = 'none';
      document.getElementById('razonSocialField').style.display = 'none';
    } else if (tipo === 'company') {
      document.getElementById('ciField').style.display = 'none';
      document.getElementById('rutField').style.display = 'block';
      document.getElementById('razonSocialField').style.display = 'block';
    }
  });

  $('#deselect-client').on('click', function () {
    deselectClient();
  });

  // Función para deseleccionar al cliente
  function deselectClient() {
    client = []; // Limpiar los datos del cliente
    saveClientToSession(client)
      .done(function () {
        // Volver a cargar el carrito desde la sesión y restaurar los precios originales
        loadCartFromSessionWithNormalPrices();

        // Actualizar la UI para deseleccionar al cliente
        $('#client-id').text('');
        $('#client-name').text('');
        $('#client-type').text('');
        $('#client-doc').text('');
        $('#client-company').hide(); // Ocultar razón social si había un cliente empresa seleccionado
        $('#client-info').hide();
        $('#client-selection-container').show();

        // Forzar la actualización del carrito al precio normal
        updateCheckoutCart();
      })
      .fail(function (xhr) {
        mostrarError('Error al guardar el cliente en la sesión: ' + xhr.responseText);
      });
  }

  // Función para cargar el carrito desde la sesión con precios normales
  function loadCartFromSessionWithNormalPrices() {
    cart = cart.map(item => {
      // Verifica si original_price es un número
      if (typeof item.original_price === 'number' && !isNaN(item.original_price)) {
        item.price = parseFloat(item.original_price); // Redondeamos a dos decimales
        console.log(`Producto con Nombre ${item.name} restaurado a precio original: ${item.price}`);
      } else {
        console.error(`Producto con ID ${item.id} no tiene original_price definido correctamente o no es un número`);
        item.price = 0; // Valor predeterminado en caso de error
      }
      return item;
    });

    // Actualizamos la vista para reflejar los cambios
    updateCheckoutCart(); // Refresca la interfaz gráfica con el carrito actualizado
  }

  loadCartFromSession();
  loadClientFromSession();
  obtenerCashRegisterLogId();
  loadStoreIdFromSession();

  function postOrder() {
    ocultarError();

    let paymentMethod = $('input[name="paymentMethod"]:checked').attr('id');
    const shippingStatus = $('#shippingStatus').val();
    let cashSales = 0;
    let posSales = 0;

    const total =
      parseFloat(
        $('.total')
          .text()
          .replace(/[^\d.-]/g, '')
      ) || 0;
    const subtotal =
      parseFloat(
        $('.subtotal')
          .text()
          .replace(/[^\d.-]/g, '')
      ) || 0;

    // Validación para ventas mayores a 12000
    if (total > 600 && (!client || !client.id)) {
      mostrarError(
        'Para ventas mayores a USD600, es necesario tener un cliente asignado a la venta. Puede seleccionar uno existente o crear uno nuevo.'
      );
      return;
    }

    if (paymentMethod === 'cash') {
      cashSales = total;
    } else {
      posSales = total;
    }

    if (paymentMethod === 'internalCredit' && (!client || !client.id)) {
      mostrarError(
        'Para ventas con crédito interno, es necesario tener un cliente asignado al pedido. Puede seleccionar uno existente o crear uno nuevo.'
      );
      return;
    }

    const orderData = {
      date: new Date().toISOString().split('T')[0],
      hour: new Date().toLocaleTimeString('it-IT'),
      cash_register_log_id: cashRegisterLogId,
      cash_sales: cashSales,
      pos_sales: posSales,
      discount: discount,
      products: JSON.stringify(
        cart.map(item => ({
          id: item.id,
          name: item.name,
          price: item.price,
          quantity: item.quantity,
          is_composite: item.isComposite || false
        }))
      ),
      subtotal: subtotal,
      total: total - discount,
      notes: $('textarea').val() || '',
      store_id: sessionStoreId,
      shipping_status: shippingStatus
    };

    // Agregar datos de cliente solo si hay uno asociado
    if (client && client.id) {
      orderData.client_id = client.id;
    }

    if (selectedQrMethod === 'qr_attended') paymentMethod = 'qr_attended';

    if (selectedQrMethod === 'qr_dynamic') paymentMethod = 'qr_dynamic';

    // POST a pos-orders
    $.ajax({
      url: `${baseUrl}admin/pos-orders`,
      type: 'POST',
      data: {
        _token: $('meta[name="csrf-token"]').attr('content'),
        ...orderData
      },
      success: function (response) {
        const ordersData = {
          ...orderData,
          origin: 'physical',
          payment_status: 'paid',
          payment_method: paymentMethod,
          shipping_method: 'standard',
          coupon_id: coupon ? coupon.coupon.id : null,
          coupon_amount: coupon ? coupon.coupon.amount : 0,
          estimate_id: null,
          shipping_id: null,
          preference_id: null,
          shipping_tracking: null,
          is_billed: 0
        };

        $.ajax({
          url: `${baseUrl}admin/orders`,
          type: 'POST',
          data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            ...ordersData
          },
          success: function (response) {
            if (paymentMethod === 'qr_attended') {
              handleMercadoPagoAtendido(response);
              return;
            }

            if (paymentMethod === 'qr_dynamic') {
              handleMercadoPagoDinamico(response);
              return;
            }
            clearCartAndClient()
              .then(() => {
                return Swal.fire({
                  customClass: {
                    popup: 'swal-popup',
                    title: 'swal-title',
                    content: 'swal-content',
                    confirmButton: 'btn btn-outline-primary',
                    cancelButton: 'btn btn-outline-danger'
                  },
                  title: 'Venta Realizada con Éxito',
                  text: 'La venta se ha realizado exitosamente.',
                  icon: 'success',
                  showCancelButton: userHasPermission('access_orders'),
                  confirmButtonText: userHasPermission('access_orders') ? 'Ver Venta' : 'Cerrar',
                  cancelButtonText: 'Cerrar',
                  timer: 5000,
                  timerProgressBar: true,
                  didOpen: toast => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                  }
                });
              })
              .then(result => {
                if (result.isConfirmed && userHasPermission('access_orders')) {
                  clearCartAndClient()
                    .then(() => {
                      window.location.href = `${baseUrl}admin/orders/${response.order_uuid}/show`;
                    })
                    .catch(error => {
                      console.error('Error al limpiar carrito y cliente:', error);
                      mostrarError(error);
                    });
                } else {
                  clearCartAndClient()
                    .then(() => {
                      window.location.href = frontRoute;
                    })
                    .catch(error => {
                      console.error('Error al limpiar carrito y cliente:', error);
                      mostrarError(error);
                    });
                }
              })
              .catch(error => {
                console.error('Error al limpiar carrito y cliente:', error);
                mostrarError(error);
              });
          },
          error: function (xhr) {
            console.error('Error al guardar la orden en /admin/orders:', xhr.responseText);
            mostrarError('Error al guardar la orden en /admin/orders: ' + xhr.responseText);
          }
        });
      },
      error: function (xhr) {
        console.error('Error al guardar en /admin/pos-orders:', xhr);
        if (xhr.responseJSON && xhr.responseJSON.errors) {
          const errores = xhr.responseJSON.errors;
          let mensajes = '';
          for (const campo in errores) {
            mensajes += `${errores[campo].join(', ')}<br>`;
          }
          mostrarError(mensajes);
        } else {
          mostrarError(xhr.responseJSON ? xhr.responseJSON.error : 'Error desconocido');
        }
      }
    });
  }

  function clearCartAndClient() {
    return new Promise((resolve, reject) => {
      try {
        // Limpia el carrito almacenado localmente
        cart = [];
        saveCartToSession();
        // Limpia los datos del cliente localmente
        client = null;
        saveClientToSession(client);

        // Resuelve la promesa si todo fue exitoso
        resolve();
      } catch (error) {
        reject('Error al limpiar el carrito y cliente.');
      }
    });
  }

  // Función para mostrar u ocultar los detalles de pago en efectivo
  function toggleCashDetails() {
    const paymentMethod = $('input[name="paymentMethod"]:checked').attr('id');
    if (paymentMethod === 'cash') {
      $('#cashDetails').show();
    } else {
      $('#cashDetails').hide();
    }
  }

  // Evento para cambios en el método de pago
  $('input[name="paymentMethod"]').on('change', toggleCashDetails);

  // Llamar a la función al cargar la página para asegurarse de que el estado inicial es correcto
  toggleCashDetails();

  $('.btn-success').on('click', function () {
    const paymentMethod = $('input[name="paymentMethod"]:checked').attr('id');
    if (!paymentMethod) {
      mostrarError('Por favor, seleccione un método de pago.');
      return;
    }
    if (paymentMethod !== 'debit' || paymentMethod !== 'credit') {
      postOrder();
    } else {
      postOrder();

      // /** Descomentar para usar el POS */
      // obtenerTokenPos().done(function (response) {
      //   const token = response.access_token;
      //   enviarTransaccionPos(token);
      // }).fail(function (error) {
      //   console.error('Error al obtener el token del POS:', error);
      // });
    }
  });

  $('#descartarVentaBtn').on('click', function () {
    client = [];
    saveClientToSession(client);
    cart = [];
    saveCartToSession();
    updateCheckoutCart();
  });

  function calcularVuelto() {
    var valorRecibido = parseFloat($('#valorRecibido').val()) || 0;
    var total =
      parseFloat(
        $('.total')
          .text()
          .replace(/[^\d.-]/g, '')
      ) || 0;

    var vuelto = valorRecibido - total;

    // Verificar si el valor recibido es menor que el total
    if (valorRecibido < total) {
      $('#mensajeError').removeClass('d-none');
    } else {
      $('#mensajeError').addClass('d-none');
    }

    // Formatear el vuelto con separadores de miles, mínimo de 0 decimales y máximo de 2
    var formattedVuelto = vuelto.toLocaleString('es-ES', { minimumFractionDigits: 0, maximumFractionDigits: 2 });

    // Mostrar el vuelto formateado
    $('#vuelto').text(`${currencySymbol}${formattedVuelto}`);
  }

  // Crear instancias de los modales con Bootstrap
  const modalQRAtendido = new bootstrap.Modal(document.getElementById('modalQRAtendido'));
  const modalQRDinamico = new bootstrap.Modal(document.getElementById('modalQRDinamico'));
  const modalMercadopago = new bootstrap.Modal(document.getElementById('modalMercadopago'));

  // Variable global para almacenar el método de QR seleccionado
  let selectedQrMethod = null;

  // Evento para QR Modelo Atendido
  $('#btnModeloAtendido').on('click', function () {
    selectedQrMethod = 'qr_attended'; // Guardar el método de pago como 'qr_attended'
    modalMercadopago.hide(); // Cerrar el modal de opciones
    postOrder();
  });

  // Evento para QR Modelo Dinámico
  $('#btnModeloDinamico').on('click', function () {
    selectedQrMethod = 'qr_dynamic'; // Guardar el método de pago como 'qr_dynamic'
    modalMercadopago.hide(); // Cerrar el modal de opciones
    postOrder();
  });

  // Cerrar el modal "QR Atendido" y volver al de opciones
  $('#modalQRAtendido').on('hidden.bs.modal', function () {
    if (selectedQrMethod === 'qr_attended') {
      modalMercadopago.show();
    }
  });

  // Cerrar el modal "QR Dinámico" y volver al de opciones
  $('#modalQRDinamico').on('hidden.bs.modal', function () {
    if (selectedQrMethod === 'qr_dynamic') {
      modalMercadopago.show();
    }
  });

  // Función para manejar el cierre forzado de un modal en caso de error
  function handleMercadoPagoAtendido(response) {
    modalMercadopago.hide();
    Swal.fire({
      title: 'Esperando Pago...',
      html: 'Por favor escanea el código QR asociada al punto de venta con la aplicación de Mercado Pago.',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    trackOrderStatus(response);
  }

  function handleMercadoPagoDinamico(response) {
    Swal.fire({
      title: 'Generando QR de Pago...',
      html: 'Por favor espera mientras se genera el código QR dinámico.',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });
    // Generar el QR dinámico después de crear la orden
    $.ajax({
      url: `${baseUrl}admin/orders/mercado-pago/qr-dinamico/${response.order_id}`,
      type: 'GET',
      contentType: 'application/json',
      success: function (qrResponse) {
        Swal.close(); // Cerrar el mensaje de carga
        // Actualizar el QR en el modal
        const qrContainer = document.getElementById('qrImageDinamicoContainer');
        qrContainer.innerHTML = ''; // Limpiar el contenedor

        if (qrResponse.qrTramma) {
          // Generar el QR usando la librería
          new QRCode(qrContainer, {
            text: qrResponse.qrTramma,
            width: 256,
            height: 256,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
          });
        } else {
          qrContainer.innerHTML = '<p>QR no disponible</p>'; // Mensaje de respaldo
        }

        // Mostrar el modal del QR
        const modalQRDinamico = new bootstrap.Modal(document.getElementById('modalQRDinamico'));
        modalQRDinamico.show();
      },
      error: function (xhr) {
        console.error('Error al generar el QR Dinámico:', xhr.responseText);
        mostrarError('Error al generar el QR Dinámico: ' + xhr.responseText);
      }
    });

    // Iniciar el seguimiento del estado de la orden sin mostrar Swal
    trackOrderStatus(response);
  }

  // Función para rastrear el estado de la orden
  function trackOrderStatus(response) {
    // Intervalo para verificar el estado de la orden
    const interval = setInterval(() => {
      $.ajax({
        url: `${baseUrl}admin/orders/mercado-pago/${response.order_id}`,
        type: 'GET',
        contentType: 'application/json',
        success: function (response) {
          if (response.status === 'paid') {
            // Cerrar todos los modales
            const modals = ['modalQRAtendido', 'modalQRDinamico', 'modalMercadopago'];
            modals.forEach(modalId => {
              const modalElement = document.getElementById(modalId);
              if (modalElement) {
                modalElement.remove(); // Elimina el modal del DOM
              }
            });

            // Eliminar cualquier backdrop remanente
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => backdrop.remove());
            clearInterval(interval); // Detener las verificaciones
            Swal.close();
            clearCartAndClient()
              .then(() => {
                return Swal.fire({
                  customClass: {
                    popup: 'swal-popup',
                    title: 'swal-title',
                    content: 'swal-content',
                    confirmButton: 'btn btn-outline-primary',
                    cancelButton: 'btn btn-outline-danger'
                  },
                  title: 'Venta Realizada con Éxito',
                  text: 'La venta se ha realizado exitosamente.',
                  icon: 'success',
                  showCancelButton: userHasPermission('access_orders'),
                  confirmButtonText: userHasPermission('access_orders') ? 'Ver Venta' : 'Cerrar',
                  cancelButtonText: 'Cerrar',
                  timer: 5000,
                  timerProgressBar: true,
                  didOpen: toast => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                  }
                });
              })
              .then(result => {
                if (result.isConfirmed && userHasPermission('access_orders')) {
                  clearCartAndClient()
                    .then(() => {
                      window.location.href = `${baseUrl}admin/orders/${response.order_uuid}/show`;
                    })
                    .catch(error => {
                      console.error('Error al limpiar carrito y cliente:', error);
                      mostrarError(error);
                    });
                } else {
                  clearCartAndClient()
                    .then(() => {
                      window.location.href = frontRoute;
                    })
                    .catch(error => {
                      console.error('Error al limpiar carrito y cliente:', error);
                      mostrarError(error);
                    });
                }
              })
              .catch(error => {
                console.error('Error al limpiar carrito y cliente:', error);
                mostrarError(error);
              });
          } else if (response.status === 'failed') {
            clearInterval(interval); // Detener las verificaciones
            Swal.close();
            Swal.fire({
              title: 'Pago Cancelado',
              text: 'El pago ha sido cancelado.',
              icon: 'error',
              showCancelButton: userHasPermission('access_orders'),
              confirmButtonText: userHasPermission('access_orders') ? 'Ver Venta' : 'Cerrar',
              cancelButtonText: 'Cerrar',
              timer: 5000,
              timerProgressBar: true,
              didOpen: toast => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
              }
            });
          }
        },
        error: function (xhr) {
          clearInterval(interval); // Detener las verificaciones en caso de error
          Swal.close();
          setTimeout(() => {
            Swal.fire({
              icon: 'error',
              title: 'Error al procesar la orden',
              text: xhr.responseJSON?.message || 'Ocurrió un error al intentar procesar la orden.'
            });
          }, 500);
        }
      });
    }, 5000); // Intervalo de 5 segundos
  }
});
