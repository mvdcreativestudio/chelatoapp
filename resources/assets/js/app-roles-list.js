document.addEventListener('DOMContentLoaded', function () {
  const columns = [
    { data: 'name' },
    { data: 'guard_name' },
    {
      data: null,
      render: function (data, type, row) {
        return row.users.length;
      }
    }
  ];

  if (window.isAdmin) {
    columns.push({
      data: null,
      render: function (data, type, row, meta) {
        return `
          <div class="dropdown">
            <button class="btn btn-icon btn-icon-only" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="bx bx-dots-horizontal-rounded"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
              <a class="dropdown-item role-edit-modal" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editRoleModal" data-role-id="${row.id}" data-role-name="${row.name}">
                <i class="bx bx-edit"></i> Editar
              </a>
              <a class="dropdown-item" href="roles/${row.id}/manage-users">
                <i class="bx bx-group"></i> Gestionar Usuarios
              </a>
              <a class="dropdown-item" href="roles/${row.id}/manage-permissions">
                <i class="bx bx-lock"></i> Gestionar Permisos
              </a>
            </div>
          </div>
        `;
      },
      orderable: false,
      searchable: false
    });
  }

  var rolesTable = $('#rolesTable').DataTable({
    data: roles,
    columns: columns,
    language: {
      searchPlaceholder: 'Buscar...',
      sLengthMenu: '_MENU_',
      info: 'Mostrando _START_ a _END_ de _TOTAL_ roles',
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
      infoFiltered: '(filtrado de un total de _MAX_ roles)',
      infoEmpty: 'Mostrando 0 a 0 de 0 roles'
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
    buttons: window.isAdmin && [
      {
        text: '<i class="bx bx-plus me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Crear</span>',
        className: 'btn btn-primary ml-3',
        attr: {
          'data-bs-toggle': 'modal',
          'data-bs-target': '#addRoleModal'
        }
      }
    ]
  });

  $('.toggle-column').on('change', function() {
    var column = rolesTable.column($(this).attr('data-column'));
    column.visible(!column.visible());
});

  $('.dataTables_length').addClass('mt-0 mt-md-3 me-3');
  $('.dt-buttons > .btn-group > button').removeClass('btn-secondary');
  $('.dt-buttons').addClass('d-flex flex-wrap');

  $('div.dataTables_filter input').addClass('form-control');
  $('div.dataTables_length select').addClass('form-select');

  $('#rolesTable tbody').on('click', '.role-edit-modal', function () {
    var data = rolesTable.row($(this).parents('tr')).data();
    $('#editRoleModal').find('#editRoleForm').attr('action', `/roles/${data.id}`);
    $('#editRoleModal').find('#roleName').val(data.name);
    $('#editRoleModal').modal('show');
  });
});
