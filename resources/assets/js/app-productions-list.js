document.addEventListener('DOMContentLoaded', function () {
  var dt_productions_table = $('.datatables-productions');

  var productionActivate = window.productionActivateTemplate;
  var productionDeactivate = window.productionDeactivateTemplate;
  var productions = window.productions;

  var columns = [
    { data: 'id' },
    { data: 'product.name', defaultContent: 'N/A' },
    { data: 'flavor.name', defaultContent: 'N/A' },
    { data: 'quantity' },
    { data: 'created_at' },
    { data: 'status' }
  ];

  var columnsDefs = [
    {
      targets: 0,
      searchable: false,
      orderable: true,
      render: function (data, type, row) {
        return data;
      }
    },
    {
      targets: 1,
      searchable: true,
      responsivePriority: 1,
      orderable: true,
      render: function (data, type, row, meta) {
        return data ? data : 'N/A';
      }
    },
    {
      targets: 2,
      searchable: true,
      responsivePriority: 2,
      orderable: true,
      render: function (data, type, row, meta) {
        return data ? data : 'N/A';
      }
    },
    {
      targets: 3,
      searchable: true,
      responsivePriority: 3,
      orderable: true,
      render: function (data, type, row, meta) {
        return data;
      }
    },
    {
      targets: 4,
      searchable: true,
      responsivePriority: 4,
      orderable: true,
      render: function (data, type, row, meta) {
        if (type === 'display' || type === 'filter') {
          var date = new Date(data);
          var options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit'};
          return date.toLocaleDateString('es-ES', options);
        }
        return data;
      }
    },
    {
      targets: 5,
      searchable: true,
      responsivePriority: 5,
      orderable: true,
      render: function (data, type, row, meta) {
        var badgeClass = data === 'active' ? 'bg-success' : 'bg-danger';
        var badgeText = data === 'active' ? 'Activa' : 'Inactiva';
        return '<span class="badge ' + badgeClass + '">' + badgeText + '</span>';
      }
    },
    {
      targets: 6,
      searchable: false,
      responsivePriority: 6,
      orderable: false,
      render: function (data, type, row, meta) {
        var actionUrl =
          row.status === 'active'
            ? productionDeactivate.replace(':id', row.id)
            : productionActivate.replace(':id', row.id);
        var actionText = row.status === 'active' ? 'Desactivar' : 'Activar';
        return `
                  <div class="dropdown">
                      <button class="btn btn-icon btn-icon-only" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          <i class="bx bx-dots-horizontal-rounded"></i>
                      </button>
                      <div class="dropdown-menu dropdown-menu-end">
                          <form class="status-form-${row.id}" action="${actionUrl}" method="POST">
                              <input type="hidden" name="_token" value="${window.csrfToken}">
                              <input type="hidden" name="production_id" value="${row.id}">
                              <div class="dropdown-item text-${row.status === 'active' ? 'danger' : 'success'} status-button" style="cursor: pointer;">
                                  <i class="bx bx-${row.status === 'active' ? 'trash' : 'check'}"></i> ${actionText}
                              </div>
                          </form>
                      </div>
                  </div>
              `;
      }
    }
  ];

  if (dt_productions_table.length) {
    // Destruir instancia previa si existe
    if ($.fn.DataTable.isDataTable('.datatables-productions')) {
      dt_productions_table.DataTable().destroy();
    }

    dt_productions_table.DataTable({
      data: productions,
      columns: columns,
      columnDefs: columnsDefs,
      language: {
        searchPlaceholder: 'Buscar...',
        sLengthMenu: '_MENU_',
        info: 'Mostrando _START_ a _END_ de _TOTAL_ Elaboraciones',
        paginate: {
          first: 'Primero',
          last: 'Último',
          next: '<span class="mx-2">Siguiente</span>',
          previous: '<span class="mx-2">Anterior</span>'
        },
        aria: {
          sortAscending: ': activar para ordenar la columna ascendente',
          sortDescending: ': activar para ordenar la columna descendente'
        },
        emptyTable: 'No hay datos disponibles en la tabla',
        zeroRecords: 'No se encontraron coincidencias',
        lengthMenu: '_MENU_',
        loadingRecords: 'Cargando...',
        processing: 'Procesando...',
        search: '',
        infoFiltered: '(filtrado de un total de _MAX_ Elaboraciones)',
        infoEmpty: 'Mostrando 0 a 0 de 0 Elaboraciones'
      },
      dom:
        '<"card-header d-flex border-top rounded-0 flex-wrap py-md-0"' +
        '<"me-5 ms-n2 pe-5"f>' +
        '<"d-flex justify-content-start justify-content-md-end align-items-baseline"<"dt-action-buttons d-flex align-items-start align-items-md-center justify-content-sm-center mb-3 mb-sm-0"lB>>' +
        '>t' +
        '<"row mx-2"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        '>',
      buttons: [
        {
          text: '<i class="bx bx-plus me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Crear</span>',
          className: 'btn btn-primary ml-3',
          action: function () {
            window.location.href = 'http://localhost:8000/admin/productions/create';
          }
        }
      ]
    });
    // Ajustar clases después de la inicialización del DataTable
    $('.dataTables_length').addClass('mt-0 mt-md-3 me-3');
    $('.dt-buttons > .btn-group > button').removeClass('btn-secondary');
    $('.dt-buttons').addClass('d-flex flex-wrap');
    $('div.dataTables_filter input').addClass('form-control');
    $('div.dataTables_length select').addClass('form-select');

    // Manejar eventos en la tabla
    $('.datatables-productions tbody').on('click', '.status-button', function () {
      var form = $(this).closest('form');

      Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción cambiará el estado de la elaboración.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, continuar!',
        cancelButtonText: 'Cancelar'
      }).then(result => {
        if (result.isConfirmed) {
          form.submit();
        }
      });
    });
  }
});
