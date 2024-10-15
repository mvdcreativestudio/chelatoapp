$(function () {
  // Variables para colores y símbolos
  let borderColor, bodyBg, headingColor;
  let $currencySymbol = $('.datatables-current-accounts').data('symbol');

  // Configuración de colores basada en el estilo (oscuro o claro)
  if (isDarkStyle) {
    borderColor = config.colors_dark.borderColor;
    bodyBg = config.colors_dark.bodyBg;
    headingColor = config.colors_dark.headingColor;
  } else {
    borderColor = config.colors.borderColor;
    bodyBg = config.colors.bodyBg;
    headingColor = config.colors.headingColor;
  }

  var dt_account_table = $('.datatables-current-accounts');

  // traducción de estados
  const statusMap = {
    Paid: { class: 'bg-success', text: 'PAGADO' },
    Unpaid: { class: 'bg-danger', text: 'NO PAGADO' },
    Partial: { class: 'bg-warning', text: 'PARCIALMENTE PAGO' }
  };

  try {
    // Inicializa DataTable si el elemento existe
    if (dt_account_table.length) {
      var dt_accounts = dt_account_table.DataTable({
        ajax: {
          url: 'current-accounts/datatable',
          data: function (d) {
            d.start_date = $('#startDate').val();
            d.end_date = $('#endDate').val();
          }
        },
        columns: [
          { data: 'switch', orderable: false, searchable: false }, // Checkbox para seleccionar filas
          { data: 'id', type: 'num' }, // ID de la transacción
          { data: 'transaction_date' }, // Fecha de la transacción
          { data: 'transaction_type' }, // Tipo de transacción (Venta/Compra)
          // {
          //   data: null,
          //   render: function (data, type, full, meta) {
          //     return full.client_name ? 'Cliente' : full.supplier_name ? 'Proveedor' : 'Sin nombre';
          //   }
          // },
          {
            // Descripción (nombre del cliente o proveedor)
            data: null,
            render: function (data, type, full, meta) {
              return full.client_name ? full.client_name : full.supplier_name ? full.supplier_name : 'Sin nombre';
            }
          },
          { data: 'total_debit' }, // Total debit
          { data: 'payment_amount' }, // Cantidad pagada
          { data: 'currency_code' }, // Nueva columna para moneda
          { data: 'status' }, // Estado de pago
          { data: '' } // Acciones
        ],
        columnDefs: [
          {
            targets: 0,
            render: function (data, type, full, meta) {
              return `<input type="checkbox" class="form-check-input" data-id="${full['id']}">`;
            }
          },
          {
            targets: 1,
            render: function (data, type, full, meta) {
              return `<a href="${baseUrl}admin/current-account-client-payments/${data}" class="text-body">#${data}</a>`;
            }
          },
          {
            targets: 2,
            render: function (data, type, full, meta) {
              return moment(data).locale('es').format('DD/MM/YY');
            }
          },
          {
            targets: 3, // Tipo de transacción
            render: function (data, type, full, meta) {
              let transactionType = full.transaction_type === 'Sale' ? 'Cliente' : 'Proveedor';
              let badgeClass = full.transaction_type === 'Sale' ? 'bg-success' : 'bg-primary';
              return `<span class="badge ${badgeClass}">${transactionType}</span>`;
            }
          },
          {
            targets: 4, // Descripción (cliente o proveedor)
            render: function (data, type, full, meta) {
              return full.client_name || full.supplier_name || 'Sin nombre';
            }
          },
          {
            targets: 5, // Total debit
            render: function (data, type, full, meta) {
              const symbol = full.symbol ?? '$';
              if (full.total_debit === null) {
                return symbol + parseFloat(0).toFixed(2);
              }
              return symbol + parseFloat(data).toFixed(2);
            }
          },
          {
            targets: 6, // Payment amount
            render: function (data, type, full, meta) {
              const symbol = full.symbol ?? '$';
              if (full.payment_amount === null) {
                return symbol + parseFloat(0).toFixed(2);
              }
              return symbol + parseFloat(data).toFixed(2);
            }
          },
          {
            targets: 8, // Estado de pago
            render: function (data, type, full, meta) {
              return `<span class="badge pill ${statusMap[data].class}">${statusMap[data].text}</span>`;
            }
          },
          {
            targets: -1, // Acciones
            title: 'Acciones',
            searchable: false,
            orderable: false,
            render: function (data, type, full, meta) {
              return `
                <div class="d-flex justify-content-center align-items-center">
                  <button class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                    <i class="bx bx-dots-vertical-rounded"></i>
                  </button>
                  <div class="dropdown-menu dropdown-menu-end m-0">
                    <a href="${baseUrl}admin/current-account-payments/${full['id']}" class="dropdown-item detail-record" data-id="${full['id']}">Ver Detalle Cuenta</a>
                    <a href="${baseUrl}admin/current-accounts/${full['id']}/edit" class="dropdown-item edit-record" data-id="${full['id']}">Editar</a>
                    <a href="javascript:void(0);" class="dropdown-item delete-record" data-id="${full['id']}">Eliminar</a>
                  </div>
                </div>`;
            }
          }
        ],
        order: [0, 'asc'],
        dom:
          '<"card-header d-flex flex-column flex-md-row align-items-start align-items-md-center pt-0"<"ms-n2"f><"d-flex align-items-md-center justify-content-md-end mt-2 mt-md-0"l<"dt-action-buttons"B>>>t' +
          '<"row mx-2"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        lengthMenu: [10, 25, 50, 100],
        language: {
          search: '',
          searchPlaceholder: 'Buscar...',
          sLengthMenu: '_MENU_',
          info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
          infoFiltered: 'filtrados de _MAX_ cuentas',
          paginate: {
            first: '<<',
            last: '>>',
            next: '>',
            previous: '<'
          },
          pagingType: 'full_numbers',
          emptyTable: 'No hay registros disponibles',
          dom: 'Bfrtip',
          renderer: 'bootstrap'
        },
        initComplete: function () {
          var api = this.api();

          // Filtro de entidad (clientes o proveedores)
          $('#entityType').on('change', function () {
            let entityType = $(this).val();
            $('.client_filter, .supplier_filter').hide(); // Ocultar ambos filtros por defecto

            api.column(3).search('').draw();
            api.column(4).search('').draw();
            if (entityType === 'client') {
              // Reiniciar la tabla antes de aplicar el filtro
              // Filtrar por transacciones de tipo 'Venta'
              api.column(3).search('Cliente', true, false).draw();
              $('.client_filter').show();
              loadUniqueEntityOptions(4, '#clientSelect', 'client_name'); // Cargar clientes únicos

              // Agregar manejador de eventos para filtrar por cliente seleccionado
              $('#clientSelect')
                .off('change')
                .on('change', function () {
                  let selectedClient = $(this).val();
                  if (selectedClient) {
                    api
                      .column(4)
                      .search(`^${$.fn.dataTable.util.escapeRegex(selectedClient)}$`, true, false)
                      .draw();
                  } else {
                    api.column(4).search('').draw();
                  }
                });
            } else if (entityType === 'supplier') {
              // Reiniciar la tabla antes de aplicar el filtro
              // Filtrar por transacciones de tipo 'Compra'
              api.column(3).search('Proveedor', true, false).draw();
              $('.supplier_filter').show();
              loadUniqueEntityOptions(4, '#supplierSelect', 'supplier_name'); // Cargar proveedores únicos

              // Agregar manejador de eventos para filtrar por proveedor seleccionado
              $('#supplierSelect')
                .off('change')
                .on('change', function () {
                  let selectedSupplier = $(this).val();
                  if (selectedSupplier) {
                    api
                      .column(4)
                      .search(`^${$.fn.dataTable.util.escapeRegex(selectedSupplier)}$`, true, false)
                      .draw();
                  } else {
                    api.column(4).search('').draw();
                  }
                });
            }
          });

          // Función para cargar opciones de cliente o proveedor de forma única
          function loadUniqueEntityOptions(columnIndex, selectId, entityField) {
            let $select = $(selectId);
            let uniqueEntities = new Set(); // Set para asegurar que los datos sean únicos

            $select.empty().append('<option value="">Seleccionar</option>');

            // Obtener datos únicos de la columna indicada y agregarlos al select
            api
              .column(columnIndex)
              .data()
              .each(function (d) {
                if (d && entityField === 'client_name' && d.client_name !== null) {
                  uniqueEntities.add(d.client_name);
                } else if (d && entityField === 'supplier_name' && d.supplier_name !== null) {
                  uniqueEntities.add(d.supplier_name);
                }
              });

            // Agregar las opciones únicas al select
            uniqueEntities.forEach(function (entity) {
              $select.append(`<option value="${entity}">${entity}</option>`);
            });

            // Agregar manejador de eventos para filtrar por entidad seleccionada
            $select.on('change', function () {
              let selectedEntity = $(this).val();
              if (selectedEntity) {
                api
                  .column(columnIndex)
                  .search(`^${$.fn.dataTable.util.escapeRegex(selectedEntity)}$`, true, false)
                  .draw();
              } else {
                api.column(columnIndex).search('').draw();
              }
            });
          }

          // Filtro de estado
          api.columns(8).every(function () {
            var column = this;
            var select = $('<select class="form-select"><option value="">Todos los estados</option></select>')
              .appendTo('.status_filter')
              .on('change', function () {
                var val = $.fn.dataTable.util.escapeRegex($(this).val());
                column.search(val ? `^${statusMap[val].text}$` : '', true, false).draw();
              });

            column
              .data()
              .unique()
              .sort()
              .each(function (d, j) {
                select.append(`<option value="${d}">${statusMap[d].text}</option>`);
              });
          });
        },
        renderer: 'bootstrap'
      });
    }

    $('.toggle-column').on('change', function () {
      var column = dt_accounts.column($(this).attr('data-column'));
      column.visible(!column.visible());
    });

    // Manejadores de eventos para UI
    $('.dataTables_length label select').addClass('form-select form-select-sm');
    $('.dataTables_filter label input').addClass('form-control');

    // Check/uncheck todos los checkboxes
    $('#checkAll').on('change', function () {
      var checkboxes = $('.datatables-current-accounts tbody input[type="checkbox"]');
      checkboxes.prop('checked', $(this).prop('checked'));
      toggleActionsMenu();
    });

    // Activar desactivar checkbox principal
    $('.datatables-current-accounts tbody').on('change', 'input[type="checkbox"]', function () {
      toggleActionsMenu();
      var allChecked =
        $('.datatables-current-accounts tbody input[type="checkbox"]').length ===
        $('.datatables-current-accounts tbody input[type="checkbox"]:checked').length;
      $('#checkAll').prop('checked', allChecked);
    });

    // Eliminar filtros de búsqueda
    $(document).on('click', '#clear-filters', function () {
      $('#entityType').val('').trigger('change');
      $('.client_filter select').val('').trigger('change');
      $('.supplier_filter select').val('').trigger('change');
      $('.status_filter select').val('').trigger('change');
      $('#startDate').val('');
      $('#endDate').val('');
      dt_accounts.search('');
      dt_accounts.ajax.reload();
    });

    // Filtrar por fechas
    $('#startDate, #endDate').on('change', function () {
      dt_accounts.ajax.reload();
    });

    function toggleActionsMenu() {
      // Muestra u oculta el menú de acciones dependiendo de la cantidad de checkboxes seleccionados
      var selectedCount = $('.datatables-current-accounts tbody input[type="checkbox"]:checked').length;
      if (selectedCount >= 2) {
        $('#dropdownMenuButton').removeClass('d-none');
        $('#columnSwitches').collapse('show');
      } else {
        $('#dropdownMenuButton').addClass('d-none');
        $('#columnSwitches').collapse('hide');
      }
    }

    $('#export-excel').on('click', function () {
      // Capturar los valores de los filtros
      let entityType = $('#entityType').val(); // Tipo de Entidad (Cliente/Proveedor)
      let client = $('#clientSelect').val(); // Cliente
      let supplier = $('#supplierSelect').val(); // Proveedor
      let status = $('.status_filter select').val(); // Estado de pago
      let startDate = $('#startDate').val(); // Fecha desde
      let endDate = $('#endDate').val(); // Fecha hasta

      // Construir la URL con los parámetros válidos
      let url = '/admin/current-accounts-export-excel?';
      let params = [];

      // Verificar y agregar los parámetros a la URL
      if (entityType) {
        params.push(`entity_type=${encodeURIComponent(entityType)}`);
      }
      if (client) {
        params.push(`client_id=${encodeURIComponent(client)}`);
      }
      if (supplier) {
        params.push(`supplier_id=${encodeURIComponent(supplier)}`);
      }
      if (status) {
        params.push(`status=${encodeURIComponent(status)}`);
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

    $('#export-pdf').on('click', function () {
      // Capturar los valores de los filtros
      let entityType = $('#entityType').val(); // Tipo de Entidad (Cliente/Proveedor)
      let client = $('#clientSelect').val(); // Cliente
      let supplier = $('#supplierSelect').val(); // Proveedor
      let status = $('.status_filter select').val(); // Estado de pago
      let startDate = $('#startDate').val(); // Fecha desde
      let endDate = $('#endDate').val(); // Fecha hasta
  
      // Construir la URL con los parámetros válidos
      let url = '/admin/current-accounts-export-pdf?';
      let params = [];
  
      // Verificar y agregar los parámetros a la URL
      if (entityType) {
          params.push(`entity_type=${encodeURIComponent(entityType)}`);
      }
      if (client) {
          params.push(`client_id=${encodeURIComponent(client)}`);
      }
      if (supplier) {
          params.push(`supplier_id=${encodeURIComponent(supplier)}`);
      }
      if (status) {
          params.push(`status=${encodeURIComponent(status)}`);
      }
      if (startDate) {
          params.push(`start_date=${encodeURIComponent(startDate)}`);
      }
      if (endDate) {
          params.push(`end_date=${encodeURIComponent(endDate)}`);
      }
  
      // Unir los parámetros a la URL
      url += params.join('&');
  
      // Redirigir a la ruta para exportar a PDF, abriendo en una nueva pestaña
      window.open(url, '_blank');
  });
  } catch (error) {
    console.error('Error al inicializar DataTable:', error);
  }
});
