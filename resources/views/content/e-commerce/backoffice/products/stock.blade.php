@extends('layouts/layoutMaster')

@section('title', 'Stock de Productos')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
  'resources/assets/vendor/libs/select2/select2.scss',
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
  'resources/assets/vendor/libs/select2/select2.js',
])

@php
$currencySymbol = $settings->currency_symbol;
@endphp

<script>
  window.currencySymbol = '{{ $currencySymbol }}';
</script>

@endsection

@section('page-script')
@vite([
  'resources/assets/js/app-ecommerce-product-stock-list.js'
])
@endsection

@section('content')
<!-- Barra de navegación superior -->
<div class="d-flex align-items-center justify-content-between bg-white p-4 mb-3 rounded shadow-lg sticky-top border-bottom border-light">

  <!-- Título de la página alineado a la izquierda -->
  <div class="d-flex flex-column justify-content-center">
    <h4 class="mb-0 page-title">
      <i class="bx bx-layer me-2"></i> Stock de Productos
    </h4>
  </div>

  <!-- Barra de búsqueda, centrada -->
  <div class="d-flex align-items-center justify-content-center flex-grow-1 gap-3">
    <div class="input-group w-50 shadow-sm">
      <span class="input-group-text bg-white">
        <i class="bx bx-search"></i>
      </span>
      <input type="text" id="searchProduct" class="form-control" placeholder="Buscar producto por Nombre..." aria-label="Buscar Producto">
    </div>
  </div>

  <!-- Botones alineados a la derecha -->
  <div class="text-end d-flex gap-2">
    <!-- Botón de filtros que abre el offcanvas -->
    <button class="btn btn-outline-primary btn-sm shadow-sm d-flex align-items-center gap-1" 
            type="button" 
            data-bs-toggle="offcanvas" 
            data-bs-target="#filterOffcanvas" 
            aria-controls="filterOffcanvas">
      <i class="fa-solid fa-filter"></i> Filtros
    </button>
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

<!-- Offcanvas para los filtros -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="filterOffcanvasLabel">Filtros de Stock</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
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

    <!-- Filtro por estado -->
    <div class="mb-3">
      <label for="statusFilter" class="form-label">Estado</label>
      <select id="statusFilter" class="form-select">
        <option value="">Todos los estados</option>
        <option value="1">Activo</option>
        <option value="0">Inactivo</option>
      </select>
    </div>

    <!-- Filtro por rango de stock -->
    <div class="mb-3">
      <label for="minStockFilter" class="form-label">Rango de Stock</label>
      <div class="input-group">
        <input type="number" id="minStockFilter" class="form-control" placeholder="Mínimo" min="0">
        <span class="input-group-text">a</span>
        <input type="number" id="maxStockFilter" class="form-control" placeholder="Máximo" min="0">
      </div>
    </div>

    <!-- Ordenar por stock -->
    <div class="mb-3">
      <label for="sortStockFilter" class="form-label">Ordenar por</label>
      <select id="sortStockFilter" class="form-select">
        <option value="">Sin ordenamiento específico</option>
        <option value="high_stock">Mayor Stock</option>
        <option value="low_stock">Menor Stock</option>
        <option value="no_stock">Sin Stock</option>
      </select>
    </div>
    
    <hr>
    
    <button id="clearFilters" class="btn btn-outline-danger w-100 mt-3">
      <i class="bx bx-trash me-1"></i> Borrar filtros
    </button>
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

  /* Estilos responsivos */
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

    .btn {
      width: 100%;
    }
  }
</style>
@endsection