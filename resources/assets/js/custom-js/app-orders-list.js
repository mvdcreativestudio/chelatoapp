/**
 * app-ecommerce-order-list Script
 */

'use strict';

// Datatable (jquery)

$(function () {
  let borderColor, bodyBg, headingColor;

  if (isDarkStyle) {
    borderColor = config.colors_dark.borderColor;
    bodyBg = config.colors_dark.bodyBg;
    headingColor = config.colors_dark.headingColor;
  } else {
    borderColor = config.colors.borderColor;
    bodyBg = config.colors.bodyBg;
    headingColor = config.colors.headingColor;
  }

  var dt_order_table = $('.datatables-order');

  if (dt_order_table.length) {
    var dt_products = dt_order_table.DataTable({
      ajax: 'orders/datatable',
      columns: [
        { data: 'id' },
        { data: 'date' },
        { data: 'client_name' },
        { data: 'store_name' },
        { data: 'total' },
        { data: 'payment_status'},
        { data: 'shipping_status'},
        { data: '' }
      ],
      columnDefs: [
        {
          targets: 0,
          render: function (data, type, full, meta) {
            return '<a href="' + baseUrl + 'orders/' + data + '/show" class="text-body">' + '#' + data + '</a>';
          }
        },
        {
          targets: 1,
          render: function (data, type, full, meta) {
            return moment(data).locale('es').format('DD/MM/YYYY');
          }
        },
        {
          targets: -1,
          title: 'Acciones',
          orderable: false,
          searchable: false,
          render: function (data, type, full, meta) {
            return (
              '<div class="d-flex justify-content-center align-items-center">' +
              '<button class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>' +
              '<div class="dropdown-menu dropdown-menu-end m-0">' +
              '<a href="' + baseUrl + 'orders/' + full['id'] + '/show" class="dropdown-item">Ver pedido</a>' +
              '<a href="javascript:void(0);" class="dropdown-item delete-record" data-id="' + full['id'] + '">Eliminar</a>' +
              '</div>' +
              '</div>'
            );
          }
        },
        {
          targets: 2,
          render: function (data, type, full, meta) {
            var $name = full['client_name'],
                $email = full['client_email'],
                $initials = $name.replace(/[^A-Z]/g, '').substring(0, 2),
                stateNum = Math.floor(Math.random() * 6),
                states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'],
                $state = states[stateNum];

            return (
              '<div class="d-flex justify-content-start align-items-center">' +
              '<div class="avatar me-2"><span class="avatar-initial rounded-circle bg-label-' + $state + '">' + $initials + '</span></div>' +
              '<div class="d-flex flex-column">' +
              '<a href="' + baseUrl + 'orders/' + full['id'] + '/show" class="text-body"><h6 class="mb-0">' + $name + '</h6></a>' +
              '<small class="text-muted">' + $email + '</small>' +
              '</div>' +
              '</div>'
            );
          }
        },
        {
          targets: 5,
          render: function (data, type, full, meta) {
            let badgeClass = (data === 'pending') ? 'bg-warning' : (data === 'paid') ? 'bg-success' : 'bg-danger';
            let text = (data === 'pending') ? 'PENDIENTE' : (data === 'paid') ? 'PAGO' : 'FALLIDO';
            return '<span class="badge pill ' + badgeClass + '">' + text + '</span>';
          }
        },
        {
          targets: 6,
          render: function (data, type, full, meta) {
            let badgeClass = (data === 'pending') ? 'bg-warning' : (data === 'shipped') ? 'bg-info' : 'bg-success';
            let text = (data === 'pending') ? 'PENDIENTE' : (data === 'shipped') ? 'ENVIADO' : 'ENTREGADO';
            return '<span class="badge pill ' + badgeClass + '">' + text + '</span>';
          }
        },
      ],
      order: [1, 'asc'],
      dom:
        '<"card-header d-flex flex-column flex-md-row align-items-start align-items-md-center"<"ms-n2"f><"d-flex align-items-md-center justify-content-md-end mt-2 mt-md-0"l<"dt-action-buttons"B>>' +
        '>t' +
        '<"row mx-2"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        '>',
        lengthMenu: [10, 25, 50, 100],
        language: {
        searchPlaceholder: 'Buscar...',
        sLengthMenu: '_MENU_',
        info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
        infoFiltered: "filtrados de _MAX_ pedidos",
        paginate: {
          first: '<<',
          last: '>>',
          next: '>',
          previous: '<'
        },
        pagingType: "full_numbers",  // Use full numbers for pagination
        dom: 'Bfrtip',
        renderer: "bootstrap"


      }
    });


    // Estilos buscador y paginación
    $('.dataTables_length label select').addClass('form-select form-select-sm');
    $('.dataTables_filter label input').addClass('form-control');


    $('.datatables-order tbody').on('click', '.delete-record', function () {
      var recordId = $(this).data('id');
      Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar!',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: baseUrl + 'orders/' + recordId,
            type: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (result) {
              if (result.success) {
                Swal.fire(
                  'Eliminado!',
                  'El pedido ha sido eliminado.',
                  'success'
                );
                dt_products.ajax.reload(null, false);  // Recarga la tabla sin resetear la paginación
              } else {
                Swal.fire(
                  'Error!',
                  'No se pudo eliminar el pedido. Intente de nuevo.',
                  'error'
                );
              }
            },
            error: function (xhr, ajaxOptions, thrownError) {
              Swal.fire(
                'Error!',
                'No se pudo eliminar el pedido: ' + xhr.responseJSON.message,
                'error'
              );
            }
          });
        }
      });
    });
  }
});
