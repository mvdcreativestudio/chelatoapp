$(function () {
  // Variables para colores y símbolos
  let borderColor, bodyBg, headingColor;
  let $currencySymbol = $('.datatables-incomes').data('symbol');

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

  var dt_income_table = $('.datatables-incomes');

  try {
    // Inicializa DataTable si el elemento existe
    if (dt_income_table.length) {
      var dt_incomes = dt_income_table.DataTable({
        ajax: {
          url: 'incomes/datatable',
          data: function (d) {
            d.start_date = $('#startDate').val();
            d.end_date = $('#endDate').val();
            d.entity_type = $('#entityType').val();
            d.category = $('#category').val();
          }
        },
        columns: [
          { data: 'id', type: 'num' },
          { data: 'income_date' },
          {
            data: null, // Columna para "Entidad" (Cliente, Proveedor, Ninguno)
            render: function (data, type, full, meta) {
              if (full.client_name) {
                return 'Cliente';
              } else if (full.supplier_name) {
                return 'Proveedor';
              } else {
                return 'Ninguno';
              }
            }
          },
          {
            data: null, // Columna para el nombre del Cliente/Proveedor o "Ninguno"
            render: function (data, type, full, meta) {
              if (full.client_name) {
                return full.client_name + ' ' + full.client_lastname;
              } else if (full.supplier_name) {
                return full.supplier_name;
              } else {
                return 'Ninguno';
              }
            }
          },
          { data: 'payment_method_name' },
          { 
            data: 'currency',  
            render: function (data, type, full, meta) {
              return data ? data : 'N/A';
            }
          },
          { data: 'income_amount' },
          {
            data: null,
            render: function (data, type, full, meta) {
              let amount = parseFloat(full.income_amount);
              if (full.currency === 'Dólar' && full.currency_rate > 0) {
                amount = amount * parseFloat(full.currency_rate);
              }
              return '$' + amount.toFixed(2);
            }
          },
          { data: 'income_category_name' },
          { data: '' }
        ],
        columnDefs: [
          {
            targets: 0,
            render: function (data, type, full, meta) {
              return `<a href="#" class="text-body">#${data}</a>`;
            }
          },
          {
            targets: 1,
            render: function (data, type, full, meta) {
              return moment(data).locale('es').format('DD/MM/YY');
            }
          },
          {
            targets: [2, 3, 4, 5, 6, 7, 8], 
            render: function (data, type, full, meta) {
              if (type === 'display') {
                return `<div class="text-truncate" style="max-width: 60px;" title="${data}">${data}</div>`;
              }
              return data;
            }
          },
          {
            targets: 5, // Columna de Moneda
            render: function (data, type, full, meta) {
              if (type === 'display') {
                const text = data || 'Sin Moneda';
                return `<div class="text-truncate" style="max-width: 60px;" title="${text}">${text}</div>`;
              }
              return data || 'Sin Moneda';
            }
          },
          {
            targets: 6, // Columna de Importe
            render: function (data, type, full, meta) {
              const symbol = full.currency_symbol ?? '$';
              return symbol + parseFloat(full.income_amount).toFixed(2);
            }
          },
          {
            targets: 7, // Columna de Total en Pesos
            render: function (data, type, full, meta) {
              let amount = parseFloat(full.income_amount);
              if (full.currency === 'Dólar' && full.currency_rate > 0) {
                amount = amount * parseFloat(full.currency_rate);
              }
              return '$' + amount.toFixed(2);
            }
          },
          {
            targets: 8, // Columna de Categoría
            render: function (data, type, full, meta) {
              if (type === 'display') {
                return `<div class="text-truncate" style="max-width: 60px;" title="${text}">${text}</div>`;
              }
              return full.income_category_name || 'Sin Categoría';
            }
          },
          {
            targets: -1,
            title: 'Acción',
            searchable: false,
            orderable: false,
            render: function (data, type, full, meta) {
              return `
                <div class="d-flex justify-content-center align-items-center">
                  <button class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                    <i class="bx bx-dots-vertical-rounded"></i>
                  </button>
                  <div class="dropdown-menu dropdown-menu-end m-0">
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
          infoFiltered: 'filtrados de _MAX_ ingresos',
          paginate: {
            first: '<<',
            last: '>>',
            next: '> ',
            previous: '<'
          },
          pagingType: 'full_numbers',
          emptyTable: 'No hay registros disponibles',
          renderer: 'bootstrap'
        },
        initComplete: function () {
          // Filtro dinámico para entidad (cliente, proveedor, otros)
          var entityColumn = this.api().column(2); // Columna de tipo de entidad (índice 2)
          var categoryColumn = this.api().column(8); // Columna de categoría (índice 8)

          // Manejador del filtro de entidad
          $('#entityType').on('change', function() {
            var val = $.fn.dataTable.util.escapeRegex($(this).val());
            entityColumn.search(val ? val : '', true, false).draw();
          });

          // Llenar y manejar el filtro de categoría
          categoryColumn.data().unique().sort().each(function(d) {
            if (d) { // Solo agregar si hay un valor
              $('#category').append(`<option value="${d}">${d}</option>`);
            }
          });

          $('#category').on('change', function() {
            var val = $.fn.dataTable.util.escapeRegex($(this).val());
            categoryColumn.search(val ? val : '', true, false).draw();
          });

          // Manejador de filtros de fecha
          $('#startDate, #endDate').on('change', function() {
            dt_incomes.ajax.reload();
          });

          // Limpiar filtros
          $('#clear-filters').on('click', function() {
            $('#entityType').val('');
            $('#category').val('');
            $('#startDate').val('');
            $('#endDate').val('');
            entityColumn.search('').draw();
            categoryColumn.search('').draw();
            dt_incomes.ajax.reload();
          });
        },
        renderer: 'bootstrap'
      });

      $('.toggle-column').on('change', function () {
        var column = dt_incomes.column($(this).attr('data-column'));
        column.visible(!column.visible());
      });

      // Manejadores de eventos para UI
      $('.dataTables_length label select').addClass('form-select form-select-sm');
      $('.dataTables_filter label input').addClass('form-control');

      // Check/uncheck todos los checkboxes
      $('#checkAll').on('change', function () {
        var checkboxes = $('.datatables-incomes tbody input[type="checkbox"]');
        checkboxes.prop('checked', $(this).prop('checked'));
        toggleActionsMenu();
      });

      // Activar/desactivar checkbox principal
      $('.datatables-incomes tbody').on('change', 'input[type="checkbox"]', function () {
        toggleActionsMenu();
        var allChecked =
          $('.datatables-incomes tbody input[type="checkbox"]').length ===
          $('.datatables-incomes tbody input[type="checkbox"]:checked').length;
        $('#checkAll').prop('checked', allChecked);
      });

      // Eliminar filtros de búsqueda
      $(document).on('click', '#clear-filters', function () {
        $('.entity_type select').val('').trigger('change'); // Limpiar filtro de entidad
        $('.category_filter select').val('').trigger('change');
        $('#startDate').val('');
        $('#endDate').val('');
        dt_incomes.search('');
        dt_incomes.ajax.reload();
      });

      // Filtrar por fechas
      $('#startDate, #endDate').on('change', function () {
        dt_incomes.ajax.reload();
      });

      function toggleActionsMenu() {
        // Muestra u oculta el menú de acciones dependiendo de la cantidad de checkboxes seleccionados
        var selectedCount = $('.datatables-incomes tbody input[type="checkbox"]:checked').length;
        if (selectedCount >= 2) {
          $('#dropdownMenuButton').removeClass('d-none');
          $('#columnSwitches').collapse('show');
        } else {
          $('#dropdownMenuButton').addClass('d-none');
          $('#columnSwitches').collapse('hide');
        }
      }
    }

    $('#export-excel').on('click', function () {
      // Capturar los valores de los filtros
      let entityType = $('.entity_type select').val(); // Tipo de Entidad (Cliente/Proveedor/Ninguno)
      let categoryId = $('.category_filter select').val(); // Categoría
      let startDate = $('#startDate').val(); // Fecha desde
      let endDate = $('#endDate').val(); // Fecha hasta

      // Construir la URL con los parámetros válidos
      let url = '/admin/incomes-export-excel?';
      let params = [];

      // Verificar y agregar los parámetros a la URL
      if (entityType) {
        params.push(`entity_type=${encodeURIComponent(entityType)}`);
      }
      if (startDate) {
        params.push(`start_date=${encodeURIComponent(startDate)}`);
      }
      // categoryId
      if (categoryId) {
        params.push(`category_id=${encodeURIComponent(categoryId)}`);
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
      let entityType = $('.entity_type select').val(); // Tipo de Entidad (Cliente/Proveedor/Ninguno)
      let categoryId = $('.category_filter select').val(); // Categoría
      let startDate = $('#startDate').val(); // Fecha desde
      let endDate = $('#endDate').val(); // Fecha hasta

      // Construir la URL con los parámetros válidos
      let url = '/admin/incomes-export-pdf?';
      let params = [];

      // Verificar y agregar los parámetros a la URL
      if (entityType) {
        params.push(`entity_type=${encodeURIComponent(entityType)}`);
      }
      if (categoryId) {
        params.push(`category_id=${encodeURIComponent(categoryId)}`);
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
  } catch (error) {
    console.error('Error al inicializar DataTable:', error);
  }
});
