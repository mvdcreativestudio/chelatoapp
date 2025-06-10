@extends('layouts/layoutMaster')

@section('title', 'Crear Orden Interna')

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

@section('content')
<div class="d-flex align-items-center justify-content-between bg-white p-4 mb-3 rounded shadow-lg sticky-top border-bottom border-light">
  <div class="d-flex flex-column justify-content-center">
    <h4 class="mb-0 page-title">
      <i class="bx bx-cart me-2"></i> Crear Orden Interna
    </h4>
  </div>
</div>

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form action="{{ route('internal-orders.store') }}" method="POST">
  @csrf

  <div class="card mb-4 shadow-sm">
    <div class="card-body">
      <h5 class="card-title">Datos de la Orden</h5>

      {{-- Selector de Tienda Origen --}}
      <div class="mb-3">
        <label for="from_store_id" class="form-label">Tienda Origen</label>
        @if($current_store->can_create_internal_orders_to_all_stores)
          <select class="form-select select2" name="from_store_id" id="from_store_id" required>
            @foreach($from_stores as $store)
              <option value="{{ $store->id }}"
                {{ $store->id == $current_store->id ? 'selected' : '' }}>
                {{ $store->name }}
              </option>
            @endforeach
          </select>
        @else
          <input type="hidden" name="from_store_id" value="{{ $current_store->id }}">
          <input type="text" class="form-control" value="{{ $current_store->name }}" disabled readonly>
        @endif
      </div>

      {{-- Selector de Tienda Destino --}}
      <div class="mb-3">
        <label for="to_store_id" class="form-label">Tienda Destino</label>
        <select class="form-select select2" name="to_store_id" id="to_store_id" required>
          <option value="">Seleccionar tienda</option>
          @foreach($stores as $store)
            <option value="{{ $store->id }}">{{ $store->name }}</option>
          @endforeach
        </select>
      </div>
    </div>
  </div>

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <h5 class="card-title">Seleccionar Productos</h5>

      {{-- Filtros UX --}}
      <div class="row mb-3">
        <div class="col-md-12">
          <label for="product-search" class="form-label">Buscar producto</label>
          <input type="text" id="product-search" class="form-control" placeholder="Nombre del producto...">
        </div>
      </div>

      <div id="product-container">
        <div class="text-muted">Seleccioná una tienda origen para ver los productos disponibles...</div>
      </div>
    </div>
  </div>

  <div class="text-end">
    <button type="submit" class="btn btn-success">
      <i class="bx bx-save"></i> Crear Orden
    </button>
  </div>
</form>
@endsection

@section('page-script')
<script>
  function initializeSelect2() {
    if (window.$ && $.fn.select2) {
      $('.select2').select2();
    }
  }

  const productRouteBase = "{{ url('/admin/internal-orders/products') }}";

  function fetchProducts(storeId, search = '') {
    const container = document.getElementById('product-container');

    container.innerHTML = '<div class="text-center py-3">Cargando productos...</div>';

    const params = new URLSearchParams({
      search: search
    });

    fetch(`${productRouteBase}/${storeId}?${params.toString()}`)
      .then(res => res.text())
      .then(html => {
        container.innerHTML = html;
        initializeSelect2();
      })
      .catch(err => {
        console.error('Error en fetch:', err);
        container.innerHTML = '<div class="text-danger">Error al cargar productos.</div>';
      });
  }

  document.addEventListener('DOMContentLoaded', function () {
    initializeSelect2();

    let currentStoreId = null;

    $(document).on('change', '#from_store_id', function () {
      currentStoreId = $(this).val();
      if (currentStoreId) fetchProducts(currentStoreId);
    });

    $(document).on('input', '#product-search', debounce(function () {
      if (currentStoreId) {
        fetchProducts(currentStoreId, this.value);
      }
    }, 300));

    // Trigger inicial si ya hay tienda preseleccionada
    const preselected = document.getElementById('from_store_id');
    if (preselected && preselected.value) {
      currentStoreId = preselected.value;
      fetchProducts(currentStoreId);
    }
  });

  function debounce(func, wait) {
    let timeout;
    return function (...args) {
      clearTimeout(timeout);
      timeout = setTimeout(() => func.apply(this, args), wait);
    };
  }
</script>
@endsection
