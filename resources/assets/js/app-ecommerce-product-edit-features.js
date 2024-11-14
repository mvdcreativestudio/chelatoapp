document.addEventListener('DOMContentLoaded', function () {
    initFeatureRepeater();
    initSizeRepeater();
    initColorRepeater();
  });

  // Agregar un listener al formulario para ver los datos que se envían

  const form = document.querySelector('form');
  form.addEventListener('submit', function (e) {
      e.preventDefault(); // Evita el envío automático
      const formData = new FormData(form);
      const entries = Array.from(formData.entries());
  
      console.log('Datos del formulario:', entries);
  
      // Luego puedes enviar el formulario manualmente si la estructura es correcta
      form.submit();
  });
  

  
  // Configuración de repetidor para características
  function initFeatureRepeater() {
    const featureContainer = document.getElementById('featuresRepeater');
    const addFeatureButton = document.getElementById('addFeature');

        if (addFeatureButton && featureContainer) {
            addFeatureButton.addEventListener('click', function () {
                const newRow = createFeatureRow();
                featureContainer.appendChild(newRow);
            });
        }

        featureContainer?.addEventListener('click', function (event) {
            if (event.target.classList.contains('remove-feature')) {
                event.target.closest('.feature-row').remove();
            }
        });
    }

    function createFeatureRow() {
        const row = document.createElement('div');
        row.className = 'row feature-row mb-3';
        row.innerHTML = `
            <div class="col-5">
                <input type="text" class="form-control" name="features[][name]" placeholder="Nombre de la característica">
            </div>
            <div class="col-5">
                <input type="text" class="form-control" name="features[][value]" placeholder="Valor de la característica">
            </div>
            <div class="col-2">
                <button type="button" class="btn btn-danger remove-feature">Eliminar</button>
            </div>
        `;
        return row;
    }
    
    
    



  
  // Configuración de repetidor para tamaños
  function initSizeRepeater() {
    const sizeContainer = document.getElementById('sizesRepeater');
    const addSizeButton = document.getElementById('addSize');
  
    if (addSizeButton && sizeContainer) {
      addSizeButton.addEventListener('click', function () {
        const newRow = createSizeRow();
        sizeContainer.appendChild(newRow);
      });
    }
  
    sizeContainer?.addEventListener('click', function (event) {
      if (event.target.classList.contains('remove-size')) {
        event.target.closest('.size-row').remove();
      }
    });
  }
  
  function createSizeRow() {
    const row = document.createElement('div');
    row.className = 'row size-row mb-3';
    row.innerHTML = `
      <div class="col-3">
        <input type="text" class="form-control" name="sizes[][size]" placeholder="Tamaño">
      </div>
      <div class="col-3">
        <input type="text" class="form-control" name="sizes[][width]" placeholder="Ancho">
      </div>
      <div class="col-3">
        <input type="text" class="form-control" name="sizes[][height]" placeholder="Alto">
      </div>
      <div class="col-3">
        <button type="button" class="btn btn-danger remove-size">Eliminar</button>
      </div>
    `;
    return row;
  }
  
  // Configuración de repetidor para colores
  function initColorRepeater() {
    const colorContainer = document.getElementById('colorsRepeater');
    const addColorButton = document.getElementById('addColor');
  
    if (addColorButton && colorContainer) {
      addColorButton.addEventListener('click', function () {
        const newRow = createColorRow();
        colorContainer.appendChild(newRow);
      });
    }
  
    colorContainer?.addEventListener('click', function (event) {
      if (event.target.classList.contains('remove-color')) {
        event.target.closest('.color-row').remove();
      }
    });
  }
  
  function createColorRow() {
    const row = document.createElement('div');
    row.className = 'row color-row mb-3';
    row.innerHTML = `
      <div class="col-10">
        <input type="text" class="form-control" name="colors[][name]" placeholder="Color">
      </div>
      <div class="col-2">
        <button type="button" class="btn btn-danger remove-color">Eliminar</button>
      </div>
    `;
    return row;
  }
  