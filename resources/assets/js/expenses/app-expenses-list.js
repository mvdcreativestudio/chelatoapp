$(function () {
  // Variables para colores y símbolos
  let borderColor, bodyBg, headingColor;
  let $currencySymbol = $('.datatables-expenses').data('symbol');

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

  var dt_expense_table = $('.datatables-expenses');

  // traduccion de estados
  const statusMap = {
    Paid: { class: 'bg-success', text: 'PAGADO' },
    Unpaid: { class: 'bg-danger', text: 'NO PAGADO' },
    Partial: { class: 'bg-warning', text: 'PARCIALMENTE PAGO' }
  };

  // traduccion de estados temporales
  const temporalStatusMap = {
    'On Time': { class: 'bg-success', text: 'EN FECHA' },
    'Due Soon': { class: 'bg-warning', text: 'POR VENCER' },
    Overdue: { class: 'bg-danger', text: 'VENCIDO' },
    'Due Today': { class: 'bg-warning', text: 'VENCE HOY' }
  };

  try {
    // Inicializa DataTable si el elemento existe
    if (dt_expense_table.length) {
      var dt_expenses = dt_expense_table.DataTable({
        ajax: {
          url: 'expenses/datatable',
          data: function (d) {
            d.start_date = $('#startDate').val();
            d.end_date = $('#endDate').val();
          }
        },
        columns: [
          { data: 'switch', orderable: false, searchable: false },
          { data: 'id', type: 'num' },
          { data: 'due_date' },
          { data: 'supplier_name' },
          { data: 'concept' },
          { data: 'store_name' },
          { data: 'amount' },
          { data: 'total_payments' },
          { data: 'category_name' },
          { data: 'currency_name' },
          { data: 'status' },
          { data: 'temporal_status' },
          { data: '' }
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
              return `<a href="${baseUrl}admin/expenses/${data}/show" class="text-body">#${data}</a>`;
            }
          },
          {
            targets: 2,
            render: function (data, type, full, meta) {
              return moment(data).locale('es').format('DD/MM/YY');
            }
          },
          {
            targets: 6,
            render: function (data, type, full, meta) {
              const symbol = full.currency_symbol ?? '$';
              return symbol + parseFloat(data).toFixed(2);
            }
          },
          {
            targets: 7,
            render: function (data, type, full, meta) {
              const symbol = full.currency_symbol ?? '$';
              return symbol + parseFloat(data).toFixed(2);
            }
          },
          {
            targets: 10,
            render: function (data, type, full, meta) {
              const statusMap = {
                Paid: { class: 'bg-success', text: 'PAGADO' },
                Unpaid: { class: 'bg-danger', text: 'NO PAGADO' },
                Partial: { class: 'bg-warning', text: 'PARCIALMENTE PAGO' }
              };
              return `<span class="badge pill ${statusMap[data].class}">${statusMap[data].text}</span>`;
            }
          },
          {
            targets: 11,
            render: function (data, type, full, meta) {
              return `<span class="badge pill ${temporalStatusMap[data].class}">${temporalStatusMap[data].text}</span>`;
            }
          },
          {
            targets: -1,
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
                    <a href="${baseUrl}admin/expense-payment-methods/${full['id']}/detail" class="dropdown-item detail-record" data-id="${full['id']}">Ver Detalle Gasto</a>
                    <a href="javascript:void(0);" class="dropdown-item edit-record" data-id="${full['id']}">Editar</a>
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
          infoFiltered: 'filtrados de _MAX_ gastos',
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
          // Filtros personalizados para columnas
          this.api()
            .columns(3)
            .every(function () {
              var column = this;
              var select = $('<select class="form-select"><option value="">Todos los proveedores</option></select>')
                .appendTo('.supplier_filter')
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

          this.api()
            .columns(5)
            .every(function () {
              var column = this;
              var select = $('<select class="form-select"><option value="">Todos los locales</option></select>')
                .appendTo('.store_filter')
                .on('change', function () {
                  var val = $.fn.dataTable.util.escapeRegex($(this).val());
                  if (val == 'Sin local') {
                    val = '^$';
                  }
                  column.search(val ? `^${val}$` : '', true, false).draw();
                });

              column
                .data()
                .unique()
                .sort()
                .each(function (d, j) {
                  if (d == null) {
                    d = 'Sin local';
                  }
                  select.append(`<option value="${d}">${d}</option>`);
                });
            });
          this.api()
            .columns(8)
            .every(function () {
              var column = this;
              var select = $('<select class="form-select"><option value="">Todos las categorias</option></select>')
                .appendTo('.category_filter')
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
          this.api()
            .columns(10)
            .every(function () {
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

      $('.toggle-column').on('change', function () {
        var column = dt_expenses.column($(this).attr('data-column'));
        column.visible(!column.visible());
      });

      // Manejadores de eventos para UI
      $('.dataTables_length label select').addClass('form-select form-select-sm');
      $('.dataTables_filter label input').addClass('form-control');

      // Check/uncheck todos los checkboxes
      $('#checkAll').on('change', function () {
        var checkboxes = $('.datatables-expenses tbody input[type="checkbox"]');
        checkboxes.prop('checked', $(this).prop('checked'));
        toggleActionsMenu();
      });

      // Activar desactivar checkbox principal
      $('.datatables-expenses tbody').on('change', 'input[type="checkbox"]', function () {
        toggleActionsMenu();
        var allChecked =
          $('.datatables-expenses tbody input[type="checkbox"]').length ===
          $('.datatables-expenses tbody input[type="checkbox"]:checked').length;
        $('#checkAll').prop('checked', allChecked);
      });

      // Eliminar filtros de búsqueda
      $(document).on('click', '#clear-filters', function () {
        $('.supplier_filter select').val('').trigger('change');
        $('.store_filter select').val('').trigger('change');
        $('.category_filter select').val('').trigger('change');
        $('.status_filter select').val('').trigger('change');
        $('#startDate').val('');
        $('#endDate').val('');
        dt_expenses.search('');
        dt_expenses.ajax.reload();
      });

      // Filtrar por fechas
      $('#startDate, #endDate').on('change', function () {
        dt_expenses.ajax.reload();
      });

      function toggleActionsMenu() {
        // Muestra u oculta el menú de acciones dependiendo de la cantidad de checkboxes seleccionados
        var selectedCount = $('.datatables-expenses tbody input[type="checkbox"]:checked').length;
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
        let supplier = $('.supplier_filter select').val(); // Proveedor
        let store = $('.store_filter select').val(); // Local
        let category = $('.category_filter select').val(); // Categoría
        let status = $('.status_filter select').val(); // Estado Pago
        let startDate = $('#startDate').val(); // Fecha desde
        let endDate = $('#endDate').val(); // Fecha hasta

        // Construir la URL con los parámetros válidos
        let url = '/admin/expenses-export-excel?';
        let params = [];

        // Verificar y agregar los parámetros a la URL
        if (supplier) {
          params.push(`supplier=${encodeURIComponent(supplier)}`);
        }
        if (store) {
          params.push(`store=${encodeURIComponent(store)}`);
        }
        if (category) {
          params.push(`category=${encodeURIComponent(category)}`);
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
        let supplier = $('.supplier_filter select').val(); // Proveedor
        let store = $('.store_filter select').val(); // Local
        let category = $('.category_filter select').val(); // Categoría
        let status = $('.status_filter select').val(); // Estado Pago
        let startDate = $('#startDate').val(); // Fecha desde
        let endDate = $('#endDate').val(); // Fecha hasta

        // Construir la URL con los parámetros válidos
        let url = '/admin/expenses-export-pdf?';
        let params = [];

        // Verificar y agregar los parámetros a la URL
        if (supplier) {
          params.push(`supplier=${encodeURIComponent(supplier)}`);
        }
        if (store) {
          params.push(`store=${encodeURIComponent(store)}`);
        }
        if (category) {
          params.push(`category=${encodeURIComponent(category)}`);
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
    }
  } catch (error) {
    console.error('Error al inicializar DataTable:', error);
  }
});
