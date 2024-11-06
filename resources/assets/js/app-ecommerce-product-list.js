'use strict';

$(function () {

  // Función para cerrar el modal de filtros al hacer clic fuera
  $(document).on('click', function (event) {
    const filterModal = $('#filterModal');
    const openFiltersButton = $('#openFilters');

    // Verifica si el modal está abierto, si el clic fue fuera del modal y si el clic no fue en el botón para abrir el modal
    if (filterModal.hasClass('open') && !filterModal.is(event.target) && !filterModal.has(event.target).length && !openFiltersButton.is(event.target) && !openFiltersButton.has(event.target).length) {
      filterModal.removeClass('open');
    }
  });
  
  let borderColor, bodyBg, headingColor;
  let currencySymbol = window.currencySymbol;

  if (isDarkStyle) {
    borderColor = config.colors_dark.borderColor;
    bodyBg = config.colors_dark.bodyBg;
    headingColor = config.colors_dark.headingColor;
  } else {
    borderColor = config.colors.borderColor;
    bodyBg = config.colors.bodyBg;
    headingColor = config.colors.headingColor;
  }


  var dt_product_list_container = $('#product-list-container');
  var searchInput = $('#searchProduct');
  var storeFilter = $('#storeFilter');
  var categoryFilter = $('#categoryFilter');
  var statusFilter = $('#statusFilter');

  function isFilterApplied() {
    return (
      searchInput.val().trim() !== '' ||
      storeFilter.val() !== '' ||
      categoryFilter.val() !== '' ||
      statusFilter.val() !== ''
    );
  }

  function resetFilters() {
    searchInput.val('');
    storeFilter.val('');
    categoryFilter.val('');
    statusFilter.val('');
    fetchProducts();
  }

  function fetchProducts() {
    var ajaxUrl = dt_product_list_container.data('ajax-url');
    var searchQuery = searchInput.val();
    var storeId = storeFilter.val();
    var categoryId = categoryFilter.val();
    var status = statusFilter.val();

    $.ajax({
      url: ajaxUrl,
      method: 'GET',
      data: {
        search: searchQuery,
        store_id: storeId,
        category_id: categoryId,
        status: status
      },
      success: function (response) {
        var rows = response.data;
        var cardContainer = $('#product-list-container').html(''); // Limpiar el contenedor

        if (rows.length === 0) {
          if (isFilterApplied()) {
            cardContainer.html(`
              <div class="alert alert-warning text-center w-100">
                <i class="bx bx-filter-alt"></i> No hay productos que coincidan con los filtros.
                <br>
                <button id="clearFilters" class="btn btn-outline-danger mt-3">Borrar filtros</button>
              </div>
            `);
            $('#clearFilters').on('click', function () {
              resetFilters();
            });
          } else {
            cardContainer.html(`
              <div class="alert alert-info text-center w-100">
                <i class="bx bx-info-circle"></i> No existen productos disponibles.
              </div>
            `);
          }
        } else {
          rows.forEach(function (rowData) {
            const stockClass =
              rowData.stock === 0 ? 'bg-danger' :
              rowData.stock <= rowData.safety_margin ? 'bg-warning' : 'bg-success';

            const statusText = rowData.status === 1 ? 'Activo' : 'Inactivo';
            const statusTextClass = rowData.status === 1 ? 'text-success' : 'text-danger';

            const truncatedName = rowData.name.length > 20 ? rowData.name.substring(0, 20) + '...' : rowData.name;

            // Determinar qué precio mostrar
            const priceToShow = rowData.price > 0 ? rowData.price : rowData.old_price; // Cambiado para mostrar old_price si price es 0
            const priceClass = rowData.price !== null ? '' : ''; // Añadido para mostrar un estilo diferente si no hay precio

            const card = `
              <div class="col-md-6 col-lg-4 col-12 mb-4">
                <a href="${baseUrl}admin/products/${rowData.id}" class="text-decoration-none">
                  <div class="product-card position-relative">
                    <div class="col-4 d-flex align-items-center">
                      <img src="${baseUrl + rowData.image}" class="img-fluid product-card-img" alt="Imagen del producto">
                    </div>
                    <div class="col-8">
                      <div class="product-card-body">
                        <!-- Título con tooltip para el nombre completo -->
                        <h5 class="product-title" title="${rowData.name}">${truncatedName}</h5>
                        <p class="product-category text-muted small">${rowData.category || 'Sin categoría'}</p>
                        <h6 class="product-price ${priceClass}">${currencySymbol}${parseFloat(priceToShow).toFixed(2)}</h6>
                        <p class="product-stock"><span class="badge ${stockClass}">${rowData.stock}</span></p>
                        <p class="product-status ${statusTextClass}">${statusText}</p>
                      </div>
                    </div>
                  </div>
                </a>
              </div>
            `;

            cardContainer.append(card);
          });
        }
      },
      error: function (xhr, status, error) {
        console.error('Error al obtener los datos:', error);
      }
    });
  }

  // Abrir el modal de importación
  $('#openImportModal').on('click', function () {
    $('#importModal').modal('show');
  });

  // Manejar el formulario de importación de productos
  $('#importForm').on('submit', function (e) {
    e.preventDefault();

    var formData = new FormData(this);

    $.ajax({
      url: $(this).attr('action'),
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        $('#importModal').modal('hide');
        if (response.success) {
          // Mostrar mensaje de éxito
          showAlert('success', response.message);
          // Recargar la lista de productos
          fetchProducts();
        }
      },
      error: function (xhr) {
        $('#importModal').modal('hide');
        if (xhr.status === 422) {
          // Mostrar los errores de validación en el frontend
          var errors = xhr.responseJSON.errors;
          var errorMessages = '';

          errors.forEach(function (error) {
            errorMessages += '<li>' + error + '</li>';
          });

          showAlert('danger', 'Errores en la importación:<ul>' + errorMessages + '</ul>');
        } else {
          // Otro tipo de error
          showAlert('danger', 'Hubo un error durante la importación.');
        }
      }
    });
  });

  // Función para mostrar alertas
  function showAlert(type, message) {
    var alertHtml = `
      <div class="alert alert-${type} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    `;
    $('#alert-container').html(alertHtml);
  }

  // Fetch products on page load
  fetchProducts();

  // Trigger search on input change
  searchInput.on('input', function () {
    fetchProducts();
  });

  // Trigger fetch on filter change
  storeFilter.on('change', function () {
    fetchProducts();
  });

  categoryFilter.on('change', function () {
    fetchProducts();
  });

  statusFilter.on('change', function () {
    fetchProducts();
  });

  // Abrir y cerrar el modal de filtros
  $('#openFilters').on('click', function () {
    $('#filterModal').addClass('open');
  });

  $('#closeFilterModal').on('click', function () {
    $('#filterModal').removeClass('open');
  });

  // Capturar los filtros y enviarlos para exportar a Excel
  $('#exportExcel').on('click', function () {
    var searchQuery = searchInput.val();
    var storeId = storeFilter.val();
    var categoryId = categoryFilter.val();
    var status = statusFilter.val();

    var params = {
      search: searchQuery,
      store_id: storeId,
      category_id: categoryId,
      status: status
    };

    var queryString = $.param(params);

    console.log(exportUrl + '?' + queryString);

    window.location.href = exportUrl + '?' + queryString;
  });

  // Manejar el clic en el botón de descargar plantilla
  $('#download-template').on('click', function (e) {
    e.preventDefault(); // Prevenir el comportamiento por defecto del enlace

    Swal.fire({
      customClass: {
        popup: 'swal-popup',
        title: 'swal-title',
        content: 'swal-content',
        confirmButton: 'btn btn-outline-primary',
        cancelButton: 'btn btn-outline-danger'
      },
      title: 'Atención',
      text: 'Recuerde crear todas las categorías de productos que necesite previo a la descarga de la plantilla',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Continuar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        // Redirigir a la ruta de descarga de plantilla
        window.location.href = $(this).attr('href');
      }
    });
  });
});

