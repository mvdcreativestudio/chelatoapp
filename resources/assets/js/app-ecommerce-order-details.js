$(function () {
  // Variable declaration for table
  var dt_details_table = $('.datatables-order-details');
  var products = window.orderProducts;
  var currencySymbol = window.currencySymbol;

  // E-commerce Products datatable
  if (dt_details_table.length) {
    var dt_products = dt_details_table.DataTable({
      data: products,
      columns: [
        {
          // Imagen del producto
          data: 'image',
          render: function (data, type, full, meta) {
            var imagePath = `${baseUrl}${data}`;
            return `
              <img src="${imagePath}"
                   onerror="this.onerror=null; this.src='${baseUrl}admin/default-image.png';"
                   style="width: 70px; height: 70px; object-fit: cover; border-radius: 10px;" />
            `;
          }
        },
        {
          // Nombre del producto con variaciones
          data: 'name',
          render: function (data, type, row, meta) {
            var flavors = row.flavors ? '<br><small>' + row.flavors + '</small>' : '';
            return '<span>' + data + flavors + '</span>';
          }
        },
        {
          // Precio unitario sin IVA
          data: 'base_price',
          render: function (data, type, row) {
            const currencySymbol = row.currency === 'Dólar' ? 'USD' : 'UYU';
            return `${currencySymbol} ${parseFloat(data).toFixed(2)} (Sin IVA)`;
          }
        },
        {
          // IVA aplicado por unidad
          data: 'tax_rate',
          render: function (data, type, row) {
            const currencySymbol = row.currency === 'Dólar' ? 'USD' : 'UYU';
            if (parseFloat(data) === 0) {
              return `${currencySymbol} 0.00 (0%)`;
            } else {
              var taxAmount = row.base_price * (parseFloat(data) / 100);
              return `${currencySymbol} ${taxAmount.toFixed(2)} (${parseFloat(data).toFixed(0)}%)`;
            }
          }
        },
        {
          // Precio total unitario con IVA
          data: 'price',
          render: function (data, type, full, meta) {
            const currencySymbol = full.currency === 'Dólar' ? 'USD' : 'UYU';
            return `${currencySymbol} ${parseFloat(data).toFixed(2)}`;
          }
        },
        {
          // Cantidad
          data: 'quantity',
          render: function (data) {
            return `${data}`;
          }
        },
        {
          // Total por producto (cantidad * precio unitario con IVA)
          data: null,
          render: function (data, type, row) {
            const currencySymbol = row.currency === 'Dólar' ? 'USD' : 'UYU';
            var total = row.price * row.quantity;
            return `${currencySymbol} ${total.toFixed(2)}`;
          }
        }
      ],
      columnDefs: [
        {
          // Renderizar precio.
          targets: 2,
          render: function (data, type, full, meta) {
            const currencySymbol = full.currency === 'Dólar' ? 'USD' : 'UYU';
            return `${currencySymbol} ${parseFloat(data).toFixed(2)}`;
          }
        },
        {
          // Renderizar total por producto.
          targets: -1,
          render: function (data, type, row) {
            const currencySymbol = full.currency === 'Dólar' ? 'USD' : 'UYU';
            var total = row.price * row.quantity;
            return `${currencySymbol} ${total.toFixed(2)}`;
          }
        }
      ],
      order: [1, 'asc'], // Ordena por nombre del producto
      dom: 't' // Solo muestra la tabla sin controles adicionales
    });
  }
});


document.addEventListener('DOMContentLoaded', function () {
  // Inicializar tooltips
  const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
  tooltipTriggerList.forEach(function (tooltipTriggerEl) {
    new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // Configurar evento de eliminación solo en botones habilitados
  document.querySelectorAll('.delete-order:not([disabled])').forEach(button => {
    button.addEventListener('click', function () {
      const orderId = this.getAttribute('data-order-id');
      const deleteUrl = `${baseUrl}admin/orders/${orderId}`;

      Swal.fire({
        title: '¿Eliminar venta?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then(result => {
        if (result.isConfirmed) {
          fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
          })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                Swal.fire('Eliminado', data.message, 'success').then(() => {
                  window.location.href = `${baseUrl}admin/orders`; // Redirige al índice de órdenes
                });
              } else {
                Swal.fire('Error', data.message, 'error');
              }
            })
            .catch(() => {
              Swal.fire('Error', 'No se pudo eliminar la venta. Intente nuevamente.', 'error');
            });
        }
      });
    });
  });

  $(document).on('click', '.btn-dispatch', function() {
    var orderUuid = $(this).data('id');
    var dispatchUrl = `${baseUrl}admin/dispatch-notes/${orderUuid}`;
    window.location.href = dispatchUrl;
  });

  document.getElementById('sendEmailForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const invoiceId = document.getElementById('invoice_id').value;
    const email = document.getElementById('email').value;
    const formAction = this.getAttribute('action');

    // Mostrar Swal de "Enviando..."
    Swal.fire({
      title: 'Enviando...',
      text: 'Por favor, espera mientras enviamos la factura.',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    fetch(formAction, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({ email: email, invoice_id: invoiceId })
    })
      .then(response => response.json())
      .then(data => {
        Swal.close(); // Cerrar Swal de "Enviando..."

        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Correo enviado correctamente',
            showConfirmButton: false,
            timer: 2000
          });
          // Cerrar el modal tras un envío exitoso
          const modal = bootstrap.Modal.getInstance(document.getElementById('sendEmailModal'));
          modal.hide();
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error al enviar el correo',
            text: data.message || 'Ocurrió un error. Inténtalo nuevamente.',
            showConfirmButton: true
          });
        }
      })
      .catch(error => {
        Swal.close(); // Cerrar Swal de "Enviando..." en caso de error

        Swal.fire({
          icon: 'error',
          title: 'Error al enviar el correo',
          text: 'Ocurrió un error. Inténtalo nuevamente.',
          showConfirmButton: true
        });
      });
  });

});
$(document).on('click', '.refund-payment', function () {
  const orderId = $(this).data('id');
  Swal.fire({
    title: '¿Estás seguro?',
    text: 'Esta acción devolverá el dinero al cliente. Este proceso no se puede revertir.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Sí, devolver!',
    cancelButtonText: 'Cancelar'
  }).then(result => {
    if (result.isConfirmed) {
      $.ajax({
        url: `${baseUrl}admin/orders/mercado-pago/refund/${orderId}`,
        type: 'POST',
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
          if (response.success) {
            Swal.fire('Reembolso realizado!', 'El dinero ha sido devuelto al cliente.', 'success').then(() => {
              location.reload(); // Recargar la página o realizar alguna acción adicional
            });
          } else {
            Swal.fire('Error!', response.message || 'No se pudo realizar el reembolso.', 'error');
          }
        },
        error: function (xhr, ajaxOptions, thrownError) {
          Swal.fire('Error!', xhr.responseJSON.message || 'No se pudo procesar el reembolso.', 'error');
        }
      });
    }
  });
});

document.addEventListener('DOMContentLoaded', function () {
  const reverseTransactionButton = document.getElementById('reverseTransactionButton');

  if (reverseTransactionButton) {
    reverseTransactionButton.addEventListener('click', function () {
      const transactionId = this.getAttribute('data-transaction-id');
      const sTransactionId = this.getAttribute('data-stransaction-id');
      const storeId = this.getAttribute('data-store-id'); // Obtener el store_id desde el atributo del botón
      const reverseUrl = `${baseUrl}api/pos/reverse`; // Construir la URL dinámica con baseUrl

      Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción reversará la transacción.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, reversar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          fetch(reverseUrl, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
              TransactionId: transactionId,
              STransactionId: sTransactionId,
              store_id: storeId // Enviar el store_id en el cuerpo de la solicitud
            })
          })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                Swal.fire('Reversado', data.message, 'success').then(() => {
                  location.reload(); // Recargar la página después de un éxito
                });
              } else {
                Swal.fire('Error', data.message, 'error');
              }
            })
            .catch(error => {
              Swal.fire('Error', 'Ocurrió un error al procesar el reverso.', 'error');
              console.error(error);
            });
        }
      });
    });
  }
});

// Anular una transacción
document.addEventListener('DOMContentLoaded', function () {
  const voidTransactionModal = document.getElementById('voidTransactionModal');
  const posDeviceSelect = document.getElementById('posDeviceSelect');
  const ticketNumberInput = document.getElementById('ticketNumber');
  const voidTransactionForm = document.getElementById('voidTransactionForm');
  const storeIdInput = document.getElementById('storeIdInput');
  const orderIdInput = document.getElementById('orderIdInput');
  let systemId = null; // Variable para almacenar dinámicamente el SystemId
  let branch = null;


  // Evento para mostrar el modal y cargar dispositivos POS y SystemId
  voidTransactionModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const storeId = button.getAttribute('data-store-id');
    const orderId = button.getAttribute('data-order-id');

    if (!storeId || !orderId) {
      console.error('store_id o order_id no están definidos.');
      Swal.fire('Error', 'No se pudo obtener el ID de la tienda o el ID de la orden.', 'error');
      return;
    }

    // Asignar valores a los campos ocultos
    storeIdInput.value = storeId;
    orderIdInput.value = orderId;

    // Reiniciar valores del formulario
    ticketNumberInput.value = '';
    posDeviceSelect.innerHTML = '<option value="" selected disabled>Seleccione un dispositivo POS</option>';
    systemId = null;

    // Obtener dispositivos POS
    fetch(`/api/pos-devices?store_id=${storeId}`)
      .then(response => response.json())
      .then(data => {
        if (data.success && data.devices.length > 0) {
          data.devices.forEach(device => {
            const option = document.createElement('option');
            option.value = device.id;
            option.textContent = `${device.name} (${device.identifier})`;
            option.setAttribute('data-pos-id', device.identifier);
            option.setAttribute('data-clientappid', device.cash_register || '');
            option.setAttribute('data-user', device.user || '');
            posDeviceSelect.appendChild(option);
          });
        } else {
          console.error('Error al obtener dispositivos POS:', data.message);
          Swal.fire('Error', 'No se pudieron cargar los dispositivos POS.', 'error');
        }
      })
      .catch(error => {
        console.error('Error al realizar la solicitud:', error);
        Swal.fire('Error', 'Hubo un problema al cargar los dispositivos POS.', 'error');
      });

    // Obtener el SystemId asociado a la tienda
    fetch(`/api/pos/get-provider/${storeId}`)
      .then(response => {
        if (!response.ok) {
          throw new Error('Error al obtener el proveedor POS: ' + response.statusText);
        }
        return response.json();
      })
      .then(data => {
        console.log('Respuesta del proveedor POS:', data);
        if (data.provider && data.provider && data.provider.system_id) {
          systemId = data.provider.system_id; // Guardar dinámicamente el SystemId
          branch = data.provider.branch; // Guardar dinámicamente la sucursal
          console.log('SystemId obtenido correctamente:', systemId);
        } else {
          console.error('No se encontró el SystemId en la respuesta:', data);
          Swal.fire('Error', 'No se pudo obtener el SystemId para la tienda seleccionada.', 'error');
        }
      })
      .catch(error => {
        console.error('Error al obtener el SystemId:', error);
        Swal.fire('Error', 'Hubo un problema al obtener el SystemId.', 'error');
      });

  });

  // Manejar el envío del formulario de anulación de transacción
  voidTransactionForm.addEventListener('submit', function (e) {
    e.preventDefault();

    const selectedOption = posDeviceSelect.options[posDeviceSelect.selectedIndex];
    if (!selectedOption) {
      Swal.fire('Error', 'Debe seleccionar un dispositivo POS.', 'error');
      return;
    }

    console.log('Opción seleccionada:', selectedOption);

    const posDeviceId = selectedOption.value;
    const posId = selectedOption.getAttribute('data-pos-id');
    const clientAppId = selectedOption.getAttribute('data-clientappid');
    const userId = selectedOption.getAttribute('data-user');
    const ticketNumber = ticketNumberInput.value;
    const storeId = storeIdInput.value;
    const orderId = orderIdInput.value;

    if (!ticketNumber) {
      Swal.fire('Error', 'Debe ingresar un número de ticket.', 'error');
      return;
    }

    if (!orderId || !storeId || !systemId) {
      Swal.fire('Error', 'Faltan datos necesarios para procesar la solicitud.', 'error');
      return;
    }

    const transactionData = {
      pos_device_id: posDeviceId,
      PosID: posId,
      SystemId: systemId, // Usar el SystemId obtenido dinámicamente
      Branch: branch,
      ClientAppId: clientAppId,
      UserId: userId,
      TransactionDateTimeyyyyMMddHHmmssSSS: new Date().toISOString().replace(/[-T:.Z]/g, '').padEnd(17, '0'),
      TicketNumber: ticketNumber,
      store_id: storeId,
      order_id: orderId,
    };

    console.log('Datos de la transacción a anular:', transactionData);

    // Enviar la solicitud de anulación
    fetch(`/api/pos/void`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
      body: JSON.stringify(transactionData),
    })
      .then(response => response.json())
      .then(data => {
        if (data.success && data.details) {
          const { TransactionId, STransactionId } = data.details;

          if (TransactionId && STransactionId) {
            pollTransactionStatus(TransactionId, STransactionId, storeId);
          } else {
            Swal.fire('Error', 'No se pudieron obtener los IDs de la transacción anulada.', 'error');
          }
        } else {
          console.error('Error al anular la transacción:', data.message);
          Swal.fire('Error', data.message || 'Error al anular la transacción.', 'error');
        }
      })
      .catch(error => {
        console.error('Error al procesar la solicitud:', error);
        Swal.fire('Error', 'Hubo un problema al procesar la solicitud.', 'error');
      });
  });

  // Función para consultar el estado de la transacción
  let swalInstance; // Variable global para manejar la instancia de SweetAlert
  function pollTransactionStatus(transactionId, sTransactionId, storeId) {
    if (!swalInstance) {
      // Mostrar el Swal de "Anulación en proceso" solo una vez
      swalInstance = Swal.fire({
        title: 'Procesando...',
        text: 'Esperando confirmación del PINPad.',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading(); // Mostrar el spinner de carga
        },
      });
    }

    const requestData = {
      TransactionId: transactionId,
      STransactionId: sTransactionId,
      store_id: storeId,
    };

    fetch(`/api/pos/poll-void-status`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
      body: JSON.stringify(requestData),
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          swalInstance.close();
          swalInstance = null;

          Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: data.message,
            showConfirmButton: false,
            timer: 2000,
          }).then(() => {
            location.reload();
          });
        } else if (data.responseCode === 999) {
          // Transacción cancelada desde el PINPad
          swalInstance.close();
          swalInstance = null;

          Swal.fire({
            icon: data.icon || 'error',
            title: 'Operación cancelada',
            text: data.message || 'La transacción fue cancelada desde el PINPad.',
            showConfirmButton: true,
          });
        } else if (data.keepPolling) {
          setTimeout(() => pollTransactionStatus(transactionId, sTransactionId, storeId), 2000);
        } else {
          swalInstance.close();
          swalInstance = null;

          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: data.message || 'Error durante el proceso de anulación.',
            showConfirmButton: true,
          });
        }
      })
      .catch(error => {
        swalInstance.close();
        swalInstance = null;

        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Hubo un problema al consultar el estado de la transacción.',
          showConfirmButton: true,
        });
        console.error('Error al consultar el estado de la transacción:', error);
      });
  }

});


document.addEventListener('DOMContentLoaded', function () {
  const refundTransactionModal = document.getElementById('refundTransactionModal');
  const refundTransactionForm = document.getElementById('refundTransactionForm');
  const refundAmountInput = document.getElementById('refundAmount');
  const refundReasonInput = document.getElementById('refundReason');
  const storeIdRefundInput = document.getElementById('storeIdRefundInput');
  const orderIdRefundInput = document.getElementById('orderIdRefundInput');
  const transactionIdRefundInput = document.getElementById('transactionIdRefundInput');
  const sTransactionIdRefundInput = document.getElementById('sTransactionIdRefundInput');
  const posDeviceSelectRefund = document.getElementById('posDeviceSelectRefund');
  let systemId = null;

  // Mostrar el modal y cargar dispositivos POS
  refundTransactionModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const storeId = button.getAttribute('data-store-id');
    const orderId = button.getAttribute('data-order-id');
    const transactionId = button.getAttribute('data-transaction-id');
    const sTransactionId = button.getAttribute('data-stransaction-id');

    if (!storeId || !orderId || !transactionId || !sTransactionId) {
      console.error('Faltan datos necesarios para procesar el refund.');
      Swal.fire('Error', 'Faltan datos necesarios para procesar el refund.', 'error');
      return;
    }

    storeIdRefundInput.value = storeId;
    orderIdRefundInput.value = orderId;
    transactionIdRefundInput.value = transactionId;
    sTransactionIdRefundInput.value = sTransactionId;

    refundAmountInput.value = '';
    refundReasonInput.value = '';

    // Limpiar el select de dispositivos POS
    posDeviceSelectRefund.innerHTML = '<option value="" selected disabled>Seleccione un dispositivo POS</option>';

    // Obtener dispositivos POS para la tienda seleccionada
    fetch(`/api/pos-devices?store_id=${storeId}`)
      .then(response => response.json())
      .then(data => {
        if (data.success && data.devices.length > 0) {
          data.devices.forEach(device => {
            const option = document.createElement('option');
            option.value = device.id;
            option.textContent = `${device.name} (${device.identifier})`;
            option.setAttribute('data-pos-id', device.identifier);
            option.setAttribute('data-branch', device.branch || '');
            option.setAttribute('data-clientappid', device.client_app_id || '');
            option.setAttribute('data-user', device.user || '');
            posDeviceSelectRefund.appendChild(option);
          });
        } else {
          console.error('No se encontraron dispositivos POS disponibles.');
          Swal.fire('Advertencia', 'No se encontraron dispositivos POS disponibles para esta tienda.', 'warning');
        }
      })
      .catch(error => {
        console.error('Error al obtener dispositivos POS:', error);
        Swal.fire('Error', 'Hubo un problema al cargar los dispositivos POS.', 'error');
      });

    // Obtener el SystemId asociado a la tienda
    fetch(`/api/pos/get-provider/${storeId}`)
      .then(response => {
        if (!response.ok) {
          throw new Error('Error al obtener el proveedor POS: ' + response.statusText);
        }
        return response.json();
      })
      .then(data => {
        console.log('Respuesta del proveedor POS:', data);
        if (data.provider && data.provider && data.provider.system_id) {
          systemId = data.provider.system_id; // Guardar dinámicamente el SystemId
          console.log('SystemId obtenido correctamente:', systemId);
        } else {
          console.error('No se encontró el SystemId en la respuesta:', data);
          Swal.fire('Error', 'No se pudo obtener el SystemId para la tienda seleccionada.', 'error');
        }
      })
      .catch(error => {
        console.error('Error al obtener el SystemId:', error);
        Swal.fire('Error', 'Hubo un problema al obtener el SystemId.', 'error');
      });

  });

  // Manejar el envío del formulario de refund
  refundTransactionForm.addEventListener('submit', function (e) {
    e.preventDefault();

    const storeId = storeIdRefundInput.value;
    const orderId = orderIdRefundInput.value;
    const transactionId = transactionIdRefundInput.value;
    const sTransactionId = sTransactionIdRefundInput.value;

    const refundAmount = refundAmountInput.value;
    const refundReason = refundReasonInput.value;
    const ticketNumber = document.getElementById('ticketNumberRefund').value;

    const selectedOption = posDeviceSelectRefund.options[posDeviceSelectRefund.selectedIndex];
    if (!selectedOption) {
      Swal.fire('Error', 'Debe seleccionar un dispositivo POS.', 'error');
      return;
    }

    const posDeviceId = selectedOption.value;
    const posId = selectedOption.getAttribute('data-pos-id');
    const branch = selectedOption.getAttribute('data-branch');
    const clientAppId = selectedOption.getAttribute('data-clientappid');
    const userId = selectedOption.getAttribute('data-user');

    // Obtener y formatear la fecha original
    const originalTransactionDateInput = document.getElementById('originalTransactionDate');
    const originalTransactionDate = originalTransactionDateInput.value;
    let formattedOriginalDate = '';
    if (originalTransactionDate) {
      formattedOriginalDate = formatDateToYYMMDD(originalTransactionDate); // Convertir a YYMMDD
    }

    const refundData = {
      store_id: storeId,
      order_id: orderId,
      transaction_id: transactionId,
      s_transaction_id: sTransactionId,
      Amount: refundAmount,
      reason: refundReason,
      TicketNumber: ticketNumber,
      PosID: posId,
      SystemId: systemId,
      Branch: branch || 'Sucursal1',
      ClientAppId: clientAppId || 'Caja1',
      UserId: userId || 'Usuario1',
      TransactionDateTimeyyyyMMddHHmmssSSS: new Date().toISOString().replace(/[-T:.Z]/g, '').padEnd(20, '0'),
      OriginalTransactionDateyyMMdd: formattedOriginalDate,
    };

    // Mostrar Swal de "Actualizando"
    Swal.fire({
      title: 'Actualizando...',
      text: 'Esperando por operación en PINPad',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      },
    });

    // Enviar solicitud de refund
    fetch(`/api/pos/refund`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
      body: JSON.stringify(refundData),
    })
      .then(response => response.json())
      .then(data => {
        // Cerrar modal
        const modal = bootstrap.Modal.getInstance(refundTransactionModal);
        modal.hide();

        Swal.close(); // Cerrar el Swal de "Actualizando"

        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Refund realizado con éxito',
            text: data.message,
          }).then(() => {
            location.reload(); // Recargar la página tras éxito
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: data.message || 'No se pudo realizar el refund.',
          });
        }
      })
      .catch(error => {
        Swal.close(); // Cerrar el Swal en caso de error
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Hubo un problema al procesar el refund. Inténtalo nuevamente.',
        });
        console.error('Error al procesar el refund:', error);
      });
  });


  // Función para convertir la fecha a formato YYMMDD
  function formatDateToYYMMDD(dateString) {
    const date = new Date(dateString);
    const year = String(date.getFullYear()).slice(-2); // Últimos 2 dígitos del año
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Mes con cero inicial si es necesario
    const day = String(date.getDate()).padStart(2, '0'); // Día con cero inicial si es necesario
    return `${year}${month}${day}`;
  }

  $('#updateClientDataForm').on('submit', function (e) {
    e.preventDefault();
    const form = $(this);

    $.ajax({
      url: form.attr('action'),
      method: 'POST',
      data: form.serialize(),
      success: function (response) {
        const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('updateClientDataOffcanvas'));
        offcanvas.hide();

        form.find('.is-invalid').removeClass('is-invalid');
        form.find('.invalid-feedback').remove();

        Swal.fire({
          icon: 'success',
          title: 'Datos actualizados',
          text: 'Los datos del cliente han sido actualizados correctamente',
          showConfirmButton: false,
          timer: 1500,
          didClose: () => {
              const billingModal = new bootstrap.Modal(document.getElementById('emitirFacturaModal'));
              billingModal.show();
          }
      });

      },
      error: function (xhr) {
        const errors = xhr.responseJSON.errors;
        Object.keys(errors).forEach(field => {
          const input = form.find(`[name="${field}"]`);
          input.addClass('is-invalid');
          input.after(`<div class="invalid-feedback">${errors[field][0]}</div>`);
        });
      }
    });
  });
});
