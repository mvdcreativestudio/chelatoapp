/**
 * App eCommerce Add Product Script
 */
'use strict';

//Javascript to handle the e-commerce product add page


(function () {
  document.addEventListener('DOMContentLoaded', function () {
    const commentEditorElement = document.querySelector('.comment-editor');

    if (commentEditorElement) {
      const quill = new Quill(commentEditorElement, {
        modules: {
          toolbar: '.comment-toolbar'
        },
        placeholder: 'Descripción del producto',
        theme: 'snow'
      });

      // Encuentra el formulario que contiene tu editor Quill
      const form = commentEditorElement.closest('form');

      // Asegúrate de que el formulario y el campo oculto existen
      if (form) {
        form.addEventListener('submit', function() {
          // Encuentra el input oculto por su ID
          const hiddenInput = document.getElementById('hiddenDescription');

          // Actualiza el valor del campo oculto con el contenido HTML de Quill
          if (hiddenInput) {
            hiddenInput.value = quill.root.innerHTML;
          }
        });
      }
    }
  });



  const previewTemplate = `<div class="dz-preview dz-file-preview">
<div class="dz-details">
  <div class="dz-thumbnail">
    <img data-dz-thumbnail>
    <span class="dz-nopreview">No preview</span>
    <div class="dz-success-mark"></div>
    <div class="dz-error-mark"></div>
    <div class="dz-error-message"><span data-dz-errormessage></span></div>
    <div class="progress">
      <div class="progress-bar progress-bar-primary" role="progressbar" aria-valuemin="0" aria-valuemax="100" data-dz-uploadprogress></div>
    </div>
  </div>
  <div class="dz-filename" data-dz-name></div>
  <div class="dz-size" data-dz-size></div>
</div>
</div>`;

  // ? Start your code from here

  // Basic Dropzone

  const dropzoneBasic = document.querySelector('#dropzone-basic');
  if (dropzoneBasic) {
    const myDropzone = new Dropzone(dropzoneBasic, {
      previewTemplate: previewTemplate,
      parallelUploads: 1,
      maxFilesize: 5,
      acceptedFiles: '.jpg,.jpeg,.png,.gif',
      addRemoveLinks: true,
      maxFiles: 1
    });
  }

  // Basic Tags

  const tagifyBasicEl = document.querySelector('#ecommerce-product-tags');
  const TagifyBasic = new Tagify(tagifyBasicEl);

  // Flatpickr

  // Datepicker
  const date = new Date();

  const productDate = document.querySelector('.product-date');

  if (productDate) {
    productDate.flatpickr({
      monthSelectorType: 'static',
      defaultDate: date
    });
  }
})();

//Jquery to handle the e-commerce product add page

$(function () {
  // Select2
  var select2 = $('.select2');
  if (select2.length) {
    select2.each(function () {
      var $this = $(this);
      $this.wrap('<div class="position-relative"></div>').select2({
        dropdownParent: $this.parent(),
        placeholder: $this.data('placeholder') // for dynamic placeholder
      });
    });
  }

});


// Switch de estado del producto
document.addEventListener('DOMContentLoaded', function () {
  var statusSwitch = document.getElementById('statusSwitch');

  // Asegura que el valor inicial sea "1" cuando el switch está activado por defecto
  statusSwitch.value = statusSwitch.checked ? '1' : '2';

  statusSwitch.addEventListener('change', function() {
    this.value = this.checked ? '1' : '2';
  });
});


$(document).ready(function() {
  $('#category-org').select2({
      placeholder: "Seleccione la(s) categoría(s)",
      allowClear: true
  });
});


// Discard Button
document.addEventListener('DOMContentLoaded', function () {
  const discardButton = document.getElementById('discardButton');

  discardButton.addEventListener('click', function () {
      // Comprobar si algún campo del formulario ha sido llenado
      let isFormFilled = Array.from(document.querySelector('form').elements).some(input => {
          if (input.type !== "submit" && input.type !== "button" && input.value !== "") {
              return true;
          }
      });

      if (isFormFilled) {
          Swal.fire({
              title: '¿Estás seguro?',
              text: "Si continúas, perderás los datos no guardados.",
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'Sí, salir',
              cancelButtonText: 'Cancelar'
          }).then((result) => {
              if (result.isConfirmed) {
                  // Si el usuario confirma, retrocede en el historial
                  history.back();
              }
          });
      } else {
          // Si el formulario no ha sido llenado, simplemente retrocede
          history.back();
      }
  });

  $(document).ready(function() {
    // Función para manejar la visibilidad del contenedor de sabores
    function toggleFlavorsContainer() {
      var productType = $('#productType').val();
      if (productType === 'configurable') {
        $('#flavorsContainer').show();
        $('#flavorsQuantityContainer').show();
      } else {
        $('#flavorsContainer').hide();
        $('#flavorsQuantityContainer').hide();
      }
    }

    // Llamada inicial para establecer la visibilidad correcta al cargar la página
    toggleFlavorsContainer();

    // Controlador de eventos para cuando cambia la selección del tipo de producto
    $('#productType').change(function() {
      toggleFlavorsContainer();
    });
  });

});

