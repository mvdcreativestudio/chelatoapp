@extends('layouts/layoutMaster')

@section('title', 'Lista de precios')

@section('content')
<div class="d-flex align-items-center justify-content-between bg-white p-4 mb-3 rounded shadow-lg sticky-top border-bottom border-light">
  <h4 class="mb-0 page-title"><i class="bx bx-list-ul me-2"></i> {{ $priceList->name }}</h4>
  <div class="d-flex gap-2">
    @can('access_edit_price-lists')
      <a href="{{ route('price-lists.edit', $priceList->id) }}" class="btn btn-sm btn-outline-primary"><i class="bx bx-edit"></i> Editar</a>
    @endcan
    <a href="{{ route('price-lists.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bx bx-arrow-back"></i> Volver</a>
  </div>
</div>

<div class="card mb-3">
  <div class="card-body">
    <p><strong>Tienda:</strong> {{ optional($priceList->store)->name }}</p>
    <p><strong>Moneda:</strong> {{ $priceList->currency }}</p>
    @if($priceList->description)
      <p><strong>Descripción:</strong> {{ $priceList->description }}</p>
    @endif
  </div>
</div>

<div class="card">
  <div class="card-body">
    <h5>Productos ({{ $priceList->products->count() }})</h5>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Producto</th>
            <th>SKU</th>
            <th class="text-end">Precio lista</th>
          </tr>
        </thead>
        <tbody>
          @forelse($priceList->products as $product)
            <tr>
              <td>{{ $product->name }}</td>
              <td><small class="text-muted">{{ $product->sku }}</small></td>
              <td class="text-end">{{ number_format($product->pivot->price, 2) }}</td>
            </tr>
          @empty
            <tr><td colspan="3" class="text-center text-muted py-4">Sin productos en esta lista.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
