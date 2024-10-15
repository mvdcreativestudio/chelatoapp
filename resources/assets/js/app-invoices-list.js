$(function () {
  let borderColor, bodyBg, headingColor;
  let $currencySymbol = $('.datatables-invoice').data('symbol');

  if (isDarkStyle) {
    borderColor = config.colors_dark.borderColor;
    bodyBg = config.colors_dark.bodyBg;
    headingColor = config.colors_dark.headingColor;
  } else {
    borderColor = config.colors.borderColor;
    bodyBg = config.colors.bodyBg;
    headingColor = config.colors.headingColor;
  }

  var dt_invoice_table = $('.datatables-invoice');

  $.fn.dataTable.ext.errMode = 'throw';

  if (dt_invoice_table.length) {
    try {
      var dt_invoices = dt_invoice_table.DataTable({
        ajax: {
          url: 'invoices/datatable',
          dataSrc: 'data'
        },
        columns: [
          { data: 'id', type: 'num'},
          { data: 'store_name' },
          { data: 'client_name' },
          { data: 'order_id' },
          { data: 'date' },
          { data: 'type' },
          { data: 'reason' },
          { data: 'balance' },
          { data: 'currency' },
          { data: 'total' },
          { data: 'associated_id' },
          { data: 'status' },
          { data: 'actions' }
        ],
        columnDefs: [
          {
            targets: 0,
            orderable: false,
            render: function (data, type, full, meta) {
              return '#' + data;
            }
          },
          {
            targets: 1,
            render: function (data, type, full, meta) {
              return full['store_name'];
            }
          },
          {
            targets: 2,
            render: function (data, type, full, meta) {
              var $name = full['client_name'] + ' ' + full['client_lastname'],
                $initials = $name.replace(/[^A-Z]/g, '').substring(0, 2),
                stateNum = Math.floor(Math.random() * 6),
                states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'],
                $state = states[stateNum],
                $avatar = full['client_avatar'];

              return (
                '<div class="d-flex justify-content-start align-items-center">' +
                '<div class="avatar me-2">' +
                ($avatar
                  ? '<img src="' + $avatar + '" alt="Avatar" class="rounded-circle">'
                  : '<span class="avatar-initial rounded-circle bg-label-' + $state + '">' + $initials + '</span>') +
                '</div>' +
                '<div class="d-flex flex-column">' +
                '<h6 class="mb-0">' +
                $name +
                '</h6>' +
                '</div>' +
                '</div>'
              );
            }
          },
          {
            targets: 3,
            render: function (data, type, full, meta) {
              return (
                '<a href="' +
                baseUrl +
                'admin/orders/' +
                full['order_uuid'] +
                '/show" class="text-body">' +
                full['order_id'] +
                '</a>'
              );
            }
          },
          {
            targets: 4,
            render: function (data, type, full, meta) {
              return data ? moment(data.date).format('DD-MM-YYYY HH:mm') : 'Fecha inválida';
            }
          },
          {
            targets: 5,
            render: function (data, type, full, meta) {
              return data;
            }
          },
          {
            targets: 6,
            render: function (data, type, full, meta) {
              return data ? data : 'Facturación';
            }
          },
          {
            targets: 7,
            render: function (data, type, full, meta) {
              return data ? $currencySymbol + data : 'N/A';
            }
          },
          {
            targets: 8,
            render: function (data, type, full, meta) {
              return full['currency'];
            }
          },
          {
            targets: 9,
            render: function (data, type, full, meta) {
              return $currencySymbol + data;
            }
          },
          {
            targets: 10, // Nueva columna para el ID asociado
            render: function (data, type, full, meta) {
              if (full['associated_id']) {
                return (
                  '<a href="#" class="search-associated-id" data-id="' +
                  full['associated_id'] +
                  '">#' +
                  full['associated_id'] +
                  '</a>'
                );
              }
              return 'N/A';
            }
          },
          {
            targets: 11, // Posición de la nueva columna Status
            render: function (data, type, full, meta) {
              var badgeClass;
              var translatedStatus;

              switch (data) {
                case 'CFE_UNKNOWN_ERROR':
                  badgeClass = 'badge bg-danger';
                  translatedStatus = 'Error desconocido';
                  break;
                case 'CREATED':
                  badgeClass = 'badge bg-info';
                  translatedStatus = 'Creado';
                  break;
                case 'CREATED_WITHOUT_CAE_NRO':
                  badgeClass = 'badge bg-warning';
                  translatedStatus = 'Creado sin número CAE';
                  break;
                case 'SENT':
                  badgeClass = 'badge bg-success';
                  translatedStatus = 'Enviado';
                  break;
                case 'SCHEDULED':
                  badgeClass = 'badge bg-primary';
                  translatedStatus = 'Programado';
                  break;
                case 'SCHEDULED_CONNECTION_ERR':
                  badgeClass = 'badge bg-danger';
                  translatedStatus = 'Error de conexión programado';
                  break;
                case 'SCHEDULED_WITHOUT_CAE_NRO':
                  badgeClass = 'badge bg-warning';
                  translatedStatus = 'Programado sin número CAE';
                  break;
                case 'PROCESSED_ACCEPTED':
                  badgeClass = 'badge bg-success';
                  translatedStatus = 'Procesado y Aceptado';
                  break;
                case 'PROCESSED_REJECTED':
                  badgeClass = 'badge bg-danger';
                  translatedStatus = 'Procesado y Rechazado';
                  break;
                case 'PROCESSED_RELIQUIDATED':
                  badgeClass = 'badge bg-info';
                  translatedStatus = 'Procesado y Reliquidado';
                  break;
                case 'FORMAT_REJECTED':
                  badgeClass = 'badge bg-danger';
                  translatedStatus = 'Rechazado por Formato';
                  break;
                case 'REPORTED_DAILY_REPORT':
                  badgeClass = 'badge bg-success';
                  translatedStatus = 'Reportado en Informe Diario';
                  break;
                case 'SOBRE_DUPLICATED':
                  badgeClass = 'badge bg-danger';
                  translatedStatus = 'Sobre Duplicado';
                  break;
                case 'DUPLICATED_AT_DGI':
                  badgeClass = 'badge bg-danger';
                  translatedStatus = 'Duplicado en DGI';
                  break;
                case 'BAD_CUSTOM_SERIE_NUMBER':
                  badgeClass = 'badge bg-danger';
                  translatedStatus = 'Número de Serie Personalizado Incorrecto';
                  break;
                case 'DELETED_MISSING_CAE':
                  badgeClass = 'badge bg-secondary';
                  translatedStatus = 'Eliminado - Falta CAE';
                  break;
                default:
                  badgeClass = 'badge bg-secondary';
                  translatedStatus = 'Estado Desconocido';
              }

              return '<span class="' + badgeClass + '">' + translatedStatus + '</span>';
            }
          },
          {
            targets: -1,
            orderable: false,
            render: function (data, type, full, meta) {
              var hideEmitirNota =
                full['type'].includes('Nota de Crédito') || full['type'].includes('Nota de Débito') ? 'd-none' : '';

              var hideEmitirRecibo = full['is_receipt'] ? 'd-none' : '';

              // Ahora si el invoice tiene hide_emit se oculta el botón de emitir nota
              var hideEmit = full['hide_emit'] ? 'd-none' : '';

                return (
                  '<div class="d-flex justify-content-center align-items-center">' +
                  '<button class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>' +
                  '<div class="dropdown-menu dropdown-menu-end m-0">' +
                  '<a href="' +
                  full['qrUrl'] +
                  '" target="_blank" class="dropdown-item">Ver QR</a>' +
                  '<a href="' +
                  baseUrl +
                  'admin/orders/' +
                  full['order_uuid'] +
                  '" class="dropdown-item">Ver Venta</a>' +
                  '<a href="#" class="dropdown-item btn-ver-detalles" data-id="' +
                  full['id'] +
                  '">Ver Detalles</a>' +
                  '<a href="' +
                  baseUrl +
                  'admin/invoices/download/' +
                  full['id'] +
                  '" class="dropdown-item">Descargar PDF</a>' +
                  '<a href="#" class="dropdown-item btn-emitir-nota ' +
                  hideEmitirNota +
                  hideEmitirRecibo +
                  hideEmit +
                  '" data-id="' +
                  full['id'] +
                  '">Emitir Nota</a>' +
                  '<a href="#" class="dropdown-item btn-emitir-recibo ' +
                  hideEmitirNota +
                  hideEmitirRecibo +
                  hideEmit +
                  '" data-id="' +
                  full['id'] +
                  '">Emitir Recibo</a>' +
                  '</div>' +
                  '</div>'
                );
            }
          }
        ],
        order: [[0, 'desc']],
        dom:
          '<"card-header d-flex flex-column flex-md-row align-items-start align-items-md-center"<"ms-n2"f><"d-flex align-items-md-center justify-content-md-end mt-2 mt-md-0"l<"dt-action-buttons"B>>' +
          '>t' +
          '<"row mx-2"' +
          '<"col-sm-12 col-md-6"i>' +
          '<"col-sm-12 col-md-6"p>' +
          '>',
        lengthMenu: [10, 25, 50, 100],
        language: {
          search: '',
          searchPlaceholder: 'Buscar...',
          sLengthMenu: '_MENU_',
          info: 'Mostrando _START_ a _END_ de _TOTAL_ facturas',
          infoFiltered: 'filtrados de _MAX_ facturas',
          paginate: {
            first: '<<',
            last: '>>',
            next: '>',
            previous: '<'
          },
          pagingType: 'full_numbers',
          emptyTable: 'No hay facturas disponibles',
          dom: 'Bfrtip',
          renderer: 'bootstrap'
        },
        rowCallback: function (row, data, index) {
          if ((data['type'].includes('Nota de Crédito') || data['type'].includes('Nota de Débito')) || data['is_receipt']) {
            $('td', row).eq(5).css('background-color', '#F5F5F9').css('color', '#566A7F');
          }
        }
      });

      $('.datatables-invoice tbody').on('click', '.search-associated-id', function (e) {
        e.preventDefault();
        var associatedId = $(this).data('id');
        dt_invoices.search('#' + associatedId).draw();
      });

      // Evento para mostrar el modal con detalles
      $('.datatables-invoice tbody').on('click', '.btn-ver-detalles', function () {
        var invoice = dt_invoices.row($(this).parents('tr')).data();

        $('#modalDetalle .modal-title').text('Detalles del CFE');
        $('#modalDetalle .modal-body').html(`
          <p><strong>Serie:</strong> ${invoice.serie}</p>
          <p><strong>CFE ID:</strong> ${invoice.cfeId}</p>
          <p><strong>Número:</strong> ${invoice.nro}</p>
          <p><strong>CAE Number:</strong> ${invoice.caeNumber}</p>
          <p><strong>CAE Range:</strong> ${invoice.caeRange}</p>
          <p><strong>CAE Expiration Date:</strong> ${moment(invoice.caeExpirationDate).format('DD-MM-YYYY')}</p>
          <p><strong>Total:</strong> ${$currencySymbol}${invoice.total}</p>
          <p><strong>Emisión Date:</strong> ${moment(invoice.emitionDate).format('DD-MM-YYYY')}</p>
          <p><strong>Hash:</strong> ${invoice.sentXmlHash}</p>
          <p><strong>Security Code:</strong> ${invoice.securityCode}</p>
          <p><strong>QR URL:</strong> <a href="${invoice.qrUrl}" target="_blank">${invoice.qrUrl}</a></p>
        `);

        $('#modalDetalle').modal('show');
      });

      $('.toggle-column').on('change', function () {
        var column = dt_invoices.column($(this).attr('data-column'));
        column.visible(!column.visible());
      });

      $('.datatables-invoice tbody').on('click', '.btn-emitir-recibo', function () {
        var invoiceId = $(this).data('id');
        $('#emitirReciboForm').attr('action', baseUrl + 'admin/invoices/' + invoiceId + '/emit-receipt');
        $('#emitirReciboModal').modal('show');
      });

      // Estilos buscador y paginación
      $('.dataTables_length label select').addClass('form-select form-select-sm');
      $('.dataTables_filter label input').addClass('form-control');

      $('.datatables-invoice tbody').on('click', '.btn-emitir-nota', function () {
        var invoiceId = $(this).data('id');
        $('#emitirNotaForm').attr('action', baseUrl + 'admin/invoices/' + invoiceId + '/emit-note');
        $('#emitirNotaModal').modal('show');
      });

    } catch (error) {
      console.log(error);
    }
  }

  $('#btn-update-cfes').on('click', function () {
    $.ajax({
      url: baseUrl + 'admin/invoices/update-cfes',
      type: 'POST',
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
      beforeSend: function () {
        $('#btn-update-cfes').prop('disabled', true).text('Actualizando...');
      },
      success: function (response) {
        $('#btn-update-cfes').prop('disabled', false).text('Actualizar CFEs');
        if (response.success) {
          // Actualizar la tabla con los nuevos datos
          dt_invoices.ajax.reload();
        } else if (response.error) {
          toastr.error(response.error, 'Error');
        }
      },
      error: function (xhr, status, error) {
        $('#btn-update-cfes').prop('disabled', false).text('Actualizar CFEs');
        toastr.error('Ocurrió un error durante la actualización de los CFEs.', 'Error');
      }
    });
  });

  $('#btn-update-all-cfes').on('click', function () {
    $.ajax({
      url: baseUrl + 'admin/invoices/update-all-cfes',
      type: 'POST',
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
      beforeSend: function () {
        $('#btn-update-all-cfes').prop('disabled', true).text('Actualizando...');
      },
      success: function (response) {
        $('#btn-update-all-cfes').prop('disabled', false).text('Actualizar todos los CFEs');
        if (response.success) {
          // Actualizar la tabla con los nuevos datos
          dt_invoices.ajax.reload();
        } else if (response.error) {
          toastr.error(response.error, 'Error');
        }
      },
      error: function (xhr, status, error) {
        $('#btn-update-all-cfes').prop('disabled', false).text('Actualizar todos los CFEs');
        toastr.error('Ocurrió un error durante la actualización de los CFEs.', 'Error');
      }
    });
  });
});
