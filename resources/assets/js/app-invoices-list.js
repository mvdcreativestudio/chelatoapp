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
          { data: 'id', type: 'num' },
          { data: 'store_name' },
          { data: 'client_name' },
          { data: 'order_id' },
          { data: 'date' },
          { data: 'type' },
          { data: 'balance' },
          { data: 'total' },
          { data: 'associated_id' },
          { data: 'status' },
          // { data: 'actions' }
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

                '<h6 class="mb-0">' +
                $name +
                '</h6>'
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
              return data ? $currencySymbol + data : 'N/A';
            }
          },
          {
            targets: 7,
            render: function (data, type, full, meta) {
              return data;
            }
          },
          {
            targets: 8, // Nueva columna para el ID asociado
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
            targets: 9, // Posición de la columna Status
            render: function (data, type, full, meta) {
              var iconClass = '';
              var tooltipText = '';
              // Elegimos el icono + color según el estado
              switch (data) {
                case 'CFE_UNKNOWN_ERROR':
                  iconClass = 'fa-exclamation-circle text-danger';
                  tooltipText = 'Error desconocido';
                  break;
                case 'CREATED':
                  iconClass = 'fa-circle-check text-info';
                  tooltipText = 'Creado';
                  break;
                case 'CREATED_WITHOUT_CAE_NRO':
                  iconClass = 'fa-triangle-exclamation text-warning';
                  tooltipText = 'Creado sin número CAE';
                  break;
                case 'SENT':
                  iconClass = 'fa-check-circle text-success';
                  tooltipText = 'Enviado';
                  break;
                case 'SCHEDULED':
                  iconClass = 'fa-clock text-primary';
                  tooltipText = 'Programado';
                  break;
                case 'SCHEDULED_CONNECTION_ERR':
                  iconClass = 'fa-exclamation-triangle text-danger';
                  tooltipText = 'Error de conexión programado';
                  break;
                case 'SCHEDULED_WITHOUT_CAE_NRO':
                  iconClass = 'fa-circle-exclamation text-warning';
                  tooltipText = 'Programado sin número CAE';
                  break;
                case 'PROCESSED_ACCEPTED':
                  iconClass = 'fa-circle-check text-success';
                  tooltipText = 'Procesado y Aceptado';
                  break;
                case 'PROCESSED_REJECTED':
                  iconClass = 'fa-circle-xmark text-danger';
                  tooltipText = 'Procesado y Rechazado';
                  break;
                case 'PROCESSED_RELIQUIDATED':
                  iconClass = 'fa-arrows-rotate text-info';
                  tooltipText = 'Procesado y Reliquidado';
                  break;
                case 'FORMAT_REJECTED':
                  iconClass = 'fa-file-circle-xmark text-danger';
                  tooltipText = 'Rechazado por Formato';
                  break;
                case 'REPORTED_DAILY_REPORT':
                  iconClass = 'fa-file-circle-check text-success';
                  tooltipText = 'Reportado en Informe Diario';
                  break;
                case 'SOBRE_DUPLICATED':
                  iconClass = 'fa-clone text-danger';
                  tooltipText = 'Sobre Duplicado';
                  break;
                case 'DUPLICATED_AT_DGI':
                  iconClass = 'fa-clone text-danger';
                  tooltipText = 'Duplicado en DGI';
                  break;
                case 'BAD_CUSTOM_SERIE_NUMBER':
                  iconClass = 'fa-ban text-danger';
                  tooltipText = 'Número de Serie Personalizado Incorrecto';
                  break;
                case 'DELETED_MISSING_CAE':
                  iconClass = 'fa-trash text-secondary';
                  tooltipText = 'Eliminado - Falta CAE';
                  break;
                default:
                  iconClass = 'fa-question-circle text-secondary';
                  tooltipText = 'Estado Desconocido';
              }
              // Devolvemos un ícono con tooltip
              // data-bs-title -> texto del tooltip | data-bs-toggle="tooltip" -> activa Bootstrap tooltip
              return (
                '<span data-bs-toggle="tooltip" data-bs-placement="top" title="' + tooltipText + '">' +
                  '<i class="fa ' + iconClass + '"></i>' +
                '</span>'
              );
            }
          },
          // {
          //   targets: -1,
          //   orderable: false,
          //   render: function (data, type, full, meta) {
          //     var hideEmitirNota =
          //       full['type'].includes('Nota de Crédito') || full['type'].includes('Nota de Débito') ? 'd-none' : '';

          //     var hideEmitirRecibo = full['is_receipt'] ? 'd-none' : '';

          //     // Ahora si el invoice tiene hide_emit se oculta el botón de emitir nota
          //     var hideEmit = full['hide_emit'] ? 'd-none' : '';
          //     var emailButtonHtml =
          //       '<a href="#" class="dropdown-item btn-send-email" data-id="' +
          //       full['id'] +
          //       '" data-email="' +
          //       full['client_email'] +
          //       '">Enviar factura por correo</a>';
          //     if (isStoreConfigEmailEnabled) {
          //       emailButtonHtml =
          //         '<a href="#" class="dropdown-item btn-send-email" data-id="' +
          //         full['id'] +
          //         '" data-email="' +
          //         full['client_email'] +
          //         '">Enviar factura por correo</a>';
          //     } else {
          //       emailButtonHtml =
          //         '<a href="#" class="dropdown-item btn-send-email disabled" data-id="' +
          //         full['id'] +
          //         '" data-email="' +
          //         full['client_email'] +
          //         '" title="Debe asociarse a una tienda para enviar correos" data-bs-toggle="tooltip" data-bs-placement="top">Enviar factura por correo</a>';
          //     }

          //     return (
          //       '<div class="d-flex justify-content-center align-items-center">' +
          //       '<button class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>' +
          //       '<div class="dropdown-menu dropdown-menu-end m-0">' +
          //       '<a href="' +
          //       full['qrUrl'] +
          //       '" target="_blank" class="dropdown-item">Ver QR</a>' +
          //       '<a href="' +
          //       baseUrl +
          //       'admin/orders/' +
          //       full['order_uuid'] +
          //       '" class="dropdown-item">Ver Venta</a>' +
          //       '<a href="#" class="dropdown-item btn-ver-detalles" data-id="' +
          //       full['id'] +
          //       '">Ver Detalles</a>' +
          //       '<a href="' +
          //       baseUrl +
          //       'admin/invoices/download/' +
          //       full['id'] +
          //       '" class="dropdown-item">Descargar PDF</a>' +
          //       '<a href="#" class="dropdown-item btn-emitir-nota ' +
          //       hideEmitirNota +
          //       hideEmitirRecibo +
          //       hideEmit +
          //       '" data-id="' +
          //       full['id'] +
          //       '">Emitir Nota</a>' +
          //       '<a href="#" class="dropdown-item btn-emitir-recibo ' +
          //       hideEmitirNota +
          //       hideEmitirRecibo +
          //       hideEmit +
          //       '" data-id="' +
          //       full['id'] +
          //       '">Emitir Recibo</a>' +
          //       // add open modal button
          //       emailButtonHtml +
          //       '</div>' +
          //       '</div>'
          //     );
          //   }
          // }
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
          if (
            data['type'].includes('Nota de Crédito') ||
            data['type'].includes('Nota de Débito') ||
            data['is_receipt']
          ) {
            $('td', row).eq(5).css('background-color', '#F5F5F9').css('color', '#566A7F');
          }
        }
      });

      $(document).on('click', '.search-associated-id', function (e) {
        e.preventDefault();
        var associatedId = $(this).data('id');
        dt_invoices.search('#' + associatedId).draw();
      });

      // Evento para mostrar el modal con detalles
      $(document).on('click', '.btn-ver-detalles', function (e) {
        e.preventDefault();

        $('#actionsModal').modal('hide');

        var invoiceId = $(this).data('id');

        Swal.fire({
          title: 'Cargando detalles...',
          text: 'Por favor, espera unos segundos.',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        $.ajax({
          url: baseUrl + 'admin/invoices/' + invoiceId + '/details',
          method: 'GET',
          success: function (response) {
            Swal.close();

            if (!response.success || !response.data) {
              Swal.fire('Error', 'No se pudo obtener la información de la factura.', 'error');
              return;
            }

            var invoice = response.data;
            var modalHtml = `
              <p><strong>Serie:</strong> ${invoice.serie ?? ''}</p>
              <p><strong>CFE ID:</strong> ${invoice.cfeId ?? ''}</p>
              <p><strong>Número:</strong> ${invoice.nro ?? ''}</p>
              <p><strong>CAE Number:</strong> ${invoice.caeNumber ?? ''}</p>
              <p><strong>CAE Range:</strong> ${invoice.caeRange ?? ''}</p>
              <p><strong>CAE Expiration Date:</strong> ${invoice.caeExpirationDate ? moment(invoice.caeExpirationDate).format('DD-MM-YYYY') : ''}</p>
              <p><strong>Total:</strong> ${invoice.total ?? ''}</p>
              <p><strong>Emisión Date:</strong> ${invoice.emitionDate ? moment(invoice.emitionDate).format('DD-MM-YYYY') : ''}</p>
              <p><strong>Hash:</strong> ${invoice.sentXmlHash ?? ''}</p>
              <p><strong>Security Code:</strong> ${invoice.securityCode ?? ''}</p>
              <p><strong>QR URL:</strong> 
                <a href="${invoice.qrUrl ?? '#'}" target="_blank">
                  ${invoice.qrUrl ?? ''}
                </a>
              </p>
            `;

            $('#modalDetalle .modal-body').html(modalHtml);
            $('#modalDetalle .modal-title').text('Detalles del CFE');

            $('#modalDetalle').modal('show');
          },
          error: function (xhr) {
            Swal.close();
            Swal.fire('Error', 'No se pudo obtener la información del CFE. Intenta de nuevo.', 'error');
          }
        });
      });

      $('.toggle-column').on('change', function () {
        var column = dt_invoices.column($(this).attr('data-column'));
        column.visible(!column.visible());
      });

      $(document).on('click', '.btn-emitir-recibo', function () {
        var invoiceId = $(this).data('id');
        $('#actionsModal').modal('hide');
        $('#emitirReciboForm').attr('action', baseUrl + 'admin/invoices/' + invoiceId + '/emit-receipt');
        $('#emitirReciboModal').modal('show');
      });

      $(document).on('click', '.btn-emitir-nota', function () {
        var invoiceId = $(this).data('id');
        $('#actionsModal').modal('hide');
        $('#emitirNotaForm').attr('action', baseUrl + 'admin/invoices/' + invoiceId + '/emit-note');
        $('#emitirNotaModal').modal('show');
      });

      // Evento para abrir el modal de envío de correo y cargar el correo electrónico
      $(document).on('click', '.btn-send-email', function (e) {
        e.preventDefault();
        $('#actionsModal').modal('hide');
        var email = $(this).data('email');
        var invoiceId = $(this).data('id');

        // Configurar los datos en el formulario del modal
        $('#email').val(email);
        $('#invoice_id').val(invoiceId);

        // Mostrar el modal
        $('#sendEmailModal').modal('show');
      });


      $('#emitirReciboForm').on('submit', function (e) {
        e.preventDefault();

        var form = $(this);
        var formData = form.serialize();

        $.ajax({
          url: form.attr('action'),
          type: 'POST',
          data: formData,
          success: function (response) {
            if (response.success) {
              toastr.success('Recibo emitido correctamente.');
              $('#emitirReciboModal').modal('hide');
              dt_invoices.ajax.reload();
            } else {
              toastr.error('Error al emitir el recibo: ' + response.message);
            }
          },
          error: function (xhr) {
            toastr.error('Error al emitir el recibo: ' + xhr.responseText);
          }
        });
      });

      // Estilos buscador y paginación
      $('.dataTables_length label select').addClass('form-select form-select-sm');
      $('.dataTables_filter label input').addClass('form-control');

      // Después de inicializar dt_invoices...
      $('.datatables-invoice tbody').on('click', 'tr', function () {
        // Obtenemos los datos de la fila
        var invoice = dt_invoices.row(this).data();

        // Si la fila no tiene data (por ejemplo, en caso de header o algo extraño), salir
        if (!invoice) return;

        // Construimos el HTML de acciones con iconos
        // (puedes usar Font Awesome, Bootstrap Icons, etc.)
        // 1) Calcular las clases que ocultarán/mostraran acciones
        var hideEmitirNota =
        invoice['type'].includes('Nota de Crédito') || invoice['type'].includes('Nota de Débito')
          ? 'd-none'
          : '';

        var hideEmitirRecibo = invoice['is_receipt'] ? 'd-none' : '';

        // Si el invoice tiene hide_emit = true, ocultar también
        var hideEmit = invoice['hide_emit'] ? 'd-none' : '';

        // Para el botón de enviar correo, manejamos la clase y el tooltip (opcional)
        var emailButtonClass = 'btn-send-email';
        var emailDisabledClass = '';
        var emailExtraAttrs = '';
        if (!isStoreConfigEmailEnabled) {
        // Deshabilitamos el botón y agregamos tooltip
        emailDisabledClass = 'disabled';
        emailExtraAttrs = 'title="Debe asociarse a una tienda para enviar correos" data-bs-toggle="tooltip" data-bs-placement="top"';
        }

        // 2) Construimos el HTML con iconos y aplicamos las clases de ocultar/mostrar
        var actionsHtml = `
        <div class="d-flex flex-wrap justify-content-center gap-3 p-3">
          <!-- Ver QR -->
          <a href="${invoice.qrUrl}" target="_blank" class="text-center">
            <i class="fa fa-qrcode fa-2x"></i><br>
            <small>Ver QR</small>
          </a>

          <!-- Ver Venta -->
          <a href="${baseUrl}admin/orders/${invoice.order_uuid}" class="text-center">
            <i class="fa fa-shopping-cart fa-2x"></i><br>
            <small>Ver Venta</small>
          </a>

          <!-- Ver Detalles -->
          <a href="#" class="text-center btn-ver-detalles" data-id="${invoice.id}">
            <i class="fa fa-info-circle fa-2x"></i><br>
            <small>Ver Detalles</small>
          </a>

          <!-- Descargar PDF -->
          <a href="${baseUrl}admin/invoices/download/${invoice.id}" class="text-center">
            <i class="fa fa-file-pdf fa-2x"></i><br>
            <small>Descargar PDF</small>
          </a>

          <!-- Emitir Nota -->
          <a href="#" class="text-center btn-emitir-nota ${hideEmitirNota} ${hideEmit}" data-id="${invoice.id}">
            <i class="fa fa-pen fa-2x"></i><br>
            <small>Emitir Nota</small>
          </a>

          <!-- Emitir Recibo -->
          <a href="#" class="text-center btn-emitir-recibo ${hideEmitirRecibo} ${hideEmit}" data-id="${invoice.id}">
            <i class="fa fa-receipt fa-2x"></i><br>
            <small>Emitir Recibo</small>
          </a>

          <!-- Enviar factura por correo -->
          <a href="#" class="text-center btn-send-email ${emailDisabledClass}"
            data-id="${invoice.id}" data-email="${invoice.client_email}"
            ${emailExtraAttrs}>
            <i class="fa fa-envelope fa-2x"></i><br>
            <small>Enviar Correo</small>
          </a>
        </div>
        `;

        // Insertamos ese HTML en un modal exclusivo de "acciones"
        $('#actionsModal .modal-body').html(actionsHtml);

        // Abrimos el modal
        $('#actionsModal').modal('show');
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
  // Código de envío del formulario de correo electrónico con jQuery
  $('#sendEmailForm').on('submit', function (e) {
    e.preventDefault();

    const invoiceId = $('#invoice_id').val();
    const email = $('#email').val();
    const formAction = $(this).attr('action');

    // Cerrar el modal de Bootstrap
    $('#sendEmailModal').modal('hide');

    // Mostrar Swal de "Enviando..."
    Swal.fire({
      title: 'Enviando...',
      text: 'Por favor, espera mientras enviamos la factura.',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    $.ajax({
      url: formAction,
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      data: JSON.stringify({ email: email, invoice_id: invoiceId }),
      success: function (data) {
        Swal.close(); // Cerrar Swal de "Enviando..."

        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'Correo enviado correctamente',
            showConfirmButton: false,
            timer: 2000
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error al enviar el correo',
            text: data.message || 'Ocurrió un error. Inténtalo nuevamente.',
            showConfirmButton: true
          });
        }
      },
      error: function () {
        Swal.close(); // Cerrar Swal de "Enviando..." en caso de error

        Swal.fire({
          icon: 'error',
          title: 'Error al enviar el correo',
          text: 'Ocurrió un error. Inténtalo nuevamente.',
          showConfirmButton: true
        });
      }
    });
  });

  $('#openFilters').on('click', function () {
    $('#filterModal').addClass('open');
  });

  $('#closeFilterModal').on('click', function () {
    $('#filterModal').removeClass('open');
  });

  $('#exportExcel').on('click', function () {
    // Obtener valores de los filtros
    const store = $('#store').val();
    const client = $('#client').val();
    const invoiceType = $('#invoiceType').val();
    const invoiceStatus = $('#invoiceStatus').val();
    const startDate = $('#startDate').val();
    const endDate = $('#endDate').val();

    // Construir la URL con los parámetros de los filtros
    const exportUrl = new URL(window.location.origin + '/admin/cfes-invoices-export-excel');

    if (store) exportUrl.searchParams.append('store_name', store);
    if (client) exportUrl.searchParams.append('client_name', client);
    if (invoiceType) exportUrl.searchParams.append('type', invoiceType);
    if (invoiceStatus) exportUrl.searchParams.append('status', invoiceStatus);
    if (startDate) exportUrl.searchParams.append('start_date', startDate);
    if (endDate) exportUrl.searchParams.append('end_date', endDate);
    // Abrir la URL en una nueva pestaña
    window
      .open(exportUrl.toString(), '_blank')
      .focus();
  });

  $('#exportPDF').on('click', function () {
    // Obtener valores de los filtros
    const store = $('#storeFilter').val();
    const client = $('#clientFilter').val();
    const invoiceType = $('#invoiceTypeFilter').val();
    const invoiceStatus = $('#invoiceStatusFilter').val();
    const startDate = $('#startDate').val();
    const endDate = $('#endDate').val();
  
    // Construir la URL con parámetros
    const exportUrl = new URL(window.location.origin + '/admin/cfes-invoices-export-pdf');
    
    if (store) exportUrl.searchParams.append('store_name', store);
    if (client) exportUrl.searchParams.append('client_name', client);
    if (invoiceType) exportUrl.searchParams.append('type', invoiceType);
    if (invoiceStatus) exportUrl.searchParams.append('status', invoiceStatus);
    if (startDate) exportUrl.searchParams.append('start_date', startDate);
    if (endDate) exportUrl.searchParams.append('end_date', endDate);
  
    // Abrir en nueva pestaña
    window.open(exportUrl.toString(), '_blank').focus();
  });
  
  $('#exportCSV').on('click', function () {
    // Obtener valores de los filtros
    const store = $('#storeFilter').val();
    const client = $('#clientFilter').val();
    const invoiceType = $('#invoiceTypeFilter').val();
    const invoiceStatus = $('#invoiceStatusFilter').val();
    const startDate = $('#startDate').val();
    const endDate = $('#endDate').val();
  
    // Construir la URL con parámetros
    const exportUrl = new URL(window.location.origin + '/admin/cfes-invoices-export-csv');
    
    if (store) exportUrl.searchParams.append('store_name', store);
    if (client) exportUrl.searchParams.append('client_name', client);
    if (invoiceType) exportUrl.searchParams.append('type', invoiceType);
    if (invoiceStatus) exportUrl.searchParams.append('status', invoiceStatus);
    if (startDate) exportUrl.searchParams.append('start_date', startDate);
    if (endDate) exportUrl.searchParams.append('end_date', endDate);
  
    // Abrir en nueva pestaña
    window.open(exportUrl.toString(), '_blank').focus();
  });
  

  function applyFilters() {
    dt_invoices.ajax.reload();
  }

  // Función para resetear los filtros
  function resetFilters() {
    searchInput.val('');
    storeFilter.val('');
    clientFilter.val('');
    invoiceTypeFilter.val('');
    invoiceStatusFilter.val('');
    startDateFilter.val('');
    endDateFilter.val('');

    applyFilters();
  }

  searchInput.on('input', applyFilters);
  storeFilter.on('change', applyFilters);
  clientFilter.on('change', applyFilters);
  invoiceTypeFilter.on('change', applyFilters);
  invoiceStatusFilter.on('change', applyFilters);
  startDateFilter.on('change', applyFilters);
  endDateFilter.on('change', applyFilters);

  $(document).on('click', '#clearFilters', function () {
    console.log('Limpiando filtros...');
    resetFilters();
  });
});
