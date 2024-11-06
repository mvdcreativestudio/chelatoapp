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
      <!-- Image -->
      <div class="mb-3">
        <label class="form-label" for="ecommerce-category-image">Imagen</label>
        <input class="form-control" name="image" type="file" id="ecommerce-category-image">
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
