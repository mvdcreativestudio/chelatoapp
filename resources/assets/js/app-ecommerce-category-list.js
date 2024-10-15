'use strict';

$(function () {
    // Inicialización de select2 para dropdowns dentro del offcanvas
    var select2 = $('.select2');
    if (select2.length) {
        select2.each(function () {
            var $this = $(this);
            $this.wrap('<div class="position-relative"></div>').select2({
                dropdownParent: $this.parent(),
                placeholder: $this.data('placeholder') // Dynamic placeholder
            });
        });
    }

    // Generación automática de slug en el frontend
    $('#ecommerce-category-title').on('input', function () {
        const slugField = $('#ecommerce-category-slug');
        const slug = $(this).val().toLowerCase().replace(/[^\w\s]+/g, '').replace(/\s+/g, '-');
        slugField.val(slug);
    });

    // Guardar nueva categoría
    $('#eCommerceCategoryListForm').submit(function(event) {
        event.preventDefault();

        var statusValue = $('#statusSwitch').is(':checked') ? '1' : '0';
        $('input[name="status"]').val(statusValue);

        this.submit();
    });

    // Función para generar tarjetas de categorías
    function generateCategoryCards(categories) {
      const categoryContainer = $('#category-list-container');
      categoryContainer.empty(); // Limpiar el contenedor antes de agregar nuevas tarjetas

      console.log(categories);

      if (categories.length === 0) {
          categoryContainer.html(`
              <div class="alert alert-info text-center w-100">
                  <i class="bx bx-info-circle"></i> No existen categorías disponibles.
              </div>
          `);
      } else {
          categories.forEach(category => {
              const statusClass = category.status === '1' ? 'bg-success' : 'bg-danger';
              const statusText = category.status === '1' ? 'Activo' : 'Inactivo';

              // Asegúrate de usar "products_sum_stock" en lugar de "stock_count"
              const stockCount = category.products_sum_stock ?? 0;



              const card = `
                  <div class="col">
                      <div class="card h-100 category-card">
                          <div class="card-body category-card-body">
                              <h5 class="category-title">${category.name}</h5>
                              <p class="category-status text-muted small"><span class="badge ${statusClass}">${statusText}</span></p>
                              <p class="category-product-count text-muted small">Productos: ${category.product_count}</p>
                              <p class="category-product-count text-muted small">Stock Total: ${stockCount}</p>
                              <div class="d-flex justify-content-end category-card-actions">
                                  <a href="javascript:void(0);" class="btn btn-sm btn-primary edit-record me-2" data-id="${category.id}">Editar</a>
                              </div>
                          </div>
                      </div>
                  </div>
              `;
              categoryContainer.append(card);
          });
      }
    }



    // Cargar categorías desde la ruta product-categories/datatable
    function fetchCategories(search = '') {
      $.ajax({
          url: 'product-categories/datatable',
          method: 'GET',
          data: { search: search }, // Enviar el término de búsqueda al backend
          success: function(response) {
              const categories = response.data;
              generateCategoryCards(categories);
          },
          error: function() {
              $('#category-list-container').html(`
                  <div class="alert alert-danger text-center w-100">
                      <i class="bx bx-error"></i> Error al cargar las categorías. Intente nuevamente.
                  </div>
              `);
          }
      });
    }


    // Evento para buscar categorías
    $('#searchCategory').on('input', function () {
        const searchQuery = $(this).val(); // Obtener el valor del input de búsqueda
        fetchCategories(searchQuery); // Llamar a la función fetchCategories con el término de búsqueda
    });

    // Cargar datos en el formulario de edición
    $(document).on('click', '.edit-record', function () {
        var recordId = $(this).data('id');

        $.ajax({
            url: 'product-categories/' + recordId + '/get-selected',
            type: 'GET',
            success: function (data) {
                $('#edit_ecommerce-category-title').val(data.name);
                $('#edit_ecommerce-category-slug').val(data.slug);
                $('#edit_ecommerce-category-parent-category').val(data.parent_id).trigger('change');
                $('#edit-statusSwitch').prop('checked', data.status == '1');

                $('#editCategoryButton').data('id', recordId);

                var editOffcanvas = new bootstrap.Offcanvas(document.getElementById('offcanvasEcommerceCategoryEdit'));
                editOffcanvas.show();
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error al cargar la categoría',
                    text: 'No se pudo cargar la categoría. Intente nuevamente.'
                });
            }
        });
    });

    // POST para actualizar la categoría
    function submitEditProductCategory(recordId) {
        var formData = new FormData();

        formData.append('name', $('#edit_ecommerce-category-title').val());
        formData.append('slug', $('#edit_ecommerce-category-slug').val());
        formData.append('store_id', $('#edit_ecommerce-category-store').val());
        formData.append('parent_id', $('#edit_ecommerce-category-parent-category').val());
        formData.append('status', $('#edit-statusSwitch').is(':checked') ? 1 : 0);

        var imageFile = $('#edit_ecommerce-category-image')[0].files[0];
        if (imageFile) {
            formData.append('image', imageFile);
        }

        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        $.ajax({
            url: `product-categories/${recordId}/update-selected`,
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function () {
                $('#offcanvasEcommerceCategoryEdit').offcanvas('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Categoría actualizada',
                    text: 'La categoría ha sido actualizada correctamente.'
                }).then(() => fetchCategories());
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error al actualizar la categoría',
                    text: 'No se pudo actualizar la categoría. Intente nuevamente.'
                });
            }
        });
    }

    // Asociar el clic del botón para editar la categoría
    $(document).on('click', '#editCategoryButton', function () {
        var recordId = $(this).data('id');
        submitEditProductCategory(recordId);
    });

    // Ajustar el estado del switch en creación
    var statusSwitch = document.getElementById('statusSwitch');
    if (statusSwitch) {
        statusSwitch.value = statusSwitch.checked ? '1' : '0';

        statusSwitch.addEventListener('change', function() {
            this.value = this.checked ? '1' : '0';
        });
    }

    // Ajustar el estado del switch en edición
    var editStatusSwitch = document.getElementById('edit-statusSwitch');
    if (editStatusSwitch) {
        editStatusSwitch.value = editStatusSwitch.checked ? '1' : '0';

        editStatusSwitch.addEventListener('change', function() {
            this.value = this.checked ? '1' : '0';
        });
    }

    // Cargar categorías al cargar la página
    fetchCategories();
});
