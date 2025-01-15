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
<script type="text/javascript">
  window.baseUrl = "{{ url('') }}/";
</script>
@vite([
'resources/assets/js/app-ecommerce-product-add.js',
'resources/assets/js/app-raw-material-add-freemium.js', 'resources/assets/js/app-ecommerce-product-add-features.js', 'resources/assets/js/app-ecommerce-product-add-gallery.js',
])
@endsection

@section('content')
<form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
  @csrf
  <div class="d-flex align-items-center justify-content-between bg-white p-4 mb-3 rounded shadow-lg sticky-top border-bottom border-light">

    <!-- Título del formulario alineado a la izquierda -->
    <div class="d-flex flex-column justify-content-center">
      <h4 class="mb-0 page-title">
        <i class="bx bx-box me-2"></i> Crear nuevo producto
      </h4>
    </div>

    <!-- Botones alineados a la derecha, ahora responsive -->
    <div class="text-end d-flex gap-2">
      <button type="button" class="btn btn-sm btn-outline-danger" id="discardButton">Descartar</button>
      {{-- <button type="submit" name="action" value="save_draft" class="btn btn-label-primary">Guardar borrador</button> --}}
      <button type="submit" name="action" value="publish" class="btn btn-sm btn-success">Guardar Producto</button>
    </div>

  </div>

  @if ($errors->any())
  <div class="alert alert-danger">
    <ul>
      @foreach ($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
  @endif

  <div class="app-ecommerce" data-raw-materials='@json($rawMaterials)' data-flavors='@json($flavors)'>

    <!-- Add Product -->

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
              <input type="text" class="form-control" id="ecommerce-product-name" placeholder="Nombre del producto" name="name" aria-label="Nombre del producto" required>
            </div>
            <div class="row mb-3">
              <div class="col"><label class="form-label" for="ecommerce-product-sku">SKU</label>
                <input type="text" class="form-control" id="ecommerce-product-sku" placeholder="SKU" name="sku" aria-label="SKU">
              </div>
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
        <div class="card mb-4" style="display: none;">
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
                    <label class="form-label" for="max-flavors">Variaciones</label>
                    <input type="text" class="form-control" id="max_flavors" placeholder="Cantidad máxima de variaciones" name="max_flavors" aria-label="Cantidad máxima de variaciones">
                  </div>
                </div>
              </div>
              <div id="flavorsContainer" class="mb-3 col-8">
                <div class="d-flex justify-content-between">
                  <label class="form-label">Variaciones disponibles</label>
                  <label class="form-label" id="selectAllFlavorsButton">Seleccionar todos</label>
                </div>
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
        <!-- Recipe -->
        <div class="card mb-4" id="recipeCard" style="display: none;">
          <div class="card-header">
            <h5 class="card-title mb-0">Receta</h5>
          </div>
          <div class="card-body">
          <a href="{{ url('admin/raw-material-freemium') }}" class="btn btn-outline-primary mb-4">
          <i class="fas fa-plus-circle me-1"></i>
          Crear Materia Prima
        </a>
            <div data-repeater-list="recipes">
              <!-- Elimina los elementos predeterminados aquí -->
            </div>
            <button type="button" class="btn btn-primary" id="addRawMaterial">Agregar Materia Prima</button>
            <!-- <button type="button" class="btn btn-secondary" id="addUsedFlavor">Agregar Sabor Usado</button> -->
        </div>
      </div> 
      <!-- /Recipe -->

      <!-- Card con Tabs -->
      <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Detalles del Producto</h5>
        </div>
        <div class="card-body">
            <!-- Navegación de Tabs -->
            <ul class="nav nav-tabs" id="productDetailsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="features-tab" data-bs-toggle="tab" data-bs-target="#featuresTabContent"
                        type="button" role="tab" aria-controls="featuresTabContent" aria-selected="true">
                        Características
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="sizes-tab" data-bs-toggle="tab" data-bs-target="#sizesTabContent"
                        type="button" role="tab" aria-controls="sizesTabContent" aria-selected="false">
                        Dimensiones
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="colors-tab" data-bs-toggle="tab" data-bs-target="#colorsTabContent"
                        type="button" role="tab" aria-controls="colorsTabContent" aria-selected="false">
                        Colores
                    </button>
                </li>
            </ul>

            <!-- Contenido de Tabs -->
            <div class="tab-content" id="productDetailsTabContent">
                <!-- Características -->
                <div class="tab-pane fade show active" id="featuresTabContent" role="tabpanel" aria-labelledby="features-tab">
                    <div id="featuresRepeater">
                        <!-- No contiene datos iniciales -->
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" id="addFeature">
                        <i class="bx bx-plus"></i> Agregar Característica
                    </button>
                </div>

                <!-- Dimensiones -->
                <div class="tab-pane fade" id="sizesTabContent" role="tabpanel" aria-labelledby="sizes-tab">
                    <small>Todas las dimensiones deben ser introducidas en CM</small>
                    <div id="sizesRepeater">
                        <!-- No contiene datos iniciales -->
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" id="addSize">
                        <i class="bx bx-plus"></i> Agregar Dimensiones
                    </button>
                </div>

                <!-- Colores -->
                <div class="tab-pane fade" id="colorsTabContent" role="tabpanel" aria-labelledby="colors-tab">
                    <div id="colorsRepeater">
                        <!-- No contiene datos iniciales -->
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" id="addColor">
                        <i class="bx bx-plus"></i> Agregar Color
                    </button>
                </div>
            </div>
        </div>
      </div>

    </div>
    <!-- /Second column -->

    <!-- Second column -->
    <div class="col-12 col-lg-4">
      <!-- Pricing Card -->
      <div class="card mb-4">
        <div class="card-body">
          <!-- Campo oculto para estado desactivado -->
          <input type="hidden" name="status" value="2">
          <!-- Switch estado -->
          <div class="d-flex justify-content-between align-items-center mb-3 pb-1">
            <span class="mb-0 h6">Estado</span>
            <div class="w-25 d-flex justify-content-end">
              <label class="switch switch-primary switch-sm me-4 pe-2">
                <input type="checkbox" class="switch-input" value="1" id="statusSwitch" checked name="status">
                <span class="switch-toggle-slider"></span>
              </label>
            </div>
          </div>

          <!-- Campo oculto para catálogo desactivado -->
          <input type="hidden" name="show_in_catalogue" value="0">
          <!-- Switch catálogo -->
          <div class="d-flex justify-content-between align-items-center mb-3 pb-1">
            <span class="mb-0 h6">Mostrar en el catálogo</span>
            <div class="w-25 d-flex justify-content-end">
              <label class="switch switch-primary switch-sm me-4 pe-2">
                <input type="checkbox" class="switch-input" value="1" id="catalogueSwitch" checked name="show_in_catalogue">
                <span class="switch-toggle-slider"></span>
              </label>
            </div>
          </div>

            <!-- Base Price -->
            <div class="mb-3">
              <label class="form-label" for="ecommerce-product-price">Precio normal - <small>IVA INCLUÍDO</small></label></label>
              <input type="number" min="0" step=".01" class="form-control" id="ecommerce-product-price" placeholder="Precio" name="old_price" aria-label="Product price" required>
            </div>
            <!-- Discounted Price -->
            <div class="mb-3">
              <label class="form-label" for="ecommerce-product-discount-price">Precio oferta - <small>IVA INCLUÍDO</small></label></label>
              <input type="number" step=".01" min="0" class="form-control" id="ecommerce-product-discount-price" placeholder="Precio rebajado" name="price" aria-label="Introduzca el precio rebajado">
            </div>
            <!-- build_price -->
            <div class="mb-3">
              <label class="form-label" for="build_price">Costo</label>
              <input type="number" step=".01" min="0" class="form-control" id="build_price" placeholder="Introduzca el costo del producto" name="build_price" aria-label="Introduzca costo">
              <div>

              </div>
            </div>
            <!-- /Pricing Card -->
            <!-- Organize Card -->
            <div class="card mb-4">
              <div class="card-header">
                <h5 class="card-title mb-0">Organizar</h5>
              </div>
              <div class="card-body">

                <!-- Stock Inicial -->
                <div class="mb-3" id="stockContainer">
                  <div class="stock-inicial mb-3">
                    <label class="form-label" for="stock">Stock Inicial</label>
                    <input type="number" class="form-control" id="stock" placeholder="Stock Inicial" name="stock" aria-label="Introduzca el stock inicial">
                  </div>
                  <div class="stock-margen">
                    <label class="form-label" for="safety_margin">Margen de Seguridad</label>
                    <input type="number" class="form-control" id="safety_margin" placeholder="¿Cuántas unidades es el minimo por el que desea ser notificado?" name="safety_margin" aria-label="Introduzca el margen de seguridad">
                  </div>
                </div>

                <!-- Category -->
                <div class="mb-3 col ecommerce-select2-dropdown">
                  <label class="form-label mb-1 d-flex justify-content-between align-items-center" for="category-org">
                    <span>Categoría</span><a href="{{ route('product-categories.index') }}" class="fw-medium">Crear categoría</a>
                  </label>
                  <select id="category-org" class="select2 form-select" data-placeholder="Seleccione la categoría" name="categories[]" multiple>
                    @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                  </select>
                </div>

                <!-- Vendor -->
                <div class="mb-3 col ecommerce-select2-dropdown">
                  <label class="form-label mb-1" for="vendor">
                    Empresa
                  </label>
                  <select id="vendor" class="select2 form-select" data-placeholder="Seleccionar local" name="store_id" required {{ count($stores) === 1 ? 'disabled' : '' }}>
                    @if(auth()->user()->hasPermissionTo('access_global_products'))
                    @foreach ($stores as $index => $store)
                    <option value="{{ $store->id }}" {{ $index === 0 ? 'selected' : '' }}>{{ $store->name }}</option>
                    @endforeach
                    @else
                    <option value="{{ auth()->user()->store_id }}" selected>{{ auth()->user()->store->name }}</option>
                    @endif
                  </select>

                  @if(count($stores) === 1)
                  <!-- Campo oculto para enviar el valor si solo hay un local disponible -->
                  <input type="hidden" name="store_id" value="{{ $stores->first()->id }}">
                  @endif
                </div>

                <!-- Código de barras -->
                <div class="mb-3" id="barCodeContainer">
                  <label class="form-label" for="bar_code">Código de barras</label>
                  <input type="text" class="form-control" id="bar_code" placeholder="Código de barras" name="bar_code" aria-label="Introduzca el código de barras">
                </div>


        </div>
      </div>
      <!-- Media -->
      <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Imágenes del Producto</h5>
        </div>
        <div class="card-body">
            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs" id="imageTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="main-image-tab" data-bs-toggle="tab" data-bs-target="#mainImageTabContent"
                        type="button" role="tab" aria-controls="mainImageTabContent" aria-selected="true">
                        Imagen Principal
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="gallery-tab" data-bs-toggle="tab" data-bs-target="#galleryTabContent"
                        type="button" role="tab" aria-controls="galleryTabContent" aria-selected="false">
                        Galería
                    </button>
                </li>
            </ul>
    
            <!-- Tabs Content -->
            <div class="tab-content" id="imageTabsContent">
                <!-- Main Image Tab -->
                <div class="tab-pane fade show active" id="mainImageTabContent" role="tabpanel" aria-labelledby="main-image-tab">
                    <div class="card-body text-center">
                        <!-- Dropzone -->
                        <div class="dropzone dz-clickable" id="dropzone">
                          <div class="dz-message needsclick">
                            <p class="fs-4 note needsclick my-2">Arrastre la imagen aquí</p>
                            <small class="text-muted d-block fs-6 my-2">o</small>
                            <span class="note needsclick btn bg-label-primary d-inline" id="btnBrowse">Buscar imagen</span>
                          </div>
                        </div>
                        <input type="file" name="image" id="productImage" class="d-none">>
                    </div>
                </div>
    
                <!-- Gallery Tab -->
                <div class="tab-pane fade" id="galleryTabContent" role="tabpanel" aria-labelledby="gallery-tab">
                    <div class="mb-3">
                        <label for="galleryImages" class="form-label fw-bold">Subir Imágenes</label>
                        <input type="file" class="form-control" name="gallery_images[]" id="galleryImages" multiple>
                    </div>
                    <div id="galleryPreview" class="row g-2 mt-3">
                        <!-- Aquí se mostrarán las imágenes cargadas -->
                    </div>
                </div>
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