document.addEventListener('DOMContentLoaded', function () {
  // Capturar el formulario del DOM
  const form = document.querySelector('form');
  if (!form) {
      console.error('Formulario no encontrado. Asegúrate de que el formulario existe en el DOM.');
      return;
  }

  // Inicializar los repetidores
  initFeatureRepeater();
  initSizeRepeater();
  initColorRepeater();

  // Validación del formulario al enviarlo
  form.addEventListener('submit', (event) => {
      const features = getFeaturesData();

      // Validar características incompletas
      const incompleteFeatures = features.some(feature => !feature.value.trim());
      if (incompleteFeatures) {
          event.preventDefault();
          displayValidationError();
          return;
      }
      console.log('Características validadas:', features);
  });
});

// Obtener datos de las características
function getFeaturesData() {
  const rows = document.querySelectorAll('.feature-row');
  return Array.from(rows).map(row => {
      const valueInput = row.querySelector('[name$="[value]"]');
      return { value: valueInput.value.trim() };
  });
}

// Mostrar errores de validación en las filas
function displayValidationError() {
  const rows = document.querySelectorAll('.feature-row');
  rows.forEach(row => {
      const valueInput = row.querySelector('[name$="[value]"]');
      const errorMsg = row.querySelector('.error-message');
      if (!valueInput.value.trim()) {
          valueInput.classList.add('is-invalid');
          errorMsg.textContent = 'Este campo es obligatorio.';
      } else {
          valueInput.classList.remove('is-invalid');
          errorMsg.textContent = '';
      }
  });
}

// Inicializar el repetidor de características
function initFeatureRepeater() {
  const featureContainer = document.getElementById('featuresRepeater');
  const addFeatureButton = document.getElementById('addFeature');

  if (addFeatureButton && featureContainer) {
      addFeatureButton.addEventListener('click', function () {
          const newRow = createFeatureRow();
          featureContainer.appendChild(newRow);
          newRow.classList.add('fade-in'); // Agregar animación
      });
  }

  featureContainer?.addEventListener('click', function (event) {
      if (event.target.closest('.remove-row')) {
          const row = event.target.closest('.feature-row');
          row.classList.add('fade-out'); // Animación para eliminar
          setTimeout(() => row.remove(), 300);
      }
  });
}

// Crear una nueva fila para las características
function createFeatureRow() {
  const row = document.createElement('div');
  row.className = 'row feature-row mb-3';
  const timestamp = new Date().getTime();

  row.innerHTML = `
      <div class="col-10">
          <div class="form-group position-relative">
              <input 
                  type="text" 
                  class="form-control mb-2" 
                  name="features[${timestamp}][value]" 
                  placeholder="Ejemplo: Resistente al agua" 
                  aria-label="Característica">
              <div class="error-message text-danger small mt-1"></div>
          </div>
      </div>
      <div class="col-2 text-center">
          <button type="button" class="btn btn-icon btn-outline-danger remove-row" title="Eliminar">
              <i class="bx bx-trash"></i>
          </button>
      </div>
  `;
  return row;
}

function initSizeRepeater() {
  const sizeContainer = document.getElementById('sizesRepeater');
  const addSizeButton = document.getElementById('addSize');

  if (addSizeButton && sizeContainer) {
      addSizeButton.addEventListener('click', function () {
          const newRow = createSizeRow();
          sizeContainer.appendChild(newRow);
          newRow.classList.add('fade-in');
      });
  }

  sizeContainer?.addEventListener('click', function (event) {
      if (event.target.closest('.remove-size')) {
          const row = event.target.closest('.size-row');
          row.classList.add('fade-out');
          setTimeout(() => row.remove(), 300);
      }
  });
}

function createSizeRow() {
  const row = document.createElement('div');
  row.className = 'row size-row mb-3';
  const timestamp = new Date().getTime();

  row.innerHTML = `
      <div class="col-3">
          <input type="text" class="form-control" name="sizes[${timestamp}][size]" placeholder="Nombre (Opcional)">
      </div>
      <div class="col-3">
          <input type="number" class="form-control" name="sizes[${timestamp}][width]" placeholder="Ancho (Opcional)">
      </div>
      <div class="col-3">
          <input type="number" class="form-control" name="sizes[${timestamp}][height]" placeholder="Alto (Opcional)">
      </div>
      <div class="col-2">
          <input type="number" class="form-control" name="sizes[${timestamp}][length]" placeholder="Largo (Opcional)">
      </div>
      <div class="col-1 text-end">
          <button type="button" class="btn btn-icon btn-outline-danger remove-size" title="Eliminar">
              <i class="bx bx-trash"></i>
          </button>
      </div>
  `;
  return row;
}

// Sincronizar el campo de texto con el selector de color (actualización en tiempo real)
function syncHexInput(colorPicker) {
  const hexInput = colorPicker.nextElementSibling;
  hexInput.value = colorPicker.value; // Actualiza el campo de texto con el valor seleccionado en el color picker
}

// Sincronizar el selector de color con el campo de texto
function syncColorPicker(hexInput) {
  const colorPicker = hexInput.previousElementSibling;
  // Si el campo de texto tiene un valor válido, actualiza el color picker
  if (/^#[0-9A-Fa-f]{6}$/.test(hexInput.value)) {
      colorPicker.value = hexInput.value;
  } else {
      // Si el campo está vacío o tiene un valor inválido, no sincroniza
      colorPicker.value = "#FFFFFF"; // Color por defecto
  }
}

// Inicializar el repetidor de colores
function initColorRepeater() {
  const colorContainer = document.getElementById('colorsRepeater');
  const addColorButton = document.getElementById('addColor');

  if (addColorButton && colorContainer) {
      addColorButton.addEventListener('click', function () {
          const newRow = createColorRow();
          colorContainer.appendChild(newRow);
          newRow.classList.add('fade-in');
      });
  }

  colorContainer?.addEventListener('click', function (event) {
      if (event.target.closest('.remove-color')) {
          const row = event.target.closest('.color-row');
          row.classList.add('fade-out');
          setTimeout(() => row.remove(), 300);
      }
  });

  // Asegurar que los eventos estén vinculados a filas existentes
  colorContainer.querySelectorAll('.color-row').forEach(row => {
      const colorPicker = row.querySelector('input[type="color"]');
      const hexInput = row.querySelector('input[type="text"]');

      // Vincular eventos
      colorPicker.addEventListener('input', () => syncHexInput(colorPicker));
      hexInput.addEventListener('input', () => syncColorPicker(hexInput));
  });
}

// Crear una nueva fila para colores
function createColorRow() {
  const row = document.createElement('div');
  row.className = 'row color-row mb-3';
  const timestamp = new Date().getTime();

  row.innerHTML = `
      <div class="col-5">
          <input type="text" class="form-control" name="colors[${timestamp}][name]" placeholder="Nombre del Color">
      </div>
      <div class="col-5 d-flex align-items-center">
          <input type="color" class="form-control-color me-2" name="colors[${timestamp}][color_picker]" value="#FFFFFF" onchange="syncHexInput(this)" oninput="syncHexInput(this)">
          <input type="text" class="form-control" name="colors[${timestamp}][hex_code]" placeholder="#FFFFFF (Opcional)" value="" oninput="syncColorPicker(this)">
      </div>
      <div class="col-2">
          <button type="button" class="btn btn-icon btn-outline-danger remove-color" title="Eliminar">
              <i class="bx bx-trash"></i>
          </button>
      </div>>
  `;

  // Asociar eventos al nuevo color picker y al campo de texto
  const colorPicker = row.querySelector('input[type="color"]');
  const hexInput = row.querySelector('input[type="text"]');

  colorPicker.addEventListener('input', () => syncHexInput(colorPicker));
  hexInput.addEventListener('input', () => syncColorPicker(hexInput));

  return row;
}
