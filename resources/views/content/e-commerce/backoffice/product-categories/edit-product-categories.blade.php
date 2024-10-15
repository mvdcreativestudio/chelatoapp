@section('content')

<!-- Modal de edición -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="editCategoryModal" aria-labelledby="offcanvasEcommerceCategoryEditLabel">
  <div class="offcanvas-header py-4">
    <h5 id="offcanvasEcommerceCategoryEditLabel" class="offcanvas-title">Editar categoría</h5>
    <button type="button" class="btn-close bg-label-secondary text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body border-top">
    <form method="POST" enctype="multipart/form-data" class="pt-0" id="eCommerceCategoryListForm">
      @csrf
      <!-- Title -->
      <div class="mb-3">
        <label class="form-label" for="ecommerce-category-title">Nombre</label>
        <input type="text" class="form-control" id="ecommerce-category-title" placeholder="Ingrese el nombre de la categoría" name="name" aria-label="category title">
      </div>
      <!-- Slug -->
      <div class="mb-3">
        <label class="form-label" for="ecommerce-category-slug">Slug</label>
        <input type="text" id="ecommerce-category-slug" class="form-control" placeholder="Ingrese el slug" aria-label="slug" name="slug">
      </div>
      <!-- Image -->
      <div class="mb-3">
        <label class="form-label" for="ecommerce-category-image">Imagen</label>
        <input class="form-control" name="image" type="file" id="ecommerce-category-image">
      </div>
      <!-- Parent category -->
      <div class="mb-3 ecommerce-select2-dropdown">
        <label class="form-label" for="ecommerce-category-parent-category">Categoría padre</label>
        <select id="ecommerce-category-parent-category" class="select2 form-select" data-placeholder="Seleccione la categoría padre">
          <option value="">Seleccione la categoría padre</option>
          <option value="Household">Household</option>
          <option value="Management">Management</option>
          <option value="Electronics">Electronics</option>
          <option value="Office">Office</option>
          <option value="Automotive">Automotive</option>
        </select>
      </div>
      <!-- Description -->
      <div class="mb-3">
        <label class="form-label">Descripción</label>
        <div class="form-control p-0 pt-1">
          <div class="comment-editor border-0" id="ecommerce-category-description">
          </div>
          <div class="comment-toolbar border-0 rounded">
            <div class="d-flex justify-content-end">
              <span class="ql-formats me-0">
                <button class="ql-bold"></button>
                <button class="ql-italic"></button>
                <button class="ql-underline"></button>
                <button class="ql-list" value="ordered"></button>
                <button class="ql-list" value="bullet"></button>
                <button class="ql-link"></button>
                <button class="ql-image"></button>
              </span>
            </div>
          </div>
        </div>
      </div>
      <!-- Campo oculto para estado desactivado -->
      <input type="hidden" name="status" value="2">
      <!-- Instock switch -->
      <div class="d-flex justify-content-between align-items-center border-top pt-3">
        <span class="mb-0 h6">Estado</span>
        <div class="w-25 d-flex justify-content-end">
          <label class="switch switch-primary switch-sm me-4 pe-2">
            <input type="checkbox" class="switch-input" value="1" id="statusSwitch" checked name="status">
            <span class="switch-toggle-slider"></span>
          </label>
        </div>
      </div>
      <!-- Submit and reset -->
      <div class="mb-3">
        <button type="button" class="btn btn-primary me-sm-3 me-1 data-submit" id="updateCategoryBtn">Actualizar categoría</button>
        <button type="reset" class="btn bg-label-danger" data-bs-dismiss="offcanvas">Cancelar</button>
      </div>
    </form>
  </div>
</div>

@include('content.e-commerce.backoffice.product-categories.product-categories')
@endsection
