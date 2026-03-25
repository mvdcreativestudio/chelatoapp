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
          render: function(data, type, full, meta) {
            var raw = data != null && String(data).trim() !== '' ? String(data).trim() : '';
            var imagePath = raw.indexOf('http') === 0 ? raw : `${baseUrl}${raw.replace(/^\//, '')}`;
            if (!raw) {
              imagePath = `${baseUrl}admin/default-image.png`;
            }
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
          render: function(data, type, row, meta) {
              var flavors = row.flavors ? '<br><small>' + row.flavors + '</small>' : '';
              return '<span>' + data + flavors + '</span>';
          }
        },
        {
          // Precio del producto
          data: 'price',
          render: function(data, type, full, meta) {
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


  // --- Emitir Nota (Crédito/Débito) ---
  const emitirNotaModalEl = document.getElementById('emitirNotaModal');
  const emitirNotaForm = document.getElementById('emitirNotaForm');

  if (emitirNotaModalEl && emitirNotaForm) {
    const emitirNotaModal = new bootstrap.Modal(emitirNotaModalEl);

    document.querySelectorAll('.emitirNotaBtn').forEach(btn => {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        const invoiceId = this.getAttribute('data-invoice-id');
        const balance = this.getAttribute('data-total');
        emitirNotaForm.setAttribute('action', `${baseUrl}admin/invoices/${invoiceId}/emit-note`);
        const noteAmountInput = document.getElementById('noteAmount');
        if (noteAmountInput) {
          noteAmountInput.setAttribute('max', balance);
          noteAmountInput.value = balance;
        }
        emitirNotaModal.show();
      });
    });

    // Manejar la sumisión del formulario de nota
    emitirNotaForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const form = e.target;
      const formData = new FormData(form);
      const actionUrl = form.getAttribute('action');

      // Mostrar Swal de "Emitiendo nota..."
      Swal.fire({
        title: 'Emitiendo nota...',
        text: 'Por favor, espera mientras procesamos la nota.',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      fetch(actionUrl, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        body: formData
      })
      .then(response => response.json()
        .then(data => ({ ok: response.ok, data }))
        .catch(() => ({ ok: false, data: { message: `Error ${response.status}` } }))
      )
      .then(({ ok, data }) => {
        Swal.close();
        if (!ok) {
          throw new Error(data?.message || data?.error || 'Error al emitir la nota');
        }
        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Nota emitida',
            text: data.message || 'Nota emitida correctamente.',
            showConfirmButton: false,
            timer: 2000
          }).then(() => {
            location.reload();
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error al emitir nota',
            text: data.message || 'Ocurrió un error. Inténtalo nuevamente.',
            showConfirmButton: true
          });
        }
      })
      .catch(error => {
        Swal.close();
        Swal.fire({
          icon: 'error',
          title: 'Error al emitir nota',
          text: error.message || 'Hubo un problema al procesar la solicitud. Inténtalo nuevamente.',
          showConfirmButton: true
        });
      });
    });
  }
});
