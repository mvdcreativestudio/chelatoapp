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
            { data: 'switch', orderable: false, searchable: false }, // 0️⃣ Checkbox
            { data: 'id' }, // 1️⃣ ID
            { data: 'code' }, // 2️⃣ Código
            { data: 'type' }, // 3️⃣ Tipo
            { data: 'amount' }, // 4️⃣ Valor
            { data: 'init_date' }, // 5️⃣ Fecha de Inicio
            { data: 'due_date' }, // 6️⃣ Fecha de Expiración
            { data: 'creator_name' }, // 7️⃣ Creado por
            { data: null, defaultContent: '' } // 8️⃣ Acciones
          ],

          columnDefs: [
            {
                targets: 0, // ✅ Checkbox (switch)
                render: function (data, type, full, meta) {
                    return '<input type="checkbox" class="form-check-input" data-id="' + full['id'] + '">';
                }
            },
            {
                targets: 2, // ✅ Código
                render: function (data, type, full, meta) {
                    return '<a href="' + baseUrl + 'coupons/' + full['id'] + '/show" class="text-body">' + data + '</a>';
                }
            },
            {
                targets: 3, // ✅ Tipo de cupón
                render: function (data, type, full, meta) {
                    return data === 'percentage' ? 'Porcentaje' : 'Descuento fijo';
                }
            },
            {
                targets: 4, // ✅ Valor del cupón (se muestra como "$" o "%")
                render: function (data, type, full, meta) {
                    return full.type === 'percentage' ? data + '%' : '$' + data;
                }
            },
            {
                targets: 5, // ✅ Fecha de Inicio
                render: function (data, type, full, meta) {
                    if (data === null) {
                        return 'Sin comienzo';
                    } else {
                        var currentDate = moment().startOf('day');
                        var initDate = moment(data).startOf('day');
                        var dateClass = initDate.isBefore(currentDate) ? 'text-danger' : 'text-success';
                        return '<span class="' + dateClass + '">' + moment(data).locale('es').format('DD/MM/YYYY') + '</span>';
                    }
                }
            },
            {
                targets: 6, // ✅ Fecha de Expiración
                render: function (data, type, full, meta) {
                    if (data === null) {
                        return 'Sin expiración';
                    } else {
                        var currentDate = moment().startOf('day');
                        var dueDate = moment(data).startOf('day');
                        var dateClass = dueDate.isBefore(currentDate) ? 'text-danger' : 'text-success';
                        return '<span class="' + dateClass + '">' + moment(data).locale('es').format('DD/MM/YYYY') + '</span>';
                    }
                }
            },
            {
                targets: 7, // ✅ Creado por
                render: function (data, type, full, meta) {
                    return data === null ? 'No registrado' : data;
                }
            },
            {
                targets: 8, // ✅ Acciones
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

            if (response.init_date) {
              var dueDate = response.init_date.split(' ')[0];
              $('#detailCouponModal #couponInit').val(dueDate);
            } else {
                $('#detailCouponModal #couponInit').val('');
            }

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

    $.ajax({
        url: 'coupons/' + recordId,
        type: 'GET',
        success: function (response) {
            console.log('Response:', response); // 🔎 Verificar datos en consola

            $('#editCouponModal #couponCode').val(response.code);
            $('#editCouponModal #couponType').val(response.type);
            $('#editCouponModal #couponAmount').val(response.amount);

            // ✅ Limpiar checkboxes antes de marcar los excluidos
            $('.editExcludedProducts').prop('checked', false);
            $('.editExcludedCategories').prop('checked', false);

            // ✅ Marcar productos excluidos
            response.excluded_products.forEach(id => {
                $('.editExcludedProducts[value="' + id + '"]').prop('checked', true);
            });

            // ✅ Marcar categorías excluidas
            response.excluded_categories.forEach(id => {
                $('.editExcludedCategories[value="' + id + '"]').prop('checked', true);
            });

            // ✅ Verificar fecha de expiración
            $('#editCouponModal #couponExpiry').val(response.due_date ? response.due_date.split(' ')[0] : '');

            // ✅ Verificar fecha de inicio
            $('#editCouponModal #couponInit').val(response.init_date ? response.init_date.split(' ')[0] : '');

            $('#editCouponModal').modal('show');
        },
        error: function (xhr) {
            console.error('Error al obtener los detalles del cupón:', xhr);
        }
    });
});




// POST para editar el cupon en la base de datos.

function submitEditCoupon(recordId) {
  var formData = {
      'code': $('#editCouponModal #couponCode').val(),
      'type': $('#editCouponModal #couponType').val(),
      'amount': $('#editCouponModal #couponAmount').val(),
      'init_date': $('#editCouponModal #couponInit').val(),
      'due_date': $('#editCouponModal #couponExpiry').val(),
      'excluded_products': [],
      'excluded_categories': [],
      '_token': $('meta[name="csrf-token"]').attr('content')
  };

  // ✅ Capturar productos excluidos
  $('input[name="excluded_products[]"]:checked').each(function () {
      formData.excluded_products.push($(this).val());
  });

  // ✅ Capturar categorías excluidas
  $('input[name="excluded_categories[]"]:checked').each(function () {
      formData.excluded_categories.push($(this).val());
  });

  console.log('Datos enviados:', formData); // 🔎 Verificar en consola

  $.ajax({
      url: 'coupons/' + recordId,
      type: 'PUT',
      data: formData,
      success: function (response) {
          console.log('Cupón actualizado:', response);
          $('#editCouponModal').modal('hide');
          dt_coupons.ajax.reload(null, false);
          Swal.fire({
              icon: 'success',
              title: 'Cupón actualizado',
              text: 'El cupón ha sido actualizado correctamente.'
          }).then(() => { window.location.reload(); });
      },
      error: function (xhr) {
          console.error('Error al actualizar el cupón:', xhr);
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
    var route = $('#submitCouponBtn').data('route');

    // 🔎 Capturar valores del formulario
    var formData = {
        'code': $('#couponCode').val(),
        'type': $('#couponType').val(),
        'amount': $('#couponAmount').val(),
        'init_date': $('#couponInit').val(),
        'due_date': $('#couponExpiry').val(),
        'excluded_products': [],
        'excluded_categories': []
    };

    // ✅ Capturar los productos excluidos marcados
    $('input[name="excluded_products[]"]:checked').each(function() {
        formData.excluded_products.push($(this).val());
    });

    // ✅ Capturar las categorías excluidas marcadas
    $('input[name="excluded_categories[]"]:checked').each(function() {
        formData.excluded_categories.push($(this).val());
    });

    console.log('Datos enviados:', formData); // 🔎 Verificar en la consola del navegador

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
