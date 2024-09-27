$(document).ready(function () {
  const $transactionSpinner = $('#transaction-spinner');
  let cart = [];
  const baseUrl = window.baseUrl || '';
  const frontRoute = window.frontRoute || '';
  let client = [];
  const cashRegisterId = window.cashRegisterId;
  let cashRegisterLogId = null;
  let posResponsesConfig = {};

  // Cargar la configuración de respuestas Scanntech desde el backend
  function loadScanntechResponses() {
      $.ajax({
          url: '/api/scanntech/scanntech_responses', // Ruta definida en Laravel
          type: 'GET',
          dataType: 'json',
          success: function (response) {
              // Almacenar la configuración en la variable global
              posResponsesConfig = response;
              console.log('Configuración de respuestas cargada:', posResponsesConfig);
          },
          error: function (xhr, status, error) {
              console.error('Error al cargar la configuración de respuestas:', error);
          }
      });
  }

  // Llama a la función para cargar la configuración al inicio
  loadScanntechResponses();

  function obtenerCashRegisterLogId() {
      if (cashRegisterId) {
          $.ajax({
              url: `log/${cashRegisterId}`,
              type: 'GET',
              success: function (response) {
                  cashRegisterLogId = response.cash_register_log_id;
              },
              error: function (xhr, status, error) {
                  alert('Error al obtener el ID de cash register log: ' + xhr.responseText);
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
          error: function (xhr, status, error) {
              console.error('Error al cargar el carrito desde la sesión:', error);
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
              }
          },
          error: function (xhr, status, error) {
              console.error('Error al cargar el cliente desde la sesión:', error);
          }
      });
  }

  function showClientInfo(client) {
      $('#client-id').text(client.id);
      $('#client-name').text(client.name);
      $('#client-ci').text(client.ci);
      $('#client-rut').text(client.rut);
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
      }).done(function (response) {
          console.log('Carrito guardado en la sesión:', response);
      }).fail(function (error) {
          console.error('Error al guardar el carrito en la sesión:', error);
      });
  }

  function updateCheckoutCart() {
      let cartHtml = '';
      let subtotal = 0;

      if (!Array.isArray(cart)) {
          console.error('El carrito no es un array:', cart);
          return;
      }

      cart.forEach(item => {
          const itemTotal = item.price * item.quantity;
          subtotal += itemTotal;

          cartHtml += `
              <li class="list-group-item d-flex justify-content-between align-items-center">
                  <div class="d-flex align-items-center">
                      <img src="${baseUrl}${item.image}" alt="${item.name}" class="img-thumbnail me-2" style="width: 50px;">
                      <div>
                          <h6 class="mb-0">${item.name}</h6>
                          <small class="text-muted">Cantidad: ${item.quantity}</small>
                      </div>
                  </div>
                  <span>$${itemTotal.toFixed(2)}</span>
              </li>
          `;
      });

      const total = subtotal;

      $('.list-group-flush').html(cartHtml);

      $('.subtotal').text(`$${subtotal.toFixed(2)}`);
      $('.total').text(`$${total.toFixed(2)}`);
  }

  // Función para cargar clientes
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
          error: function (xhr, status, error) {
              console.error('Error al obtener los clientes:', error);
          }
      });
  }

  function displayClients(clients) {
      const clientList = $('#client-list');
      clientList.empty();
      clients.forEach(client => {
          const clientItem = `
              <li class="list-group-item d-flex justify-content-between align-items-center client-item"
                  data-name="${String(client.name).toLowerCase()}"
                  data-ci="${String(client.ci).toLowerCase()}"
                  data-rut="${String(client.rut).toLowerCase()}">
                  <div>
                      ${client.name}, CI: ${client.ci}, RUT: ${client.rut}
                  </div>
                  <button class="btn btn-primary btn-sm add-client" data-client='${JSON.stringify(client)}'>+</button>
              </li>
          `;
          clientList.append(clientItem);
      });

      $('.add-client').on('click', function () {
          const client = $(this).data('client');
          showClientInfo(client);

          saveClientToSession(client).done(function () {
              loadClientFromSession();
          }).fail(function (error) {
              console.error('Error al guardar el cliente en la sesión:', error);
          });
      });
  }

  // Filtrar clientes por búsqueda
  $('#search-client').on('input', function () {
      const searchText = $(this).val().toLowerCase();
      $('#client-list li').each(function () {
          const name = $(this).data('name').toString().toLowerCase();
          const ci = $(this).data('ci').toString().toLowerCase();
          const rut = $(this).data('rut').toString().toLowerCase();

          if (name.includes(searchText) || ci.includes(searchText) || rut.includes(searchText)) {
              $(this).show();
          } else {
              $(this).hide();
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
      }).done(function (response) {
          console.log('Cliente guardado en la sesión:', response);
      }).fail(function (error) {
          console.error('Error al guardar el cliente en la sesión:', error);
      });
  }

  // Cargar clientes al abrir el offcanvas
  $('#offcanvasEnd').on('show.bs.offcanvas', function () {
      loadClients();
  });

  //Guardar cliente en base de datos
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

  document.getElementById('tipoCliente').addEventListener('change', function () {
      let tipo = this.value;
      if (tipo === 'individual') {
          document.getElementById('ciField').style.display = 'block';
          document.getElementById('rutField').style.display = 'none';
      } else if (tipo === 'company') {
          document.getElementById('ciField').style.display = 'none';
          document.getElementById('rutField').style.display = 'block';
      }
  });

  //Si hay un cliente seleccionado, que permita deseleccionarlo.
  $('#deselect-client').on('click', function () {
      deselectClient();
  });

  function deselectClient() {
      client = [];
      saveClientToSession(client);

      $('#client-id').text('');
      $('#client-name').text('');
      $('#client-ci').text('');
      $('#client-rut').text('');
      $('#client-info').hide();
      $('#client-selection-container').show();
  }

  loadCartFromSession();
  loadClientFromSession();
  obtenerCashRegisterLogId();

  function obtenerTokenScanntech() {
      return $.ajax({
          url: `${baseUrl}api/scanntech/token`,
          type: 'GET',
          success: function (response) {
              console.log('Access token obtenido:', response.access_token);
          },
          error: function (xhr, status, error) {
              console.error('Error al obtener el token de Scanntech:', error);
          }
      });
  }

  function enviarTransaccionScanntech(token) {
      // Ajusta estos valores según la estructura de tu sistema y los datos disponibles en tu interfaz
      const posID = $('#posID').val() || "7"; // Ejemplo: Obtener PosID desde un campo oculto o asignar un valor por defecto
      const empresa = $('#empresa').val() || "2024"; // Ejemplo: Obtener Empresa desde un campo o asignar un valor por defecto
      const local = $('#local').val() || "1";
      const caja = $('#caja').val() || "7";
      const userId = $('#userId').val() || "Usuario1";
      // Generar la fecha y hora actual en el formato yyyyMMddHHmmssSSS
      const now = new Date();
      const transactionDateTime = now.getFullYear().toString() +
          String(now.getMonth() + 1).padStart(2, '0') + // Mes actual (0 indexado, por eso se suma 1)
          String(now.getDate()).padStart(2, '0') + // Día del mes
          String(now.getHours()).padStart(2, '0') + // Hora en formato de 24 horas
          String(now.getMinutes()).padStart(2, '0') + // Minutos
          String(now.getSeconds()).padStart(2, '0');
      console.log('Fecha del pago:', transactionDateTime);

      // Extrae los valores de la compra real desde tu interfaz
      const amount = parseFloat($('.total').text().replace('$', '')); // Total de la compra
      const quotas = 1.5; // Ajusta este valor según la lógica de cuotas
      const plan = 1; // Ajusta según el plan que manejes
      const currency = "858"; // Ajusta según el tipo de moneda
      const taxableAmount = amount; // Ajusta si es necesario
      const invoiceAmount = amount; // Ajusta si es necesario
      const taxAmount = amount * 2; // Ajusta según los impuestos aplicables
      const ivaAmount = amount * 2; // Ajusta según los impuestos aplicables
      const needToReadCard = false; // Ajusta según si es necesario leer tarjeta

      const transactionData = {
          PosID: posID,
          Empresa: empresa,
          Local: local,
          Caja: caja,
          UserId: userId,
          TransactionDateTimeyyyyMMddHHmmssSSS: transactionDateTime,
          Amount: amount.toString() + "00", // Convertir a string sin punto decimal
          Quotas: quotas,
          Plan: plan,
          Currency: currency,
          TaxableAmount: taxableAmount.toString() + "00",
          InvoiceAmount: invoiceAmount.toString() + "00",
          TaxAmount: taxAmount.toString() + "00",
          IVAAmount: ivaAmount.toString() + "00",
          NeedToReadCard: needToReadCard
      };
      console.log('Datos de la transacción:', transactionData);

      // Realiza la solicitud AJAX para enviar los datos al backend
      $.ajax({
          url: `${baseUrl}api/scanntech/purchase`,
          type: 'POST',
          headers: {
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${token}`
          },
          data: JSON.stringify(transactionData),
          success: function (response) {
              console.log('Respuesta de la transacción:', response);

              // Extrae correctamente el TransactionId
              const transactionId = response.TransactionId;
              const sTransactionId = response.STransactionId;

              if (transactionId) {
                  showTransactionStatus('Transacción enviada correctamente, consultando estado...');
                  consultarEstadoTransaccion(transactionId, sTransactionId, transactionDateTime);
              } else {
                  showTransactionStatus('No se recibió un transactionId válido.', true);
              }
          },
          error: function (xhr, status, error) {
              showTransactionStatus(`Error al enviar la transacción: ${xhr.responseText}`, true);
              console.error('Error al enviar la transacción a Scanntech:', error);
          }
      });
  }

  function consultarEstadoTransaccion(transactionId, sTransactionId, transactionDateTime, token) {
      let attempts = 0;
      const maxAttempts = 30;

      function poll() {
          if (attempts >= maxAttempts) {
              showTransactionStatus('Tiempo de espera excedido al consultar el estado de la transacción.', true);
              return;
          }

          setTimeout(function () {
              attempts++;

              const dataToSend = {
                  PosID: $('#posID').val() || "7",
                  Empresa: $('#empresa').val() || "2024",
                  Local: $('#local').val() || "1",
                  Caja: $('#caja').val() || "7",
                  UserId: $('#userId').val() || "Usuario1",
                  TransactionDateTimeyyyyMMddHHmmssSSS: transactionDateTime,
                  TransactionId: transactionId,
                  STransactionId: sTransactionId
              };

              if (attempts === 1) {
                  showTransactionStatus('Transacción en progreso...', false, true);
              }

              $.ajax({
                  url: `${baseUrl}api/scanntech/transaction-state`,
                  type: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                      'Authorization': `Bearer ${token}`
                  },
                  data: JSON.stringify(dataToSend),
                  success: function (response) {
                      const responseCode = response.responseCode;
                      showTransactionStatus(responseCode, false, false);

                      if (responseCode === 10 || responseCode === 113 || responseCode === 12) {
                          poll();
                      } else if (responseCode === 111) {
                          postOrder();
                      }
                  },
                  error: function (xhr) {
                      showTransactionStatus(`Error al consultar el estado: ${xhr.status} - ${xhr.responseText}`, true);
                  }
              });
          }, 500);
      }

      poll();
  }

  // Función principal para procesar la orden
  function postOrder() {
      const paymentMethod = $('input[name="paymentMethod"]:checked').attr('id');
      let cashSales = 0;
      let posSales = 0;

      if (paymentMethod === 'cash') {
          cashSales = parseInt($('.total').text().replace('$', ''));
      } else {
          posSales = parseInt($('.total').text().replace('$', ''));

          // Obtener el token y enviar la transacción a Scanntech
          obtenerTokenScanntech().done(function (response) {
              const token = response.access_token; // Usar el token obtenido
              enviarTransaccionScanntech(token); // Enviar la transacción usando el token obtenido
          }).fail(function (error) {
              console.error('Error al obtener el token de Scanntech:', error);
          });
      }

      const orderData = {
          date: new Date().toISOString().split('T')[0],
          hour: new Date().toLocaleTimeString('it-IT'),
          cash_register_log_id: cashRegisterLogId,
          cash_sales: cashSales,
          pos_sales: posSales,
          discount: 0,
          client_id: client ? client.id : null,
          client_type: client ? client.type : 'individual',
          products: JSON.stringify(cart),
          subtotal: parseInt($('.subtotal').text().replace('$', '')),
          total: parseInt($('.total').text().replace('$', '')),
          notes: $('textarea').val() || ''
      };

      $.ajax({
          url: `${baseUrl}admin/pos-orders`,
          type: 'POST',
          data: {
              _token: $('meta[name="csrf-token"]').attr('content'),
              ...orderData
          },
          success: function (response) {
              cart = [];
              saveCartToSession().done(function () {
                  updateCheckoutCart();
                  client = [];
                  saveClientToSession(client).done(function () {
                      // window.location.href = frontRoute; // Recargar la página luego de la venta
                  }).fail(function (error) {
                      console.error('Error al guardar el cliente en la sesión:', error);
                  });
              }).fail(function (error) {
                  console.error('Error al guardar el carrito en la sesión:', error);
              });
          },
          error: function (xhr, status, error) {
              console.error('Error al guardar la orden:', error);
          }
      });
  }

  $('.btn-success').on('click', function () {
      postOrder();
  });

  $('#descartarVentaBtn').on('click', function (event) {
      client = [];
      saveClientToSession(client);
      cart = [];
      saveCartToSession();
      updateCheckoutCart();
  });

  $('#valorRecibido').on('input', function () {
      var valorRecibido = parseFloat($(this).val()) || 0;
      var total = parseFloat($('.total').text().replace('$', '')) || 0;
      var vuelto = valorRecibido - total;

      if (valorRecibido < total) {
          $('#mensajeError').removeClass('d-none');
      } else {
          $('#mensajeError').addClass('d-none');
      }
      $('#vuelto').text(vuelto.toFixed(2));
  });

  let swalInstance;

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
                      window.history.back();
                  });
              }
          }
      }
  }
});
