document.addEventListener('DOMContentLoaded', function () {
  var dt_raw_materials_table = $('.datatables-raw-materials');

  var rawMaterialAdd = window.rawMaterialAdd;
  var rawMaterialEdit = window.rawMaterialEditTemplate;
  var rawMaterialDelete = window.rawMaterialDeleteTemplate;
  var baseUrlAsset = window.baseUrlAsset;
  var originUrlAsset = window.originUrlAsset;
  var hasViewAllRawMaterialsPermission = window.hasViewAllRawMaterialsPermission;

  var columns = [
    { data: 'image_url' },
    { data: 'name' },
    { data: 'description' },
    { data: 'unit_of_measure' },
    { data: 'stock' }
  ];

  if (hasViewAllRawMaterialsPermission) {
    columns.push({ data: 'store', searchable: true, orderable: true });
  }

  var columnsDefs = [
    {
      targets: 0,
      searchable: false,
      orderable: false,
      render: function (data, type, row) {
        if (!data) {
          return `<img src="${originUrlAsset}/noimage.jpg" alt="Imagen por defecto" class="img-fluid rounded" style="max-width: 60px; height: auto;">`;
        }
        var imageUrl = baseUrlAsset + '/' + data;
        return `<img src="${imageUrl}" alt="Imagen" class="img-fluid rounded" style="max-width: 60px; height: auto;">`;
      }
    },
    {
      targets: 1,
      searchable: true,
      responsivePriority: 1,
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
        return data && data.length > 50 ? '<span>' + data.substr(0, 50) + '...' + '</span>' : data;
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
      targets: hasViewAllRawMaterialsPermission ? 6 : 5,
      searchable: false,
      responsivePriority: 6,
      orderable: false,
      render: function (data, type, row, meta) {
        return `
            <div class="dropdown">
              <button class="btn btn-icon btn-icon-only" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="bx bx-dots-horizontal-rounded"></i>
              </button>
              <div class="dropdown-menu dropdown-menu-end">
                <a class="dropdown-item" href="${rawMaterialEdit.replace(':id', row.id)}">
                  <i class="bx bx-pencil"></i> Editar
                </a>
                <form class="delete-form-${row.id}" action="${rawMaterialDelete.replace(':id', row.id)}" method="POST">
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

  if (hasViewAllRawMaterialsPermission) {
    columnsDefs.push({
      targets: 5,
      render: function (data, type, row) {
        return row.store ? row.store.name : 'Tienda sin nombre';
      }
    });
  }

  if (dt_raw_materials_table.length) {
    dt_raw_materials_table.DataTable({
      data: rawMaterials,
      columns: columns,
      columnDefs: columnsDefs,
      language: {
        searchPlaceholder: 'Buscar...',
        sLengthMenu: '_MENU_',
        info: 'Mostrando _START_ a _END_ de _TOTAL_ Materias Primas',
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
        infoFiltered: '(filtrado de un total de _MAX_ Materias Primas)',
        infoEmpty: 'Mostrando 0 a 0 de 0 Materias Primas'
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
            window.location.href = rawMaterialAdd;
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

  $('.delete-button').click(function () {
    var form = $(this).closest('form');
    if (confirm('¿Estás seguro de querer eliminar este elemento?')) {
      form.submit();
    }
  });
});
