@extends('layouts/layoutMaster')

@section('title', 'Listas de Precios')

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
  'resources/assets/js/app-price-lists.js'
])
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between bg-white p-4 mb-3 rounded shadow-lg sticky-top border-bottom border-light">
  <div class="d-flex flex-column justify-content-center">
    <h4 class="mb-0 page-title">
      <i class="bx bx-list-ul me-2"></i> Listas de Precios
    </h4>
  </div>

  {{-- <div class="d-flex align-items-center justify-content-center flex-grow-1 gap-3">
    <div class="input-group w-50 shadow-sm">
      <span class="input-group-text bg-white">
        <i class="bx bx-search"></i>
      </span>
      <input type="text" id="searchPriceList" class="form-control" placeholder="Buscar lista por Nombre..." aria-label="Buscar Lista de Precios">
    </div>
  </div> --}}
  @can('access_create-price-lists')
    <div class="text-end d-flex gap-2">
        <!-- Botón para abrir el modal de crear lista de precios -->
        <button type="button" class="btn btn-success btn-sm shadow-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#createPriceListModal">
            <i class="bx bx-plus"></i> Nueva Lista de Precios
        </button>
    </div>
  @endcan
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

<!-- Price List Cards -->
<div class="row row-cols-1" id="price-list-container" data-ajax-url="{{ route('price-lists.datatable') }}">
    <!-- Aquí se generarán las tarjetas de listas de precios mediante JS -->
</div>

<!-- Modal para Crear Lista de Precios -->
<div class="modal fade" id="createPriceListModal" tabindex="-1" aria-labelledby="createPriceListModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="createPriceListForm" action="{{ route('price-lists.store') }}" method="POST">
          @csrf
          <div class="modal-header">
            <h5 class="modal-title" id="createPriceListModalLabel">Crear Lista de Precios</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="name" class="form-label">Nombre de la Lista</label>
              <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
              <label for="description" class="form-label">Descripción (Opcional)</label>
              <textarea class="form-control" id="description" name="description"></textarea>
            </div>
            <div class="mb-3">
              <label for="store_id" class="form-label">Tienda</label>
              <select id="store_id" name="store_id" class="form-select" required>
                <option value="">Seleccionar Tienda</option>
                @foreach($stores as $store)
                  <option value="{{ $store->id }}">{{ $store->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-primary">Crear Lista de Precios</button>
          </div>
        </form>
      </div>
    </div>
</div>
  

<style>
  .price-list-card {
    display: flex;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    transition: transform 0.2s ease-in-out;
    height: 150px;
  }

  .price-list-card:hover {
    transform: translateY(-3px);
  }

  .price-list-card-body {
    padding: 10px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    width: 100%;
  }

  .price-list-title {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 5px;
  }

  .price-list-description {
    font-size: 0.75rem;
    margin-bottom: 5px;
  }

  .price-list-store {
    font-size: 0.75rem;
    font-weight: 600;
    margin-bottom: 5px;
  }

  .price-list-card-actions {
    text-align: right;
    margin-top: auto;
  }

  .badge {
    padding: 3px 8px;
    font-size: 0.75rem;
  }
</style>

@endsection
