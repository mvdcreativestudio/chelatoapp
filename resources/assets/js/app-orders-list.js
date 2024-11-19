'use strict';

$(function () {
  let currencySymbol = window.currencySymbol;
  let uniqueClients = new Set();
  let uniqueStore = new Set();

  var dt_order_list_container = $('#order-list-container');
  var searchInput = $('#searchOrder');
  var paymentStatusFilter = $('#paymentStatusFilter');
  var shippingStatusFilter = $('#shippingStatusFilter');
  var clientFilter = $('#clientFilter');
  var storeFilter = $('#storeFilter');
  var startDateFilter = $('#startDate');
  var endDateFilter = $('#endDate');

  // Función para verificar si algún filtro está aplicado
  function isFilterApplied() {
    return (
      searchInput.val().trim() !== '' ||
      paymentStatusFilter.val() !== '' ||
      shippingStatusFilter.val() !== '' ||
      clientFilter.val() !== '' ||
      storeFilter.val() !== '' ||
      startDateFilter.val().trim() !== '' ||
      endDateFilter.val().trim() !== ''
    );
  }

  // Función para resetear los filtros
  function resetFilters() {
    searchInput.val('');
    paymentStatusFilter.val('');
    shippingStatusFilter.val('');
    clientFilter.val('');
    storeFilter.val('');
    startDateFilter.val('');
    endDateFilter.val('');
    fetchOrders();
  }

  $(document).on('click', '#clearFilters', function () {
    resetFilters();
  });
  
  
  // Función para obtener las órdenes
  function fetchOrders() {
    var ajaxUrl = dt_order_list_container.data('ajax-url');
    var searchQuery = searchInput.val();
    var paymentStatus = paymentStatusFilter.val();
    var shippingStatus = shippingStatusFilter.val();
    var client = clientFilter.val();
    var store = storeFilter.val();
    var startDate = startDateFilter.val();
    var endDate = endDateFilter.val();


    $.ajax({
      url: ajaxUrl,
      method: 'GET',
      data: {
        search: searchQuery,
        payment_status: paymentStatus,
        shipping_status: shippingStatus,
        client: client,
        store: store,
        start_date: startDate,
        end_date: endDate
      },
      success: function (response) {
        var rows = response.data;
        var cardContainer = $('#order-list-container').html('');

        // Limpiar los filtros únicos para cliente y tienda
        uniqueClients.clear();
        uniqueStore.clear();

        if (rows.length === 0) {
          if (isFilterApplied()) {
            cardContainer.html(`
              <div class="alert alert-warning text-center w-100">
                <i class="bx bx-filter-alt"></i> No hay órdenes que coincidan con los filtros.
                <br>
                <button id="clearFilters" class="btn btn-outline-danger mt-3">Borrar filtros</button>
              </div>
            `);
          } else {
            cardContainer.html(`
              <div class="alert alert-info text-center w-100">
                <i class="bx bx-info-circle"></i> No existen órdenes disponibles.
              </div>
            `);
          }
        } else {
          rows.forEach(function (orderData) {
            if (orderData.client_name) {
              uniqueClients.add(orderData.client_name);
            }
            if (orderData.store_name) {
              uniqueStore.add(orderData.store_name);
            }
            const paymentStatusText =
              orderData.payment_status === 'paid'
                ? 'Pagado'
                : orderData.payment_status === 'pending'
                  ? 'Pago Pendiente'
                  : 'Pago Fallido';
            const paymentStatusClass =
              orderData.payment_status === 'paid'
                ? 'bg-success'
                : orderData.payment_status === 'pending'
                  ? 'bg-warning'
                  : 'bg-danger';
            const shippingStatusText =
              orderData.shipping_status === 'delivered'
                ? 'Entregado'
                : orderData.shipping_status === 'shipped'
                  ? 'Enviado'
                  : 'No enviado';
            const shippingStatusClass =
              orderData.shipping_status === 'delivered'
                ? 'bg-success'
                : orderData.shipping_status === 'shipped'
                  ? 'bg-warning'
                  : 'bg-danger';

            const card = `
                  <div class="col-md-6 col-lg-4 col-12 mb-4">
                    <div class="order-card position-relative">
                      <div class="order-card-body">
                        <h5 class="order-title">#${orderData.id}-${orderData.client_name}</h5>
                        <p class="order-date text-muted small">${moment(orderData.date).format('DD/MM/YYYY')}</p>
                        <p class="order-payment-status"><span class="badge ${paymentStatusClass}">${paymentStatusText}</span></p>
                        <p class="order-shipping-status"><span class="badge ${shippingStatusClass}">${shippingStatusText}</span></p>
                        <h6 class="order-total">${currencySymbol}${parseFloat(orderData.total).toFixed(2)}</h6>
                        <div class="d-inline-flex justify-content-end mt-auto mb-2 gap-1">
                          <a href="${baseUrl}admin/orders/${orderData.uuid}" class="btn view-order p-1"><i class="far fa-eye"></i></a>
                          <button data-id="${orderData.id}" class="btn delete-order p-1 delete-record"><i class="far fa-trash-alt"></i></button>
                        </div>
                      </div>
                    </div>
                  </div>
                `;
            cardContainer.append(card);
          });

          // Populate filters with unique values
          populateFilterOptions(clientFilter, uniqueClients, 'Todos los clientes');
          populateFilterOptions(storeFilter, uniqueStore, 'Todas las tiendas');
        }
      },
      error: function (xhr, status, error) {
        console.error('Error al obtener los datos:', error);
      }
    });
  }

  // Helper function to populate filter options
  function populateFilterOptions(filter, items, defaultOptionText) {
    const currentValue = filter.val();
    filter.empty(); // Clear existing options
    filter.append(`<option value="">${defaultOptionText}</option>`); // Add default option
    items.forEach(item => {
      filter.append(`<option value="${item}">${item}</option>`);
    });
    filter.val(currentValue); // Set the selected value
  }

  // Click en la tarjeta de Total de Ventas para resetear todos los filtros
  $('.card-border-shadow-primary').on('click', function () {
    resetFilters();
  });

  // Click en la tarjeta de Ventas Pagas para aplicar filtro
  $('.card-border-shadow-success').on('click', function () {
    paymentStatusFilter.val('paid');
    fetchOrders();
  });

  // Click en la tarjeta de Ventas Fallidas para aplicar filtro
  $('.card-border-shadow-danger').on('click', function () {
    paymentStatusFilter.val('failed');
    fetchOrders();
  });

  // Click en la tarjeta de Ventas Impagas para aplicar filtro
  $('.card-border-shadow-warning').on('click', function () {
    paymentStatusFilter.val('pending');
    fetchOrders();
  });

  $('#openFilters').on('click', function () {
    $('#filterModal').addClass('open');
  });

  $('#closeFilterModal').on('click', function () {
    $('#filterModal').removeClass('open');
  });

  $(document).on('click', '.delete-record', function () {
    var recordId = $(this).data('id');
    Swal.fire({
      title: '¿Estás seguro?',
      text: 'Esta acción eliminará completamente el pedido, perdiendo definitivamente sus datos',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Sí, eliminar!',
      cancelButtonText: 'Cancelar'
    }).then(result => {
      if (result.isConfirmed) {
        $.ajax({
          url: baseUrl + 'admin/orders/' + recordId,
          type: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function (result) {
            if (result.success) {
              Swal.fire(
                'Eliminado!',
                'El pedido ha sido eliminado y el stock de los productos ha sido reintegrado.',
                'success'
              );
              fetchOrders(); // Recarga la lista de órdenes
            } else {
              Swal.fire('Error!', 'No se pudo eliminar el pedido. Intente de nuevo.', 'error');
            }
          },
          error: function (xhr, ajaxOptions, thrownError) {
            Swal.fire('Error!', 'No se pudo eliminar el pedido: ' + xhr.responseJSON.message, 'error');
          }
        });
      }
    });
  });

  searchInput.on('input', function () {
    fetchOrders();
  });

  paymentStatusFilter.on('change', function () {
    fetchOrders();
  });

  shippingStatusFilter.on('change', function () {
    fetchOrders();
  });

  clientFilter.on('change', function () {
    fetchOrders();
  });

  storeFilter.on('change', function () {
    fetchOrders();
  });

  startDateFilter.on('change', function () {
    fetchOrders();
  });

  endDateFilter.on('change', function () {
    fetchOrders();
  });

  fetchOrders();
});
