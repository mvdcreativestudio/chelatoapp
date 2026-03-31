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


  function limitTwoDecimals(event) {
    const input = event.target;
    let value = input.value;
    const cursorPosition = input.selectionStart;
    const regex = /^\d+(\.\d{0,2})?$/;
    if (!regex.test(value)) {
      value = parseFloat(value).toFixed(2);
      if (!isNaN(value)) {
        input.value = value;
      }
    }
    setTimeout(() => {
      input.selectionStart = cursorPosition;
      input.selectionEnd = cursorPosition;
    }, 0);
  }

  $('#fixed-discount').on('input', limitTwoDecimals);
  $('#valorRecibido').on('input', limitTwoDecimals);
  $('#valorRecibido').on('input', calcularVuelto);

  // ==================== POS INTEGRATION ====================

  function loadPosResponses() {
    $.ajax({
      url: `${baseUrl}api/pos/responses`,
      type: 'GET',
      dataType: 'json',
      success: function (response) {
        posResponsesConfig = response;
      },
      error: function (xhr, status, error) {
        console.error('Error al cargar la configuración de respuestas:', error);
      }
    });
  }

  loadPosResponses();

  function obtenerTokenPos() {
    return $.ajax({
      url: `${baseUrl}api/pos/token`,
      type: 'GET',
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
    const posID = $('#posID').val() || "7";
    const empresa = $('#empresa').val() || "2024";
    const local = $('#local').val() || "1";
    const caja = $('#caja').val() || "7";
    const userId = $('#userId').val() || "Usuario1";
    const now = new Date();
    const transactionDateTime = now.getFullYear().toString() +
      String(now.getMonth() + 1).padStart(2, '0') +
      String(now.getDate()).padStart(2, '0') +
      String(now.getHours()).padStart(2, '0') +
      String(now.getMinutes()).padStart(2, '0') +
      String(now.getSeconds()).padStart(2, '0');

    const amount = parseFloat($('.total').first().text().replace(/[^\d.-]/g, ''));
    const quotas = 1.5;
    const plan = 1;
    const currency = "858";
    const taxableAmount = amount;
    const invoiceAmount = amount;
    const taxAmount = amount * 2;
    const ivaAmount = amount * 2;
    const needToReadCard = false;

    const transactionData = {
      PosID: posID, Empresa: empresa, Local: local, Caja: caja, UserId: userId,
      TransactionDateTimeyyyyMMddHHmmssSSS: transactionDateTime,
      Amount: amount.toString() + "00", Quotas: quotas, Plan: plan, Currency: currency,
      TaxableAmount: taxableAmount.toString() + "00", InvoiceAmount: invoiceAmount.toString() + "00",
      TaxAmount: taxAmount.toString() + "00", IVAAmount: ivaAmount.toString() + "00",
      NeedToReadCard: needToReadCard
    };

    showTransactionStatus(10, false, true);

    $.ajax({
      url: `${baseUrl}api/pos/process-transaction`,
      type: 'POST',
      headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
      data: JSON.stringify(transactionData),
      success: function (response) {
        const transactionId = response.TransactionId;
        const sTransactionId = response.STransactionId;
        if (transactionId && sTransactionId) {
          sessionStorage.setItem('TransactionId', transactionId);
          sessionStorage.setItem('STransactionId', sTransactionId);
          consultarEstadoTransaccion(transactionId, sTransactionId, transactionDateTime, token);
        } else {
          showTransactionStatus(999);
        }
      },
      error: function (xhr, status, error) {
        showTransactionStatus(999);
        console.error('Error al enviar la transacción a Scanntech:', error);
      }
    });
  }

  function consultarEstadoTransaccion(transactionId, sTransactionId, transactionDateTime, token) {
    let attempts = 0;
    const maxAttempts = 30;
    let isTransactionComplete = false;

    function poll() {
      if (attempts >= maxAttempts) {
        showTransactionStatus('Tiempo de espera excedido al consultar el estado de la transacción.', true);
        return;
      }
      if (isTransactionComplete) return;

      setTimeout(function () {
        attempts++;
        const dataToSend = {
          PosID: $('#posID').val() || "7", Empresa: $('#empresa').val() || "2024",
          Local: $('#local').val() || "1", Caja: $('#caja').val() || "7",
          UserId: $('#userId').val() || "Usuario1",
          TransactionDateTimeyyyyMMddHHmmssSSS: transactionDateTime,
          TransactionId: transactionId, STransactionId: sTransactionId
        };

        if (attempts === 1) showTransactionStatus('Transacción en progreso...', false, true);

        $.ajax({
          url: `${baseUrl}api/pos/check-transaction-status`,
          type: 'POST',
          headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
          data: JSON.stringify(dataToSend),
          success: function (response) {
            const responseCode = response.responseCode;
            showTransactionStatus(responseCode, false, false);
            if (responseCode === 10 || responseCode === 113 || responseCode === 12 || responseCode === 0) {
              poll();
            } else if (responseCode === 111) {
              if (swalInstance) swalInstance.close();
              isTransactionComplete = true;
              postOrder();
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
      }, 2000);
    }

    poll();
  }

  let swalInstance;

  function showTransactionStatus(code, isError = false, isInitial = false) {
    if (!posResponsesConfig || !posResponsesConfig[code]) {
      const defaultConfig = { message: 'Error desconocido.', icon: 'error', showCloseButton: true };
      showSweetAlert(defaultConfig, isInitial);
      return;
    }
    showSweetAlert(posResponsesConfig[code], isInitial);
  }

  function showSweetAlert(responseConfig, isInitial) {
    if (isInitial) {
      swalInstance = Swal.fire({
        icon: responseConfig.icon || 'question', title: 'Estado de Transacción',
        html: responseConfig.message, showConfirmButton: false, allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
      });
    } else {
      if (swalInstance) {
        swalInstance.update({
          icon: responseConfig.icon, html: responseConfig.message,
          showConfirmButton: responseConfig.showCloseButton,
          confirmButtonText: 'Cerrar', allowOutsideClick: responseConfig.showCloseButton
        });
        if (!responseConfig.message.includes('en progreso') && !responseConfig.message.includes('Esperando por operación en el PINPad')) {
          Swal.hideLoading();
        }
        if (responseConfig.showCloseButton) {
          swalInstance.then(() => { Swal.close(); });
        }
      }
    }
  }

  // ==================== HELPERS ====================

  function userHasPermission(permission) {
    return window.userPermissions && window.userPermissions.includes(permission);
  }

  function mostrarError(mensaje) {
    $('#errorContainer').html(mensaje).removeClass('d-none');
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  function ocultarError() {
    $('#errorContainer').addClass('d-none');
  }

  function mostrarToast(mensaje, tipo = 'success') {
    const toast = document.createElement('div');
    toast.className = 'pdv-toast pdv-toast-' + tipo;
    toast.innerHTML = '<i class="bx bx-check me-2"></i>' + mensaje;
    toast.style.cssText = 'position:fixed;top:20px;right:20px;z-index:99999;padding:12px 20px;border-radius:8px;color:#fff;font-size:14px;font-weight:500;box-shadow:0 4px 12px rgba(0,0,0,0.15);opacity:0;transition:opacity 0.3s ease;' + (tipo === 'success' ? 'background:#28a745;' : 'background:#dc3545;');
    document.body.appendChild(toast);
    setTimeout(() => toast.style.opacity = '1', 10);
    setTimeout(() => {
      toast.style.opacity = '0';
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }

  // ==================== SESSION LOADERS ====================

  function obtenerCashRegisterLogId() {
    if (cashRegisterId) {
      $.ajax({
        url: `log/${cashRegisterId}`, type: 'GET',
        success: function (response) {
          cashRegisterLogId = response.cash_register_log_id;
          sessionStoreId = response.store_id;
        },
        error: function (xhr) { mostrarError('Error al obtener el ID de cash register log: ' + xhr.responseText); }
      });
    } else {
      console.error('ID de caja registradora no definido');
    }
  }

  function loadCartFromSession() {
    $.ajax({
      url: `cart`, type: 'GET', dataType: 'json',
      success: function (response) {
        cart = Array.isArray(response.cart) ? response.cart : [];
        updateCheckoutCart();
      },
      error: function (xhr) { mostrarError('Error al cargar el carrito desde la sesión: ' + xhr.responseText); }
    });
  }

  function loadClientFromSession() {
    $.ajax({
      url: `client-session`, type: 'GET', dataType: 'json',
      success: function (response) {
        client = response.client;
        if (client && client.id) {
          showClientInfo(client);
          $('#client-selection-container').hide();
        }
      },
      error: function (xhr) { mostrarError('Error al cargar el cliente desde la sesión: ' + xhr.responseText); }
    });
  }

  function loadStoreIdFromSession() {
    $.ajax({
      url: `storeid-session`, type: 'GET', dataType: 'json',
      success: function (response) {
        const id = response.id;
        if (id != null && id !== '' && !Array.isArray(id)) sessionStoreId = id;
      },
      error: function (xhr) { mostrarError('Error al cargar store id: ' + xhr.responseText); }
    });
  }

  // ==================== CLIENT ====================

  function showClientInfo(clientData) {
    client = clientData;
    const clientType = client.type === 'company' ? 'Empresa' : 'Persona';
    const clientDocLabel = client.type === 'company' ? 'RUT' : 'CI';
    const clientDoc = client.type === 'company' ? client.rut : client.ci;
    const fullName = `${client.name || ''} ${client.lastname || ''}`.trim() || '-';

    // Avatar initials
    const initials = fullName.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
    $('#client-avatar').text(initials);
    $('#client-id').val(client.id || '');
    $('#client-name').text(fullName);
    $('#client-type-label').text(clientType);
    $('#client-doc-label').text(clientDocLabel);
    $('#client-doc').text(clientDoc || 'No disponible');

    if (client.type === 'company' && client.company_name) {
      $('#client-company-name').text(client.company_name);
      $('#client-company').show();
    } else {
      $('#client-company').hide();
    }

    $('#client-info').show();
    $('#client-selection-container').hide();
  }

  function saveCartToSession() {
    return $.ajax({
      url: 'cart', type: 'POST',
      data: { _token: $('meta[name="csrf-token"]').attr('content'), cart: cart }
    }).fail(function (xhr) { mostrarError('Error al guardar el carrito en la sesión: ' + xhr.responseText); });
  }

  function saveClientToSession(clientData) {
    return $.ajax({
      url: 'client-session', type: 'POST',
      data: { _token: $('meta[name="csrf-token"]').attr('content'), client: clientData }
    }).fail(function (xhr) { mostrarError('Error al guardar el cliente en la sesión: ' + xhr.responseText); });
  }

  // ==================== CART RENDERING ====================

  function updateCheckoutCart() {
    const container = $('#cart-items');
    const emptyState = $('#cart-empty');
    let subtotal = 0;

    if (!Array.isArray(cart) || cart.length === 0) {
      container.empty();
      emptyState.show();
      $('#cart-count').text('0');
      calcularTotal();
      return;
    }

    emptyState.hide();
    let html = '';

    cart.forEach((item, index) => {
      const itemTotal = item.price * item.quantity;
      subtotal += itemTotal;
      const fmtPrice = formatCurrency(item.price);
      const fmtTotal = formatCurrency(itemTotal);

      html += `
        <div class="cart-item" data-index="${index}">
          <img src="${baseUrl}${item.image}" alt="${item.name}" class="cart-item-img"
               onerror="this.src='${baseUrl}assets/img/product-placeholder.png'">
          <div class="cart-item-info">
            <div class="cart-item-name">${item.name}</div>
            <div class="cart-item-price">${currencySymbol}${fmtPrice} c/u</div>
          </div>
          <div class="qty-control">
            <button class="qty-btn qty-dec" data-index="${index}" type="button">
              <i class="bx bx-minus"></i>
            </button>
            <span class="qty-value">${item.quantity}</span>
            <button class="qty-btn qty-inc" data-index="${index}" type="button">
              <i class="bx bx-plus"></i>
            </button>
          </div>
          <div class="cart-item-total">${currencySymbol}${fmtTotal}</div>
          <button class="cart-item-remove" data-index="${index}" type="button" title="Eliminar">
            <i class="bx bx-trash" style="font-size:1.2rem;"></i>
          </button>
        </div>`;
    });

    container.html(html);

    const totalQty = cart.reduce((sum, item) => sum + item.quantity, 0);
    $('#cart-count').text(totalQty);

    calcularTotal();
  }

  function formatCurrency(value) {
    return (Math.round(value * 100) / 100).toLocaleString('es-ES', { minimumFractionDigits: 2 });
  }

  // ==================== CART MODIFICATION (QUANTITY + REMOVE) ====================

  $(document).on('click', '.qty-inc', function () {
    const index = $(this).data('index');
    if (cart[index]) {
      cart[index].quantity++;
      updateCheckoutCart();
      saveCartToSession();
    }
  });

  $(document).on('click', '.qty-dec', function () {
    const index = $(this).data('index');
    if (cart[index]) {
      if (cart[index].quantity > 1) {
        cart[index].quantity--;
      } else {
        cart.splice(index, 1);
      }
      updateCheckoutCart();
      saveCartToSession();
    }
  });

  $(document).on('click', '.cart-item-remove', function () {
    const index = $(this).data('index');
    if (cart[index]) {
      const name = cart[index].name;
      cart.splice(index, 1);
      updateCheckoutCart();
      saveCartToSession();
      mostrarToast(`"${name}" eliminado del carrito`, 'success');
    }
  });

  // ==================== TOTALS ====================

  function calcularTotal() {
    let subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
    let total = subtotal - discount;
    if (total < 0) total = 0;

    subtotal = Math.round(subtotal * 100) / 100;
    total = Math.round(total * 100) / 100;
    discount = Math.round(discount * 100) / 100;

    const fmtSubtotal = `${currencySymbol}${subtotal.toFixed(2)}`;
    const fmtTotal = `${currencySymbol}${total.toFixed(2)}`;
    const fmtDiscount = `-${currencySymbol}${discount.toFixed(2)}`;

    $('.subtotal').text(fmtSubtotal);
    $('.total').text(fmtTotal);
    $('.discount-amount').text(fmtDiscount);
  }

  // ==================== DISCOUNTS ====================

  // Discount type toggle buttons
  $(document).on('click', '.discount-type-btn', function () {
    $('.discount-type-btn').removeClass('active');
    $(this).addClass('active');
    $('#discount-type-value').val($(this).data('type'));
  });

  $('#apply-coupon-btn').on('click', function () {
    aplicarDescuento();
  });

  $('#apply-fixed-btn').on('click', function () {
    aplicarDescuentoFijo();
  });

  function aplicarDescuento() {
    const couponCode = $('#coupon-code').val();
    if (!couponCode) { removeDiscount(); return; }

    $.ajax({
      url: `${baseUrl}admin/get-coupon/${couponCode}`, type: 'GET',
      success: function (response) {
        if (response) { aplicarDescuentoPorCupon(response); }
        else { mostrarError('Cupón no válido o no encontrado.'); }
      },
      error: function () { mostrarError('Error al aplicar el cupón.'); }
    });
  }

  function aplicarDescuentoPorCupon(couponResponse) {
    coupon = couponResponse;
    let subtotal = 0;
    let excludedProducts = (coupon.coupon.excluded_products || []).map(id => id.toString());
    let currentDate = new Date().toISOString().split('T')[0];

    let initDate = coupon.coupon.init_date;
    let dueDate = coupon.coupon.due_date;

    if (initDate && currentDate < initDate) { mostrarError('Este cupón aún no está activo.'); return; }
    if (dueDate && currentDate > dueDate) { mostrarError('Este cupón ha expirado.'); return; }

    let filteredCart = cart.filter(item => !excludedProducts.includes(item.id.toString()));

    filteredCart.forEach(item => { subtotal += item.price * item.quantity; });

    if (subtotal <= 0) { mostrarError('Este cupón no aplica a los productos seleccionados.'); return; }

    if (coupon.coupon.type === 'percentage') {
      discount = (coupon.coupon.amount / 100) * subtotal;
    } else if (coupon.coupon.type === 'fixed') {
      discount = coupon.coupon.amount;
    }

    if (discount > subtotal) discount = subtotal;
    discount = Math.round(discount * 100) / 100;

    calcularTotal();
    $('#quitarDescuento').show();
  }

  function aplicarDescuentoFijo() {
    const discountType = $('#discount-type-value').val();
    const discountValue = parseFloat($('#fixed-discount').val());

    if (!discountValue || isNaN(discountValue) || discountValue <= 0) {
      mostrarError('Por favor, ingrese un valor de descuento válido.');
      return;
    }

    let subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);

    if (discountType === 'percentage') {
      discount = (discountValue / 100) * subtotal;
    } else {
      discount = discountValue;
    }

    if (discount > subtotal) discount = subtotal;
    discount = Math.round(discount * 100) / 100;

    calcularTotal();
    $('#quitarDescuento').show();
  }

  function removeDiscount() {
    discount = 0;
    coupon = null;
    $('#coupon-code').val('');
    $('#fixed-discount').val('');
    calcularTotal();
    $('#quitarDescuento').hide();
    ocultarError();
  }

  $('#quitarDescuento').on('click', function () { removeDiscount(); });

  // ==================== CLIENTS ====================

  function loadClients() {
    $.ajax({
      url: 'clients/json', type: 'GET', dataType: 'json',
      success: function (response) {
        const clients = response.clients;
        const clientCount = response.count;
        if (clientCount > 0) $('#search-client-container').show();
        else $('#search-client-container').hide();
        displayClients(clients);
      },
      error: function (xhr) { mostrarError('Error al obtener los clientes: ' + xhr.responseText); }
    });
  }

  function displayClients(clients) {
    const clientList = $('#client-list');
    clientList.empty();

    clients.forEach(c => {
      const clientType = c.type === 'company' ? 'Empresa' : 'Persona';
      const clientDoc = c.type === 'company' ? c.rut : c.ci;
      const clientDocLabel = c.type === 'company' ? 'RUT' : 'CI';
      const displayName = c.type === 'company'
        ? c.company_name || '-'
        : `${c.name || '-'} ${c.lastname || '-'}`;

      const razonSocial = c.type === 'company' ? `<p class="client-info mb-0"><small><strong>Razón Social:</strong> ${c.company_name || '-'}</small></p>` : '';

      const clientCard = `
        <div class="client-card card mb-2 border-0 shadow-sm">
          <div class="card-body d-flex justify-content-between align-items-center p-2">
            <div class="client-details">
              <h6 class="card-title mb-1">${displayName}</h6>
              ${razonSocial}
              <p class="client-info mb-0"><small><strong>${clientDocLabel}:</strong> ${clientDoc || 'No disponible'}</small></p>
            </div>
            <button class="btn btn-primary btn-sm btn-select-client" data-client='${JSON.stringify(c)}'>Seleccionar</button>
          </div>
        </div>`;

      clientList.append(clientCard);
    });

    $('.btn-select-client').on('click', function () {
      const selectedClient = $(this).data('client');
      showClientInfo(selectedClient);
      saveClientToSession(selectedClient).done(function () { loadClientFromSession(); });
    });
  }

  $('#search-client').on('input', function () {
    const searchText = $(this).val().toLowerCase();
    $('#client-list .client-card').each(function () {
      const text = $(this).text().toLowerCase();
      $(this).toggleClass('d-none', !text.includes(searchText));
    });
  });

  $('#offcanvasEnd').on('show.bs.offcanvas', function () { loadClients(); });

  // Client type toggle
  document.getElementById('tipoCliente').addEventListener('change', function () {
    let tipo = this.value;
    document.getElementById('ciField').style.display = tipo === 'individual' ? 'block' : 'none';
    document.getElementById('rutField').style.display = tipo === 'company' ? 'block' : 'none';
    document.getElementById('razonSocialField').style.display = tipo === 'company' ? 'block' : 'none';

    const nombreAsterisk = document.querySelector('label[for="nombreCliente"] .text-danger');
    const apellidoAsterisk = document.querySelector('label[for="apellidoCliente"] .text-danger');
    if (nombreAsterisk) nombreAsterisk.style.display = tipo === 'individual' ? 'inline' : 'none';
    if (apellidoAsterisk) apellidoAsterisk.style.display = tipo === 'individual' ? 'inline' : 'none';
  });

  // Save new client
  document.getElementById('guardarCliente').addEventListener('click', function () {
    const nombre = document.getElementById('nombreCliente');
    const apellido = document.getElementById('apellidoCliente');
    const tipo = document.getElementById('tipoCliente');
    const email = document.getElementById('emailCliente');
    const ci = document.getElementById('ciCliente');
    const rut = document.getElementById('rutCliente');
    const direccion = document.getElementById('direccionCliente');
    const razonSocial = document.getElementById('razonSocialCliente');

    let hasError = false;
    clearErrors();

    if (tipo.value.trim() === '') { showError(tipo, 'Este campo es obligatorio'); hasError = true; }

    if (tipo.value === 'individual') {
      if (nombre.value.trim() === '') { showError(nombre, 'El nombre es obligatorio'); hasError = true; }
      if (apellido.value.trim() === '') { showError(apellido, 'El apellido es obligatorio'); hasError = true; }
    }

    if (tipo.value === 'company') {
      if (rut.value.trim() === '') { showError(rut, 'Este campo es obligatorio'); hasError = true; }
      if (razonSocial.value.trim() === '') { showError(razonSocial, 'Este campo es obligatorio'); hasError = true; }
    }

    if (hasError) return;

    let data = {
      store_id: sessionStoreId,
      name: nombre.value.trim(),
      lastname: apellido.value.trim(),
      type: tipo.value,
      email: email.value.trim(),
      address: direccion.value.trim()
    };

    if (tipo.value === 'individual') {
      data.ci = ci.value.trim();
    } else if (tipo.value === 'company') {
      data.rut = rut.value.trim();
      data.company_name = razonSocial.value.trim();
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
      .then(responseData => {
        let offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('crearClienteOffcanvas'));
        offcanvas.hide();
        document.getElementById('formCrearCliente').reset();

        if (responseData.success && responseData.client) {
          client = responseData.client;
          showClientInfo(client);
          saveClientToSession(client);
        }
        mostrarToast('Cliente creado correctamente');
      })
      .catch(error => { mostrarError('Error al guardar el cliente: ' + error); });
  });

  function showError(input, message) {
    const errorElement = document.createElement('small');
    errorElement.className = 'text-danger';
    errorElement.innerText = message;
    input.parentElement.appendChild(errorElement);
  }

  function clearErrors() {
    document.querySelectorAll('#formCrearCliente .text-danger:not(span)').forEach(el => el.remove());
  }

  $('#deselect-client').on('click', function () { deselectClient(); });

  function deselectClient() {
    client = [];
    saveClientToSession(client);
    $('#client-info').hide();
    $('#client-selection-container').show();
  }

  // ==================== INIT ====================

  loadCartFromSession();
  loadClientFromSession();
  obtenerCashRegisterLogId();
  loadStoreIdFromSession();

  // ==================== ORDER SUBMISSION ====================

  function postOrder() {
    ocultarError();

    const paymentMethod = $('input[name="paymentMethod"]:checked').attr('id');
    const shippingStatus = $('#shippingStatus').val();
    let cashSales = 0;
    let posSales = 0;

    const total = parseFloat($('.total').first().text().replace(/[^\d.-]/g, '')) || 0;
    const subtotal = parseFloat($('.subtotal').first().text().replace(/[^\d.-]/g, '')) || 0;

    if (total > 25000 && (!client || !client.id)) {
      mostrarError('Para ventas mayores a $25000, es necesario tener un cliente asignado a la venta.');
      setFinalizarBtnsDisabled(false);
      return;
    }

    if (paymentMethod === 'cash') { cashSales = total; }
    else { posSales = total; }

    if (paymentMethod === 'internalCredit') {
      if (!client || !client.id) {
        mostrarError('Para ventas con crédito interno, es necesario tener un cliente asignado.');
        setFinalizarBtnsDisabled(false);
        return;
      }
    }

    let docType = null;
    let doc = null;
    if (client && client.id) {
      if (client.type === 'company') { docType = 2; doc = client.rut; }
      else { docType = 3; doc = client.ci ? client.ci : '00000000'; }
    } else {
      docType = 3; doc = '00000000';
    }

    const orderData = {
      date: new Date().toISOString().split('T')[0],
      hour: new Date().toLocaleTimeString('it-IT'),
      cash_register_log_id: cashRegisterLogId,
      cash_sales: cashSales,
      pos_sales: posSales,
      discount: discount,
      client_id: client && client.id ? client.id : null,
      client_type: client && client.type ? client.type : 'no-client',
      products: JSON.stringify(cart.map(item => ({
        id: item.id, name: item.name, price: item.price,
        quantity: item.quantity, image: item.image ?? '',
        is_composite: item.isComposite || false
      }))),
      subtotal: subtotal,
      total: total - discount,
      notes: $('#orderNotes').val() || '',
      store_id: sessionStoreId,
      shipping_status: shippingStatus,
    };

    $.ajax({
      url: `${baseUrl}admin/pos-orders`, type: 'POST',
      data: { _token: $('meta[name="csrf-token"]').attr('content'), ...orderData },
      success: function (response) {
        const isClientValid = client && Object.keys(client).length > 0;
        const ordersData = {
          date: orderData.date, time: orderData.hour, origin: 'physical',
          client_id: orderData.client_id, store_id: sessionStoreId,
          products: orderData.products, subtotal: orderData.subtotal,
          tax: 0, shipping: 0,
          coupon_id: coupon ? coupon.coupon.id : null,
          coupon_amount: coupon ? coupon.coupon.amount : 0,
          discount: orderData.discount, total: orderData.total,
          estimate_id: null, shipping_id: null,
          payment_status: 'paid', shipping_status: orderData.shipping_status,
          payment_method: paymentMethod, shipping_method: 'standard',
          preference_id: null, shipping_tracking: null, is_billed: 0,
          doc_type: docType, document: doc,
          name: isClientValid && client.name ? client.name : null,
          lastname: isClientValid && client.lastname ? client.lastname : null,
          address: isClientValid && client.address ? client.address : null,
          phone: isClientValid && client.phone ? client.phone : null,
          email: isClientValid && client.email ? client.email : null,
          cash_register_log_id: cashRegisterLogId
        };

        $.ajax({
          url: `${baseUrl}admin/orders`, type: 'POST',
          data: { _token: $('meta[name="csrf-token"]').attr('content'), ...ordersData },
          success: function (response) {
            if (response.auto_print_ticket && response.is_billed && response.invoice_id) {
              const printUrl = `${baseUrl}admin/invoices/print80mm/${response.invoice_id}`;
              const printWindow = window.open(printUrl, 'print_ticket', 'width=350,height=600,left=100,top=100');
              if (printWindow) { printWindow.onload = function () { printWindow.print(); }; }
            }

            clearCartAndClient().then(() => {
              return Swal.fire({
                customClass: {
                  popup: 'swal-popup', title: 'swal-title', content: 'swal-content',
                  confirmButton: 'btn btn-outline-primary', cancelButton: 'btn btn-outline-danger'
                },
                title: 'Venta Realizada con Éxito',
                text: response.is_billed ? 'Venta facturada correctamente.' : 'La venta se ha realizado exitosamente.',
                icon: 'success',
                showCancelButton: userHasPermission('access_orders'),
                confirmButtonText: userHasPermission('access_orders') ? 'Ver Venta' : 'Cerrar',
                cancelButtonText: 'Cerrar',
                timer: 5000, timerProgressBar: true,
                didOpen: (toast) => {
                  toast.addEventListener('mouseenter', Swal.stopTimer);
                  toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
              });
            }).then(result => {
              if (result.isConfirmed && userHasPermission('access_orders')) {
                clearCartAndClient().then(() => { window.location.href = `${baseUrl}admin/orders/${response.order_uuid}/show`; })
                  .catch(error => { console.error('Error:', error); mostrarError(error); });
              } else {
                clearCartAndClient().then(() => { window.location.href = frontRoute; })
                  .catch(error => { console.error('Error:', error); mostrarError(error); });
              }
            }).catch(error => { console.error('Error:', error); mostrarError(error); });
          },
          error: function (xhr) {
            console.error('Error al guardar la orden:', xhr.responseText);
            mostrarError('Error al guardar la orden: ' + xhr.responseText);
            setFinalizarBtnsDisabled(false);
          }
        });
      },
      error: function (xhr) {
        console.error('Error al guardar en pos-orders:', xhr);
        if (xhr.responseJSON && xhr.responseJSON.errors) {
          const errores = xhr.responseJSON.errors;
          let mensajes = '';
          for (const campo in errores) { mensajes += `${errores[campo].join(', ')}<br>`; }
          mostrarError(mensajes);
        } else {
          mostrarError(xhr.responseJSON ? xhr.responseJSON.error : 'Error desconocido');
        }
        setFinalizarBtnsDisabled(false);
      }
    });
  }

  function clearCartAndClient() {
    return new Promise((resolve, reject) => {
      try {
        cart = [];
        saveCartToSession();
        client = null;
        saveClientToSession(client);
        resolve();
      } catch (error) {
        reject('Error al limpiar el carrito y cliente.');
      }
    });
  }

  // ==================== PAYMENT METHOD ====================

  function toggleCashDetails() {
    const paymentMethod = $('input[name="paymentMethod"]:checked').attr('id');
    if (paymentMethod === 'cash') { $('#cashDetails').show(); }
    else { $('#cashDetails').hide(); }
  }

  $('input[name="paymentMethod"]').on('change', toggleCashDetails);
  toggleCashDetails();

  // ==================== FINALIZE ====================

  function setFinalizarBtnsDisabled(disabled) {
    $('#finalizarVentaBtn, #finalizarVentaMobileBtn').prop('disabled', disabled);
  }

  function handleFinalize() {
    const paymentMethod = $('input[name="paymentMethod"]:checked').attr('id');
    if (!paymentMethod) {
      mostrarError('Por favor, seleccione un método de pago.');
      return;
    }
    setFinalizarBtnsDisabled(true);
    postOrder();
    /** Descomentar para usar el POS
    if (paymentMethod !== 'cash') {
      obtenerTokenPos().done(function (response) {
        const token = response.access_token;
        enviarTransaccionPos(token);
      }).fail(function (error) {
        console.error('Error al obtener el token del POS:', error);
      });
    } else {
      postOrder();
    }
    */
  }

  $('#finalizarVentaBtn').on('click', handleFinalize);
  $('#finalizarVentaMobileBtn').on('click', handleFinalize);

  $('#descartarVentaBtn').on('click', function () {
    client = [];
    saveClientToSession(client);
    cart = [];
    saveCartToSession();
    updateCheckoutCart();
  });

  function calcularVuelto() {
    var valorRecibido = parseFloat($('#valorRecibido').val()) || 0;
    var total = parseFloat($('.total').first().text().replace(/[^\d.-]/g, '')) || 0;
    var vuelto = valorRecibido - total;

    if (valorRecibido < total) { $('#mensajeError').removeClass('d-none'); }
    else { $('#mensajeError').addClass('d-none'); }

    var formattedVuelto = vuelto.toLocaleString('es-ES', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    $('#vuelto').text(`${currencySymbol}${formattedVuelto}`);
  }

  // ==================== DISCOUNT COLLAPSE ICON ====================
  $('#discountCollapse').on('show.bs.collapse', function () {
    $('.discount-toggle').removeClass('collapsed');
  });
  $('#discountCollapse').on('hide.bs.collapse', function () {
    $('.discount-toggle').addClass('collapsed');
  });
});
