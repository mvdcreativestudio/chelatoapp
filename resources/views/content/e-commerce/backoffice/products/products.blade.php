@extends('layouts/layoutMaster')

@section('title', 'Productos')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/select2/select2.scss',
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/select2/select2.js',
])

@php
$currencySymbol = $settings->currency_symbol;
@endphp

<script>
  window.currencySymbol = '{{ $currencySymbol }}';
  let exportUrl = "{{ route('products.export') }}";
</script>

@endsection

@section('page-script')
@vite([
  'resources/assets/js/app-ecommerce-product-list.js'
])
@endsection

@section('content')



<div class="d-flex align-items-center justify-content-between bg-white p-4 mb-3 rounded shadow-lg sticky-top border-bottom border-light">

  <!-- Título del formulario alineado a la izquierda -->
  <div class="d-flex flex-column justify-content-center">
    <h4 class="mb-0 page-title">
      <i class="bx bx-box me-2"></i> Productos
    </h4>
  </div>

  <!-- Barra de búsqueda y botón de filtros, con espacio intermedio -->
  <div class="d-flex align-items-center justify-content-center flex-grow-1 gap-3">

    <!-- Búsqueda por nombre de producto, centrada y con ancho del 100% en mobile -->
    <div class="input-group w-50 shadow-sm">
      <span class="input-group-text bg-white">
        <i class="bx bx-search"></i>
      </span>
      <input type="text" id="searchProduct" class="form-control" placeholder="Buscar producto por Nombre..." aria-label="Buscar Producto">
    </div>

  </div>

  <!-- Botones alineados a la derecha, ahora responsive -->
  <div class="text-end d-flex gap-2">

      <!-- Botón para crear nuevo producto -->
      <a href="{{ route('products.create') }}" class="btn btn-success btn-sm shadow-sm d-flex align-items-center gap-1">
        <i class="fa-solid fa-plus"></i> Nuevo Producto
      </a>

    <!-- Botón de filtros -->
    <button id="openFilters" class="btn btn-outline-primary btn-sm shadow-sm d-flex align-items-center gap-1">
      <i class="fa-solid fa-filter"></i> Filtros
    </button>

    <!-- Desplegable para Importar/Exportar -->
    <div class="dropdown">
      <button class="btn btn-outline-primary btn-sm shadow-sm  dropdown-toggle" type="button" id="dropdownImportExport" data-bs-toggle="dropdown" aria-expanded="false">
        <span><i class="fa-solid fa-download"></i> Exportar/Importar</span> 
      </button>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownImportExport">
        <li><a class="dropdown-item" href="#" id="exportExcel"><i class="fa-solid fa-table"></i> Exportar a Excel</a></li>
        <li><a class="dropdown-item" href="{{ route('products.download-template') }}" id="download-template"><i class="bx bx-download"></i> Descargar Plantilla</a></li>
        <li><a class="dropdown-item" href="#" id="openImportModal"><i class="bx bx-upload"></i> Importar Plantilla</a></li>
      </ul>
    </div>

    <!-- Desplegable para Acciones -->
    <div class="dropdown">
      <button class="btn btn-primary btn-sm shadow-sm d-flex align-items-center gap-1 dropdown-toggle" type="button" id="dropdownImportExport" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fa-solid fa-bars"></i> Acciones
      </button>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownImportExport">
        <li><a class="dropdown-item" href="{{ route('products.addBulk') }}"><i class="fa-regular fa-square-plus"></i> Crear en masa</a></li>
        <li><a class="dropdown-item" href="{{ route('products.editBulk') }}"><i class="fa-regular fa-pen-to-square"></i> Editar en masa</a></li>

      </ul>
    </div>

  </div>

</div>



@if(session('success'))
  <div class="alert alert-success d-flex" role="alert">
    <span class="badge badge-center rounded-pill bg-success border-label-success p-3 me-2"><i class="bx bx-user fs-6"></i></span>
    <div class="d-flex flex-column ps-1">
      <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">¡Correcto!</h6>
      <span>{{ session('success') }}</span>
    </div>
  </div>
@elseif(session('error'))
  <div class="alert alert-danger d-flex" role="alert">
    <span class="badge badge-center rounded-pill bg-danger border-label-danger p-3 me-2"><i class="bx bx-user fs-6"></i></span>
    <div class="d-flex flex-column ps-1">
      <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">¡Error!</h6>
      <span>{{ session('error') }}</span>
    </div>
  </div>
@endif

<div id="alert-container"></div>


<!-- Product List Cards -->
<div class="row row-cols-1" id="product-list-container" data-ajax-url="{{ route('products.datatable') }}">
  <!-- Aquí se generarán las tarjetas de productos mediante JS -->
</div>

<!-- Modal de Filtros -->
<div id="filterModal" class="filter-modal">
  <div class="filter-modal-content">
    <button id="closeFilterModal" class="close-filter-modal">
      <i class="bx bx-x"></i>
    </button>

    <!-- Filtros -->
    <h5 class="mb-4">Filtros</h5>

    <!-- Filtro por tienda -->
    <div class="mb-3">
      <label for="storeFilter" class="form-label">Tienda</label>
      @if(count($stores) == 1)
        <input type="text" class="form-control" value="{{ $stores[0]->name }}" readonly disabled>
        <input type="hidden" id="storeFilter" value="{{ $stores[0]->id }}">
      @else
        <select id="storeFilter" class="form-select">
          <option value="">Todas las tiendas</option>
          @foreach($stores as $store)
            <option value="{{ $store->id }}">{{ $store->name }}</option>
          @endforeach
        </select>
      @endif
    </div>

    <!-- Filtro por categoría -->
    <div class="mb-3">
      <label for="categoryFilter" class="form-label">Categoría</label>
      <select id="categoryFilter" class="form-select">
        <option value="">Todas las categorías</option>
        @foreach($categories as $category)
          <option value="{{ $category->id }}">{{ $category->name }}</option>
        @endforeach
      </select>
    </div>

    <!-- Filtro por estado -->
    <div class="mb-3">
      <label for="statusFilter" class="form-label">Estado</label>
      <select id="statusFilter" class="form-select">
        <option value="">Todos los estados</option>
        <option value="1">Activo</option>
        <option value="0">Inactivo</option>
      </select>
    </div>
  </div>
</div>

<!-- Modal para Importar Productos -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="importModalLabel">Importar Productos</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="importForm" action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="mb-3">
            <label for="importFile" class="form-label">Subir archivo Excel (.xlsx)</label>
            <input class="form-control" type="file" id="importFile" name="file" accept=".xlsx" required>
          </div>
          <div class="mb-3">
            <a href="{{ route('products.download-template') }}" class="text-muted text-sm" id="download-template">Haz click aquí para descargar la plantilla</a>
          </div>
          <button type="submit" class="btn btn-primary">Subir</button>
        </form>
      </div>
    </div>
  </div>
</div>

<style>
  .product-card {
    display: flex;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    transition: transform 0.2s ease-in-out;
    height: 150px;
  }

  .product-card:hover {
    transform: translateY(-3px);
  }

  .product-card-img {
    object-fit: cover;
    width: 100%;
    height: 100%;
    max-height: 150px;
  }

  .product-card-body {
    padding: 10px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    width: 100%;
  }

  .product-title {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 5px;
  }

  .product-category {
    font-size: 0.75rem;
    margin-bottom: 5px;
  }

  .product-price {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 5px;
  }

  .product-stock {
    font-size: 0.75rem;
    margin-bottom: 5px;
  }

  .product-status {
    font-size: 0.75rem;
    font-weight: 600;
    margin-bottom: 5px;
  }

  .product-card-actions {
    text-align: right;
    margin-top: auto;
  }

  .badge {
    padding: 3px 8px;
    font-size: 0.75rem;
  }

  /* Modal de Filtros */
  .filter-modal {
    position: fixed;
    top: 0;
    right: -300px;
    width: 300px;
    height: 100%;
    background: #fff;
    box-shadow: -2px 0 10px rgba(0, 0, 0, 0.2);
    z-index: 2000;
    transition: right 0.3s ease-in-out;
    overflow-y: auto;
  }

  .filter-modal.open {
    right: 0;
  }

  .filter-modal-content {
    padding: 20px;
  }

  .close-filter-modal {
    position: absolute;
    top: 15px;
    right: 15px;
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
  }

  @media (max-width: 768px) {
  .d-flex {
    flex-direction: column;
  }
  .page-title {
    margin-bottom: 10px!important;
  }

  .input-group {
    width: 100% !important;
  }

  .text-end {
    margin-top: 1rem;
    width: 100%;
    justify-content: center;
  }

  .dropdown-menu-end {
    right: 0;
    left: auto;
  }

  .dropdown-toggle {
    width: 100%;
    text-align: center;
  }

  .btn {
    width: 100%;
  }
}

</style>
@endsection
