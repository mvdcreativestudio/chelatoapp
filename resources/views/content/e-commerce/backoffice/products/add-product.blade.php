@extends('layouts/layoutMaster')

@section('title', 'Crear Producto')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/quill/typography.scss',
  'resources/assets/vendor/libs/quill/katex.scss',
  'resources/assets/vendor/libs/quill/editor.scss',
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/dropzone/dropzone.scss',
  'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
  'resources/assets/vendor/libs/tagify/tagify.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/quill/katex.js',
  'resources/assets/vendor/libs/quill/quill.js',
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/dropzone/dropzone.js',
  'resources/assets/vendor/libs/jquery-repeater/jquery-repeater.js',
  'resources/assets/vendor/libs/flatpickr/flatpickr.js',
  'resources/assets/vendor/libs/tagify/tagify.js'
])
@endsection

@section('page-script')
@vite([
  'resources/assets/js/app-ecommerce-product-add.js'
])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">E-Commerce /</span><span> Crear producto</span>
</h4>

<div class="app-ecommerce">

  <!-- Add Product -->
<form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">

    <div class="d-flex flex-column justify-content-center">
      <h4 class="mb-1 mt-3">Crear un nuevo producto</h4>
    </div>
    <div class="d-flex align-content-center flex-wrap gap-3">
      <button type="button" class="btn btn-label-secondary" id="discardButton">Descartar</button>
      <button type="submit" name="action" value="save_draft" class="btn btn-label-primary">Guardar borrador</button>
      <button type="submit" name="action" value="publish" class="btn btn-primary">Publicar</button>
    </div>

  </div>

  <div class="row">

    <!-- First column-->
    <div class="col-12 col-lg-8">
      <!-- Product Information -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="card-tile mb-0">Información del producto</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label" for="ecommerce-product-name">Nombre</label>
            <input type="text" class="form-control" id="ecommerce-product-name" placeholder="Nombre del producto" name="name" aria-label="Nombre del producto">
          </div>
          <div class="row mb-3">
            <div class="col"><label class="form-label" for="ecommerce-product-sku">SKU</label>
              <input type="number" class="form-control" id="ecommerce-product-sku" placeholder="SKU" name="sku" aria-label="SKU"></div>
          </div>
          <!-- Description -->
          <div>
            <label class="form-label">Descripción <span class="text-muted">(Opcional)</span></label>
            <div class="form-control p-0 pt-1">
              <div class="comment-toolbar border-0 border-bottom">
                <div class="d-flex justify-content-start">
                  <span class="ql-formats me-0">1
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
              <div class="comment-editor border-0 pb-4" id="ecommerce-category-description"></div>
              <!-- Campo oculto para enviar la descripción -->
              <input type="hidden" name="description" id="hiddenDescription">
            </div>
          </div>

        </div>
      </div>
      <!-- /Product Information -->
      <!-- Variants -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="card-title mb-0">Tipo de producto y variaciones</h5>
        </div>
        <div class="card-body">
          <div data-repeater-list="group-a">
            <div data-repeater-item>
              <div class="row">
                <div class="mb-3 col-4">
                  <label class="form-label" for="form-repeater-1-1">Tipo de producto</label>
                  <select id="productType" class="select2 form-select" data-placeholder="type" name="type">
                    <option value="simple">Simple</option>
                    <option value="configurable">Variable</option>
                  </select>
                </div>
                <div id="flavorsQuantityContainer" class="mb-3 col-4">
                  <label class="form-label" for="max-flavors">Sabores</label>
                  <input type="text" class="form-control" id="max_flavors" placeholder="Cantidad máxima de sabores" name="max_flavors" aria-label="Cantidad máxima de sabores">
                </div>
              </div>
            </div>
            <div id="flavorsContainer" class="mb-3 col-8">
              <label class="form-label">Sabores disponibles</label>
              <select class="select2 form-select variationOptions" multiple="multiple" name="flavors[]">
                @foreach ($flavors as $flavor)
                  <option value="{{ $flavor->id }}">{{ $flavor->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
      </div>
      <!-- /Variants -->
      <!-- Inventory -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="card-title mb-0">Stock</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <!-- Navigation -->
            <div class="col-12 col-md-4 mx-auto card-separator">
              <div class="d-flex justify-content-between flex-column mb-3 mb-md-0 pe-md-3">
                <ul class="nav nav-align-left nav-pills flex-column">
                  <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#restock">
                      <i class="bx bx-cube me-2"></i>
                      <span class="align-middle">Restock</span>
                    </button>
                  </li>
                  <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#attributes">
                      <i class="bx bx-link me-2"></i>
                      <span class="align-middle">Atributos</span>
                    </button>
                  </li>
                </ul>
              </div>
            </div>
            <!-- /Navigation -->
            <!-- Options -->
            <div class="col-12 col-md-8 pt-4 pt-md-0">
              <div class="tab-content p-0 pe-md-5 ps-md-3">
                <!-- Restock Tab -->
                <div class="tab-pane fade show active" id="restock" role="tabpanel">
                  <h5>Opciones</h5>
                  <label class="form-label" for="ecommerce-product-stock">Agregar a stock</label>
                  <div class="row mb-3 g-3">
                    <div class="col-12 col-sm-9">
                      <input type="number" class="form-control" id="ecommerce-product-stock" placeholder="Cantidad" name="quantity" aria-label="Quantity"></div>
                    <div class="col-12 col-sm-3">
                      <button class="btn btn-primary"><i class='bx bx-check me-2'></i>Confirmar</button>
                    </div>
                  </div>
                  <div>
                    <h6>En stock ahora: <span class="text-muted">54</span></h6>
                    <h6>Último re-stock: <span class="text-muted">24 de Junio de 2023</span></h6>
                    <h6>Stock total en el tiempo: <span class="text-muted">2430</span></h6>
                  </div>
                </div>
                <!-- Attributes Tab -->
                <div class="tab-pane fade" id="attributes" role="tabpanel">
                  <h5 class="mb-4">Atributos</h5>
                  <div>
                    <!-- Fragile Product -->
                    <div class="form-check mb-3">
                      <input class="form-check-input" type="checkbox" value="fragile" id="fragile">
                      <label class="form-check-label" for="fragile">
                        <span class="mb-0 h6">Producto fragil</span>
                      </label>
                    </div>
                    <!-- Biodegradable -->
                    <div class="form-check mb-3">
                      <input class="form-check-input" type="checkbox" value="biodegradable" id="biodegradable">
                      <label class="form-check-label" for="biodegradable">
                        <span class="mb-0 h6">Biodegradable</span>
                      </label>
                    </div>
                    <!-- Frozen Product -->
                    <div class="form-check mb-3">
                      <input class="form-check-input" type="checkbox" value="frozen" checked>
                      <label class="form-check-label w-75 pe-5" for="frozen">
                        <span class="mb-1 h6">Producto congelado</span>
                        <input type="number" class="form-control" placeholder="Max. allowed Temperature" id="frozen">
                      </label>
                    </div>
                    <!-- Exp Date -->
                    <div class="form-check mb-4">
                      <input class="form-check-input" type="checkbox" value="expDate" id="expDate" checked>
                      <label class="form-check-label w-75 pe-5" for="date-input">
                        <span class="mb-1 h6">Fecha de vencimiento</span>
                        <input type="date" class="product-date form-control" id="date-input">
                      </label>
                    </div>
                  </div>
                </div>
                <!-- /Attributes Tab -->

              </div>
            </div>
            <!-- /Options-->
          </div>
        </div>
      </div>
      <!-- /Inventory -->
    </div>
    <!-- /Second column -->

    <!-- Second column -->
    <div class="col-12 col-lg-4">
      <!-- Pricing Card -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="card-title mb-0">Precio</h5>
        </div>
        <div class="card-body">
          <!-- Base Price -->
          <div class="mb-3">
            <label class="form-label" for="ecommerce-product-price">Precio normal</label>
            <input type="number" class="form-control" id="ecommerce-product-price" placeholder="Precio" name="old_price" aria-label="Product price">
          </div>
          <!-- Discounted Price -->
          <div class="mb-3">
            <label class="form-label" for="ecommerce-product-discount-price">Precio rebajado</label>
            <input type="number" class="form-control" id="ecommerce-product-discount-price" placeholder="Precio rebajado" name="price" aria-label="Introduzca el precio rebajado">
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
        </div>
      </div>
      <!-- /Pricing Card -->
      <!-- Organize Card -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="card-title mb-0">Organizar</h5>
        </div>
        <div class="card-body">
          <!-- Vendor -->
          <div class="mb-3 col ecommerce-select2-dropdown">
            <label class="form-label mb-1" for="vendor">
              Local
            </label>
            <select id="vendor" class="select2 form-select" data-placeholder="Seleccionar local" name="store_id">
              <option value="">Seleccionar local</option>
              @foreach ($stores as $store)
                <option value="{{ $store->id }}">{{ $store->name }}</option>
              @endforeach

            </select>
          </div>
          <!-- Category -->
          <div class="mb-3 col ecommerce-select2-dropdown">
            <label class="form-label mb-1 d-flex justify-content-between align-items-center" for="category-org">
              <span>Categoría</span><a href="javascript:void(0);" class="fw-medium">Crear categoría</a>
            </label>
            <select id="category-org" class="select2 form-select" data-placeholder="Seleccione la categoría" name="categories[]" multiple>
              @foreach ($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
              @endforeach
            </select>

          </div>
        </div>
      </div>
      <!-- Media -->
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0 card-title">Imagen</h5>
          <a href="javascript:void(0);" class="fw-medium">Agregar imagen desde URL</a>
        </div>
        <div class="card-body">
          <div class="dz-message needsclick my-5">
            <p class="fs-4 note needsclick my-2">Arrastre la imagen aquí</p>
            <small class="text-muted d-block fs-6 my-2">o</small>
            <span class="note needsclick btn bg-label-primary d-inline" id="btnBrowse">Buscar imagen</span>
          </div>
          <div class="fallback">
            <input name="image" type="file" />
          </div>
        </div>
      </div>
      <!-- /Media -->

      <!-- /Organize Card -->
    </div>
    <!-- /Second column -->
  </div>
</form>
</div>


@endsection
