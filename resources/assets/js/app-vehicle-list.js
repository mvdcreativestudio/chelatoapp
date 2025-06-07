$(function () {
  var vehicleListContainer = $('.vehicle-list-container');

  function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
      day: '2-digit',
      month: '2-digit',
      year: '2-digit'
    });
  }

  function displayVehicles() {
    vehicleListContainer.html('');

    if (!vehicles || vehicles.length === 0) {
      vehicleListContainer.html(`
          <div class="col-12">
            <div class="alert alert-info text-center">
              <i class="bx bx-info-circle"></i> No hay vehículos disponibles.
            </div>
          </div>
        `);
    } else {
      vehicles.forEach(function (vehicle) {
        const number = vehicle.number || '';
        const brand = vehicle.brand || '';
        const plate = vehicle.plate || '';
        const capacity = vehicle.capacity || 0;
        const applus_date = vehicle.applus_date ? formatDate(vehicle.applus_date) : '';
        const truncatedNumber = number.length > 20 ? number.substring(0, 20) + '...' : number;

        const card = `
            <div class="col-md-6 col-lg-4 col-12 vehicle-card-wrapper">
              <div class="clients-card-container">
                <div class="clients-card position-relative">
                  <div class="clients-card-header d-flex justify-content-between align-items-center">
                    <h5 class="clients-name mb-0" title="${number}" data-full-name="${number}" data-truncated-name="${truncatedNumber}">
                      ${truncatedNumber}
                    </h5>
                    <div class="d-flex align-items-center">
                      <div class="clients-card-toggle">
                        <i class="bx bx-chevron-down fs-3"></i>
                      </div>
                    </div>
                  </div>
                  <div class="clients-card-body" style="display: none;">
                    <div class="d-flex flex-column h-100">
                      <div>
                        ${brand ? `
                          <p class="mb-2">
                            <i class="bx bx-car me-2"></i> Marca: ${brand} 
                          </p>
                        ` : ''}
                        ${plate ? `
                          <p class="mb-2">
                             <i class="bx bx-barcode me-2"></i> Matrícula: ${plate}
                          </p>
                        ` : ''}
                        ${capacity ? `
                          <p class="mb-2">
                             <i class="bx bx-cube me-2"></i> </i> Capacidad: ${capacity}
                          </p>
                        ` : ''}
                        ${applus_date ? `
                          <p class="mb-2">
                             <i class="bx bx-calendar me-2"></i> Fecha de APPLUS: ${applus_date}
                          </p>
                        ` : ''}
                      </div>
                      <div class="d-inline-flex justify-content-end mt-auto mb-2 gap-1">
                          <a class="btn delete-vehicle p-1" data-vehicle-id="${vehicle.id}">
                          <i class="bx bx-trash"></i>
                        </a>                       
                       </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>`;
        vehicleListContainer.append(card);
      });

      // Event Listeners para las cards
      $('.clients-card').on('click', function (e) {
        if (!$(e.target).closest('.view-clients').length) {
          e.preventDefault();
          e.stopPropagation();
          const $this = $(this);
          const $icon = $this.find('.clients-card-toggle i');
          const $body = $this.find('.clients-card-body');
          const $wrapper = $this.closest('.vehicle-card-wrapper');
          const $name = $this.find('.clients-name');

          $icon.toggleClass('bx-chevron-down bx-chevron-up');
          $body.slideToggle();

          if ($body.is(':visible')) {
            $name.text($name.data('full-name'));
          } else {
            $name.text($name.data('truncated-name'));
          }

          $('.clients-card-body').not($body).hide();
          $('.clients-card-toggle i').not($icon).removeClass('bx-chevron-up').addClass('bx-chevron-down');
          $('.vehicle-card-wrapper').not($wrapper).find('.clients-name').each(function () {
            $(this).text($(this).data('truncated-name'));
          });
        }
      });

      $('.view-clients').on('click', function (e) {
        e.stopPropagation();
      });
    }
  }

  displayVehicles();

  // Buscador
  $('#searchVehicle').on('input', function () {
    var searchTerm = $(this).val().toLowerCase();
    $('.vehicle-card-wrapper').each(function () {
      var vehicleInfo = $(this).text().toLowerCase();
      $(this).toggle(vehicleInfo.includes(searchTerm));
    });
  });

  // Evento para abrir el offcanvas al hacer clic en "Agregar vehículo"
  $('.btn-primary').on('click', function () {
    $('#addVehicleOffCanvas').offcanvas('show');
  });

  // Evento para manejar el envío del formulario
  $('#addVehicleForm').on('submit', function (e) { // Cambiado para usar el ID del formulario
    e.preventDefault();

    const formData = $(this).serialize();

    $.ajax({
        url: 'vehicles',
        type: 'POST',
        data: formData,
        headers: {
            'X-CSRF-TOKEN': window.csrfToken
        },
        success: function (response) {
            $('#addVehicleOffCanvas').offcanvas('hide');
            Swal.fire({
                title: '¡Éxito!',
                text: 'El vehículo ha sido agregado correctamente.',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                if (result.isConfirmed) {
                    location.reload();
                }
            });
        },
        error: function (error) {
            Swal.fire({
                title: 'Error',
                text: 'Hubo un problema al agregar el vehículo.',
                icon: 'error',
                confirmButtonText: 'Aceptar'
            });
        }
    });
});

  $('.delete-vehicle').on('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const vehicleId = $(this).data('vehicle-id');
    const $vehicleCard = $(this).closest('.vehicle-card-wrapper');

    Swal.fire({
      title: '¿Estás seguro?',
      text: "Esta acción no se puede deshacer",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: `vehicles/${vehicleId}`,
          type: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': window.csrfToken
          },
          success: function(response) {
            Swal.fire(
              '¡Eliminado!',
              'El vehículo ha sido eliminado correctamente.',
              'success'
            ).then((result) => {
              if (result.isConfirmed) {
                location.reload();
              }
            });
          },
          error: function(error) {
            Swal.fire(
              'Error',
              'No se pudo eliminar el vehículo.',
              'error'
            );
          }
        });
      }
    });
  });


});
