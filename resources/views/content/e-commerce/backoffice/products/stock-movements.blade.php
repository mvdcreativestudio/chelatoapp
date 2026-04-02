@extends('layouts/layoutMaster')

@section('title', 'Historial de Movimientos de Stock')

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light"></span> Historial de Movimientos de Stock
</h4>

<!-- Filtros -->
<div class="card mb-4">
  <div class="card-body">
    <form method="GET" action="{{ route('stock-movements.index') }}">
      <div class="row g-3 align-items-end">
        <div class="col-lg-3 col-md-6">
          <label class="form-label">Producto</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bx bx-search"></i></span>
            <input type="text" name="search" class="form-control" placeholder="Buscar producto..." value="{{ request('search') }}">
          </div>
        </div>
        <div class="col-lg-2 col-md-6">
          <label class="form-label">Tipo</label>
          <select name="type" class="form-select">
            <option value="">Todos</option>
            <option value="manual" {{ request('type') === 'manual' ? 'selected' : '' }}>Ajuste manual</option>
            <option value="sale" {{ request('type') === 'sale' ? 'selected' : '' }}>Venta</option>
            <option value="order_delete" {{ request('type') === 'order_delete' ? 'selected' : '' }}>Eliminación de orden</option>
            <option value="credit_note" {{ request('type') === 'credit_note' ? 'selected' : '' }}>Nota de crédito</option>
          </select>
        </div>
        <div class="col-lg-2 col-md-6">
          <label class="form-label">Desde</label>
          <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
        </div>
        <div class="col-lg-2 col-md-6">
          <label class="form-label">Hasta</label>
          <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
        </div>
        <div class="col-lg-3 col-md-6">
          <button type="submit" class="btn btn-primary me-2"><i class="bx bx-filter-alt"></i> Filtrar</button>
          <a href="{{ route('stock-movements.index') }}" class="btn btn-outline-secondary"><i class="bx bx-x"></i> Limpiar</a>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Tabla -->
<div class="card">
  <div class="table-responsive text-nowrap">
    <table class="table table-hover">
      <thead class="table-light">
        <tr>
          <th>Fecha</th>
          <th>Producto</th>
          <th>Usuario</th>
          <th>Tipo</th>
          <th>Cantidad</th>
          <th>Stock anterior</th>
          <th>Stock nuevo</th>
          <th>Motivo</th>
        </tr>
      </thead>
      <tbody>
        @forelse($movements as $movement)
          <tr>
            <td>{{ $movement->created_at->format('d/m/Y H:i') }}</td>
            <td>
              {{ $movement->product_name }}
              @if($movement->product_type === 'App\\Models\\CompositeProduct')
                <span class="badge bg-label-info">Compuesto</span>
              @endif
            </td>
            <td>{{ $movement->user?->name ?? 'Sistema' }}</td>
            <td>
              @switch($movement->type)
                @case('manual')
                  <span class="badge bg-label-warning">Ajuste manual</span>
                  @break
                @case('sale')
                  <span class="badge bg-label-success">Venta</span>
                  @break
                @case('order_delete')
                  <span class="badge bg-label-danger">Eliminación de orden</span>
                  @break
                @case('credit_note')
                  <span class="badge bg-label-info">Nota de crédito</span>
                  @break
                @default
                  <span class="badge bg-label-secondary">{{ $movement->type }}</span>
              @endswitch
            </td>
            <td>
              <span class="{{ $movement->quantity > 0 ? 'text-success' : 'text-danger' }} fw-bold">
                {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
              </span>
            </td>
            <td>{{ $movement->old_stock }}</td>
            <td>{{ $movement->new_stock }}</td>
            <td>{{ $movement->reason ?? '-' }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center py-4">
              <i class="bx bx-info-circle"></i> No se encontraron movimientos de stock.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($movements->hasPages())
    <div class="card-footer d-flex justify-content-center">
      {{ $movements->links() }}
    </div>
  @endif
</div>
@endsection
