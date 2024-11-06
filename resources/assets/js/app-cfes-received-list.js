$(function () {
  let borderColor, bodyBg, headingColor;
  let $currencySymbol = $('.datatables-cfes-received').data('symbol');

  if (isDarkStyle) {
    borderColor = config.colors_dark.borderColor;
    bodyBg = config.colors_dark.bodyBg;
    headingColor = config.colors_dark.headingColor;
  } else {
    borderColor = config.colors.borderColor;
    bodyBg = config.colors.bodyBg;
    headingColor = config.colors.headingColor;
  }

  var dt_cfe_table = $('.datatables-cfes-received');

  $.fn.dataTable.ext.errMode = 'throw';

  if (dt_cfe_table.length) {
    try {
      var dt_cfes = dt_cfe_table.DataTable({
        ajax: {
          url: 'invoices/received/datatable',
          dataSrc: 'data'
        },
        columns: [
          { data: 'id', type: 'num' },
          { data: 'issuer_name' }, // Nombre del Emisor
          { data: 'date' },         // Fecha de emisión
          { data: 'type' },         // Tipo de documento
          { data: 'reason' },       // Razón del documento
          { data: 'currency' },     // Moneda
          { data: 'total' },        // Total del documento
          { data: 'status' },       // Estado del documento
          { data: 'actions' }       // Acciones
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
              return full['issuer_name'];
            }
          },
          {
            targets: 2,
            render: function (data, type, full, meta) {
              return data ? moment(data.date).format('DD-MM-YYYY HH:mm') : 'Fecha inválida';
            }
          },
          {
            targets: 3,
            render: function (data, type, full, meta) {
              return data ? data : 'Sin Tipo';
            }
          },
          {
            targets: 4, // Columna para Razón, mostrar un tooltip y modal
            render: function (data, type, full, meta) {
              let truncatedText = data ? (data.length > 50 ? data.substring(0, 50) + '...' : data) : 'Sin Razón';
              return `
                <span class="razon-text" data-bs-toggle="tooltip" title="${data}">
                  ${truncatedText}
                  <button type="button" class="btn btn-link p-0 view-reason" data-full-reason="${data}" style="font-size: 12px;">
                    Ver más
                  </button>
                </span>
              `;
            }
          },
          {
            targets: 5,
            render: function (data, type, full, meta) {
              return full['currency'];
            }
          },
          {
            targets: 6,
            render: function (data, type, full, meta) {
              return $currencySymbol + parseFloat(data).toFixed(2);
            }
          },
          {
            targets: 7, // Nueva columna para el Status
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
                case 'PENDING_REVISION':
                  badgeClass = 'badge bg-warning';
                  translatedStatus = 'Pendiente de Revisión';
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
              return (
                '<div class="d-flex justify-content-center align-items-center">' +
                '<button class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>' +
                '<div class="dropdown-menu dropdown-menu-end m-0">' +
                '<a href="#" class="dropdown-item btn-ver-detalles" data-id="' +
                full['id'] +
                '">Ver Detalles</a>' +
                '</div>' +
                '</div>'
              );
            }
          }
        ],
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
          info: 'Mostrando _START_ a _END_ de _TOTAL_ CFEs recibidos',
          infoFiltered: 'filtrados de _MAX_ CFEs recibidos',
          paginate: {
            first: '<<',
            last: '>>',
            next: '>',
            previous: '<'
          },
          pagingType: 'full_numbers',
          emptyTable: 'No hay CFEs recibidos disponibles',
          dom: 'Bfrtip',
          renderer: 'bootstrap'
        }
      });

      // Evento para mostrar el modal con la razón completa
      $('.datatables-cfes-received tbody').on('click', '.view-reason', function () {
        var fullReason = $(this).data('full-reason');
        $('#modalDetalle .modal-title').text('Razón Completa');
        $('#modalDetalle .modal-body').html(`<p>${fullReason}</p>`);
        $('#modalDetalle').modal('show');
      });

      // Inicializar tooltips
      $('[data-bs-toggle="tooltip"]').tooltip();

    } catch (error) {
      console.log(error);
    }
  }
});
