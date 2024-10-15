$(function () {
  // Declaración de variables
  var clientListContainer = $('.client-list-container');
  var ajaxUrl = clientListContainer.data('ajax-url');

  function fetchClients() {
    $.ajax({
      url: ajaxUrl,
      method: 'GET',
      success: function (response) {
        var clients = response.data;
        clientListContainer.html(''); // Limpiar el contenedor

        if (clients.length === 0) {
          clientListContainer.html(`
            <div class="col-12">
              <div class="alert alert-info text-center">
                <i class="bx bx-info-circle"></i> No hay clientes disponibles.
              </div>
            </div>
          `);
        } else {
          clients.forEach(function (client) {
            const fullName = `${client.name} ${client.lastname}`;
            const truncatedName = fullName.length > 20 ? fullName.substring(0, 20) + '...' : fullName;

            // Aplica la función de capitalización a los nombres y cualquier otro campo relevante
            const capitalizedFullName = capitalizeFirstLetter(fullName);
            const capitalizedCompanyName = client.company_name ? capitalizeFirstLetter(client.company_name) : '';
            const capitalizedTruncatedName = capitalizeFirstLetter(truncatedName);


            const card = `
            <div class="col-md-6 col-lg-4 col-12 client-card-wrapper">
              <div class="clients-card-container">
                <div class="clients-card position-relative">
                  <div class="clients-card-header d-flex justify-content-between align-items-center">
                    <h5 class="clients-name mb-0" title="${client.type === 'company' ? capitalizedCompanyName : capitalizedTruncatedName}" data-full-name="${client.type === 'company' ? capitalizedCompanyName : capitalizedFullName}" data-truncated-name="${client.type === 'company' ? capitalizedCompanyName : capitalizedTruncatedName}">
                      ${client.type === 'company' ? capitalizedCompanyName : capitalizedTruncatedName.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()).join(' ')}
                    </h5>
                    <div class="d-flex align-items-center">
                      <span class="clients-type badge ${client.type === 'company' ? 'bg-primary' : 'bg-primary-op'} me-2">
                        ${client.type === 'company' ? 'Empresa' : 'Persona'}
                      </span>
                      <div class="clients-card-toggle">
                        <i class="bx bx-chevron-down fs-3"></i>
                      </div>
                    </div>
                  </div>
                  <div class="clients-card-body" style="display: none;">
                    <div class="d-flex flex-column h-100">
                      <div>
                        <!-- Condicional para mostrar nombre y apellido si están disponibles -->
                        ${client.type === 'company' && client.name && client.lastname ? `
                          <p class="clients-personal-name mb-2">
                            <strong>Representante:</strong> ${capitalizeFirstLetter(client.name)} ${capitalizeFirstLetter(client.lastname)}
                          </p>
                        ` : ''}

                        <p class="clients-document mb-2">
                          <strong>${client.type === 'company' ? 'RUT:' : 'CI:'}</strong>
                          ${client.type === 'company' ? client.rut : client.ci}
                        </p>
                        ${client.type === 'company' ? `<p class="clients-company mb-2"><strong>Razón Social:</strong> ${capitalizedCompanyName}</p>` : ''}

                        <!-- Condicional para email (siempre presente) -->
                        <p class="clients-email mb-2"><i class="bx bx-envelope me-2"></i> ${client.email}</p>

                        <!-- Condicionales para evitar mostrar campos vacíos, nulos o con valor "-" -->
                        ${client.address && client.address !== '-' ? `<p class="clients-address mb-2"><i class="bx bx-map me-2"></i> ${capitalizeFirstLetter(client.address)}</p>` : ''}
                        ${(client.city && client.city !== '-') || (client.state && client.state !== '-') || (client.department && client.department !== '-') ? `
                          <p class="clients-location mb-2">
                            <i class="bx bx-buildings me-2"></i>
                            ${client.city && client.city !== '-' ? capitalizeFirstLetter(client.city) : ''}${client.city && client.city !== '-' && ((client.state && client.state !== '-') || (client.department && client.department !== '-')) ? ', ' : ''}${client.state && client.state !== '-' ? capitalizeFirstLetter(client.state) : ''}${client.state && client.state !== '-' && (client.department && client.department !== '-') ? ', ' : ''}${client.department && client.department !== '-' ? capitalizeFirstLetter(client.department) : ''}
                          </p>` : ''}
                        ${client.phone && client.phone !== '-' ? `<p class="clients-phone mb-2"><i class="bx bx-phone me-2"></i> ${client.phone}</p>` : ''}
                        ${client.website && client.website !== '-' ? `<p class="clients-website mb-2"><i class="bx bx-globe me-2"></i> <a href="${client.website}" target="_blank">${client.website}</a></p>` : ''}

                      </div>
                      <div class="text-end mt-auto mb-2">
                        <a href="clients/${client.id}" class="btn btn-sm btn-outline-primary view-clients">
                          Ver Cliente <i class="bx bx-right-arrow-alt ms-1"></i>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          `;

            clientListContainer.append(card);
          });

          // Función para capitalizar solo la primera letra de cada palabra y convertir el resto a minúsculas
          function capitalizeFirstLetter(str) {
            return str
              .toLowerCase() // Convierte todo a minúsculas primero
              .replace(/(^|\s)[a-záéíóúñ]/g, function (match) {
                return match.toUpperCase(); // Luego capitaliza la primera letra de cada palabra
              });
          }

          // Agregar evento de clic para desplegar la información
          $('.clients-card').on('click', function (e) {
            if (!$(e.target).closest('.view-clients').length) {
              e.preventDefault();
              e.stopPropagation();
              const $this = $(this);
              const $icon = $this.find('.clients-card-toggle i');
              const $body = $this.find('.clients-card-body');
              const $wrapper = $this.closest('.clients-card-wrapper');
              const $name = $this.find('.clients-name');

              // Alternar la tarjeta seleccionada
              $icon.toggleClass('bx-chevron-down bx-chevron-up');
              $body.slideToggle();


              // Mostrar nombre completo o truncado según si la tarjeta está abierta o cerrada
              if ($body.is(':visible')) {
                $name.text(capitalizeFirstLetter($name.data('full-name').toLowerCase()));
              } else {
                $name.text(capitalizeFirstLetter($name.data('truncated-name').toLowerCase()));
              }

              // Cerrar las demás tarjetas y restaurar su tamaño
              $('.clients-card-body').not($body).hide();
              $('.clients-card-toggle i').not($icon).removeClass('bx-chevron-up').addClass('bx-chevron-down');
              $('.clients-card-wrapper').not($wrapper).find('.clients-name').each(function() {
                $(this).text(capitalizeFirstLetter($(this).data('truncated-name').toLowerCase()));
              });
            }
          });


          // Prevenir el cierre de la tarjeta al hacer clic en "Ver Cliente"
          $('.view-clients').on('click', function(e) {
            e.stopPropagation();
          });

          // Evitar que el clic en el botón de edición propague al contenedor de la tarjeta
          $('.edit-clients').on('click', function(e) {
            e.stopPropagation();
            // Aquí puedes agregar la lógica para editar el cliente
          });
        }
      },
      error: function (xhr, status, error) {
        console.error('Error al obtener los datos de clientes:', error);
        clientListContainer.html(`
          <div class="col-12">
            <div class="alert alert-danger text-center">
              <i class="bx bx-error-circle"></i> Error al cargar los clientes. Por favor, intente nuevamente.
            </div>
          </div>
        `);
      }
    });
  }

  // Fetch clients on page load
  fetchClients();

  // Implementar búsqueda de clientes
  $('#searchClient').on('input', function () {
    var searchTerm = $(this).val().toLowerCase();
    $('.client-card').each(function () {
      var clientInfo = $(this).text().toLowerCase();
      $(this).closest('.col-md-6').toggle(clientInfo.includes(searchTerm));
    });
  });
});


// Validation & Phone mask
(function () {
  const phoneMaskList = document.querySelectorAll('.phone-mask'),
    eCommerceCustomerAddForm = document.getElementById('eCommerceCustomerAddForm');

  // Phone Number
  if (phoneMaskList) {
    phoneMaskList.forEach(function (phoneMask) {
      new Cleave(phoneMask, {
        phone: true,
        phoneRegionCode: 'US'
      });
    });
  }
  // Add New customer Form Validation
  const fv = FormValidation.formValidation(eCommerceCustomerAddForm, {
    fields: {
      customerName: {
        validators: {
          notEmpty: {
            message: 'Please enter fullname '
          }
        }
      },
      customerEmail: {
        validators: {
          notEmpty: {
            message: 'Please enter your email'
          },
          emailAddress: {
            message: 'The value is not a valid email address'
          }
        }
      }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        // Use this for enabling/changing valid/invalid class
        eleValidClass: '',
        rowSelector: function (field, ele) {
          // field is the field name & ele is the field element
          return '.mb-3';
        }
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      // Submit the form when all fields are valid
      defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
      autoFocus: new FormValidation.plugins.AutoFocus()
    }
  });
})();

// Campo CI o RUT dependiendo si es CF o Empresa
$(document).ready(function () {
  // Escucha cambios en los botones de radio del tipo de cliente
  $('input[type=radio][name=type]').change(function () {
    clearErrors();
    if (this.value == 'individual') {
      $('#ciField').show();
      $('#ci').attr('required', true);
      $('#rutField').hide();
      $('#company_name').removeAttr('required');
      $('#rut').removeAttr('required');
      $('#ciudadAsterisk').hide();
      $('#departamentoAsterisk').hide();
    } else if (this.value == 'company') {
      $('#ciField').hide();
      $('#ci').removeAttr('required');
      $('#rutField').show();
      $('#razonSocialField').show();
      $('#company_name').attr('required', true);
      $('#rut').attr('required', true);
      $('#ciudadAsterisk').show();
      $('#departamentoAsterisk').show();
    }
  });
});


document.getElementById('guardarCliente').addEventListener('click', function (e) {
  e.preventDefault();

  // Obtener los elementos de los campos del formulario
  const nombre = document.getElementById('ecommerce-customer-add-name');
  const apellido = document.getElementById('ecommerce-customer-add-lastname');
  const tipo = document.querySelector('input[name="type"]:checked');  // Seleccionar el tipo de cliente (individual o company)
  const email = document.getElementById('ecommerce-customer-add-email');
  const ci = document.getElementById('ci');
  const rut = document.getElementById('rut');
  const razonSocial = document.getElementById('company_name');
  const direccion = document.getElementById('ecommerce-customer-add-address');
  const ciudad = document.getElementById('ecommerce-customer-add-town');
  const departamento = document.getElementById('ecommerce-customer-add-state');

  // Limpiar errores anteriores
  clearErrors();

  // Inicializar el indicador de error
  let hasError = false;

  // Validar campos obligatorios
  if (nombre.value.trim() === '') {
      showError(nombre, 'Este campo es obligatorio');
      hasError = true;
  }

  if (apellido.value.trim() === '') {
      showError(apellido, 'Este campo es obligatorio');
      hasError = true;
  }

  if (email.value.trim() === '') {
      showError(email, 'Este campo es obligatorio');
      hasError = true;
  }

  if (direccion.value.trim() === '') {
      showError(direccion, 'Este campo es obligatorio');
      hasError = true;
  }

  if (tipo.value === 'individual') {
      // Validar CI para personas individuales
      if (ci.value.trim() === '') {
          showError(ci, 'Este campo es obligatorio');
          hasError = true;
      }
      // Ocultar RUT y Razón Social si es individual
      document.getElementById('rutField').style.display = 'none';
      document.getElementById('ciField').style.display = 'block';
  } else if (tipo.value === 'company') {
      // Validar Razón Social y RUT para empresas
    if (razonSocial.value.trim() === '') {
      showError(razonSocial, 'Este campo es obligatorio');
      hasError = true;
    }

    if (rut.value.trim() === '') {
        // Validar solo una vez el RUT
        if (!rut.parentElement.querySelector('.error-message')) {
            showError(rut, 'Este campo es obligatorio');
        }
        hasError = true;
    }

    if (ciudad.value.trim() === '') {
        showError(ciudad, 'Este campo es obligatorio');
        hasError = true;
    }

    if (departamento.value.trim() === '') {
        showError(departamento, 'Este campo es obligatorio');
        hasError = true;
    }

    // Mostrar campos RUT, Razón Social, Dirección, Ciudad y Departamento si es empresa
    document.getElementById('rutField').style.display = 'block';
    document.getElementById('razonSocialField').style.display = 'block';
    document.getElementById('ciField').style.display = 'none';
  }

  if (hasError) {
      return; // Detener la ejecución si hay errores
  }

  // Crear el objeto de datos a enviar
  let data = {
      name: nombre.value.trim(),
      lastname: apellido.value.trim(),
      type: tipo.value,
      email: email.value.trim(),
      address: direccion.value.trim(),
      city: ciudad.value.trim(),
      state: departamento.value.trim(),
  };

  if (tipo.value === 'individual') {
      data.ci = ci.value.trim();
  } else if (tipo.value === 'company') {
      data.rut = rut.value.trim();
      data.company_name = razonSocial.value.trim();
  }

  // Continuar con el envío del formulario
  document.getElementById('eCommerceCustomerAddForm').submit();
});

function showError(input, message) {
  const errorElement = document.createElement('small');
  errorElement.className = 'text-danger error-message';
  errorElement.innerText = message;
  input.parentElement.appendChild(errorElement);
}

function clearErrors() {
  // Eliminar solo los mensajes de error previos, no los asteriscos
  const errors = document.querySelectorAll('.text-danger.error-message');
  errors.forEach(error => error.remove());
}
