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

  var dt_entry_table = $('.datatables-entries');

  try {
    // Inicializa DataTable si el elemento existe
    if (dt_entry_table.length) {
      var dt_entries = dt_entry_table.DataTable({
        ajax: {
          url: 'entries/datatable',
          data: function (d) {
            d.start_date = $('#startDate').val();
            d.end_date = $('#endDate').val();
          }
        },
        columns: [
          { data: 'switch', orderable: false, searchable: false },
          { data: 'id', type: 'num' },
          { data: 'entry_date' },
          { data: 'entry_type_name' },
          { data: 'currency_name' },
          { data: 'concept' },
          { data: 'total_debit' },
          { data: 'total_credit' },
          { data: 'is_balanced' },
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
              return `<a href="${baseUrl}admin/entries/${data}/show" class="text-body">#${data}</a>`;
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
              return parseFloat(data).toFixed(2);
            }
          },
          {
            targets: 7,
            render: function (data, type, full, meta) {
              return parseFloat(data).toFixed(2);
            }
          },
          {
            targets: 8,
            render: function (data, type, full, meta) {
              return data
                ? '<span class="badge bg-success">BALANCEADO</span>'
                : '<span class="badge bg-danger">NO BALANCEADO</span>';
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
                    <a href="${baseUrl}admin/entry-details/${full['id']}" class="dropdown-item detail-record" data-id="${full['id']}">Ver Detalle Asiento</a>
                    <a href="${baseUrl}admin/entries/${full['id']}/edit" class="dropdown-item edit-record" data-id="${full['id']}">Editar</a>
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
          infoFiltered: 'filtrados de _MAX_ asientos',
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
              var select = $(
                '<select class="form-select"><option value="">Todos los tipos de asientos</option></select>'
              )
                .appendTo('.entry_type_filter')
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
            .columns(4)
            .every(function () {
              var column = this;
              var select = $('<select class="form-select"><option value="">Todas las monedas</option></select>')
                .appendTo('.currency_filter')
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

      $('.toggle-column').on('change', function () {
        var column = dt_entries.column($(this).attr('data-column'));
        column.visible(!column.visible());
      });

      // Manejadores de eventos para UI
      $('.dataTables_length label select').addClass('form-select form-select-sm');
      $('.dataTables_filter label input').addClass('form-control');

      // Check/uncheck todos los checkboxes
      $('#checkAll').on('change', function () {
        var checkboxes = $('.datatables-entries tbody input[type="checkbox"]');
        checkboxes.prop('checked', $(this).prop('checked'));
        toggleActionsMenu();
      });

      // Activar desactivar checkbox principal
      $('.datatables-entries tbody').on('change', 'input[type="checkbox"]', function () {
        toggleActionsMenu();
        var allChecked =
          $('.datatables-entries tbody input[type="checkbox"]').length ===
          $('.datatables-entries tbody input[type="checkbox"]:checked').length;
        $('#checkAll').prop('checked', allChecked);
      });

      // Eliminar filtros de búsqueda
      $(document).on('click', '#clear-filters', function () {
        $('.entry_type_filter select').val('').trigger('change');
        $('.currency_filter select').val('').trigger('change');
        $('#startDate').val('');
        $('#endDate').val('');
        dt_entries.search('');
        dt_entries.ajax.reload();
      });

      // Filtrar por fechas
      $('#startDate, #endDate').on('change', function () {
        dt_entries.ajax.reload();
      });

      function toggleActionsMenu() {
        // Muestra u oculta el menú de acciones dependiendo de la cantidad de checkboxes seleccionados
        var selectedCount = $('.datatables-entries tbody input[type="checkbox"]:checked').length;
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
        let entryType = $('.entry_type_filter select').val(); // Tipo de Asiento
        let currency = $('.currency_filter select').val(); // Moneda
        let startDate = $('#startDate').val(); // Fecha desde
        let endDate = $('#endDate').val(); // Fecha hasta

        // Construir la URL con los parámetros válidos
        let url = '/admin/entries-export-excel?';
        let params = [];

        // Verificar y agregar los parámetros a la URL
        if (entryType) {
          params.push(`entry_type=${encodeURIComponent(entryType)}`);
        }
        if (currency) {
          params.push(`currency=${encodeURIComponent(currency)}`);
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
        let entryType = $('.entry_type_filter select').val(); // Tipo de Asiento
        let currency = $('.currency_filter select').val(); // Moneda
        let startDate = $('#startDate').val(); // Fecha desde
        let endDate = $('#endDate').val(); // Fecha hasta

        // Construir la URL con los parámetros válidos
        let url = '/admin/entries-export-pdf?';
        let params = [];

        // Verificar y agregar los parámetros a la URL
        if (entryType) {
          params.push(`entry_type=${encodeURIComponent(entryType)}`);
        }
        if (currency) {
          params.push(`currency=${encodeURIComponent(currency)}`);
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
