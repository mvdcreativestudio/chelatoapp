@extends('layouts/layoutMaster')

@section('title', 'Categorías')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/select2/select2.scss',
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/select2/select2.js',
])


@endsection

@section('page-script')
@vite([
  'resources/assets/js/app-ecommerce-category-list.js'
])
@endsection

@section('content')

<div class="row">
<!-- Tarjeta 1: Total de Categorías -->
<div class="col-sm-12 col-md-4 mb-4">
  <div class="card animated-card card-border-shadow-primary h-100">
    <div class="card-body d-flex flex-column">
      <!-- Encabezado de la tarjeta con ícono y título alineados -->
      <div class="d-flex align-items-center mb-3">
        <div class="avatar me-3">
          <span class="avatar-initial rounded bg-label-primary d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
            <i class="bx bx-hive fs-4"></i>
          </span>
        </div>
        <h4 class="ms-2 mb-0 flex-grow-1 text-truncate">{{ $totalCategories }}</h4>
      </div>

      <!-- Información adicional centrada verticalmente -->
      <div class="d-flex flex-column justify-content-center">
        <p class="mb-1 fw-medium text-muted">Total de Categorías</p>
      </div>
    </div>
  </div>
</div>

<!-- Tarjeta 2: Categoría con más productos -->
<div class="col-sm-12 col-md-4 mb-4">
  <div class="card animated-card card-border-shadow-warning h-100">
    <div class="card-body d-flex flex-column justify-content-between">
      <!-- Encabezado de la tarjeta con ícono y título alineados -->
      <div class="d-flex align-items-center mb-3">
        <div class="avatar me-3">
          <span class="avatar-initial rounded bg-label-warning d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
            <i class='bx bx-up-arrow-alt fs-4'></i>
          </span>
        </div>
        <h4 class="ms-2 mb-0 flex-grow-1 text-truncate">{{ $categoryWithMostProducts['name'] }}</h4>
      </div>

      <!-- Información adicional centrada verticalmente -->
      <div class="d-flex flex-column justify-content-center">
        <p class="mb-1 fw-medium text-muted">Categoría con más productos</p>
        <span class="fw-bold text-warning fs-5">{{ $categoryWithMostProducts['product_count'] }} productos</span>
      </div>
    </div>
  </div>
</div>

<!-- Tarjeta 3: Categoría con más stock -->
<div class="col-sm-12 col-md-4 mb-4">
  <div class="card animated-card card-border-shadow-success h-100">
    <div class="card-body d-flex flex-column justify-content-between">
      <!-- Encabezado de la tarjeta con ícono y título alineados -->
      <div class="d-flex align-items-center mb-3">
        <div class="avatar me-3">
          <span class="avatar-initial rounded bg-label-success d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
            <i class='bx bx-package fs-4'></i>
          </span>
        </div>
        <h4 class="ms-2 mb-0 flex-grow-1 text-truncate">{{ $categoryWithMostStock['name'] }}</h4>
      </div>

      <!-- Información adicional centrada verticalmente -->
      <div class="d-flex flex-column justify-content-center">
        <p class="mb-1 fw-medium text-muted">Categoría con más stock</p>
        <span class="fw-bold text-success fs-5">{{ $categoryWithMostStock['stock_count'] }} unidades</span>
      </div>
    </div>
  </div>
</div>


</div>

<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between bg-white p-4 mb-3 rounded shadow-lg sticky-top border-bottom border-light animated-card">
  <!-- Título de la página alineado a la izquierda -->
  <div class="d-flex flex-column justify-content-center w-100 mb-3 mb-md-0">
    <h4 class="mb-0 page-title">
      <i class="bx bx-category-alt me-2"></i> Categorías
    </h4>
  </div>

  <!-- Barra de búsqueda con espacio intermedio -->
  <div class="d-flex align-items-center justify-content-center w-100 mb-3 mb-md-0">
    <div class="input-group shadow-sm">
      <span class="input-group-text bg-white">
        <i class="bx bx-search"></i>
      </span>
      <input type="text" id="searchCategory" class="form-control" placeholder="Buscar categoría por Nombre..." aria-label="Buscar Categoría">
    </div>
  </div>

  <!-- Botón Nueva Categoría alineado a la derecha -->
  <div class="text-end w-100 w-md-auto">
    <div style="max-width: 150px;" class="float-end">
    <a href="#" class="btn btn-success btn-sm shadow-sm d-flex align-items-center gap-1" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEcommerceCategoryList">
      <i class="bx bx-plus"></i> Nueva Categoría
    </a>
    </div>
  </div>
</div>


<div id="alert-container"></div>

@if (session('success'))
<div class="alert alert-success mt-3 mb-3">
    {{ session('success') }}
</div>
@endif

@if (session('error'))
<div class="alert alert-danger mt-3 mb-3">
    {{ session('error') }}
</div>
@endif

@if ($errors->any())
@foreach ($errors->all() as $error)
<div class="alert alert-danger">
    {{ $error }}
</div>
@endforeach
@endif

<!-- Lista de tarjetas de categorías -->
<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="category-list-container">
  <!-- Aquí se generarán las tarjetas de categorías mediante JS -->
</div>

<!-- Offcanvas to add new category -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEcommerceCategoryList" aria-labelledby="offcanvasEcommerceCategoryListLabel">
  <!-- Offcanvas Header -->
  <div class="offcanvas-header py-4">
    <h5 id="offcanvasEcommerceCategoryListLabel" class="offcanvas-title">Crear categoría</h5>
    <button type="button" class="btn-close bg-label-secondary text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <!-- Offcanvas Body -->
  <div class="offcanvas-body border-top">
    <form action="{{ route('product-categories.store') }}" method="POST" enctype="multipart/form-data" class="pt-0" id="eCommerceCategoryListForm">
      @csrf
      <!-- Title -->
      <div class="mb-3">
        <label class="form-label" for="ecommerce-category-title">Nombre</label>
        <input type="text" class="form-control" id="ecommerce-category-title" placeholder="Ingrese el nombre de la categoría" name="name" aria-label="category title">
      </div>
      <!-- Slug -->
      <div class="mb-3" style="display: none;">
        <label class="form-label" for="ecommerce-category-slug">Slug</label>
        <input type="text" id="ecommerce-category-slug" class="form-control" placeholder="Ingrese el slug" aria-label="slug" name="slug">
      </div>
      <!-- Store -->
      <div class="mb-3">
        <label class="form-label" for="ecommerce-category-store">Empresa</label>
        <select id="ecommerce-category-store" class="form-select" name="store_id"
          @if(!auth()->user()->can('access_global_products')) disabled @endif>
          @if(auth()->user()->can('access_global_products'))
            <option value="">Seleccione la tienda</option>
            @foreach($stores as $store)
              <option value="{{ $store->id }}">{{ $store->name }}</option>
            @endforeach
          @else
            <option value="{{ auth()->user()->store_id }}">{{ auth()->user()->store->name }}</option>
          @endif
        </select>
      </div>
      <!-- Hidden status field -->
      <input type="hidden" name="status" value="2">
      <!-- Instock switch -->
      <div class="d-flex justify-content-between align-items-center border-top pt-3 pb-3">
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
        <button type="submit" class="btn btn-sm btn-primary me-sm-3 me-1 data-submit">Crear categoría</button>
        <button type="reset" class="btn btn-sm btn-outline-danger" data-bs-dismiss="offcanvas">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<!-- Offcanvas to edit category -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEcommerceCategoryEdit" aria-labelledby="offcanvasEcommerceCategoryEditLabel">
<div class="offcanvas-header py-4">
  <h5 id="offcanvasEcommerceCategoryEditLabel" class="offcanvas-title">Editar categoría</h5>
  <button type="button" class="btn-close bg-label-secondary text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
</div>
<div class="offcanvas-body border-top">
  <form id="editECommerceCategoryListForm" enctype="multipart/form-data" class="pt-0">
    @csrf
    <div class="mb-3">
      <label class="form-label" for="edit_ecommerce-category-title">Nombre</label>
      <input type="text" class="form-control" id="edit_ecommerce-category-title" placeholder="Ingrese el nombre de la categoría" name="name" aria-label="category title">
    </div>
    <div class="mb-3" style="display: none;">
      <label class="form-label" for="edit_ecommerce-category-slug">Slug</label>
      <input type="text" id="edit_ecommerce-category-slug" class="form-control" placeholder="Ingrese el slug" aria-label="slug" name="slug">
    </div>
    <div class="mb-3" style="display: none;">
      <label class="form-label" for="edit_ecommerce-category-image">Imagen</label>
      <input class="form-control" name="image" type="file" id="edit_ecommerce-category-image">
    </div>
    <div class="mb-3 ecommerce-select2-dropdown" style="display: none;">
      <label class="form-label" for="edit_ecommerce-category-parent-category">Categoría padre</label>
      <select id="edit_ecommerce-category-parent-category" class="select2 form-select" data-placeholder="Seleccione la categoría padre" name="parent_id">
        <option value="">Seleccione la categoría padre</option>
      </select>
    </div>
    <div class="mb-3" style="display: none;">
      <label class="form-label">Descripción</label>
      <input type="hidden" name="description" id="edit-hidden-description">
      <div class="form-control p-0 pt-1">
        <div class="comment-editor border-0" id="edit_ecommerce-category-description" contenteditable="true"></div>
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
    <input type="hidden" name="status" value="2">
    <div class="d-flex justify-content-between align-items-center border-top pt-3 mb-3">
      <span class="mb-0 h6">Estado</span>
      <div class="w-25 d-flex justify-content-end">
        <label class="switch switch-primary switch-sm me-4 pe-2">
          <input type="checkbox" class="switch-input" value="1" id="edit-statusSwitch" checked name="status">
          <span class="switch-toggle-slider"></span>
        </label>
      </div>
    </div>
    <div class="mb-3">
      <button type="button" id="editCategoryButton" class="btn btn-sm btn-primary me-sm-3 me-1">Editar categoría</button>
      <button type="reset" class="btn btn-sm btn-outline-danger" data-bs-dismiss="offcanvas">Cancelar</button>
    </div>
  </form>
</div>
</div>
@endsection
