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

  var dt_coupon_table = $('.datatables-coupons');

  if (dt_coupon_table.length) {
      var dt_coupons = dt_coupon_table.DataTable({
          ajax: 'coupons/datatable',
          columns: [
            { data: 'switch', orderable: false, searchable: false },
            { data: 'id' },
            { data: 'code' },
            { data: 'type' },
            {
              data: 'amount',
              render: function(data, type, full, meta) {
                  if (full.type === 'percentage') {
                      return data + '%'; // Agrega '%' después del número
                  } else {
                      return '$' + data; // Agrega '$' antes del número
                  }
              }
            },
            { data: 'created_at'},
            { data: 'due_date' },
            { data: 'creator_name' },
            { data: null, defaultContent: '' }
          ],
          columnDefs: [
            {
                targets: 0,
                render: function (data, type, full, meta) {
                    return '<input type="checkbox" class="form-check-input" data-id="' + full['id'] + '">';
                }
            },
            {
                targets: 2,
                render: function (data, type, full, meta) {
                    return '<a href="' + baseUrl + 'coupons/' + full['id'] + '/show" class="text-body">' + data + '</a>';
                }
            },
            {
                targets: 3,
                render: function (data, type, full, meta) {
                    return data === 'percentage' ? 'Porcentaje' : 'Descuento fijo';
                }
            },
            {
                targets: 5,
                render: function (data, type, full, meta) {
                  if (data === null) {
                      return 'No registrado';
                  } else {
                    return moment(data).locale('es').format('DD/MM/YYYY');
                }
              }
            },
            {
                targets: 6,
                render: function (data, type, full, meta) {
                  if (data === null) {
                      return 'Sin expiración';
                  } else {
                    var currentDate = moment().startOf('day');
                    var dueDate = moment(data).startOf('day');
                    var dateClass = dueDate.isBefore(currentDate) ? 'text-danger' : 'text-success';
                    return '<span class="' + dateClass + '">' + moment(data).locale('es').format('DD/MM/YYYY') + '</span>';
                }
              },
            },
            {
                targets: 7,
                render: function (data, type, full, meta) {
                    return data === null ? 'No registrado' : data;
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
                      '<a href="javascript:void(0);" class="dropdown-item detail-record" data-id="' + full['id'] + '">Ver</a>' +
                      '<a href="javascript:void(0);" class="dropdown-item edit-record" data-id="' + full['id'] + '">Editar</a>' +
                      '<a href="javascript:void(0);" class="dropdown-item delete-record" data-id="' + full['id'] + '">Eliminar</a>' +
                      
                      '</div>' +
                      '</div>'
                  );
              }
            }
          ],

          order: [2, 'asc'],
          dom:
              '<"card-header d-flex flex-column flex-md-row align-items-start align-items-md-center"<"ms-n2"f><"d-flex align-items-md-center justify-content-md-end mt-2 mt-md-0"l<"dt-action-buttons"B>>' +
              '>t' +
              '<"row mx-2"' +
              '<"col-sm-12 col-md-6"i>' +
              '<"col-sm-12 col-md-6"p>' +
              '>',
          lengthMenu: [10, 25, 50, 100],
          language: {
              infoEmpty: 'No hay cupones para mostrar',
              emptyTable: 'No existe ningún cupón',
              search: "",
              searchPlaceholder: 'Buscar...',
              sLengthMenu: '_MENU_',
              info: 'Mostrando _START_ a _END_ de _TOTAL_ cupones',
              infoFiltered: "(filtrados de _MAX_ cupones)",
              paginate: {
                  first: '<<',
                  last: '>>',
                  next: '>',
                  previous: '<'
              },
          },
          renderer: "bootstrap"
      });

      $('.toggle-column').on('change', function() {
        var column = dt_coupons.column($(this).attr('data-column'));
        column.visible(!column.visible());
    });

      $('.dataTables_length label select').addClass('form-select form-select-sm');
      $('.dataTables_filter label input').addClass('form-control');

      // Agrega el evento change para el checkbox maestro
      $('#checkAll').on('change', function () {
          var checkboxes = $('.datatables-coupons tbody input[type="checkbox"]');
          checkboxes.prop('checked', $(this).prop('checked'));
      });

      // Agrega el evento change para los checkboxes de cada fila
      $('.datatables-coupons tbody').on('change', 'input[type="checkbox"]', function () {
          var selectedCount = $('.datatables-coupons tbody input[type="checkbox"]:checked').length;

          if (selectedCount >= 2) {
              $('#dropdownMenuButton').removeClass('d-none');
          } else {
            $('#dropdownMenuButton').addClass('d-none');
          }

          var allChecked = $('.datatables-coupons tbody input[type="checkbox"]').length === selectedCount;
          $('#checkAll').prop('checked', allChecked);
      });

      $('.datatables-coupons tbody').on('click', '.delete-record', function () {
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
                      url: 'coupons/' + recordId,
                      type: 'DELETE',
                      headers: {
                          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                      },
                      success: function (result) {
                          if (result.success) {
                              Swal.fire(
                                  'Eliminado!',
                                  'El cupón ha sido eliminado.',
                                  'success'
                              );
                              dt_coupons.ajax.reload(null, false);
                          } else {
                              Swal.fire(
                                  'Error!',
                                  'No se pudo eliminar el cupón. Intente de nuevo.',
                                  'error'
                              );
                          }
                      },
                      error: function (xhr, ajaxOptions, thrownError) {
                          Swal.fire(
                              'Error!',
                              'No se pudo eliminar el cupón: ' + xhr.responseJSON.message,
                              'error'
                          );
                      }
                  });
              }
          });
      });
  }


  //Ver detalles del cupón.

  $('.datatables-coupons tbody').on('click', '.detail-record', function () {
    var recordId = $(this).data('id'); 

    var $couponExpiryInput = $('#detailCouponModal #couponExpiry'); 

    $.ajax({
        url: 'coupons/' + recordId, 
        type: 'GET',
        success: function (response) {
            console.log('Detalles del cupon:', response); 
            
            $('#detailCouponModal #couponCode').val(response.code);
            $('#detailCouponModal #couponType').val(response.type);
            $('#detailCouponModal #couponAmount').val(response.amount);

            if (response.due_date) {
                var dueDate = response.due_date.split(' ')[0];
                $('#detailCouponModal #couponExpiry').val(dueDate);
            } else {
                $('#detailCouponModal #couponExpiry').val('');
            }
            $('#detailCouponModal').modal('show'); // Asegúrate de que el ID es del modal contenedor
        },
        error: function (xhr) {
            console.error('Error al obtener los detalles del cupón:', xhr);
        }
    });
});

  // Enviar FORM de edicion del cupon.

  $('#editCouponModal').on('click', '#updateCouponBtn', function () {
    var recordId = $(this).data('id');
    console.log('recordId:', recordId);
    submitEditCoupon(recordId); //
  });

  // Abrir FORM para editar el cupon.
  
  $('.datatables-coupons tbody').on('click', '.edit-record', function () {
    var recordId = $(this).data('id'); // Obtener el ID del cupón
    $('#updateCouponBtn').attr('data-id', recordId); // Asignar el ID del cupón al botón de "Actualizar Cupón"

    // Acceder al input de fecha de expiración del modal de edición
    var $couponExpiryInput = $('#editCouponModal #couponExpiry'); 

    // Realizar la solicitud Ajax para obtener los detalles del cupón
    $.ajax({
        url: 'coupons/' + recordId, // Reemplaza 'coupons/' por la ruta correcta para obtener los detalles del cupón
        type: 'GET',
        success: function (response) {
            // Llenar los campos del formulario de edición con los detalles del cupón obtenidos de la base de datos
            console.log('Response:', response); // Añadir log para verificar la respuesta
            
            $('#editCouponModal #couponCode').val(response.code);
            $('#editCouponModal #couponType').val(response.type);
            $('#editCouponModal #couponAmount').val(response.amount);

            // Verificar si existe fecha de expiración antes de intentar dividirla
            if (response.due_date) {
                var dueDate = response.due_date.split(' ')[0];
                $('#editCouponModal #couponExpiry').val(dueDate);
            } else {
                // Si no hay fecha de expiración, dejar el campo vacío o manejarlo según sea necesario
                $('#editCouponModal #couponExpiry').val('');
            }

            $('#editCouponModal').modal('show');
        },
        error: function (xhr) {
            console.error('Error al obtener los detalles del cupón:', xhr);
            // Manejar el error si es necesario
        }
    });
});


// POST para editar el cupon en la base de datos.

  function submitEditCoupon(recordId) {
    var formData = {
        'code': $('#editCouponModal #couponCode').val(),
        'type': $('#editCouponModal #couponType').val(),
        'amount': $('#editCouponModal #couponAmount').val(),
        'due_date': $('#editCouponModal #couponExpiry').val(),
        '_token': $('meta[name="csrf-token"]').attr('content')
    };

    $.ajax({
        url: 'coupons/' + recordId,
        type: 'PUT',
        data: formData,
        success: function (response) {
            console.log('Cupón actualizado:', response);
            $('#editCouponModal').modal('hide');
            dt_coupons.ajax.reload(null, false);

            // Mostrar SweetAlert de éxito
            Swal.fire({
                icon: 'success',
                title: 'Cupón actualizado',
                text: 'El cupón ha sido actualizado correctamente.'
            }).then((result) => {window.location.reload();});    
            
        },
        error: function (xhr) {
            console.error('Error al actualizar el cupón:', xhr);
            // Mostrar SweetAlert de error
            Swal.fire({
                icon: 'error',
                title: 'Error al actualizar el cupón',
                text: 'No se pudo actualizar el cupón. Intente nuevamente.'
            });
        }
    });
  }


  $('#deleteSelected').on('click', function () {
      var selectedIds = [];

      $('.datatables-coupons tbody input[type="checkbox"]:checked').each(function () {
          selectedIds.push($(this).data('id'));
      });

      if (selectedIds.length === 0) {
          Swal.fire({
              icon: 'warning',
              title: 'Atención',
              text: 'Por favor, seleccione al menos un cupón para eliminar.'
          });
          return;
      }

      Swal.fire({
          title: '¿Estás seguro?',
          text: 'Esta acción no se puede deshacer',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Sí, eliminar!',
          cancelButtonText: 'Cancelar'
      }).then((result) => {
          if (result.isConfirmed) {
              $.ajax({
                  url: 'coupons/delete-selected',
                  type: 'POST',
                  headers: {
                      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                  },
                  data: {
                      ids: selectedIds
                  },
                  success: function (result) {
                      if (result.success) {
                          Swal.fire(
                              'Eliminado!',
                              'Los cupones seleccionados han sido eliminados.',
                              'success'
                          );
                          dt_coupons.ajax.reload(null, false);
                      } else {
                          Swal.fire(
                              'Error!',
                              'No se pudieron eliminar los cupones seleccionados. Intente de nuevo.',
                              'error'
                          );
                      }
                  },
                  error: function (xhr, ajaxOptions, thrownError) {
                      Swal.fire(
                          'Error!',
                          'No se pudieron eliminar los cupones seleccionados: ' + xhr.responseJSON.message,
                          'error'
                      );
                  }
              });
          }
      });
  });

  // Limitar a 100% cuando está seleccionado porcentaje en add-coupon
  $(document).ready(function() {
    // Función para aplicar la restricción de valor máximo
    function applyMaxAmountConstraint($typeSelect, $amountInput) {
        if ($typeSelect.val() === 'percentage') {
            $amountInput.attr('max', '100');
            if (parseInt($amountInput.val()) > 100) {
                $amountInput.val('100');
            }
        } else {
            $amountInput.removeAttr('max');
        }
    }

    // Aplicar las restricciones tanto en el modal de añadir como en el de editar
    var $addTypeSelect = $('#addCouponModal #couponType');
    var $addAmountInput = $('#addCouponModal #couponAmount');
    $addTypeSelect.on('change', function() {
        applyMaxAmountConstraint($addTypeSelect, $addAmountInput);
    });
    $addAmountInput.on('input', function() {
        applyMaxAmountConstraint($addTypeSelect, $addAmountInput);
    });

    var $editTypeSelect = $('#editCouponModal #couponType');
    var $editAmountInput = $('#editCouponModal #couponAmount');
    $editTypeSelect.on('change', function() {
        applyMaxAmountConstraint($editTypeSelect, $editAmountInput);
    });
    $editAmountInput.on('input', function() {
        applyMaxAmountConstraint($editTypeSelect, $editAmountInput);
    });

    // Inicialización inicial para ambos modales
    applyMaxAmountConstraint($addTypeSelect, $addAmountInput);
    applyMaxAmountConstraint($editTypeSelect, $editAmountInput);
  });



  $('#addCouponModal').on('click', '#submitCouponBtn', function () {
      submitNewCoupon();
  });


  function submitNewCoupon() {
      var route = $(this).data('route');
      var formData = {
          'code': $('#couponCode').val(),
          'type': $('#couponType').val(),
          'amount': $('#couponAmount').val(),
          'due_date': $('#couponExpiry').val()
      };

      $.ajax({
        url: route,
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        success: function (response) {
            $('#addCouponModal').modal('hide');
            $('.datatables-coupons').DataTable().ajax.reload();
            Swal.fire({
                icon: 'success',
                title: 'Cupón Agregado',
                text: response.message
            }).then((result) => {window.location.reload();});       
        },
        error: function (xhr) {
            
            $('#addCouponModal').modal('hide'); 

            var errorMessage = xhr.responseJSON && xhr.responseJSON.errors
                ? Object.values(xhr.responseJSON.errors).flat().join('\n')
                : 'Error desconocido al guardar.';
            Swal.fire({
                icon: 'error',
                title: 'Error al guardar',
                text: errorMessage
            });
        }
      });
  }
});
