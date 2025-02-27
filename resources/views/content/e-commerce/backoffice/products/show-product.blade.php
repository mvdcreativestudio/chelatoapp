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

@if (session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@elseif (session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if ($errors->any())
@foreach ($errors->all() as $error)
<div class="alert alert-danger">
{{ $error }}
</div>
@endforeach
@endif

<div class="d-flex flex-wrap align-items-center justify-content-between bg-light p-4 mb-3 rounded shadow sticky-top">
  <!-- Título del formulario -->
  <div class="d-flex flex-column justify-content-center">
    <h5 class="mb-0">
      <i class="bx bx-info-circle me-2"></i> Detalle del Producto
    </h5>
  </div>

  <!-- Botones de acciones -->
  <div class="d-flex justify-content-end gap-3">
    <button onclick="window.location.href='{{ route('products.edit', $product->id) }}'" class="btn btn-primary btn-sm d-flex align-items-center ">
      <i class="bx bx-edit me-1"></i> Editar
    </button>
    <button type="button" class="btn btn-danger btn-sm d-flex align-items-center " onclick="if(confirm('¿Estás seguro de que deseas eliminar este producto?')) { document.getElementById('delete-form').submit(); }">
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
        @if($product->sku)
        <div class="row mb-3">
          <div class="col">
            <label class="form-label">SKU:</label>
            <p>{{ $product->sku }}</p>
          </div>
        </div>
        @endif
        @if($product->description !== '<p><br></p>')
        <!-- Descripción -->
        <div class="mb-3">
          <label class="form-label">Descripción:</label>
          <p>{!! $product->description !!}</p>
        </div>
        @endif
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
        <h5 class="card-title mb-0">Precios y Estado</h5>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label">Moneda:</label>
          <p>{{ $product->currency }}</p>
        </div>
        <div class="mb-3">
          <label class="form-label">Moneda:</label>
          <p>{{ $product->currency }}</p>
        </div>
        <div class="mb-3">
          <label class="form-label">Precio:</label>
          <p>{{ $product->old_price }}</p>
        </div>
        @if($product->price)
        <div class="mb-3">
          <label class="form-label">Precio oferta:</label>
          <p>{{ $product->price }}</p>
        </div>
        @endif
        <div class="mb-3">
          <label class="form-label">Precio Final (Imp. Incluídos):</label>
          <p>
            {{$settings->currency}}
            @if($product->price)
              {{ number_format($product->price * (1 + ($product->taxRate->rate ?? 0) / 100), 2) }}
            @else
              {{ number_format($product->old_price * (1 + ($product->taxRate->rate ?? 0) / 100), 2) }}
            @endif
          </p>
        </div>
        {{-- build price --}}
        <div class="mb-3">
          <label class="form-label">Precio de costo:</label>
          <p>{{ $product->build_price ?? 'No establecido' }}</p>
        </div>
        <div class="mb-3">
          <label class="form-label">Estado:</label>
          <p>
            @if($product->status == 1)
              <span class="badge bg-success">Activo</span>
            @else
              <span class="badge bg-danger">Inactivo</span>
            @endif
          </p>
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
