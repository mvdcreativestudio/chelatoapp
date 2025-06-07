'use strict';

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

  var dt_flavor_table = $('.datatables-flavors');

  if (dt_flavor_table.length) {
    var dt_flavors = dt_flavor_table.DataTable({
      ajax: 'products/flavors/datatable',
      columns: [
        { data: 'id' },
        { data: 'name' },
        { data: 'status' },
        { data: 'stock' },
        { data: null, defaultContent: '' }
      ],
      columnDefs: [
        {
          targets: 2,
          searchable: true,
          orderable: true,
          render: function (data, type, full, meta) {
            return data === 'active'
              ? '<span class="badge pill bg-success">Activo</span>'
              : '<span class="badge pill bg-danger">Inactivo</span>';
          }
        },
        {
          targets: 3,
          render: function (data, type, full, meta) {
            if (full.stock === 0) {
              return `<span class="badge bg-danger">${full.stock}</span>`;
            } else if (full.stock < 10) {
              return `<span class="badge bg-warning">${full.stock}</span>`;
            } else {
              return `<span class="badge bg-success">${full.stock}</span>`;
            }
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
              '<a href="javascript:void(0);" class="dropdown-item edit-record" data-id="' +
              full['id'] +
              '">Editar</a>' +
              '<a href="javascript:void(0);" class="dropdown-item switch-status" data-id="' +
              full['id'] +
              '" data-status="' +
              full['status'] +
              '" style="color:' +
              (full['status'] === 'active' ? 'red' : 'green') +
              ';">' +
              (full['status'] === 'active' ? 'Desactivar' : 'Activar') +
              '</a>' +
              '<a href="javascript:void(0);" class="dropdown-item delete-record" style="color: red;" data-id="' +
              full['id'] +
              '">Eliminar</a>' +
              '</div>' +
              '</div>'
            );
          }
        }
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
        infoEmpty: 'No hay variaciones para mostrar',
        emptyTable: 'No existe ningún sabor',
        search: '',
        searchPlaceholder: 'Buscar...',
        sLengthMenu: '_MENU_',
        info: 'Mostrando _START_ a _END_ de _TOTAL_ variaciones',
        infoFiltered: '(filtrados de _MAX_ variaciones)',
        paginate: {
          first: '<<',
          last: '>>',
          next: '>',
          previous: '<'
        }
      },
      renderer: 'bootstrap'
    });

    $('.toggle-column').on('change', function() {
      var column = dt_flavors.column($(this).attr('data-column'));
      column.visible(!column.visible());
  });

    $('.dataTables_length label select').addClass('form-select form-select-sm');
    $('.dataTables_filter label input').addClass('form-control');

    $('.datatables-flavors tbody').on('click', '.delete-record', function () {
      var recordId = $(this).data('id');
      Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar!',
        cancelButtonText: 'Cancelar'
      }).then(result => {
        if (result.isConfirmed) {
          $.ajax({
            url: baseUrl + 'admin/product-flavors/' + recordId + '/delete',
            type: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (result) {
              if (result.success) {
                Swal.fire('Eliminado!', 'El sabor ha sido eliminado.', 'success');
                dt_flavors.ajax.reload(null, false);
              } else {
                Swal.fire('Error!', 'No se pudo eliminar el sabor. Intente de nuevo.', 'error');
              }
            },
            error: function (xhr, ajaxOptions, thrownError) {
              Swal.fire('Error!', 'No se pudo eliminar el sabor: ' + xhr.responseJSON.message, 'error');
            }
          });
        }
      });
    });

    $('.datatables-flavors tbody').on('click', '.switch-status', function () {
      var recordId = $(this).data('id');
      var currentStatus = $(this).data('status');
      var newStatus = currentStatus === 'active' ? 'inactive' : 'active'; // Toggle status

      Swal.fire({
        title: '¿Estás seguro?',
        text: 'Estás por cambiar el estado del sabor.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, cambiar!',
        cancelButtonText: 'Cancelar'
      }).then(result => {
        if (result.isConfirmed) {
          switchStatus(recordId, newStatus); // Llamada a la función switchStatus
        }
      });
    });

    $('.datatables-flavors tbody').on('click', '.edit-record', function () {
      var recordId = $(this).data('id');
      $.ajax({
        url: baseUrl + 'admin/product-flavors/' + recordId,
        type: 'GET',
        success: function (response) {
          $('#editFlavorForm #flavorName').val(response.name); // Colocar el nombre del sabor en el input
          loadRecipes(response.recipes);
          $('#editFlavorModal').modal('show');
          $('#updateFlavorBtn').data('id', recordId);
        },
        error: function (xhr) {
          console.error('Error al obtener los detalles del sabor:', xhr);
        }
      });
    });

    $('#editFlavorModal').on('click', '#updateFlavorBtn', function () {
      var recordId = $(this).data('id');
      submitEditFlavor(recordId);
    });
  }

  function submitEditFlavor(recordId) {
    var formData = {
      name: $('#editFlavorForm #flavorName').val(),
      recipes: getRecipeData(),
      _token: $('meta[name="csrf-token"]').attr('content')
    };

    $.ajax({
      url: baseUrl + 'admin/product-flavors/' + recordId,
      type: 'PUT',
      data: formData,
      success: function (response) {
        $('#editFlavorModal').modal('hide');
        $('.datatables-flavors').DataTable().ajax.reload(null, false);

        Swal.fire({
          icon: 'success',
          title: 'Sabor actualizado',
          text: 'El sabor ha sido actualizado correctamente.'
        });
      },
      error: function (xhr) {
        console.error('Error al actualizar el sabor:', xhr);
        Swal.fire({
          icon: 'error',
          title: 'Error al actualizar el sabor',
          text: 'No se pudo actualizar el sabor. Intente nuevamente.'
        });
      }
    });
  }

  function getRecipeData() {
    var recipes = [];
    $('#editRecipesList [data-repeater-item]').each(function (index, element) {
      recipes.push({
        raw_material_id: $(element).find('select[name^="recipes"]').val(),
        quantity: $(element).find('input[name^="recipes"]').val()
      });
    });
    return recipes;
  }

  function updateRawMaterialOptions() {
    const selectedMaterials = [];
    $('.raw-material-select').each(function () {
      if ($(this).val()) {
        selectedMaterials.push($(this).val());
      }
    });

    $('.raw-material-select').each(function () {
      const currentSelect = $(this);
      currentSelect.find('option').each(function () {
        if ($(this).val() && selectedMaterials.includes($(this).val()) && $(this).val() !== currentSelect.val()) {
          $(this).prop('disabled', true);
        } else {
          $(this).prop('disabled', false);
        }
      });
    });
  }

  function loadRecipes(recipes) {
    try {
      const recipesList = $('#editRecipesList');
      recipesList.empty(); // Limpiar la lista de recetas
      if (recipes.length === 0) {
        const row = `
        <div data-repeater-item class="row mb-3">
          <div class="col-4">
            <label class="form-label" for="raw-material">Materia Prima</label>
            <select class="form-select raw-material-select" name="recipes[0][raw_material_id]">
              <option value="" disabled selected>Selecciona materia prima</option>
              ${rawMaterials.map(rawMaterial => `<option value="${rawMaterial.id}" data-unit="${rawMaterial.unit_of_measure}">${rawMaterial.name}</option>`).join('')}
            </select>
          </div>
          <div class="col-3">
            <label class="form-label" for="quantity">Cantidad</label>
            <input type="number" class="form-control" name="recipes[0][quantity]" placeholder="Cantidad" aria-label="Cantidad">
          </div>
          <div class="col-3 d-flex align-items-end">
            <input type="text" class="form-control unit-of-measure" placeholder="Unidad de medida" readonly>
          </div>
          <div class="col-2 d-flex align-items-end">
            <button type="button" class="btn btn-danger" data-repeater-delete>Eliminar</button>
          </div>
        </div>
      `;
        recipesList.append(row);
      } else {
        recipes.forEach((recipe, index) => {
          const row = `
          <div data-repeater-item class="row mb-3">
            <div class="col-4">
              <label class="form-label" for="raw-material">Materia Prima</label>
              <select class="form-select raw-material-select" name="recipes[${index}][raw_material_id]">
                <option value="" disabled>Selecciona materia prima</option>
                ${rawMaterials.map(rawMaterial => `<option value="${rawMaterial.id}" data-unit="${rawMaterial.unit_of_measure}" ${rawMaterial.id == recipe.raw_material_id ? 'selected' : ''}>${rawMaterial.name}</option>`).join('')}
              </select>
            </div>
            <div class="col-3">
              <label class="form-label" for="quantity">Cantidad</label>
              <input type="number" class="form-control" name="recipes[${index}][quantity]" placeholder="Cantidad" aria-label="Cantidad" value="${recipe.quantity}">
            </div>
            <div class="col-3 d-flex align-items-end">
              <input type="text" class="form-control unit-of-measure" placeholder="Unidad de medida" readonly value="${recipe.unit_of_measure}">
            </div>
            <div class="col-2 d-flex align-items-end">
              <button type="button" class="btn btn-danger" data-repeater-delete>Eliminar</button>
            </div>
          </div>
        `;
          recipesList.append(row);
        });
      }

      updateRawMaterialOptions();
    } catch (error) {
      console.error('Error al cargar las recetas:', error);
    }
  }

  function initRepeater() {
    $('[data-repeater-create]')
      .off('click')
      .on('click', function () {
        const repeaterList = $(this).closest('.card-body').find('[data-repeater-list]');
        const index = repeaterList.children().length;

        const row = `
        <div data-repeater-item class="row mb-3">
          <div class="col-4">
            <label class="form-label" for="raw-material">Materia Prima</label>
            <select class="form-select raw-material-select" name="recipes[${index}][raw_material_id]">
              <option value="" disabled selected>Selecciona materia prima</option>
              ${window.rawMaterials.map(rawMaterial => `<option value="${rawMaterial.id}" data-unit="${rawMaterial.unit_of_measure}">${rawMaterial.name}</option>`).join('')}
            </select>
          </div>
          <div class="col-3">
            <label class="form-label" for="quantity">Cantidad</label>
            <input type="number" class="form-control" name="recipes[${index}][quantity]" placeholder="Cantidad" aria-label="Cantidad">
          </div>
          <div class="col-3 d-flex align-items-end">
            <input type="text" class="form-control unit-of-measure" placeholder="Unidad de medida" readonly>
          </div>
          <div class="col-2 d-flex align-items-end">
            <button type="button" class="btn btn-danger" data-repeater-delete>Eliminar</button>
          </div>
        </div>
      `;

        repeaterList.append(row);
        updateRawMaterialOptions();
      });

    $(document).on('click', '[data-repeater-delete]', function () {
      $(this).closest('[data-repeater-item]').remove();
      updateRawMaterialOptions();
    });

    $(document).on('change', '.raw-material-select', function () {
      const unitOfMeasure = $(this).find('option:selected').data('unit');
      $(this).closest('.row').find('.unit-of-measure').val(unitOfMeasure);
      updateRawMaterialOptions();
    });
  }

  $('#addFlavorModal').on('shown.bs.modal', function () {
    initRepeater();
  });

  $('#editFlavorModal').on('shown.bs.modal', function () {
    initRepeater();
  });

  function switchStatus(recordId, newStatus) {
    $.ajax({
      url: baseUrl + `admin/product-flavors/${recordId}/switch-status`,
      method: 'PUT',
      data: {
        status: newStatus,
        _token: $('meta[name="csrf-token"]').attr('content')
      },
      success: function (response) {
        Swal.fire('Actualizado!', 'El estado del sabor ha sido actualizado.', 'success');
        $('.datatables-flavors').DataTable().ajax.reload(); // Recargar datos de la DataTable
      },
      error: function (xhr, ajaxOptions, thrownError) {
        Swal.fire('Error!', 'No se pudo cambiar el estado: ' + xhr.responseJSON.message, 'error');
      }
    });
  }
});
