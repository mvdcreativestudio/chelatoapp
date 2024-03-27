document.addEventListener('DOMContentLoaded', function () {
  var dt_suppliers_table = $('.datatables-suppliers');

  var supplierAdd = window.supplierAdd;
  var supplierEdit = window.supplierEditTemplate;
  var supplierDelete = window.supplierDeleteTemplate;
  var hasViewAllSuppliersPermission = window.hasViewAllSuppliersPermission;

  var columns = [
    { data: 'name' },
    { data: 'phone' },
    { data: 'email' },
    { data: 'city' },
    { data: 'state' },
    { data: 'country' },
    { data: 'doc_type' },
    { data: 'doc_number' }
  ];

  if (hasViewAllSuppliersPermission) {
    columns.push({ data: 'store', searchable: true, orderable: true });
  }

  columns.push({ data: null, orderable: false, searchable: false });

  var columnDefs = [
    {
      targets: 0,
      searchable: true,
      responsivePriority: 1,
      orderable: true,
      render: function (data, type, row, meta) {
        return '<span>' + data + '</span>';
      }
    },
    {
      targets: 1,
      searchable: true,
      responsivePriority: 2,
      orderable: true,
      render: function (data, type, row, meta) {
        return '<span>' + data + '</span>';
      }
    },
    {
      targets: 2,
      searchable: true,
      responsivePriority: 3,
      orderable: true,
      render: function (data, type, row, meta) {
        return '<span>' + data + '</span>';
      }
    },
    {
      targets: 3,
      searchable: true,
      responsivePriority: 4,
      orderable: true,
      render: function (data, type, row, meta) {
        return '<span>' + data + '</span>';
      }
    },
    {
      targets: 4,
      searchable: true,
      responsivePriority: 5,
      orderable: true,
      render: function (data, type, row, meta) {
        return '<span>' + data + '</span>';
      }
    },
    {
      targets: 5,
      searchable: true,
      responsivePriority: 6,
      orderable: true,
      render: function (data, type, row, meta) {
        return '<span>' + data + '</span>';
      }
    },
    {
      targets: 6,
      searchable: true,
      responsivePriority: 7,
      orderable: true,
      render: function (data, type, row, meta) {
        return '<span>' + data + '</span>';
      }
    },
    {
      targets: 7,
      searchable: true,
      responsivePriority: 8,
      orderable: true,
      render: function (data, type, row, meta) {
        return '<span>' + data + '</span>';
      }
    },
    {
      targets: hasViewAllSuppliersPermission ? 9 : 8,
      render: function (data, type, row) {
        var editUrl = supplierEdit.replace(':id', row.id);
        var deleteUrl = supplierDelete.replace(':id', row.id);
        return `
            <div class="dropdown">
              <button class="btn btn-icon btn-icon-only" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="bx bx-dots-horizontal-rounded"></i>
              </button>
              <div class="dropdown-menu dropdown-menu-end">
                <a class="dropdown-item" href="${editUrl}">
                  <i class="bx bx-pencil"></i> Editar
                </a>
                <form class="delete-form-${row.id}" action="${deleteUrl}" method="POST">
                  <input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content')}">
                  <input type="hidden" name="_method" value="DELETE">
                  <div class="dropdown-item text-danger delete-button" style="cursor: pointer;">
                    <i class="bx bx-trash"></i> Eliminar
                  </div>
                </form>
              </div>
            </div>
          `;
      }
    }
  ];

  if (hasViewAllSuppliersPermission) {
    columnDefs.push({
      targets: 8,
      render: function (data, type, row) {
        return row.store ? row.store.name : 'Tienda sin nombre';
      }
    });
  }

  if (dt_suppliers_table.length) {
    dt_suppliers_table.DataTable({
      data: suppliers,
      columns: columns,
      columnDefs: columnDefs,
      language: {
        searchPlaceholder: 'Buscar...',
        sLengthMenu: '_MENU_',
        info: 'Mostrando _START_ a _END_ de _TOTAL_ Proveedores',
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
        infoFiltered: '(filtrado de un total de _MAX_ Proveedores)',
        infoEmpty: 'Mostrando 0 a 0 de 0 Proveedores'
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
            window.location.href = supplierAdd;
          }
        }
      ]
    });
  }

  $('.dataTables_length').addClass('mt-0 mt-md-3 me-3');
  $('.dt-buttons > .btn-group > button').removeClass('btn-secondary');
  $('.dt-buttons').addClass('d-flex flex-wrap');

  $('div.dataTables_filter input').addClass('form-control');
  $('div.dataTables_length select').addClass('form-select');

  dt_suppliers_table.on('click', '.delete-button', function () {
    var form = $(this).closest('form');
    if (confirm('¿Estás seguro de querer eliminar este elemento?')) {
      form.submit();
    }
  });
});
