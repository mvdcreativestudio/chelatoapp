document.addEventListener('DOMContentLoaded', function () {
  const elaborationsContainer = document.getElementById('elaborations-container');
  const addElaborationButton = document.getElementById('add-elaboration');
  const instructionText = document.getElementById('instruction-text');

  function updateRecipeDetails(container, recipes) {
    const recipeDetails = container.querySelector('.recipe-details');
    recipeDetails.innerHTML = '';
    recipes.forEach(recipe => {
      const recipeItem = document.createElement('div');
      recipeItem.classList.add('mb-2');
      const name = recipe.name ? recipe.name : recipe.raw_material ? recipe.raw_material.name : recipe.used_flavor.name;
      const unit = recipe.unit ? recipe.unit : recipe.raw_material ? recipe.raw_material.unit_of_measure : 'baldes';
      recipeItem.innerHTML = `
        <span>${name}: ${recipe.quantity} ${unit}</span>
      `;
      recipeDetails.appendChild(recipeItem);
    });
  }

  function calculateTotalRecipes(recipes, quantity) {
    return recipes.map(recipe => ({
      name: recipe.raw_material ? recipe.raw_material.name : recipe.used_flavor.name,
      unit: recipe.raw_material ? recipe.raw_material.unit_of_measure : 'baldes',
      quantity: recipe.quantity * quantity
    }));
  }

  function updateDisabledOptions() {
    const selectedValues = Array.from(document.querySelectorAll('.product-or-flavor')).map(select => select.value);
    document.querySelectorAll('.product-or-flavor option').forEach(option => {
      option.disabled = selectedValues.includes(option.value) && option.value !== '';
    });
  }

  function handleProductOrFlavorChange(event) {
    const selectElement = event.target;
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const recipes = JSON.parse(selectedOption.getAttribute('data-recipes'));
    const elaborationItem = selectElement.closest('.elaboration-item');
    const recipeContainer = elaborationItem.querySelector('.recipe-container');
    const quantityInput = elaborationItem.querySelector('.quantity');
    const quantityLabel = elaborationItem.querySelector('.quantity-label');
    const hiddenField = elaborationItem.querySelector('.hidden-product-or-flavor');

    hiddenField.value = selectElement.value;

    if (recipes.length > 0) {
      recipeContainer.style.display = 'block';
      quantityInput.style.display = 'block';
      quantityLabel.style.display = 'block';
      updateRecipeDetails(recipeContainer, recipes);
    } else {
      recipeContainer.style.display = 'none';
      quantityInput.style.display = 'none';
      quantityLabel.style.display = 'none';
    }

    quantityInput.addEventListener('input', function () {
      const quantity = parseFloat(quantityInput.value) || 1;
      const totalRecipes = calculateTotalRecipes(recipes, quantity);
      updateRecipeDetails(recipeContainer, totalRecipes);
    });

    updateDisabledOptions();
  }

  function synchronizeHiddenFields() {
    document.querySelectorAll('.product-or-flavor').forEach((select, index) => {
      const hiddenField = select.closest('.elaboration-item').querySelector('.hidden-product-or-flavor');
      hiddenField.value = select.value;
    });
  }

  document.addEventListener('change', function (event) {
    if (event.target.matches('.product-or-flavor')) {
      handleProductOrFlavorChange(event);
    }
  });

  addElaborationButton.addEventListener('click', function () {
    if (instructionText) {
      instructionText.remove();
    }

    const index = document.querySelectorAll('.elaboration-item').length;
    const elaborationItem = document.createElement('div');
    elaborationItem.classList.add('elaboration-item', 'mb-3');
    elaborationItem.style.paddingBottom = '20px';
    elaborationItem.style.borderBottom = '2px solid #e0e0e0';
    elaborationItem.innerHTML = `
      <label class="form-label" for="product_or_flavor_${index}">Producto o Sabor</label>
      <select class="form-select product-or-flavor" id="product_or_flavor_${index}" required>
        <option value="">Seleccione un producto o sabor</option>
        <optgroup label="Productos">
          ${products.map(product => `<option value="product_${product.id}" data-recipes='${JSON.stringify(product.recipes)}'>${product.name}</option>`).join('')}
        </optgroup>
        <optgroup label="Variaciones">
          ${flavors.map(flavor => `<option value="flavor_${flavor.id}" data-recipes='${JSON.stringify(flavor.recipes)}'>${flavor.name}</option>`).join('')}
        </optgroup>
      </select>
      <input type="hidden" class="hidden-product-or-flavor" name="elaborations[${index}][product_or_flavor]" value="">
      <div class="recipe-container mt-4 card" style="background-color: #F5F5F9; display: none; padding: 10px; margin: 15px 0;">
        <h6 class="mb-2 card-header">Receta</h6>
        <div class="recipe-details card-body"></div>
      </div>
      <label class="form-label mt-3 quantity-label" style="display: none;" for="quantity_${index}">Cantidad a Elaborar</label>
      <input type="number" class="form-control quantity" id="quantity_${index}" name="elaborations[${index}][quantity]" placeholder="Cantidad a elaborar" min="1" style="display: none;" required>
    `;
    elaborationsContainer.appendChild(elaborationItem);

    const newSelect = elaborationItem.querySelector('.product-or-flavor');
    newSelect.addEventListener('change', handleProductOrFlavorChange);
    updateDisabledOptions();
    synchronizeHiddenFields();
  });

  document.querySelectorAll('.product-or-flavor').forEach(select => {
    select.addEventListener('change', handleProductOrFlavorChange);
  });

  updateDisabledOptions();

  form.addEventListener('submit', function (event) {
    synchronizeHiddenFields();
  });
});
