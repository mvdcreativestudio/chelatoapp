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
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light"></span> Stock de Productos
</h4>

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

<!-- Filtros de búsqueda -->
<div class="row mb-4 g-2 align-items-end">
  <!-- Búsqueda por nombre de producto -->
  <div class="col-lg-4 col-md-6">
    <div class="input-group">
      <span class="input-group-text bg-light">
        <i class="bx bx-search"></i>
      </span>
      <input type="text" id="searchProduct" class="form-control" placeholder="Buscar producto por Nombre...">
    </div>
  </div>

  <!-- Filtro por tienda -->
  <div class="col-lg-4 col-md-6">
    <div class="input-group">
      <span class="input-group-text bg-light">
        <i class="bx bx-store"></i>
      </span>
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
  </div>

  <!-- Filtro por estado -->
  <div class="col-lg-2 col-md-6">
    <div class="input-group">
      <span class="input-group-text bg-light">
        <i class="bx bx-check-circle"></i>
      </span>
      <select id="statusFilter" class="form-select">
        <option value="">Todos los estados</option>
        <option value="1">Activo</option>
        <option value="0">Inactivo</option>
      </select>
    </div>
  </div>

  <!-- Filtro por rango de stock -->
  <div class="col-lg-2 col-md-6">
    <div class="input-group">
      <span class="input-group-text bg-light">
        <i class="bx bx-layer"></i>
      </span>
      <input type="number" id="minStockFilter" class="form-control" placeholder="Mín. stock" min="0">
      <span class="input-group-text">a</span>
      <input type="number" id="maxStockFilter" class="form-control" placeholder="Máx. stock" min="0">
    </div>
  </div>

  <div class="col-lg-4 col-md-6">
    <div class="input-group">
      <span class="input-group-text bg-light">
        <i class="bx bx-sort"></i>
      </span>
      <select id="sortStockFilter" class="form-select">
        <option value="">Ordenar por</option>
        <option value="high_stock">Mayor Stock</option>
        <option value="low_stock">Menor Stock</option>
        <option value="no_stock">Sin Stock</option>
      </select>
    </div>
  </div>
</div>



<!-- Product List Cards -->
<div class="row row-cols-1" id="product-list-container" data-ajax-url="{{ route('products.datatable') }}">
  <!-- Aquí se generarán las tarjetas de productos mediante JS -->
</div>

<style>

</style>
@endsection
