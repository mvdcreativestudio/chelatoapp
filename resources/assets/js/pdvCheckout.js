$(document).ready(function () {

  // Variables globales
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
  const storePosProviderId = window.storePosProviderId || null;
  const posDeviceName = window.posDeviceName || null;
  $('#client-info').hide();


  document.querySelectorAll('.card-header').forEach(header => {
    header.addEventListener('click', function () {
      const icon = this.querySelector('i');
      icon.classList.toggle('bx-chevron-down');
      icon.classList.toggle('bx-chevron-up');
    });
  });


  // Limitar la cantidad de decimales en los campos de descuento y valor recibido
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

  function enviarTransaccionPos(token, posOrderId, orderId, orderUuid) {
    console.log('Order ID recibido en enviarTransaccionPos:', orderId);

    $.ajax({
      url: `${baseUrl}api/pos/get-device-info/${cashRegisterId}`,
      type: 'GET',
      success: function (device) {
        const posID = device.data.identifier;
        const transactionDateTime = new Date().toISOString().replace(/[-:.TZ]/g, '').substring(0, 17);
        const amount = parseFloat($('.total').text().replace('$', ''));
        const quotasInput = $('#quotas').val();
        const quotas = quotasInput && !isNaN(quotasInput) ? parseInt(quotasInput) : 1;

        const transactionData = {
          cash_register_id: cashRegisterId,
          store_id: sessionStoreId,
          pos_order_id: posOrderId,
          order_id: orderId,
          PosID: posID,
          TransactionDateTimeyyyyMMddHHmmssSSS: transactionDateTime,
          Amount: Math.round(amount * 100).toString(),
          Quotas: quotas,
        };

        showTransactionStatus({ message: 'Procesando transacción...', icon: 'info', showCloseButton: false });

        $.ajax({
          url: `${baseUrl}api/pos/process-transaction`,
          type: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
          },
          data: JSON.stringify(transactionData),
          success: function (response) {
            const transactionId = response.TransactionId ?? response.response?.TransactionId;
            const sTransactionId = response.STransactionId ?? response.response?.STransactionId;

            if (transactionId && sTransactionId) {
              showTransactionStatus({
                message: 'Procesando transacción...',
                icon: 'info',
                showCloseButton: false,
                transactionId,
                sTransactionId,
                token
              });

              consultarEstadoTransaccion(transactionId, sTransactionId, token, orderId, orderUuid);
            } else {
              console.error('Faltan TransactionId o STransactionId.');
              showTransactionStatus({
                message: 'Error en la transacción POS. Intente nuevamente.',
                icon: 'error',
                showCloseButton: true
              });
            }
          },
          error: function (xhr) {
            console.error('Error en la transacción POS:', xhr.responseText);
            showTransactionStatus({
              message: 'Error al procesar la transacción POS.',
              icon: 'error',
              showCloseButton: true
            });
          }
        });
      },
      error: function (xhr) {
        console.error('Error al obtener información del dispositivo POS:', xhr.responseText);
      }
    });
  }


  function consultarEstadoTransaccion(transactionId, sTransactionId, token, orderId, orderUuid) {
    let attempts = 0;
    const maxAttempts = 30;

    function poll() {
        if (attempts >= maxAttempts) {
            showTransactionStatus({
                message: 'El tiempo de espera para la transacción ha expirado.',
                icon: 'error',
                showCloseButton: true
            });
            return;
        }

        attempts++;

        // Asegúrate de que `sessionStoreId` sea solo el ID
        const storeId = typeof sessionStoreId === 'object' ? sessionStoreId.id : sessionStoreId;

        $.ajax({
            url: `${baseUrl}api/pos/check-transaction-status`,
            type: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            data: JSON.stringify({
                TransactionId: transactionId,
                STransactionId: sTransactionId,
                store_id: storeId // Solo enviamos el ID
            }),
            success: function (response) {
                const { transactionSuccess, message, icon, keepPolling, details } = response;

                if (transactionSuccess) {
                    // Actualizar el estado de pago y confirmar la venta
                    $.ajax({
                        url: `${baseUrl}admin/orders/${orderId}/set-order-as-paid`,
                        type: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            payment_status: 'paid'
                        },
                        success: function () {
                            confirmarVenta({ order_uuid: orderUuid }, 'POS');
                        },
                        error: function (xhr) {
                            console.error('Error al actualizar el estado de pago de la orden:', xhr.responseText);
                            showTransactionStatus({
                                message: 'Error al actualizar el estado del pedido.',
                                icon: 'error',
                                showCloseButton: true
                            });
                        }
                    });
                } else if (keepPolling) {
                    setTimeout(poll, 2000);
                } else {
                    showTransactionStatus({
                        message: message || 'Transacción fallida.',
                        icon: icon || 'error',
                        showCloseButton: true
                    });

                    // Manejar casos como transacción cancelada (CT)
                    if (details && details.PosResponseCode === 'CT') {
                        console.warn('Transacción cancelada desde el dispositivo POS.');
                    }
                }
            },
            error: function (xhr) {
                console.error('Error al consultar estado de transacción:', xhr.responseText);
            }
        });
    }

    poll();
  }


  function confirmarVenta(response, paymentMethod) {
    clearCartAndClient()
    Swal.fire({
      title: 'Venta Realizada con Éxito',
      text: 'La venta se ha realizado exitosamente.',
      icon: 'success',
      showCancelButton: userHasPermission('access_orders'),
      confirmButtonText: userHasPermission('access_orders') ? 'Ver Venta' : 'Cerrar',
      cancelButtonText: 'Cerrar',
      timer: 5000,
      timerProgressBar: true
    }).then(result => {
      if (result.isConfirmed && userHasPermission('access_orders')) {
        window.location.href = `${baseUrl}admin/orders/${response.order_uuid}/show`;
      } else {
        window.location.href = frontRoute;
      }
    });
  }

  let swalInstance;

// Función para mostrar el mensaje de estado de la transacción
function showTransactionStatus({ message, icon, showCloseButton, transactionId, sTransactionId}) {
  if (!swalInstance) {
    swalInstance = Swal.fire({
      icon: icon || 'info',
      title: 'Estado de Transacción',
      html: `
        ${message || 'Procesando...'}
        <div class="mt-3">
          <button
            id="cancelTransactionButton"
            class="btn btn-danger btn-sm"
            style="display: ${transactionId && sTransactionId ? 'inline-block' : 'none'}"
          >
            Cancelar
          </button>
        </div>
      `,
      showConfirmButton: showCloseButton,
      confirmButtonText: 'Cerrar',
      allowOutsideClick: showCloseButton,
      didOpen: () => {
        const cancelButton = document.getElementById('cancelTransactionButton');
        if (cancelButton) {
          cancelButton.addEventListener('click', function () {
            if (!transactionId || !sTransactionId) {
              console.error('TransactionId, STransactionId o token están vacíos.');
              Swal.fire({
                title: 'Error',
                text: 'No se pudo cancelar la transacción. Faltan datos requeridos.',
                icon: 'error',
                confirmButtonText: 'Cerrar'
              });
              return;
            }
            cancelarTransaccion(transactionId, sTransactionId);
          });
        }
        if (!showCloseButton) {
          Swal.showLoading();
        }
      }
    });
  } else {
    swalInstance.update({
      icon: icon || 'info',
      html: `
        ${message || 'Procesando...'}
        <div class="mt-3">
          <button
            id="cancelTransactionButton"
            class="btn btn-danger btn-sm"
            style="display: ${transactionId && sTransactionId ? 'inline-block' : 'none'}"
          >
            Cancelar
          </button>
        </div>
      `,
      showConfirmButton: showCloseButton,
      confirmButtonText: 'Cerrar',
      allowOutsideClick: showCloseButton
    });

    const cancelButton = document.getElementById('cancelTransactionButton');
    if (cancelButton) {
      cancelButton.addEventListener('click', function () {
        if (!transactionId || !sTransactionId) {
          console.error('TransactionId o STransactionId  están vacíos.');
          Swal.fire({
            title: 'Error',
            text: 'No se pudo cancelar la transacción. Faltan datos requeridos.',
            icon: 'error',
            confirmButtonText: 'Cerrar'
          });
          return;
        }
        cancelarTransaccion(transactionId, sTransactionId);
      });
    }

    if (showCloseButton) {
      Swal.hideLoading();
    }
  }
}





// Función para cancelar la transacción
function cancelarTransaccion(transactionId, sTransactionId, token) {
  if (!transactionId || !sTransactionId) {
    console.error('TransactionId o STransactionId son inválidos.');
    Swal.fire({
      title: 'Error',
      text: 'No se pudo cancelar la transacción. Faltan datos requeridos.',
      icon: 'error',
      confirmButtonText: 'Cerrar'
    });
    return;
  }

  // Solo enviar los datos mínimos necesarios
  const requestData = {
    TransactionId: transactionId,
    STransactionId: sTransactionId,
    store_id: sessionStoreId // Si el store_id es necesario
  };

  $.ajax({
    url: `${baseUrl}api/pos/cancel-transaction`,
    type: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    data: JSON.stringify(requestData),
    success: function (response) {
      console.log('Transacción cancelada exitosamente:', response);
      Swal.fire({
        title: 'Cancelación Exitosa',
        text: 'La transacción ha sido cancelada con éxito.',
        icon: 'success',
        confirmButtonText: 'Cerrar'
      });
    },
    error: function (xhr) {
      console.error('Error al cancelar la transacción:', xhr.responseText);
      Swal.fire({
        title: 'Error',
        text: 'No se pudo cancelar la transacción. Haga click en el botón rojo del POS.',
        icon: 'error',
        confirmButtonText: 'Cerrar'
      });
    }
  });
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

        if (!responseConfig.message.includes('en progreso') && !responseConfig.message.includes('Esperando por operación en el PINPad')) {
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
      url: `api-cart`,
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
    return $.ajax({
      url: 'client-session',
      type: 'GET',
      dataType: 'json'
    })
      .done(function (response) {
        if (response.client && Object.keys(response.client).length > 0) {
          client = response.client;
          showClientInfo(client);
          if (client.price_list_id) {
            updateCartPricesWithPriceList(client.price_list_id);
          }
        } else {
          client = null;
        }
      })
      .fail(function (xhr) {
        console.error('Error loading client:', xhr.responseText);
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
      $('#client-company').html(`<strong class="text-muted">Razón Social:</strong> <span class="text-body fw-bold">${client.company_name || '-'}</span>`);
      $('#client-company').show();
    } else {
      $('#client-company').hide();
    }

    console.log('Cliente en showClientInfo:', client);

    $('#client-info').show();
    $('#client-selection-container').hide();
  }



  function saveCartToSession() {
    return $.ajax({
      url: 'api-cart',
      type: 'POST',
      data: {
        _token: $('meta[name="csrf-token"]').attr('content'),
        cart: cart
      }
    })
      .fail(function (xhr) {
        mostrarError('Error al guardar el carrito en la sesión: ' + xhr.responseText);
      });
  }

  function calcularTotal() {
    let subtotal = 0; // Subtotal sin impuestos
    let totalTax = 0; // Total del IVA
    let total = 0; // Total con impuestos

    cart.forEach(item => {
        const basePrice = parseFloat(item.base_price || 0); // Precio base sin impuestos
        const productTaxRate = parseFloat(item.tax_rate?.rate || 0); // Tasa de impuesto del producto
        const clientTaxRate = client?.tax_rate?.rate !== undefined ? parseFloat(client.tax_rate.rate) : null; // Tasa de impuesto del cliente
        const finalTaxRate = clientTaxRate !== null ? clientTaxRate : productTaxRate; // Priorizar el cliente si tiene tasa asignada
        const quantity = parseInt(item.quantity || 1);

        if (isNaN(basePrice) || isNaN(finalTaxRate) || isNaN(quantity)) {
            console.error(`Error en datos del producto:`, item);
            return; // Saltar este ítem si los datos no son válidos
        }

        const taxAmount = (basePrice * finalTaxRate / 100) * quantity;
        const itemTotal = (basePrice * quantity) + taxAmount;

        subtotal += basePrice * quantity;
        totalTax += taxAmount;
        total += itemTotal;
    });

    // Redondear valores a dos decimales
    subtotal = Math.round(subtotal * 100) / 100;
    totalTax = Math.round(totalTax * 100) / 100;
    total = Math.round(total * 100) / 100;

    // Formatear valores para mostrarlos con separadores de miles y dos decimales
    const formattedSubtotal = subtotal.toLocaleString('es-ES', { minimumFractionDigits: 2 });
    const formattedTotalTax = totalTax.toLocaleString('es-ES', { minimumFractionDigits: 2 });
    const formattedTotal = total.toLocaleString('es-ES', { minimumFractionDigits: 2 });

    // Actualizar los valores en la interfaz
    $('.subtotal').text(`${currencySymbol}${formattedSubtotal}`);
    $('.iva-total').text(`${currencySymbol}${formattedTotalTax}`);
    $('.total').text(`${currencySymbol}${formattedTotal}`);
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
    let subtotal = 0; // Subtotal sin impuestos
    let totalTax = 0; // Total del IVA
    let total = 0; // Total con impuestos

    console.log('Carrito actual:', cart); // Verificar el estado del carrito

    cart.forEach(item => {
      const basePrice = parseFloat(item.base_price || 0); // Asegurar que sea un número válido
      const taxRate = parseFloat(item.tax_rate?.rate || 0); // Extraer y validar la tasa de impuesto
      const quantity = parseInt(item.quantity || 1); // Validar cantidad

      if (isNaN(basePrice) || isNaN(taxRate) || isNaN(quantity)) {
          console.error(`Error en datos del producto:`, item);
          return; // Saltar este ítem si los datos no son válidos
      }

      const taxAmount = (basePrice * taxRate / 100) * quantity;
      const itemTotal = (basePrice * quantity) + taxAmount;

      subtotal += basePrice * quantity;
      totalTax += taxAmount;
      total += itemTotal;


        // Formatear los precios para mostrarlos
        const formattedBasePrice = basePrice.toLocaleString('es-ES', { minimumFractionDigits: 2 });
        const formattedItemTotal = itemTotal.toLocaleString('es-ES', { minimumFractionDigits: 2 });

        // Renderizar el HTML del producto con validaciones
        cartHtml += `
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                  <img src="${baseUrl}${item.image || 'default.jpg'}" alt="${item.name || 'Producto'}" class="img-thumbnail me-2" style="width: 50px;">
                  <div>
                      <h6 class="mb-0 text-truncate" style="max-width: 150px;">${item.name || 'Sin nombre'}</h6>
                      <small class="text-muted">Cantidad: ${quantity} x ${currencySymbol}${formattedBasePrice}</small>
                  </div>
              </div>
              <span>${currencySymbol}${formattedItemTotal}</span>
            </li>
        `;
    });

    // Redondear valores a dos decimales
    subtotal = Math.round(subtotal * 100) / 100;
    totalTax = Math.round(totalTax * 100) / 100;
    total = Math.round(total * 100) / 100;

    console.log('Resumen de totales:', {
        subtotal,
        totalTax,
        total
    });

    // Formatear valores para mostrarlos con separadores de miles y dos decimales
    const formattedSubtotal = subtotal.toLocaleString('es-ES', { minimumFractionDigits: 2 });
    const formattedTotalTax = totalTax.toLocaleString('es-ES', { minimumFractionDigits: 2 });
    const formattedTotal = total.toLocaleString('es-ES', { minimumFractionDigits: 2 });

    console.log('Valores formateados:', {
        formattedSubtotal,
        formattedTotalTax,
        formattedTotal
    });

    // Actualizar los valores en la interfaz
    $('.list-group-flush').html(cartHtml); // Renderizar el carrito
    $('.subtotal').text(`${currencySymbol}${formattedSubtotal}`); // Mostrar subtotal sin impuestos
    $('.iva-total').text(`${currencySymbol}${formattedTotalTax}`); // Mostrar total del IVA
    $('.total').text(`${currencySymbol}${formattedTotal}`); // Mostrar total con impuestos

    // Verificar si el carrito está vacío y mostrar un mensaje
    if (cart.length === 0) {
        $('.list-group-flush').html('<li class="list-group-item text-center">El carrito está vacío.</li>');
    }

    // Llamar a la función de cálculo total si es necesario
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
            $('#client-price-list').text(client.price_list_name || 'No se pudo obtener el nombre de la lista de precios');

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
    })
      .fail(function (xhr) {
        mostrarError('Error al guardar el cliente en la sesión: ' + xhr.responseText);
      });
  }


  $('#offcanvasEnd').on('show.bs.offcanvas', function () {
    loadClients();
  });

  // Mostrar/Ocultar campos según el tipo de cliente seleccionado
  $('#tipoCliente').change(function () {
    clearErrors();
    const tipo = $(this).val();

    if (tipo == 'individual') {
      $('#ciField').show();
      $('#ciCliente').attr('required', false);

      $('#nombreCliente, #apellidoCliente').attr('required', true);
      $('#nombreAsterisk, #apellidoAsterisk').show();
      $('label[for="nombreCliente"] .text-danger, label[for="apellidoCliente"] .text-danger').show();

      $('.responsible-text').hide();

      $('#rutField, #razonSocialField, #taxIdField').hide();
      $('#razonSocialCliente, #rutCliente, #taxIdCliente').val('').removeAttr('required');
      $('label[for="razonSocialCliente"] .text-danger, label[for="rutCliente"] .text-danger').hide();

    } else if (tipo == 'company') {
      $('#ciField').hide();
      $('#ciCliente').val('').removeAttr('required');

      $('label[for="nombreCliente"] .text-danger, label[for="apellidoCliente"] .text-danger').hide();
      $('#nombreCliente, #apellidoCliente').removeAttr('required');
      $('#nombreAsterisk, #apellidoAsterisk').hide();

      $('.responsible-text').show();

      $('#rutField, #razonSocialField, #taxIdField').show();
      $('#razonSocialCliente, #rutCliente, #taxIdField').attr('required', true);
      $('label[for="razonSocialCliente"] .text-danger, label[for="rutCliente"] .text-danger').show();
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
    const taxId = document.getElementById('taxIdCliente');

    let hasError = false;
    clearErrors();

    // Validación básica...
    if (tipo.value.trim() === '') {
      showError(tipo, 'Este campo es obligatorio');
      hasError = true;
    }
    let missingFields = [];

    // Si el tipo de cliente es "individual", validar nombre y apellido
    if (tipo.value === 'individual') {
      if (nombre.value.trim() === '') {
        showError(nombre, 'El nombre es obligatorio para clientes individuales');
        missingFields.push('Nombre');
      }

      if (apellido.value.trim() === '') {
        showError(apellido, 'El apellido es obligatorio para clientes individuales');
        missingFields.push('Apellido');
      }
    }

    if (tipo.value === 'company') {
      if (rut.value.trim() === '') {
        showError(rut, 'Este campo es obligatorio para empresas');
        missingFields.push('RUT');
      }

      if (taxId.value.trim() === '') {
        showError(taxId, 'Este campo es obligatorio para empresas');
        missingFields.push('RUT');
      }

      if (razonSocial.value.trim() === '') {
        showError(razonSocial, 'Este campo es obligatorio empresas');
        missingFields.push('Razón Social');
      }
    }

    if (missingFields.length > 0) {
      const offcanvasCliente = bootstrap.Offcanvas.getInstance(document.getElementById('crearClienteOffcanvas'));
      offcanvasCliente.hide();
      Swal.fire({
        icon: 'error',
        title: 'Campos requeridos',
        html: `Por favor complete los siguientes campos:<br><br>${missingFields.join('<br>')}`,
        confirmButtonText: 'Entendido'
      });
      return;
    }

    // Crear el objeto con los datos a enviar
    let data = {
      store_id: parseInt(sessionStoreId, 10),
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
      data.tax_rate_id = taxId.value;
    }
    // Realizar la petición para crear el cliente
    fetch(`${baseUrl}admin/clients`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
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
    client = [];  // Limpiar los datos del cliente
    saveClientToSession(client)
      .done(function () {
        // Volver a cargar el carrito desde la sesión y restaurar los precios originales
        loadCartFromSessionWithNormalPrices();

        // Actualizar la UI para deseleccionar al cliente
        $('#client-id').text('');
        $('#client-name').text('');
        $('#client-type').text('');
        $('#client-doc').text('');
        $('#client-company').hide();  // Ocultar razón social si había un cliente empresa seleccionado
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

  function postOrder(paymentMethod, paymentStatus) {
    ocultarError();

    const shippingStatus = $('#shippingStatus').val();
    let cashSales = 0;
    let posSales = 0;

    const subtotal = cart.reduce((sum, item) => sum + (item.base_price * item.quantity), 0);
    const tax = cart.reduce((sum, item) => {
      const taxRate = parseFloat(item.tax_rate?.rate || 0);
      return sum + ((item.base_price * taxRate / 100) * item.quantity);
    }, 0);

    const total = subtotal + tax - discount; // Aplicar el descuento al total si es necesario

    console.log('Subtotal de la venta:', subtotal.toFixed(2));
    console.log('Tax (Impuestos):', tax.toFixed(2));
    console.log('Total de la venta:', total.toFixed(2));


    if (total > 25000 && (!client || !client.id)) {
        mostrarError('Para ventas mayores a $25000, es necesario tener un cliente asignado a la venta.');
        return;
    }

    if (paymentMethod === 'cash') {
        cashSales = total;
    } else {
        posSales = total;
    }

    if (paymentMethod === 'internalCredit' && (!client || !client.id)) {
        mostrarError('Para ventas con crédito interno, es necesario tener un cliente asignado al pedido.');
        return;
    }

    if (selectedQrMethod === 'qr_attended') paymentMethod = 'qr_attended';

    if (selectedQrMethod === 'qr_dynamic') paymentMethod = 'qr_dynamic';

    // Capturar las cuotas si el método de pago es crédito
    let quotas = null;
    if (paymentMethod === 'credit' && storePosProviderId !== null) {
        quotas = parseInt($('#quotas').val()) || 1; // Valor predeterminado de 1 si está vacío o no válido
    }

    console.log('carrito al crear venta:', cart);

    const orderData = {
        date: new Date().toISOString().split('T')[0],
        hour: new Date().toLocaleTimeString('it-IT'),
        cash_register_log_id: cashRegisterLogId,
        cash_sales: cashSales,
        pos_sales: posSales,
        discount: discount,
        products: JSON.stringify(cart.map(item => ({
            id: item.id,
            name: item.name,
            base_price: item.base_price,
            price: item.price,
            quantity: item.quantity,
            is_composite: item.isComposite || false,
            tax_rate: item.tax_rate?.rate || 0
        }))),
        subtotal: subtotal.toFixed(2),
        tax: tax.toFixed(2),
        total: total.toFixed(2),
        notes: $('textarea').val() || '',
        store_id: sessionStoreId,
        shipping_status: shippingStatus,
        quotas: quotas
    };

    if (client && client.id) {
        orderData.client_id = client.id;
    }

    // Crear la orden en pos-orders primero
    $.ajax({
        url: `${baseUrl}admin/pos-orders`,
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            ...orderData
        },
        success: function (posOrderResponse) {
            const posOrderId = posOrderResponse.order_uuid; // ID de la orden en pos-orders

            // Crear la orden en orders
            const ordersData = {
                ...orderData,
                origin: 'physical',
                payment_status: paymentStatus,
                payment_method: paymentMethod,
                shipping_method: 'standard',
                coupon_id: coupon ? coupon.coupon.id : null,
                coupon_amount: coupon ? coupon.coupon.amount : 0,
                estimate_id: null,
                shipping_id: null,
                preference_id: null,
                shipping_tracking: null,
                is_billed: 0,
            };

            $.ajax({
                url: `${baseUrl}admin/orders`,
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    ...ordersData
                },
                success: function (orderResponse) {
                    const orderId = orderResponse.order_id; // ID de la orden en orders
                    const orderUuid = orderResponse.order_uuid; // UUID de la orden en orders

                    if ((paymentMethod === 'debit' || paymentMethod === 'credit') && storePosProviderId !== null && posDeviceName !== null) {
                      // Si el método de pago es debit o credit, inicia el flujo de transacción POS
                        obtenerTokenPos().then(token => {
                            enviarTransaccionPos(token, posOrderId, orderId, orderUuid, quotas); // Enviar la orden al POS
                        }).catch(error => {
                            console.error('Error al obtener token POS:', error);
                            mostrarError('Error al procesar el pago con POS.');
                        });
                    } else if (paymentMethod === 'qr_attended') {
                        handleMercadoPagoAtendido(orderResponse);
                        return;
                    } else if (paymentMethod === 'qr_dynamic') {
                        handleMercadoPagoDinamico(orderResponse);
                        return;
                    } else {
                        // Para otros métodos de pago, confirmar venta directamente
                        confirmarVenta(orderResponse, paymentMethod);
                    }
                },
                error: function (xhr) {
                    console.error('Error al guardar en /admin/orders:', xhr);
                    mostrarError(xhr.responseJSON ? xhr.responseJSON.error : 'Error desconocido al procesar la venta.');
                }
            });
        },
        error: function (xhr) {
            console.error('Error al guardar en /admin/pos-orders:', xhr);
            mostrarError(xhr.responseJSON ? xhr.responseJSON.error : 'Error desconocido al procesar la venta.');
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

  // Función para manejar el cambio de método de pago y mostrar/ocultar detalles
  function togglePaymentDetails() {
    const paymentMethod = $('input[name="paymentMethod"]:checked').attr('id');

    // Mostrar/ocultar detalles de pago en efectivo
    $('#cashDetails').toggle(paymentMethod === 'cash');

    // Mostrar/ocultar detalles de cuotas
    $('#quotasDetails').toggle(paymentMethod === 'credit');
  }

  // Evento para cambios en el método de pago
  $('input[name="paymentMethod"]').on('change', togglePaymentDetails);

  // Inicializar la visibilidad al cargar la página
  togglePaymentDetails();


  $('.btn-success').on('click', function () {
    ocultarError();

    const paymentMethod = $('input[name="paymentMethod"]:checked').attr('id');
    if (!paymentMethod) {
        mostrarError('Por favor, seleccione un método de pago.');
        return;
    }

    console.log('Método de pago seleccionado:', paymentMethod);

    // Validación adicional para crédito interno
    if (paymentMethod === 'internalCredit' && (!client || !client.id)) {
        mostrarError('Para ventas con crédito interno, es necesario tener un cliente asignado al pedido.');
        return;
    }

    // Determinar el estado inicial de la orden basado en el método de pago
    let paymentStatus = 'paid'; // Por defecto, el estado es 'paid'

    // Verificar si posProviderId está definido para decidir el flujo del POS
    if ((paymentMethod === 'debit' || paymentMethod === 'credit') && storePosProviderId === null || posDeviceName === null) {
        console.log('Sin proveedor POS vinculado, marcando transacción como debit/credit en la DB.');
        paymentStatus = 'paid'; // No se envía al POS, simplemente se marca como pago completado.
    } else if (paymentMethod === 'debit' || paymentMethod === 'credit') {
        paymentStatus = 'pending'; // Cambiar a 'pending' para métodos de pago con POS
    }

    // Crear la orden
    postOrder(paymentMethod, paymentStatus);
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
    var total = parseFloat($('.total').text().replace(/[^\d.-]/g, '')) || 0;

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

