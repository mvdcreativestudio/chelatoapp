@extends('layouts/layoutMaster')

@section('title', 'Editar Producto')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/quill/typography.scss', 'resources/assets/vendor/libs/quill/katex.scss', 'resources/assets/vendor/libs/quill/editor.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/dropzone/dropzone.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/tagify/tagify.scss', 'resources/assets/vendor/libs/toastr/toastr.scss',
])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/quill/katex.js', 'resources/assets/vendor/libs/quill/quill.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/dropzone/dropzone.js', 'resources/assets/vendor/libs/jquery-repeater/jquery-repeater.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/tagify/tagify.js', 'resources/assets/vendor/libs/toastr/toastr.js',
])
@endsection

@section('page-script')
@vite(['resources/assets/js/app-ecommerce-product-edit.js', 'resources/assets/js/app-ecommerce-product-edit-features.js', 'resources/assets/js/app-ecommerce-product-edit-gallery.js'])
@endsection

@section('content')
<div>
    <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="d-flex flex-wrap align-items-center justify-content-between bg-light p-4 mb-3 rounded shadow sticky-top">

            <!-- Título del formulario -->
            <div class="d-flex flex-column justify-content-center">
              <h5 class="mb-0">
                  <i class="bx bx-edit-alt me-2"></i> Editar Producto
              </h5>
            </div>

            <!-- Botones de acciones -->
            <div class="d-flex justify-content-end gap-3">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="history.back();">
                    <i class="bx bx-x me-1"></i> Cancelar
                </button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bx bx-save me-1"></i> Guardar Cambios
                </button>
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

        <div class="app-ecommerce" data-raw-materials='@json($rawMaterials)'
            data-recipes='@json($product->recipes)' data-flavors='@json($flavors)'>

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
                                <input type="text" class="form-control" id="ecommerce-product-name"
                                    placeholder="Nombre del producto" name="name" value="{{ $product->name }}"
                                    aria-label="Nombre del producto">
                            </div>
                            <div class="row mb-3">
                                <div class="col"><label class="form-label" for="ecommerce-product-sku">SKU</label>
                                    <input type="number" class="form-control" id="ecommerce-product-sku" placeholder="SKU"
                                        name="sku" value="{{ $product->sku }}" aria-label="SKU">
                                </div>
                            </div>
                            <!-- Description -->
                            <div>
                                <label class="form-label">Descripción <span class="text-muted">(Opcional)</span></label>
                                <div class="form-control p-0 pt-1">
                                    <div class="comment-toolbar border-0 border-bottom">
                                        <div class="d-flex justify-content-start">
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
                                    <div class="comment-editor border-0 pb-4" id="ecommerce-category-description"></div>
                                    <!-- Campo oculto para enviar la descripción -->
                                    <input type="hidden" name="description" id="hiddenDescription"
                                        value="{{ old('description', $product->description) }}">
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
                                            <select id="productType" class="select2 form-select" name="type">
                                                <option value="simple" @selected($product->type == 'simple')>Simple</option>
                                                {{-- <option value="configurable" @selected($product->type == 'configurable')>Variable</option> --}}
                                            </select>
                                        </div>
                                        <div id="flavorsQuantityContainer" class="mb-3 col-4">
                                            <label class="form-label" for="max-flavors">Variaciones</label>
                                            <input type="text" class="form-control" id="max_flavors"
                                                value="{{ $product->max_flavors }}"
                                                placeholder="Cantidad máxima de variaciones" name="max_flavors"
                                                aria-label="Cantidad máxima de variaciones">
                                        </div>
                                    </div>
                                </div>
                                <div id="flavorsContainer" class="mb-3 col-8">
                                    <label class="form-label">Variaciones disponibles</label>
                                    <select class="select2 form-select variationOptions" multiple="multiple"
                                        name="flavors[]"
                                        data-selected="{{ json_encode($product->flavors->pluck('id')->toArray()) }}">
                                        @foreach ($flavors as $flavor)
                                        <option value="{{ $flavor->id }}"
                                            {{ in_array($flavor->id, $product->flavors->pluck('id')->toArray()) ? 'selected' : '' }}>
                                            {{ $flavor->name }}
                                        </option>
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
                            <div data-repeater-list="recipes">
                            </div>
                            <button type="button" class="btn btn-primary" id="addRawMaterial">Agregar Materia Prima</button>
                            {{-- <button type="button" class="btn btn-secondary" id="addUsedFlavor">Agregar Sabor Usado</button> --}}
                        </div>
                    </div>

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
                                        @foreach ($product->features as $index => $feature)
                                        <div class="row feature-row mb-3">
                                            <div class="col-10">
                                                <input type="text" class="form-control" name="features[{{ $index }}][value]"
                                                    placeholder="Ejemplo: Resistente al agua" value="{{ $feature->value }}">
                                                <div class="error-message text-danger small mt-1"></div>
                                            </div>
                                            <div class="col-2 text-center">
                                                <button type="button" class="btn btn-icon btn-outline-danger remove-row" title="Eliminar">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    <button type="button" class="btn btn-primary btn-sm" id="addFeature">
                                        <i class="bx bx-plus"></i> Agregar Característica
                                    </button>
                                </div>

                                <!-- Dimensiones -->
                                <div class="tab-pane fade" id="sizesTabContent" role="tabpanel" aria-labelledby="sizes-tab">
                                    <small>Todas las dimensiones deben ser introducidas en CM</small>
                                    <div id="sizesRepeater">
                                        @foreach ($product->sizes as $index => $size)
                                        <div class="row size-row mb-3">
                                            <div class="col-3">
                                                <input type="text" class="form-control" name="sizes[{{ $index }}][size]"
                                                    placeholder="Nombre (Opcional)" value="{{ $size->size }}">
                                            </div>
                                            <div class="col-3">
                                                <input type="number" class="form-control" name="sizes[{{ $index }}][width]"
                                                    placeholder="Ancho (Opcional)" value="{{ $size->width }}">
                                            </div>
                                            <div class="col-3">
                                                <input type="number" class="form-control" name="sizes[{{ $index }}][height]"
                                                    placeholder="Alto (Opcional)" value="{{ $size->height }}">
                                            </div>
                                            <div class="col-2">
                                                <input type="number" class="form-control" name="sizes[{{ $index }}][length]"
                                                    placeholder="Largo (Opcional)" value="{{ $size->length }}">
                                            </div>
                                            <div class="col-1 text-end">
                                                <button type="button" class="btn btn-icon btn-outline-danger remove-size" title="Eliminar">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    <button type="button" class="btn btn-primary btn-sm" id="addSize">
                                        <i class="bx bx-plus"></i> Agregar Dimensiones
                                    </button>
                                </div>

                                <!-- Colores -->
                                <div class="tab-pane fade" id="colorsTabContent" role="tabpanel" aria-labelledby="colors-tab">
                                    <div id="colorsRepeater">
                                        @foreach ($product->colors as $index => $color)
                                        <div class="row color-row mb-3">
                                            <div class="col-5">
                                                <input type="text" class="form-control" name="colors[{{ $index }}][name]"
                                                    placeholder="Nombre del Color" value="{{ $color->color_name }}">
                                            </div>
                                            <div class="col-5 d-flex align-items-center">
                                                <input type="color" class="form-control-color me-2" name="colors[{{ $index }}][color_picker]"
                                                    value="{{ $color->hex_code ?? '#FFFFFF' }}" onchange="syncHexInput(this)"
                                                    oninput="syncHexInput(this)">
                                                <input type="text" class="form-control" name="colors[{{ $index }}][hex_code]"
                                                    placeholder="#FFFFFF" value="{{ $color->hex_code ?? '' }}"
                                                    oninput="syncColorPicker(this)">
                                            </div>
                                            <div class="col-2">
                                                <button type="button" class="btn btn-icon btn-outline-danger remove-color" title="Eliminar">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </div>

                                        </div>
                                        @endforeach
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
                        <div class="card-header">
                            <h5 class="card-title mb-0">Precio</h5>
                        </div>
                        <div class="card-body">
                            <!-- Currency -->
                            <div class="mb-3">
                                <label class="form-label" for="ecommerce-product-currency">Moneda</label>
                                <select class="form-select" id="ecommerce-product-currency" name="currency" required>
                                    <option value="">Seleccionar moneda</option>
                                    <option value="Peso" {{ $product->currency == 'Peso' ? 'selected' : '' }}>Peso</option>
                                    <option value="Dólar" {{ $product->currency == 'Dólar' ? 'selected' : '' }}>Dólar</option>
                                </select>
                            </div>
                            <!-- Base Price -->
                            <div class="mb-3">
                                <label class="form-label" for="ecommerce-product-price">Precio normal</label>
                                <input type="number" class="form-control" step=".01" min="0"
                                    id="ecommerce-product-price" placeholder="Precio" name="old_price"
                                    value="{{ $product->old_price }}" aria-label="Product price" required>
                            </div>
                            <!-- Discounted Price -->
                            <div class="mb-3">
                                <label class="form-label" for="ecommerce-product-discount-price">Precio oferta</label>
                                <input type="number" class="form-control" min="0" step=".01"
                                    id="ecommerce-product-discount-price" placeholder="Precio oferta" name="price"
                                    value="{{ $product->price }}" aria-label="Introduzca el precio rebajado">
                            </div>
                            <!-- Tax Rate -->
                            <div class="mb-3">
                              <label class="form-label" for="tax_rate_id">IVA</label>
                              <select class="form-control" id="tax_rate_id" name="tax_rate_id" aria-label="Seleccione el IVA">
                                  <option value="" disabled {{ $product->tax_rate_id ? '' : 'selected' }}>Seleccione una tasa de IVA</option>
                                  @foreach($taxRates as $taxRate)
                                      <option value="{{ $taxRate->id }}"
                                          {{ $product->tax_rate_id == $taxRate->id ? 'selected' : '' }}>
                                          {{ $taxRate->name }} ({{ $taxRate->rate }}%)
                                      </option>
                                  @endforeach
                              </select>
                            </div>
                            <!-- Build Price -->
                            <div class="mb-3">
                                <label class="form-label" for="build_price">Costo</label>
                                <input type="number" step=".01" min="0" class="form-control"
                                    id="build_price" placeholder="Introduzca el costo del producto" name="build_price"
                                    aria-label="Introduzca el costo"
                                    value="{{ $product->build_price }}">
                            </div>
                            <!-- Hidden field for disabled status -->
                            <input type="hidden" name="status" value="2">
                            <!-- Instock switch -->
                            <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-3">
                                <span class="mb-0 h6">Estado</span>
                                <div class="w-25 d-flex justify-content-end">
                                  <label class="switch switch-primary switch-sm me-4 pe-2">
                                    <input
                                      type="checkbox"
                                      class="switch-input"
                                      value="{{ $product->status }}"
                                      id="statusSwitch"
                                      {{ $product->status == 1 ? 'checked' : '' }}
                                      name="status"
                                    >
                                    <span class="switch-toggle-slider"></span>
                                  </label>
                                </div>
                            </div>
                            <!-- Show in catalogue switch -->
                            <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-3">
                                <span class="mb-0 h6">Mostrar en el catálogo</span>
                                <div class="w-25 d-flex justify-content-end">
                                    <!-- Campo oculto para asegurar que siempre se envíe un valor -->
                                    <input type="hidden" name="show_in_catalogue" value="0">
                                    <label class="switch switch-primary switch-sm me-4 pe-2">
                                        <input type="checkbox" class="switch-input" value="1" id="catalogueSwitch"
                                            {{ $product->show_in_catalogue == 1 ? 'checked' : '' }} name="show_in_catalogue">
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
                            <div class="mb-3">
                                <label class="form-label" for="vendor">Empresa</label>
                                <select id="vendor" class="select2 form-select" data-placeholder="Seleccionar local"
                                    name="store_id" required>
                                    @if (auth()->user()->hasPermissionTo('access_global_products'))
                                    <option value="">Seleccionar local</option>
                                    @foreach ($stores as $store)
                                    <option value="{{ $store->id }}"
                                        {{ $product->store_id == $store->id ? 'selected' : '' }}>
                                        {{ $store->name }}
                                    </option>
                                    @endforeach
                                    @else
                                    <option value="{{ auth()->user()->store_id }}" selected>
                                        {{ auth()->user()->store->name }}
                                    </option>
                                    @endif
                                </select>
                            </div>

                            <!-- Category -->
                            <div class="mb-3 col ecommerce-select2-dropdown">
                                <select id="category-org" class="select2 form-select" data-placeholder="Seleccione la categoría" name="categories[]" multiple data-selected="{{ json_encode($product->categories->pluck('id')->toArray()) }}">
                                    @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">
                                        {{ $category->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>


                            <!-- Stock -->
                            <div class="mb-3">
                                <label class="form-label" for="stock">Stock</label>
                                <input type="number" class="form-control" id="stock" placeholder="Stock"
                                    name="stock" value="{{ $product->stock }}" aria-label="Introduzca el stock">
                            </div>

                            <!-- Safety Margin -->
                            <div class="mb-3">
                                <label class="form-label" for="safety_margin">Margen de Seguridad</label>
                                <input type="number" class="form-control" id="safety_margin"
                                    placeholder="Margen de seguridad" name="safety_margin"
                                    value="{{ $product->safety_margin }}" aria-label="Introduzca el margen de seguridad">
                            </div>

                            <!-- Barcode -->
                            <div class="mb-3">
                                <label class="form-label" for="bar_code">Código de Barras</label>
                                <input type="text" class="form-control" id="bar_code" placeholder="Código de barras"
                                    name="bar_code" value="{{ $product->bar_code }}"
                                    aria-label="Introduzca el código de barras">
                            </div>
                        </div>
                    </div>
                    <!-- /Organize Card -->

                    <!-- Media and Gallery Card -->
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
                                        <!-- Imagen Principal -->
                                        <div class="mb-3 d-flex justify-content-center" id="existingImage">
                                            @if ($product->image)
                                            <img src="{{ asset($product->image) }}" alt="Imagen del producto"
                                                class="img-thumbnail rounded shadow-sm"
                                                id="productImagePreview"
                                                style="max-width: 200px; border: 1px solid #ddd;">
                                            @endif
                                        </div>
                                        <!-- Dropzone -->
                                        <div class="dropzone dz-clickable border rounded p-3 bg-light" id="dropzone">
                                            <div class="dz-message m-2 needsclick text-muted">
                                                <p class="fs-6 fw-semibold">Arrastra la imagen aquí</p>
                                                <small class="d-block fs-6">o</small>
                                                <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="btnBrowse">Buscar imagen</button>
                                            </div>
                                        </div>
                                        <!-- Input oculto -->
                                        <input type="file" name="image" id="productImage" class="d-none">
                                    </div>
                                </div>


                                <!-- Gallery Tab -->
                                <div class="tab-pane fade" id="galleryTabContent" role="tabpanel" aria-labelledby="gallery-tab">
                                    <div class="mb-3">
                                        <label for="galleryImages" class="form-label fw-bold">Subir Imágenes</label>
                                        <input type="file" class="form-control" name="gallery_images[]" id="galleryImages" multiple>
                                    </div>
                                    <div id="galleryPreview" class="row g-2 mt-3">
                                        @foreach ($product->gallery as $image)
                                        <div class="col-12 col-xl-6 d-flex justify-content-center">
                                            <div class="card shadow-sm border-0 w-100 text-center">
                                                <div class="position-relative d-flex align-items-center justify-content-center">
                                                    <img src="{{ asset($image->image) }}"
                                                        class="card-img-top rounded mx-auto d-block"
                                                        alt="Imagen del producto"
                                                        style="max-height: 200px; object-fit: cover; width: 100%;">
                                                    <button type="button" class="btn btn-sm btn-danger remove-image position-absolute top-0 end-0 m-2"
                                                        data-url="{{ route('products.gallery.delete', ['imageId' => $image->id]) }}"
                                                        style="border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Media and Gallery Card -->



                </div>
                <!-- /Second column -->

            </div>
    </form>
</div>
@endsection
