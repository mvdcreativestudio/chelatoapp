document.addEventListener('DOMContentLoaded', function () {
  var dtSupplierOrdersTable = $('.datatables-supplier-orders');

  const supplierOrderCreateTemplate = window.supplierOrderCreateTemplate;
  const supplierOrderEditTemplate = window.supplierOrderEditTemplate;
  const supplierOrderDeleteTemplate = window.supplierOrderDeleteTemplate;
  const hasViewAllSupplierOrdersPermission = window.hasViewAllSupplierOrdersPermission;

  var columns = [
    { title: 'ID', data: 'id' },
    { title: 'Proveedor', data: 'supplier.name' },
    { title: 'Fecha de Orden', data: 'order_date' },
    {
      title: 'Estado de Envío',
      data: 'shipping_status',
      render: function (data) {
        return data === 'completed'
          ? '<span class="badge bg-label-success">Completado</span>'
          : data === 'sending'
            ? '<span class="badge bg-label-warning">Enviando</span>'
            : '<span class="badge bg-label-secondary">Pendiente</span>';
      }
    },
    {
      title: 'Materias Primas',
      data: 'raw_materials',
      render: function (data, type, row) {
        return `<button class="btn btn-primary btn-sm view-raw-materials" data-raw-materials='${JSON.stringify(data)}'>Ver materias primas</button>`;
      }
    }
  ];

  if (hasViewAllSupplierOrdersPermission) {
    columns.push({ title: 'Tienda', data: 'store.name' });
  }

  columns.push({
    title: 'Acciones',
    data: null,
    render: function (data, type, row) {
      return `
      <div class="dropdown">
          <button class="btn btn-icon btn-icon-only" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="bx bx-dots-horizontal-rounded"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end">
            <a class="dropdown-item" href="${supplierOrderEditTemplate.replace(':id', row.id)}">
              <i class="bx bx-pencil"></i> Editar
            </a>
            <a class="dropdown-item" href="/supplier-orders/${row.id}/pdf" target="_blank">
              <i class="bx bx-file"></i> Descargar PDF
            </a>
            <form class="delete-form-${row.id}" action="${supplierOrderDeleteTemplate.replace(':id', row.id)}" method="POST">
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
  });

  if (dtSupplierOrdersTable.length) {
    dtSupplierOrdersTable.DataTable({
      data: supplierOrders,
      columns: columns,
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
            window.location.href = supplierOrderCreateTemplate;
          }
        }
      ],
      responsive: true,
      language: {
        searchPlaceholder: 'Buscar...',
        sLengthMenu: '_MENU_',
        info: 'Mostrando _START_ a _END_ de _TOTAL_ ordenes',
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
        infoFiltered: '(filtrado de un total de _MAX_ ordenes)',
        infoEmpty: 'Mostrando 0 a 0 de 0 ordenes'
      }
    });
  }

  $('.dataTables_length').addClass('mt-0 mt-md-3 me-3');
  $('.dt-buttons > .btn-group > button').removeClass('btn-secondary');
  $('.dt-buttons').addClass('d-flex flex-wrap');

  $('div.dataTables_filter input').addClass('form-control');
  $('div.dataTables_length select').addClass('form-select');

  $(document).on('click', '.view-raw-materials', function () {
    const rawMaterialsData = $(this).data('raw-materials');
    showRawMaterialsModal(rawMaterialsData);
  });

  function showRawMaterialsModal(rawMaterials) {
    console.log('AAAAA');
    let modalBodyContent = rawMaterials
      .map(rawMaterial => {
        return `<p>${rawMaterial.name}: ${rawMaterial.pivot.quantity} ${rawMaterial.unit_of_measure}</p>`;
      })
      .join('');

    document.getElementById('modalRawMaterialsBody').innerHTML = modalBodyContent;

    // Muestra el modal (Bootstrap 5)
    var modal = new bootstrap.Modal(document.getElementById('modalRawMaterials'));
    modal.show();
  }

  dtSupplierOrdersTable.on('click', '.delete-button', function () {
    var form = $(this).closest('form');
    if (confirm('¿Estás seguro de querer eliminar este elemento?')) {
      form.submit();
    }
  });
});
