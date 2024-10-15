@extends('layouts/layoutMaster')

@section('title', 'Detalle del Producto')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/dropzone/dropzone.scss',
  'resources/assets/vendor/libs/flatpickr/flatpickr.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/dropzone/dropzone.js',
  'resources/assets/vendor/libs/flatpickr/flatpickr.js'
])
@endsection

@section('content')

<div class="d-flex flex-wrap align-items-center justify-content-between bg-light p-4 mb-3 rounded shadow sticky-top">
  <!-- Título del formulario -->
  <div class="d-flex flex-column justify-content-center">
    <h4 class="mb-0">
      <i class="bx bx-info-circle me-2"></i> Detalle del Producto
    </h4>
  </div>

  <!-- Botones de acciones -->
  <div class="d-flex justify-content-end gap-3">
    <button onclick="window.location.href='{{ route('products.edit', $product->id) }}'" class="btn btn-primary d-flex align-items-center">
      <i class="bx bx-edit me-1"></i> Editar
    </button>
    <button type="button" class="btn btn-danger d-flex align-items-center" onclick="if(confirm('¿Estás seguro de que deseas eliminar este producto?')) { document.getElementById('delete-form').submit(); }">
      <i class="bx bx-trash me-1"></i> Eliminar
    </button>
    <form id="delete-form" action="{{ route('products.destroy', $product->id) }}" method="POST" style="display: none;">
      @csrf
      @method('DELETE')
    </form>
  </div>
</div>

<div class="row">
  <!-- Primera columna -->
  <div class="col-12 col-lg-8">
    <!-- Información del Producto -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">Información del producto</h5>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label">Nombre:</label>
          <p>{{ $product->name }}</p>
        </div>
        <div class="row mb-3">
          <div class="col">
            <label class="form-label">SKU:</label>
            <p>{{ $product->sku }}</p>
          </div>
        </div>
        <!-- Descripción -->
        <div class="mb-3">
          <label class="form-label">Descripción:</label>
          <p>{!! $product->description !!}</p>
        </div>
      </div>
    </div>
    <!-- /Información del Producto -->

    <!-- Variantes -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">Tipo de producto y variaciones</h5>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label">Tipo de producto:</label>
          <p>{{ $product->type == 'simple' ? 'Simple' : 'Variable' }}</p>
        </div>
        @if($product->type == 'configurable')
        <div class="mb-3">
          <label class="form-label">Variaciones disponibles:</label>
          <ul>
            @foreach ($product->flavors as $flavor)
            <li>{{ $flavor->name }}</li>
            @endforeach
          </ul>
        </div>
        @endif
      </div>
    </div>
    <!-- /Variantes -->
  </div>
  <!-- /Primera columna -->

  <!-- Segunda columna -->
  <div class="col-12 col-lg-4">
    <!-- Tarjeta de Precios -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">Precio</h5>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label">Precio normal:</label>
          <p>{{ $product->old_price }} (IVA incluido)</p>
        </div>
        <div class="mb-3">
          <label class="form-label">Precio oferta:</label>
          <p>{{ $product->price }} (IVA incluido)</p>
        </div>
        {{-- build price --}}
        <div class="mb-3">
          <label class="form-label">Precio de costo:</label>
          <p>{{ $product->build_price ?? 'No establecido' }}</p>
        </div>
        <div class="mb-3">
          <label class="form-label">Estado:</label>
          <p>{{ $product->status == 1 ? 'Activo' : 'Inactivo' }}</p>
        </div>

      </div>
    </div>
    <!-- /Tarjeta de Precios -->

    <!-- Media -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">Imagen del producto</h5>
      </div>
      <div class="card-body text-center">
        @if($product->image)
          <img src="{{ asset($product->image) }}" alt="Imagen del producto" class="img-fluid">
        @else
          <p>No hay imagen disponible</p>
        @endif
      </div>
    </div>
    <!-- /Media -->
  </div>
  <!-- /Segunda columna -->
</div>

@endsection
