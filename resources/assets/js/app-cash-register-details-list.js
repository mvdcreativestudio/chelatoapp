$(document).ready(function () {
  var baseUrl = window.location.origin + '/admin/';

  // Funcionalidad para ver ventas
  $(document).on('click', '.btn-view-sales', function () {
    var detailId = $(this).data('id');
    window.location.href = baseUrl + 'point-of-sale/details/sales/' + detailId;
  });

  // Funcionalidad para desplegar SOLO la tarjeta seleccionada
  $(document).on('click', '.clients-card-toggle', function () {
    let card = $(this).closest('.clients-card');
    let body = card.find('.clients-card-body');
    let icon = $(this).find('i');

    // Cerrar todas las demas tarjetas
    $('.clients-card-body').not(body).slideUp(200);
    $('.clients-card-toggle i').not(icon).removeClass('bx-chevron-up').addClass('bx-chevron-down');

    // Abrir o cerrar solo la tarjeta seleccionada
    body.stop(true, true).slideToggle(200);
    icon.toggleClass('bx-chevron-down bx-chevron-up');
  });

  // Abrir el modal de filtros
  $('#openFilters').click(function () {
    $('#filterModal').addClass('open');
  });

  // Cerrar el modal de filtros
  $('#closeFilterModal').click(function () {
    $('#filterModal').removeClass('open');
  });

  // Aplicar filtros con AJAX
  $('#applyFilters').click(function () {
    let status = $('#statusFilter').val();
    let startDate = $('#startDate').val();
    let endDate = $('#endDate').val();

    $.ajax({
      url: window.location.href,
      type: 'GET',
      data: { status, start_date: startDate, end_date: endDate },
      beforeSend: function () {
        $('#viewGrid .row').html('<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2">Cargando registros...</p></div>');
      },
      success: function (response) {
        if (!response || !response.details || response.details.length === 0) {
          $('#viewGrid .row').html('<div class="col-12 text-center py-5"><h5>No hay registros</h5><p class="text-muted">No se encontraron registros con los filtros seleccionados</p></div>');
          $('#viewList tbody').html('<tr><td colspan="7" class="text-center">No hay registros disponibles.</td></tr>');
        } else {
          renderData(response.details, window.currency_symbol);
        }
        $('#openCount').text(response.openCount || 0);
        $('#closedCount').text(response.closedCount || 0);
        $('#filterModal').removeClass('open');

        if (status || startDate || endDate) {
          $('#clearFiltersBtn').removeClass('d-none');
        }
      },
      error: function () {
        $('#viewGrid .row').html('<div class="col-12 text-center py-5 text-danger">Error al cargar los registros.</div>');
      }
    });
  });

  // Limpiar filtros y recargar la vista
  $('#clearFiltersBtn').click(function () {
    $('#statusFilter').val('');
    $('#startDate').val('');
    $('#endDate').val('');

    $.ajax({
      url: window.location.href,
      type: 'GET',
      beforeSend: function () {
        $('#viewGrid .row').html('<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>');
      },
      success: function (response) {
        if (!response || !response.details || response.details.length === 0) {
          $('#viewGrid .row').html('<div class="col-12 text-center py-5"><h5>No hay registros</h5></div>');
        } else {
          renderData(response.details, window.currency_symbol);
        }
        $('#openCount').text(response.openCount || 0);
        $('#closedCount').text(response.closedCount || 0);
        $('#clearFiltersBtn').addClass('d-none');
      },
      error: function () {
        $('#viewGrid .row').html('<div class="col-12 text-center py-5 text-danger">Error al cargar los registros.</div>');
      }
    });
  });

  // Cambiar a vista en Cards
  $('#btnViewGrid').click(function () {
    $('#viewGrid').removeClass('d-none');
    $('#viewList').addClass('d-none');
    $('#btnViewGrid').addClass('active');
    $('#btnViewList').removeClass('active');
  });

  // Cambiar a vista en Lista
  $('#btnViewList').click(function () {
    $('#viewList').removeClass('d-none');
    $('#viewGrid').addClass('d-none');
    $('#btnViewList').addClass('active');
    $('#btnViewGrid').removeClass('active');
  });

  // Funcion para renderizar datos en ambas vistas (Cards y Lista)
  function renderData(details, currency_symbol) {
    let cardsHtml = '';
    let tableHtml = '';

    details.forEach(detail => {
      let cashSales = detail.cash_sales || 0;
      let posSales = detail.pos_sales || 0;
      let mercadopagoSales = detail.mercadopago_sales || 0;
      let bankTransferSales = detail.bank_transfer_sales || 0;
      let internalCreditSales = detail.internal_credit_sales || 0;
      let totalExpenses = detail.total_expenses || 0;
      let cashFloat = detail.cash_float || 0;
      let totalSales = cashSales + posSales + mercadopagoSales + bankTransferSales + internalCreditSales;
      let finalCash = cashFloat + cashSales - totalExpenses;

      // Cards
      cardsHtml += `
        <div class="col-md-6 col-lg-4 col-xl-4 clients-card-container">
            <div class="clients-card position-relative">
                <div class="card-header bottom p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar avatar-sm">
                                <span class="avatar-initial rounded-circle ${detail.close_time ? 'bg-label-danger' : 'bg-label-success'}">
                                    <i class="bx ${detail.close_time ? 'bx-lock' : 'bx-lock-open'}"></i>
                                </span>
                            </div>
                            <h6 class="mb-0">${detail.name ? detail.name.charAt(0).toUpperCase() + detail.name.slice(1) : 'Registro #' + detail.id}</h6>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge ${detail.close_time ? 'bg-danger' : 'bg-success'}">
                                ${detail.close_time ? 'CERRADA' : 'ABIERTA'}
                            </span>
                            <div class="clients-card-toggle ms-2" style="cursor:pointer;">
                                <i class="bx bx-chevron-down fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clients-card-body" style="display: none;">
                    <div class="card-body p-3">
                        <div class="mb-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bx bx-calendar text-muted"></i>
                                <span class="text-muted">Apertura: ${formatDate(detail.open_time)}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bx bx-time text-muted"></i>
                                <span class="text-muted">${formatTime(detail.open_time)}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bx bx-calendar text-muted"></i>
                                <span class="text-muted">Cierre: ${detail.close_time ? formatDate(detail.close_time) : 'Pendiente'}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bx bx-time text-muted"></i>
                                <span class="text-muted">${detail.close_time ? formatTime(detail.close_time) : '-'}</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bx bx-calculator text-muted"></i>
                                <span class="text-muted">Fondo de caja: ${currency_symbol}${numberFormat(cashFloat)}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-2 mt-2">
                                <span class="text-muted fw-semibold">VENTAS POR METODO:</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bx bx-money text-success"></i>
                                <span class="text-muted">Efectivo: ${currency_symbol}${numberFormat(cashSales)}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bx bx-credit-card-alt text-info"></i>
                                <span class="text-muted">POS (Cred/Deb): ${currency_symbol}${numberFormat(posSales)}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bx bxl-paypal text-primary"></i>
                                <span class="text-muted">Mercadopago: ${currency_symbol}${numberFormat(mercadopagoSales)}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bx bx-transfer text-warning"></i>
                                <span class="text-muted">Transferencias: ${currency_symbol}${numberFormat(bankTransferSales)}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bx bx-notepad text-secondary"></i>
                                <span class="text-muted">Cuenta corriente: ${currency_symbol}${numberFormat(internalCreditSales)}</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bx bx-wallet text-danger"></i>
                                <span class="text-muted">Gastos: ${currency_symbol}${numberFormat(totalExpenses)}</span>
                            </div>
                            ${detail.close_time && detail.actual_cash !== null ? `
                            <hr class="my-2">
                            <div class="d-flex align-items-center gap-2 mb-2 mt-2">
                                <span class="text-muted fw-semibold">VERIFICACION DE EFECTIVO:</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bx bx-calculator text-primary"></i>
                                <span class="text-muted">Efectivo esperado: ${currency_symbol}${numberFormat(finalCash)}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bx bx-money text-info"></i>
                                <span class="text-muted">Efectivo contado: ${currency_symbol}${numberFormat(detail.actual_cash)}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                ${detail.cash_difference == 0
                                    ? '<i class="bx bx-check-circle text-success"></i><span class="text-success fw-semibold">Cuadra perfecto</span>'
                                    : (detail.cash_difference > 0
                                        ? `<i class="bx bx-up-arrow-alt text-warning"></i><span class="text-warning fw-semibold">Sobrante: ${currency_symbol}${numberFormat(Math.abs(detail.cash_difference))}</span>`
                                        : `<i class="bx bx-down-arrow-alt text-danger"></i><span class="text-danger fw-semibold">Faltante: ${currency_symbol}${numberFormat(Math.abs(detail.cash_difference))}</span>`
                                    )
                                }
                            </div>
                            ` : ''}
                        </div>
                        <div class="card-footer bg-light border-top pt-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <button class="btn btn-sm btn-primary btn-view-sales" data-id="${detail.id}">
                                    Ver Ventas <i class='bx bx-right-arrow-alt'></i>
                                </button>
                                <div class="text-end">
                                    <small class="text-muted d-block">Efectivo final en caja:</small>
                                    <h5 class="mb-0 fw-semibold">
                                        ${currency_symbol}${numberFormat(detail.close_time && detail.actual_cash !== null ? detail.actual_cash : finalCash)}
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      `;

      // Table rows
      tableHtml += `
        <tr>
            <td>${detail.id}</td>
            <td>${detail.name ? detail.name.charAt(0).toUpperCase() + detail.name.slice(1) : 'Registro #' + detail.id}</td>
            <td><span class="badge ${detail.close_time ? 'bg-danger' : 'bg-success'}">${detail.close_time ? 'CERRADA' : 'ABIERTA'}</span></td>
            <td>${formatDate(detail.open_time)} ${formatTime(detail.open_time)}</td>
            <td>${detail.close_time ? formatDate(detail.close_time) + ' ' + formatTime(detail.close_time) : 'Pendiente'}</td>
            <td>
                <div class="text-end">
                    <div><strong>Efectivo:</strong> ${currency_symbol}${numberFormat(cashSales)}</div>
                    <div><strong>POS:</strong> ${currency_symbol}${numberFormat(posSales)}</div>
                    <div><strong>MP:</strong> ${currency_symbol}${numberFormat(mercadopagoSales)}</div>
                    <div><strong>Transf:</strong> ${currency_symbol}${numberFormat(bankTransferSales)}</div>
                    <div><strong>Cta.Cte:</strong> ${currency_symbol}${numberFormat(internalCreditSales)}</div>
                    <div class="border-top mt-1 pt-1"><strong>Total:</strong> ${currency_symbol}${numberFormat(totalSales)}</div>
                </div>
            </td>
            <td>
                <button class="btn btn-primary btn-sm btn-view-sales" data-id="${detail.id}">
                    Ver Ventas <i class='bx bx-right-arrow-alt'></i>
                </button>
            </td>
        </tr>
      `;
    });

    const gridContainer = $('#viewGrid .row');
    const listContainer = $('#viewList tbody');

    if (gridContainer.length > 0) {
        gridContainer.html(cardsHtml);
    }

    if (listContainer.length > 0) {
        listContainer.html(tableHtml);
    }
  }

  // Funciones auxiliares
  function formatDate(dateString) {
    if (!dateString) return '-';
    let date = new Date(dateString);
    return date.toLocaleDateString('es-ES', { day: '2-digit', month: 'long', year: 'numeric' });
  }

  function formatTime(dateString) {
    if (!dateString) return '-';
    let date = new Date(dateString);
    return date.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', hour12: true });
  }

  function numberFormat(number) {
    return new Intl.NumberFormat('es-ES', { minimumFractionDigits: 0 }).format(number || 0);
  }
});
