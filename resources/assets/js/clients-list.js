$(function () {
  // Declaración de variables
  var clientListContainer = $('.client-list-container');
  var ajaxUrl = clientListContainer.data('ajax-url');
  const hasSensitiveDataAccess = window.hasSensitiveDataAccess;

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
            const fullName = client.lastname ? `${client.name} ${client.lastname}` : client.name;
            const truncatedName = fullName.length > 20 ? fullName.substring(0, 20) + '...' : fullName;

            // Capitalizar nombres y otros campos
            const capitalizedFullName = capitalizeFirstLetter(fullName);
            const capitalizedCompanyName = client.company_name ? capitalizeFirstLetter(client.company_name) : '';
            const capitalizedTruncatedName = capitalizeFirstLetter(truncatedName);

            // Generar enlaces de contacto
            let phoneNumber = client.phone ? client.phone.replace(/\D/g, '') : '';
            if (phoneNumber.startsWith('0')) {
              phoneNumber = phoneNumber.substring(1);
            }
            const whatsappUrl = phoneNumber ? `https://wa.me/598${phoneNumber}` : '#';
            const telUrl = phoneNumber ? `tel:+598${phoneNumber}` : '#';

            // Renderizar CI solo si tiene permiso
            const ciHtml = hasSensitiveDataAccess && client.ci
              ? `<p class="clients-document mb-2"><strong>CI:</strong> ${client.ci}</p>`
              : '';

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
                          ${client.type === 'company' && client.name && client.lastname ? `
                            <p class="clients-personal-name mb-2">
                              <strong>Representante:</strong> ${capitalizeFirstLetter(client.name)} ${capitalizeFirstLetter(client.lastname)}
                            </p>
                          ` : ''}
                          <p class="clients-document mb-2">${ciHtml}</p>
                          ${client.type === 'company' ? `<p class="clients-company mb-2"><strong>Razón Social:</strong> ${capitalizedCompanyName}</p>` : ''}
                          <p class="clients-email mb-2"><i class="bx bx-envelope me-2"></i> ${client.email}</p>
                          ${client.address && client.address !== '-' ? `<p class="clients-address mb-2"><i class="bx bx-map me-2"></i> ${capitalizeFirstLetter(client.address)}</p>` : ''}
                          ${(client.city && client.city !== '-') || (client.state && client.state !== '-') || (client.department && client.department !== '-') ? `
                            <p class="clients-location mb-2">
                              <i class="bx bx-buildings me-2"></i>
                              ${client.city && client.city !== '-' ? capitalizeFirstLetter(client.city) : ''}${client.city && client.city !== '-' && ((client.state && client.state !== '-') || (client.department && client.department !== '-')) ? ', ' : ''}${client.state && client.state !== '-' ? capitalizeFirstLetter(client.state) : ''}${client.state && client.state !== '-' && (client.department && client.department !== '-') ? ', ' : ''}${client.department && client.department !== '-' ? capitalizeFirstLetter(client.department) : ''}
                            </p>` : ''}
                          ${client.phone && client.phone !== '-' ? `<p class="clients-phone mb-2"><i class="bx bx-phone me-2"></i> ${client.phone}</p>` : ''}
                          ${client.website && client.website !== '-' ? `<p class="clients-website mb-2"><i class="bx bx-globe me-2"></i> <a href="${client.website}" target="_blank">${client.website}</a></p>` : ''}
                        </div>
                        <div class="d-inline-flex justify-content-end mt-auto mb-2 gap-1">
                          <a href="clients/${client.id}" class="btn view-clients p-1"><i class="far fa-eye"></i></a>
                          <a href="${whatsappUrl}" class="btn view-clients p-1" target="_blank"><i class="fa-brands fa-whatsapp"></i></a>
                          <a href="${telUrl}" class="btn view-clients p-1"><i class="bx bx-phone"></i></a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>`;
            clientListContainer.append(card);
          });

          function capitalizeFirstLetter(str) {
            return str.toLowerCase().replace(/(^|\s)[a-záéíóúñ]/g, function (match) {
              return match.toUpperCase();
            });
          }

          $('.clients-card').on('click', function (e) {
            if (!$(e.target).closest('.view-clients').length) {
              e.preventDefault();
              e.stopPropagation();
              const $this = $(this);
              const $icon = $this.find('.clients-card-toggle i');
              const $body = $this.find('.clients-card-body');
              const $wrapper = $this.closest('.clients-card-wrapper');
              const $name = $this.find('.clients-name');
              $icon.toggleClass('bx-chevron-down bx-chevron-up');
              $body.slideToggle();
              if ($body.is(':visible')) {
                $name.text(capitalizeFirstLetter($name.data('full-name').toLowerCase()));
              } else {
                $name.text(capitalizeFirstLetter($name.data('truncated-name').toLowerCase()));
              }
              $('.clients-card-body').not($body).hide();
              $('.clients-card-toggle i').not($icon).removeClass('bx-chevron-up').addClass('bx-chevron-down');
              $('.clients-card-wrapper').not($wrapper).find('.clients-name').each(function () {
                $(this).text(capitalizeFirstLetter($(this).data('truncated-name').toLowerCase()));
              });
            }
          });

          $('.view-clients').on('click', function (e) {
            e.stopPropagation();
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

  fetchClients();
  $('#searchClient').on('input', function () {
    var searchTerm = $(this).val().toLowerCase();
    $('.client-card-wrapper').each(function () {
      var clientInfo = $(this).text().toLowerCase();
      $(this).toggle(clientInfo.includes(searchTerm));
    });
  });
});

// Máscara de teléfono y validación del formulario
(function () {
  const phoneMaskList = document.querySelectorAll('.phone-mask'),
    eCommerceCustomerAddForm = document.getElementById('eCommerceCustomerAddForm');
  if (phoneMaskList) {
    phoneMaskList.forEach(function (phoneMask) {
      new Cleave(phoneMask, { phone: true, phoneRegionCode: 'US' });
    });
  }
  const fv = FormValidation.formValidation(eCommerceCustomerAddForm, {
    fields: {
      customerName: {
        validators: {
          notEmpty: { message: 'Please enter fullname' }
        }
      },
      customerEmail: {
        validators: {
          notEmpty: { message: 'Please enter your email' },
          emailAddress: { message: 'The value is not a valid email address' }
        }
      }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        eleValidClass: '',
        rowSelector: function (field, ele) {
          return '.mb-3';
        }
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
      autoFocus: new FormValidation.plugins.AutoFocus()
    }
  });
})();

$(document).ready(function () {
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
  const nombre = document.getElementById('ecommerce-customer-add-name');
  const apellido = document.getElementById('ecommerce-customer-add-lastname');
  const tipo = document.querySelector('input[name="type"]:checked');
  const email = document.getElementById('ecommerce-customer-add-email');
  const ci = document.getElementById('ci');
  const rut = document.getElementById('rut');
  const razonSocial = document.getElementById('company_name');
  const direccion = document.getElementById('ecommerce-customer-add-address');
  const ciudad = document.getElementById('ecommerce-customer-add-town');
  const departamento = document.getElementById('ecommerce-customer-add-state');
  clearErrors();
  let hasError = false;
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
    if (ci.value.trim() === '') {
      showError(ci, 'Este campo es obligatorio');
      hasError = true;
    }
    document.getElementById('rutField').style.display = 'none';
    document.getElementById('ciField').style.display = 'block';
  } else if (tipo.value === 'company') {
    if (razonSocial.value.trim() === '') {
      showError(razonSocial, 'Este campo es obligatorio');
      hasError = true;
    }
    if (rut.value.trim() === '') {
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
    document.getElementById('rutField').style.display = 'block';
    document.getElementById('razonSocialField').style.display = 'block';
    document.getElementById('ciField').style.display = 'none';
  }
  if (hasError) return;
  let data = {
    name: nombre.value.trim(),
    lastname: apellido.value.trim(),
    type: tipo.value,
    email: email.value.trim(),
    address: direccion.value.trim(),
    city: ciudad.value.trim(),
    state: departamento.value.trim(),
  };
  if (tipo.value === 'individual') data.ci = ci.value.trim();
  else if (tipo.value === 'company') {
    data.rut = rut.value.trim();
    data.company_name = razonSocial.value.trim();
  }
  document.getElementById('eCommerceCustomerAddForm').submit();
  sessionStorage.clear();
});

function showError(input, message) {
  const errorElement = document.createElement('small');
  errorElement.className = 'text-danger error-message';
  errorElement.innerText = message;
  input.parentElement.appendChild(errorElement);
}

function clearErrors() {
  const errors = document.querySelectorAll('.text-danger.error-message');
  errors.forEach(error => error.remove());
}

$(document).ready(function () {

  // Función para abrir el modal de creación de lista de precios desde el modal de crear cliente
  $('#createNewPriceListLink').on('click', function () {
    $('#createPriceListModal').modal('show'); // Muestra el modal de creación de lista de precios
  });

  // Almacenar los datos del formulario en sessionStorage al cambiar
  $('#eCommerceCustomerAddForm input, #eCommerceCustomerAddForm select').on('input change', function () {
    sessionStorage.setItem($(this).attr('id'), $(this).val());
  });

  // Cargar valores guardados en sessionStorage al recargar la página
  $('#eCommerceCustomerAddForm input, #eCommerceCustomerAddForm select').each(function () {
    const savedValue = sessionStorage.getItem($(this).attr('id'));
    if (savedValue) {
      $(this).val(savedValue);
    }
  });

  // Verificar si se debe abrir el modal de creación de cliente automáticamente
  if (sessionStorage.getItem('openClientModalAfterReload') === 'true') {
    $('#offcanvasEcommerceCustomerAdd').modal('show'); // Abre el modal de creación del cliente
    sessionStorage.removeItem('openClientModalAfterReload'); // Limpia la clave para evitar reapertura
  }

  // Evento de guardar cliente
  $('#guardarCliente').on('click', function () {
    sessionStorage.clear(); // Limpiar sessionStorage al guardar cliente
  });


  document.getElementById('guardarCliente').addEventListener('click', function (e) {
    e.preventDefault();

    const form = document.getElementById('eCommerceCustomerAddForm');
    const formData = new FormData(form);
    const clientType = document.querySelector('input[name="type"]:checked').value;

    let requiredFields = {
      name: 'Nombre',
      lastname: 'Apellido',
      email: 'Correo electrónico',
      address: 'Dirección'
    };

    if (clientType === 'individual') {
      requiredFields.ci = 'Cédula de Identidad';
    } else if (clientType === 'company') {
      requiredFields.company_name = 'Razón Social';
      requiredFields.rut = 'RUT';
      requiredFields.city = 'Ciudad';
      requiredFields.state = 'Departamento';
    }

    let missingFields = [];
    for (let field in requiredFields) {
      const value = formData.get(field);
      if (!value || value.trim() === '') {
        missingFields.push(requiredFields[field]);
      }
    }
    
    const offcanvasInstance = bootstrap.Offcanvas.getInstance(document.getElementById('offcanvasEcommerceCustomerAdd'));
    offcanvasInstance.hide();

    if (missingFields.length > 0) {
      Swal.fire({
        icon: 'error',
        title: 'Campos requeridos',
        html: `Por favor complete los siguientes campos:<br><br>${missingFields.join('<br>')}`,
        confirmButtonText: 'Entendido'
      });
      return;
    }

    $.ajax({
      url: `${window.baseUrl}admin/clients`,
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function (response) {
        if (response.success) {
          const modal = bootstrap.Modal.getInstance(document.getElementById('offcanvasEcommerceCustomerAdd'));
          modal.hide();

          Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: 'Cliente creado correctamente',
            showConfirmButton: false,
            timer: 1500
          });

          form.reset();
          fetchClients();
        }
      },
      error: function (xhr) {
        const errors = xhr.responseJSON?.errors || {};
        let errorMessage = 'Ocurrieron los siguientes errores:<br><br>';

        Object.keys(errors).forEach(key => {
          errorMessage += `${errors[key][0]}<br>`;
        });

        Swal.fire({
          icon: 'error',
          title: 'Error',
          html: errorMessage,
          confirmButtonText: 'Entendido'
        });
      }
    });
  });

});
