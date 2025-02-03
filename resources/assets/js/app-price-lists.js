'use strict';

$(function () {
  var dt_price_list_container = $('#price-list-container');

  // Función para obtener y renderizar las listas de precios
  function fetchPriceLists() {
    var ajaxUrl = dt_price_list_container.data('ajax-url');

    $.ajax({
      url: ajaxUrl,
      method: 'GET',
      success: function (response) {
        var rows = response.data;
        var cardContainer = $('#price-list-container').html(''); // Limpiar el contenedor

        if (rows.length === 0) {
          cardContainer.html(`
            <div class="alert alert-info text-center w-100">
              <i class="bx bx-info-circle"></i> No existen listas de precios disponibles.
            </div>
          `);
        } else {
          rows.forEach(function (rowData) {
            const card = `
              <div class="col-md-6 col-lg-4 col-12 mb-4">
                <a href="${baseUrl}admin/price-lists/${rowData.id}" class="text-decoration-none">
                  <div class="price-list-card">
                    <div class="price-list-card-body">
                      <h5 class="price-list-title">${rowData.name}</h5>
                      <p class="price-list-description">${rowData.description || 'Sin descripción'}</p>
                      <p class="price-list-store">
                        <i class='bx bx-store-alt'></i> ${rowData.store_name}
                      </p>
                      <div class="price-list-info">
                        <i class='bx bx-purchase-tag'></i>
                        Precios modificados: ${rowData.products_count}
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

  // Cargar listas de precios al cargar la página
  fetchPriceLists();
});
