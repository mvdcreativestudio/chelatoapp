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
