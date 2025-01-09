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
          // Precio del producto
          data: 'price',
          render: function (data, type, full, meta) {
            return `${currencySymbol}${parseFloat(data).toFixed(2)}`;
          }
        },
        { data: 'quantity' },
        {
          // Total por producto
          data: null,
          render: function (data, type, row, meta) {
            return `${currencySymbol}${(row.price * row.quantity).toFixed(2)}`;
          }
        }
      ],
      columnDefs: [
        {
          // Renderizar Precio
          targets: 2,
          render: function (data, type, full, meta) {
            return `${currencySymbol}${parseFloat(data).toFixed(2)}`;
          }
        },
        {
          // Renderizar Total por Producto
          targets: -1,
          render: function (data, type, full, meta) {
            return `${currencySymbol}${(full.price * full.quantity).toFixed(2)}`;
          }
        }
      ],
      order: [2, ''],
      dom: 't'
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


  // Evento para mostrar el modal y cargar dispositivos POS
  voidTransactionModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const storeId = button.getAttribute('data-store-id');
    const orderId = button.getAttribute('data-order-id');

    if (!storeId || !orderId) {
      console.error('store_id o order_id no están definidos.');
      Swal.fire('Error', 'No se pudo obtener el ID de la tienda o el ID de la orden.', 'error');
      return;
    }

    storeIdInput.value = storeId;
    orderIdInput.value = orderId;


    ticketNumberInput.value = '';
    posDeviceSelect.innerHTML = '<option value="" selected disabled>Seleccione un dispositivo POS</option>';

    // Obtener dispositivos POS
    fetch(`/api/pos-devices?store_id=${storeId}`)
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          data.devices.forEach(device => {
            const option = document.createElement('option');
            option.value = device.id;
            option.textContent = `${device.name} (${device.identifier})`;
            option.setAttribute('data-pos-id', device.identifier);
            option.setAttribute('data-branch', device.branch || '');
            option.setAttribute('data-clientappid', device.client_app_id || '');
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
  });

  // Manejar el envío del formulario de anulación de transacción
  voidTransactionForm.addEventListener('submit', function (e) {
    e.preventDefault();

    const selectedOption = posDeviceSelect.options[posDeviceSelect.selectedIndex];
    if (!selectedOption) {
      Swal.fire('Error', 'Debe seleccionar un dispositivo POS.', 'error');
      return;
    }

    const posDeviceId = selectedOption.value;
    const posId = selectedOption.getAttribute('data-pos-id');
    const branch = selectedOption.getAttribute('data-branch');
    const clientAppId = selectedOption.getAttribute('data-clientappid');
    const userId = selectedOption.getAttribute('data-user');
    const ticketNumber = ticketNumberInput.value;
    const storeId = storeIdInput.value;
    const orderId = document.getElementById('orderIdInput').value;


    if (!ticketNumber) {
      Swal.fire('Error', 'Debe ingresar un número de ticket.', 'error');
      return;
    }

    if (!orderId || !storeId) {
      Swal.fire('Error', 'Faltan datos necesarios para procesar la solicitud.', 'error');
      return;
    }

    const transactionData = {
      pos_device_id: posDeviceId,
      PosID: posId,
      SystemId: "E62FC666-5E4A-5E1D-B80A-EAB805050505",
      Branch: branch || 'Sucursal1',
      ClientAppId: clientAppId || 'Caja1',
      UserId: userId || 'Usuario1',
      TransactionDateTimeyyyyMMddHHmmssSSS: new Date().toISOString().replace(/[-T:.Z]/g, '').padEnd(20, '0'),
      TicketNumber: ticketNumber,
      store_id: storeId,
      order_id: orderId,
    };

    fetch(`/api/pos/void`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify(transactionData)
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
      title: 'Anulación en proceso...',
      text: 'Continúa con la anulación desde el POS.',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading(); // Mostrar el spinner de carga
      }
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
        // Cerrar el Swal de "Anulación en proceso" solo al éxito
        swalInstance.close();
        swalInstance = null;

        // Mostrar Swal de éxito
        Swal.fire({
          icon: 'success',
          title: 'Éxito',
          text: data.message,
          showConfirmButton: false,
          timer: 2000, // Mostrar por 2 segundos y luego cerrar automáticamente
        }).then(() => {
          location.reload(); // Recargar la página después del éxito
        });
      } else if (data.keepPolling) {
        // Continuar el polling después de 2 segundos
        setTimeout(() => pollTransactionStatus(transactionId, sTransactionId, storeId), 2000);
      } else {
        // Cerrar el Swal de "Anulación en proceso"
        swalInstance.close();
        swalInstance = null;

        // Mostrar Swal de error
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: data.message || 'Error durante el proceso de anulación.',
          showConfirmButton: true,
        });
      }
    })
    .catch(error => {
      // Cerrar el Swal de "Anulación en proceso" en caso de error
      swalInstance.close();
      swalInstance = null;

      // Mostrar Swal de error
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
