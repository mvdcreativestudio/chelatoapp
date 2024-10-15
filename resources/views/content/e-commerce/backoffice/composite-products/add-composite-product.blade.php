@extends('layouts/layoutMaster')

@section('title', 'Crear Producto Compuesto')

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
'resources/assets/js/composite-products/app-composite-product-add.js'
])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light"></span><span> Crear Producto Compuesto</span>
</h4>

@if ($errors->any())
<div class="alert alert-danger">
  <ul>
    @foreach ($errors->all() as $error)
    <li>{{ $error }}</li>
    @endforeach
  </ul>
</div>
@endif

<div class="app-ecommerce" data-products='@json($products)'>

  <!-- Add Composite Product -->
  <form action="{{ route('composite-products.store') }}" method="POST" enctype="multipart/form-data" id="addCompositeProductForm">
    @csrf
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
      <!-- Título de la página y botones de acciones -->
      <div class="d-flex flex-column justify-content-center">
        <h4 class="mb-1 mt-3">Crear un nuevo producto compuesto</h4>
      </div>
      <div class="d-flex align-content-center flex-wrap gap-3">
        <button type="button" class="btn btn-label-secondary" id="discardButton">Descartar</button>
        <button type="submit" name="action" value="publish" class="btn btn-primary" id="saveButton">Guardar</button>
      </div>
    </div>

    <!-- Primera fila con dos columnas de igual altura -->
    <div class="row gx-3">
      <!-- Primera columna (Información del producto) -->
      <div class="col-lg-8">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title mb-0">Información del Producto Compuesto</h5>
          </div>
          <div class="card-body">
            <!-- Campo para el título del producto compuesto -->
            <div class="mb-3">
              <label class="form-label" for="composite-product-name">Título</label>
              <input type="text" class="form-control" id="composite-product-name"
                placeholder="Nombre del producto compuesto" name="name" aria-label="Nombre del producto compuesto"
                required>
            </div>
            <!-- Campo para la descripción del producto compuesto -->
            <div class="mb-3">
              <label class="form-label" for="description">Descripción</label>
              <textarea class="form-control" id="description" name="description"
                placeholder="Descripción del producto compuesto"></textarea>
            </div>
          </div>
        </div>
      </div>

      <!-- Segunda columna (Precios) -->
      <div class="col-lg-4">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title mb-0">Precios</h5>
          </div>
          <div class="card-body">
            <!-- Campo para el precio final -->
            <div class="mb-3">
              <label class="form-label" for="price">Precio Final</label>
              <input type="number" class="form-control" id="price" placeholder="Precio del producto compuesto"
                name="price">
            </div>
            <!-- Campo para el precio recomendado -->
            <div class="mb-3">
              <label class="form-label" for="recommended_price">Precio Recomendado</label>
              <input type="number" class="form-control" id="recommended_price"
                placeholder="Precio recomendado (calculado automáticamente)" name="recommended_price" required disabled>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Segunda fila con dos columnas de igual altura -->
    <div class="row gx-3 mt-4">
      <!-- Primera columna (Productos incluidos) -->
      <div class="col-lg-8">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title mb-0">Productos Incluidos</h5>
          </div>
          <div class="card-body">
            <!-- Campo para seleccionar productos incluidos -->
            <div class="mb-3">
              <div class="d-flex justify-content-between">
                <label class="form-label" for="product_ids">Productos</label>
              </div>
              <select class="select2 form-select" id="product_ids" name="product_ids[]" multiple="multiple" required>
                @foreach ($products as $product)
                <option value="{{ $product->id }}" data-build-price="{{ $product->build_price }}">
                  {{ $product->name }}
                </option>
                @endforeach
              </select>
            </div>

            <!-- Tabla para productos seleccionados -->
            <table class="table" id="selectedProductsTable">
              <thead>
                <tr>
                  <th>Producto</th>
                  <th>Cantidad</th>
                  <th>Precio Unitario</th>
                  <th>Subtotal</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>

            <!-- Alerta si algún producto no tiene build_price -->
            <div class="alert alert-danger d-none" id="priceAlert">
              Uno o más productos no tienen un precio asociado, no se puede calcular el precio recomendado.
            </div>
          </div>
        </div>
      </div>

      <!-- Segunda columna (Organizar) -->
      <div class="col-lg-4">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title mb-0">Organizar</h5>
          </div>
          <div class="card-body">
            <!-- Campo para seleccionar la Empresa -->
            <div class="mb-3">
              <label class="form-label" for="store_id">Empresa</label>
              <select id="store_id" class="form-select" data-placeholder="Seleccione la Empresa" name="store_id"
                required>
                <option value="" selected disabled>Seleccione una empresa</option>
                @foreach ($stores as $store)
                <option value="{{ $store->id }}">{{ $store->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>

@endsection
