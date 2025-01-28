document.addEventListener('DOMContentLoaded', function () {
  const supplierListContainer = $('.supplier-list-container');

  $('[place-id="buttonCreate"]').html(`
    <a href="suppliers/create" class="btn btn-primary d-flex align-items-center gap-1">
      <i class="bx bx-plus"></i>
      <span>Nuevo Proveedor</span>
    </a>
  `);

  function translateDocType(docType) {
    const translations = {
      'CI': 'CI',
      'PASSPORT': 'Pasaporte',
      'OTHER': 'Otro',
      'RUT': 'RUT'
    };
    return translations[docType] || docType;
  }

  function translatePaymentMethod(method) {
    const translations = {
      'cash': 'Efectivo',
      'credit': 'Crédito',
      'debit': 'Débito',
      'check': 'Cheque'
    };
    return translations[method] || method;
  }



  function fetchSuppliers() {
    $.ajax({
      url: 'suppliers-all',
      method: 'GET',
      success: function (response) {
        supplierListContainer.html('');

        if (response.length === 0) {
          supplierListContainer.html(`
            <div class="col-12">
              <div class="alert alert-info text-center">
                <i class="bx bx-info-circle"></i> No hay proveedores disponibles.
              </div>
            </div>
          `);
        } else {
          response.forEach(function (supplier) {
            const card = `
              <div class="col-md-6 col-lg-4 col-12 supplier-card-wrapper">
                <div class="clients-card-container">
                  <div class="clients-card position-relative">
                    <div class="clients-card-header d-flex justify-content-between align-items-center">
                      <h5 class="clients-name mb-0" title="${supplier.name}">
                        ${supplier.name}
                      </h5>
                      <div class="d-flex align-items-center">
                        <div class="clients-card-toggle">
                          <i class="bx bx-chevron-down fs-3"></i>
                        </div>
                      </div>
                    </div>
                    <div class="clients-card-body" style="display: none;">
                      <div class="d-flex flex-column h-100">
                        <div>
                          <p class="mb-2"><i class="bx bx-envelope me-2"></i> ${supplier.email ?? 'No disponible'}</p>
                          <p class="mb-2"><i class="bx bx-phone me-2"></i> ${supplier.phone ?? 'No disponible'}</p>
                          <p class="mb-2"><i class="bx bx-map me-2"></i> ${[supplier.city, supplier.state, supplier.country].filter(Boolean).join(', ') || 'No disponible'}</p>
                          <p class="mb-2"><i class="bx bx-id-card me-2"></i> ${supplier.doc_type ? `${translateDocType(supplier.doc_type)}: ${supplier.doc_number ?? 'No disponible'}` : 'No disponible'}</p>
                          <p class="mb-2"><i class="bx bx-credit-card me-2"></i> Método de pago: ${supplier.default_payment_method ? translatePaymentMethod(supplier.default_payment_method) : 'No disponible'}</p>
                        </div>
                        <div class="d-inline-flex justify-content-end mt-auto mb-2 gap-1">
                          <a href="suppliers/${supplier.id}/edit" class="btn view-clients p-1"><i class="bx bx-edit"></i></a>
                          <form class="delete-form-${supplier.id}" action="suppliers/${supplier.id}" method="POST" style="display: inline;">
                            <input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content')}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="button" class="btn view-clients p-1 delete-button mt-3" title="Eliminar Proveedor">       
                              <i class="bx bx-trash"></i>
                            </button>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>`;
            supplierListContainer.append(card);
          });

          // Card toggle functionality
          $('.clients-card').on('click', function (e) {
            if (!$(e.target).closest('.view-clients').length) {
              const $icon = $(this).find('.clients-card-toggle i');
              const $body = $(this).find('.clients-card-body');

              $icon.toggleClass('bx-chevron-down bx-chevron-up');
              $body.slideToggle();
            }
          });
        }
      },
      error: function (xhr, status, error) {
        console.error('Error al obtener los datos de proveedores:', error);
        supplierListContainer.html(`
          <div class="col-12">
            <div class="alert alert-danger text-center">
              <i class="bx bx-error-circle"></i> Error al cargar los proveedores. Por favor, intente nuevamente.
            </div>
          </div>
        `);
      }
    });
  }

  fetchSuppliers();

  $('#searchSupplier').on('input', function () {
    var searchTerm = $(this).val().toLowerCase();
    $('.supplier-card-wrapper').each(function () {
      var supplierInfo = $(this).text().toLowerCase();
      $(this).toggle(supplierInfo.includes(searchTerm));
    });
  });

  $(document).on('click', '.delete-button', function(e) {
    e.preventDefault();
    const form = $(this).closest('form');
    
    Swal.fire({
      title: '¿Estás seguro?',
      text: 'Esta acción eliminará completamente al proveedor, perdiendo definitivamente sus datos',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Sí, eliminar!',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        form.submit();
      }
    });
  });

});