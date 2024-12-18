$(function () {
  var driverListContainer = $('.driver-list-container');
  
  function formatPhoneNumber(phone) {
    if (!phone || typeof phone !== 'string') return '';
    return phone.replace(/\D/g, '');
  }

  function displayDrivers() {
    driverListContainer.html('');

    if (!drivers || drivers.length === 0) {
      driverListContainer.html(`
        <div class="col-12">
          <div class="alert alert-info text-center">
            <i class="bx bx-info-circle"></i> No hay choferes disponibles.
          </div>
        </div>
      `);
    } else {
      drivers.forEach(function (driver) {
        // Validar que name y last_name existan
        const name = driver.name || '';
        const lastName = driver.last_name || '';
        const fullName = `${name} ${lastName}`.trim();
        const truncatedName = fullName.length > 20 ? fullName.substring(0, 20) + '...' : fullName;
        const healthDate = driver.health_date ? new Date(driver.health_date).toLocaleDateString() : '';
        
        function capitalizeFirstLetter(str) {
          if (!str) return '';
          return str.toLowerCase().replace(/(^|\s)[a-záéíóúñ]/g, function (match) {
            return match.toUpperCase();
          });
        }

        const capitalizedFullName = capitalizeFirstLetter(fullName);
        const capitalizedTruncatedName = capitalizeFirstLetter(truncatedName);

        // Manejo seguro del teléfono
        let phoneNumber = formatPhoneNumber(driver.phone);
        if (phoneNumber.startsWith('0')) {
          phoneNumber = phoneNumber.substring(1);
        }
        const whatsappUrl = phoneNumber ? `https://wa.me/598${phoneNumber}` : '#';
        const telUrl = phoneNumber ? `tel:+598${phoneNumber}` : '#';

        // Formatear fecha de licencia de manera segura
        const licenseDate = driver.license_date 
          ? new Date(driver.license_date).toLocaleDateString()
          : 'No disponible';

        const card = `
          <div class="col-md-6 col-lg-4 col-12 driver-card-wrapper">
            <div class="clients-card-container">
              <div class="clients-card position-relative">
                <div class="clients-card-header d-flex justify-content-between align-items-center">
                  <h5 class="clients-name mb-0" title="${capitalizedFullName}" data-full-name="${capitalizedFullName}" data-truncated-name="${capitalizedTruncatedName}">
                    ${capitalizedTruncatedName}
                  </h5>
                  <div class="d-flex align-items-center">
                    <span class="badge ${driver.is_active ? 'bg-success' : 'bg-danger'} me-2">
                      ${driver.is_active ? 'Activo' : 'Inactivo'}
                    </span>
                    <div class="clients-card-toggle">
                      <i class="bx bx-chevron-down fs-3"></i>
                    </div>
                  </div>
                </div>
                <div class="clients-card-body" style="display: none;">
                  <div class="d-flex flex-column h-100">
                    <div>
                      ${driver.document ? `
                        <p class="mb-2">
                          <i class="bx bx-id-card me-2"></i> Documento: ${driver.document} 
                        </p>
                      ` : ''}
                      ${driver.address ? `
                        <p class="mb-2">
                          <i class="bx bx-map me-2"></i> ${capitalizeFirstLetter(driver.address)}
                        </p>
                      ` : ''}
                      ${driver.phone ? `
                        <p class="mb-2">
                          <i class="bx bx-phone me-2"></i> ${driver.phone}
                        </p>
                      ` : ''}
                      ${driver.license_date ? `
                        <p class="mb-2">
                          <i class="bx bx-calendar me-2"></i> Vencimiento licencia: ${licenseDate}
                        </p>
                      ` : ''}
                      ${driver.health_date ? `
                        <p class="mb-2">
                          <i class="bx bx-calendar me-2"></i> Validez carnet salud: ${healthDate}
                        </p>
                      ` : ''}
                    </div>
                    <div class="d-inline-flex justify-content-end mt-auto mb-2 gap-1">
                          <a class="btn delete-driver p-1" data-driver-id="${driver.id}">
                          <i class="bx bx-trash"></i>
                        </a>                       
                       </div>
                  </div>
                </div>
              </div>
            </div>
          </div>`;
        driverListContainer.append(card);
      });

      // Event Listeners para las cards
      $('.clients-card').on('click', function (e) {
        if (!$(e.target).closest('.view-clients').length) {
          e.preventDefault();
          e.stopPropagation();
          const $this = $(this);
          const $icon = $this.find('.clients-card-toggle i');
          const $body = $this.find('.clients-card-body');
          const $wrapper = $this.closest('.driver-card-wrapper');
          const $name = $this.find('.clients-name');
          
          $icon.toggleClass('bx-chevron-down bx-chevron-up');
          $body.slideToggle();
          
          if ($body.is(':visible')) {
            $name.text(capitalizeFirstLetter($name.data('full-name')));
          } else {
            $name.text(capitalizeFirstLetter($name.data('truncated-name')));
          }
          
          $('.clients-card-body').not($body).hide();
          $('.clients-card-toggle i').not($icon).removeClass('bx-chevron-up').addClass('bx-chevron-down');
          $('.driver-card-wrapper').not($wrapper).find('.clients-name').each(function () {
            $(this).text(capitalizeFirstLetter($(this).data('truncated-name')));
          });
        }
      });

      $('.view-clients').on('click', function (e) {
        e.stopPropagation();
      });
    }
  }

  displayDrivers();

  // Buscador
  $('#searchClient').on('input', function () {
    var searchTerm = $(this).val().toLowerCase();
    $('.driver-card-wrapper').each(function () {
      var driverInfo = $(this).text().toLowerCase();
      $(this).toggle(driverInfo.includes(searchTerm));
    });
  });

   // Evento para abrir el offcanvas al hacer clic en "Agregar vehículo"
   $('.btn-primary').on('click', function () {
    $('#addDriverOffCanvas').offcanvas('show');
  });

  // Evento para manejar el envío del formulario
  $('#addDriverForm').on('submit', function (e) {
    e.preventDefault();

    const formData = $(this).serialize(); // Serializar datos del formulario

    $.ajax({
      url: 'drivers',
      type: 'POST',
      data: formData,
      headers: {
        'X-CSRF-TOKEN': window.csrfToken
      },
      success: function (response) {
        $('#addDriverOffCanvas').offcanvas('hide');
        Swal.fire({
          title: '¡Éxito!',
          text: 'El chofer ha sido agregado correctamente.',
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
          text: 'Hubo un problema al agregar el chofer.',
          icon: 'error',
          confirmButtonText: 'Aceptar'
        });
      }
    });
  });

  $('.delete-driver').on('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const driverId = $(this).data('driver-id');
    const $driverCard = $(this).closest('.driver-card-wrapper');

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
          url: `drivers/${driverId}`,
          type: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': window.csrfToken
          },
          success: function(response) {
            Swal.fire(
              '¡Eliminado!',
              'El chofer ha sido eliminado correctamente.',
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
              'No se pudo eliminar el chofer.',
              'error'
            );
          }
        });
      }
    });
  });


});