$(function () {
  // Variables para colores y símbolos
  let borderColor, bodyBg, headingColor;

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

  var dt_income_category_table = $('.datatables-income-categories');

  try {
    // Inicializa DataTable si el elemento existe
    if (dt_income_category_table.length) {
      var dt_income_categories = dt_income_category_table.DataTable({
        ajax: {
          url: 'income-categories/datatable',
          data: function (d) {
            d.start_date = $('#startDate').val();
            d.end_date = $('#endDate').val();
          }
        },
        columns: [
          { data: 'switch', orderable: false, searchable: false },
          { data: 'id', type: 'num' },
          { data: 'income_name' },
          { data: 'income_description' },
          { data: 'created_at' },
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
              return `<a href="#" class="text-body">#${data}</a>`;
            }
          },
          {
            targets: 4,
            render: function (data, type, full, meta) {
              return moment(data).locale('es').format('DD/MM/YY');
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
          infoFiltered: 'filtrados de _MAX_ categorías de ingresos',
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
            .columns(2)
            .every(function () {
              var column = this;
              var select = $('<select class="form-select"><option value="">Todas las categorías</option></select>')
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
        },
        renderer: 'bootstrap'
      });

      $('.toggle-column').on('change', function() {
        var column = dt_income_categories.column($(this).attr('data-column'));
        column.visible(!column.visible());
      });

      // Manejadores de eventos para UI
      $('.dataTables_length label select').addClass('form-select form-select-sm');
      $('.dataTables_filter label input').addClass('form-control');

      // Check/uncheck todos los checkboxes
      $('#checkAll').on('change', function () {
        var checkboxes = $('.datatables-income-categories tbody input[type="checkbox"]');
        checkboxes.prop('checked', $(this).prop('checked'));
        toggleActionsMenu();
      });

      // Activar desactivar checkbox principal
      $('.datatables-income-categories tbody').on('change', 'input[type="checkbox"]', function () {
        toggleActionsMenu();
        var allChecked =
          $('.datatables-income-categories tbody input[type="checkbox"]').length ===
          $('.datatables-income-categories tbody input[type="checkbox"]:checked').length;
        $('#checkAll').prop('checked', allChecked);
      });

      // Eliminar filtros de búsqueda
      $(document).on('click', '#clear-filters', function () {
        $('.category_filter select').val('').trigger('change');
        $('#startDate').val('');
        $('#endDate').val('');
        dt_income_categories.search('');
        dt_income_categories.ajax.reload();
      });

      // Filtrar por fechas
      $('#startDate, #endDate').on('change', function () {
        dt_income_categories.ajax.reload();
      });

      function toggleActionsMenu() {
        // Muestra u oculta el menú de acciones dependiendo de la cantidad de checkboxes seleccionados
        var selectedCount = $('.datatables-income-categories tbody input[type="checkbox"]:checked').length;
        if (selectedCount >= 2) {
          $('#dropdownMenuButton').removeClass('d-none');
          $('#columnSwitches').collapse('show');
        } else {
          $('#dropdownMenuButton').addClass('d-none');
          $('#columnSwitches').collapse('hide');
        }
      }

    }
  } catch (error) {
    console.error('Error al inicializar DataTable:', error);
  }
});
