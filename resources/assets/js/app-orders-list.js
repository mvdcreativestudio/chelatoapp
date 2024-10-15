$(function () {
  let borderColor, bodyBg, headingColor;
  let $currencySymbol = $('.datatables-order').data('symbol');

  if (isDarkStyle) {
    borderColor = config.colors_dark.borderColor;
    bodyBg = config.colors_dark.bodyBg;
    headingColor = config.colors_dark.headingColor;
  } else {
    borderColor = config.colors.borderColor;
    bodyBg = config.colors.bodyBg;
    headingColor = config.colors.headingColor;
  }

  var dt_order_table = $('.datatables-order');

  try {
    if (dt_order_table.length) {
      var dt_products = dt_order_table.DataTable({
        ajax: {
          url: 'orders/datatable',
          data: function (d) {
            d.store_id = $('select[name="store_id"]').val(); // Añadir store_id a la petición
            d.start_date = $('#startDate').val(); // Captura la fecha desde
            d.end_date = $('#endDate').val(); // Captura la fecha hasta
          }
        },
        columns: [
          { data: 'id', type: 'num' },
          {
            data: 'date',
            type: 'datetime', // Definir explícitamente como datetime
            render: function (data, type, full, meta) {
              // Usar tanto la fecha (data) como la hora (full['time']) para el renderizado
              return moment(data + ' ' + full['time']).locale('es').format('DD/MM/YY - hh:mm a'); // Combina fecha y hora
            }
          },
          { data: 'client_name' },
          { data: 'store_name' },
          { data: 'total' },
          { data: 'payment_status' },
          { data: 'is_billed' },
          { data: '' }
        ],
        order: [[0, 'desc']],  // Ordenar por la columna de fecha de manera descendente
        columnDefs: [
          {
            targets: 0, // Enlazar el ID del pedido
            orderable: false,
            render: function (data, type, full, meta) {
              var uuid = full['uuid'];
              return '<a class="text-muted" href="' + baseUrl + 'admin/orders/' + uuid + '/show">#' + data + '</a>';
            }
          },
          {
            targets: 4,
            render: function (data, type, full, meta) {
              return $currencySymbol + data;
            }
          },
          {
            targets: 5,
            render: function (data, type, full, meta) {
              let badgeClass = data === 'pending' ? 'bg-warning' : data === 'paid' ? 'bg-success' : 'bg-danger';
              let text = data === 'pending' ? 'PENDIENTE' : data === 'paid' ? 'PAGO' : 'FALLIDO';
              return '<span class="badge pill ' + badgeClass + '">' + text + '</span>';
            }
          },
          {
            targets: 6, // Columna para mostrar si ha sido facturado
            render: function (data, type, full, meta) {
              return data
                ? '<span class="badge bg-success">Facturado</span>'
                : '<span class="badge bg-danger">No Facturado</span>';
            }
          },
          {
            targets: -1,
            title: 'Acciones',
            orderable: false,
            searchable: false,
            render: function (data, type, full, meta) {
              var uuid = full['uuid'];
              return (
                '<div class="d-flex justify-content-center align-items-center">' +
                '<button class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>' +
                '<div class="dropdown-menu dropdown-menu-end m-0">' +
                '<a href="' +
                baseUrl +
                'admin/orders/' +
                uuid +
                '/show" class="dropdown-item">Ver pedido</a>' +
                '<a href="javascript:void(0);" class="dropdown-item delete-record" data-id="' +
                full['id'] +
                '">Eliminar</a>' +
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
          info: 'Mostrando _START_ a _END_ de _TOTAL_ ventas',
          infoFiltered: 'filtradas de _MAX_ ventas',
          paginate: {
            first: '<<',
            last: '>>',
            next: '>',
            previous: '<'
          },
          pagingType: 'full_numbers',
          emptyTable: 'No hay ventas disponibles',
          dom: 'Bfrtip',
          renderer: 'bootstrap'
        },
        initComplete: function () {
          // Filtros personalizados para Cliente
          this.api()
            .columns(2) // Columna de cliente
            .every(function () {
              var column = this;
              // Crear el select para el filtro de clientes
              var select = $('<select class="form-select"><option value="">Todos los clientes</option></select>')
                .appendTo('.client_filter')
                .on('change', function () {
                  var val = $.fn.dataTable.util.escapeRegex($(this).val());
                  column.search(val ? val : '', true, false).draw();
                });

              // Poblar el select con los valores únicos de la columna de clientes
              column
                .data()
                .unique()
                .sort()
                .each(function (d, j) {
                  select.append(`<option value="${d}">${d}</option>`);
                });
            });

          // Filtros personalizados para Empresa
          this.api()
            .columns(3)
            .every(function () {
              var column = this;
              var select = $('<select class="form-select"><option value="">Todas las empresas</option></select>')
                .appendTo('.company_filter')
                .on('change', function () {
                  var val = $.fn.dataTable.util.escapeRegex($(this).val());
                  column.search(val ? `^${val}$` : '', true, false).draw();
                });

              column
                .data()
                .unique()
                .sort()
                .each(function (d, j) {
                  select.append(`<option value="${d}">${d}</option>`);
                });
            });

          // Filtros personalizados para Estado de Pago
          this.api()
            .columns(5)
            .every(function () {
              var column = this;
              var select = $(
                '<select class="form-select"><option value="">Todos los pagos</option><option value="PAGO">Pagado</option><option value="PENDIENTE">Pendiente</option><option value="FALLIDO">Fallido</option></select>'
              )
                .appendTo('.payment_filter')
                .on('change', function () {
                  var val = $.fn.dataTable.util.escapeRegex($(this).val());
                  column.search(val ? `^${val}$` : '', true, false).draw();
                });
            });

          // Filtros personalizados para Facturado
          this.api()
            .columns(6) // Columna 6 es la que contiene el estado de facturación
            .every(function () {
              var column = this;
              var select = $(
                '<select class="form-select">' +
                  '<option value="">Todos</option>' +
                  '<option value="Facturado">Facturado</option>' +
                  '<option value="No Facturado">No Facturado</option>' +
                  '</select>'
              )
                .appendTo('.billed_filter') // Añade el filtro a la clase .billed_filter
                .on('change', function () {
                  var val = $.fn.dataTable.util.escapeRegex($(this).val());
                  column.search(val ? `^${val}$` : '', true, false).draw();
                });
            });
        }
      });

      // Eliminar filtros de búsqueda
      $(document).on('click', '#clear-filters', function () {
        $('.client_filter select').val('').trigger('change');
        $('.company_filter select').val('').trigger('change');
        $('.payment_filter select').val('').trigger('change');
        $('.billed_filter select').val('').trigger('change');
        $('#startDate').val('');
        $('#endDate').val('');
        dt_products.search('').draw();
      });

      // Filtrar por fechas
      $('#startDate, #endDate').on('change', function () {
        dt_products.ajax.reload();
      });

      // Estilos buscador y paginación
      $('.dataTables_length label select').addClass('form-select form-select-sm');
      $('.dataTables_filter label input').addClass('form-control');

      $('.toggle-column').on('change', function () {
        var column = dt_products.column($(this).attr('data-column'));
        column.visible(!column.visible());
      });

      $('.datatables-order tbody').on('click', '.delete-record', function () {
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
                  dt_products.ajax.reload(null, false); // Recarga la tabla sin resetear la paginación
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

      $('#export-excel').on('click', function () {
        // Capturar los valores de los filtros
        let client = $('.client_filter select').val();
        let company = $('.company_filter select').val();
        let payment = $('.payment_filter select').val();
        let billed = $('.billed_filter select').val();
        let startDate = $('#startDate').val();
        let endDate = $('#endDate').val();

        // Construir la URL con los parámetros válidos
        let url = '/admin/orders-export-excel?';
        let params = [];

        if (client) {
          params.push(`client=${encodeURIComponent(client)}`);
        }
        if (company) {
          params.push(`company=${encodeURIComponent(company)}`);
        }
        if (payment) {
          params.push(`payment=${encodeURIComponent(payment)}`);
        }
        if (billed) {
          params.push(`billed=${encodeURIComponent(billed)}`);
        }
        if (startDate) {
          params.push(`start_date=${encodeURIComponent(startDate)}`);
        }
        if (endDate) {
          params.push(`end_date=${encodeURIComponent(endDate)}`);
        }

        // Unir los parámetros a la URL
        url += params.join('&');

        // Redirigir a la ruta para exportar, abriendo en una nueva pestaña
        window.open(url, '_blank');
      });
    }
  } catch (error) {
    console.log('Error: '.error);
  }
});
