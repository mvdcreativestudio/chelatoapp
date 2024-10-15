'use strict';

$(function () {
  let borderColor, bodyBg, headingColor;

  if (isDarkStyle) {
    borderColor = config.colors_dark.borderColor;
    bodyBg = config.colors_dark.bodyBg;
    headingColor = config.colors_dark.headingColor;
  } else {
    borderColor = config.colors.borderColor;
    bodyBg = config.colors.bodyBg;
    headingColor = config.colors.headingColor;
  }

  var dt_product_stock_table = $('#product-list-container');
  var searchInput = $('#searchProduct');
  var storeFilter = $('#storeFilter');
  var statusFilter = $('#statusFilter');
  var minStockFilter = $('#minStockFilter');
  var maxStockFilter = $('#maxStockFilter');

  function isFilterApplied() {
    return (
      searchInput.val().trim() !== '' ||
      storeFilter.val() !== '' ||
      statusFilter.val() !== '' ||
      minStockFilter.val() !== '' ||
      maxStockFilter.val() !== ''
    );
  }

  function resetFilters() {
    searchInput.val('');
    storeFilter.val('');
    statusFilter.val('');
    minStockFilter.val('');
    maxStockFilter.val('');
    fetchProducts();
  }

  function fetchProducts() {
    var ajaxUrl = dt_product_stock_table.data('ajax-url');
    var searchQuery = searchInput.val();
    var storeId = storeFilter.val();
    var status = statusFilter.val();
    var minStock = minStockFilter.val();
    var maxStock = maxStockFilter.val();
    var sortStock = $('#sortStockFilter').val(); // Nueva variable para el orden de stock

    $.ajax({
      url: ajaxUrl,
      method: 'GET',
      data: {
        search: searchQuery,
        store_id: storeId,
        status: status,
        min_stock: minStock,
        max_stock: maxStock,
        sort_stock: sortStock
      },
      success: function (response) {
        var rows = response.data;
        var cardContainer = $('#product-list-container').html(''); // Limpiar el contenedor

        if (rows.length === 0) {
          // Si no hay productos y hay filtros aplicados
          if (isFilterApplied()) {
            cardContainer.html(`
              <div class="alert alert-warning text-center w-100">
                <i class="bx bx-filter-alt"></i> No existen productos que concuerden con el filtro.
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
          // Mostrar productos si hay resultados
          rows.forEach(function (rowData) {
            const stockClass =
              rowData.stock === 0 ? 'bg-danger' :
              rowData.stock <= rowData.safety_margin ? 'bg-warning' : 'bg-success';

            const statusText = rowData.status === 1 ? 'Activo' : 'Inactivo';
            const statusTextClass = rowData.status === 1 ? 'text-success' : 'text-danger';

            const card = `
              <div class="col-md-6 col-lg-4 col-12 mb-4">
                <div class="card h-100 shadow-sm">
                  <div class="row g-0">
                    <div class="col-4 d-flex align-items-center">
                      <img src="${baseUrl + rowData.image}" class="img-fluid rounded-start w-100 h-auto object-fit-cover" alt="Imagen del producto" style="max-height: 150px;">
                    </div>
                    <div class="col-8">
                      <div class="card-body p-1 d-flex flex-column justify-content-between">
                        <div>
                          <h6 class="card-title">${rowData.name}</h6>
                          <p class="card-text mb-2">Stock: <span class="badge ${stockClass}">${rowData.stock}</span></p>
                          <p class="card-text mb-2">Empresa: ${rowData.store_name}</p>
                        </div>
                        <div>
                          <p class="card-text">Estado: <span class="${statusTextClass}">${statusText}</span></p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
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

  statusFilter.on('change', function () {
    fetchProducts();
  });

  // Trigger fetch on stock range change
  minStockFilter.on('input', function () {
    fetchProducts();
  });

  maxStockFilter.on('input', function () {
    fetchProducts();
  });

  $('#sortStockFilter').on('change', function () {
    fetchProducts();
  });

});
