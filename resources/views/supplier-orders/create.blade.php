@extends('layouts/layoutMaster')

@section('title', 'Agregar Orden a Proveedor')

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Órdenes a Proveedores /</span> Crear Orden
</h4>

<div class="app-ecommerce">
  <form action="{{ route('supplier-orders.store') }}" method="POST">
  @csrf
    <div class="row">
      <div class="col-12">
        <div class="card mb-4">
            <div class="mb-3">
              <label for="supplier_id" class="form-label">Proveedor</label>
              <select class="form-select" id="supplier_id" name="supplier_id" required>
                <option selected disabled>Seleccione un proveedor</option>
                @foreach($suppliers as $supplier)
                  <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="mb-3">
              <label for="order_date" class="form-label">Fecha de Orden</label>
              <input type="date" class="form-control" id="order_date" name="order_date" required>
            </div>

            <div class="mb-3">
              <label for="shipping_status" class="form-label">Estado de Envío</label>
              <select class="form-select" id="shipping_status" name="shipping_status" required>
                <option value="pendiente">Pendiente</option>
                <option value="enviando">Enviando</option>
                <option value="completado">Completado</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="payment_status" class="form-label">Estado del Pago</label>
              <select class="form-select" id="payment_status" name="payment_status" required>
                <option value="pendiente">Pendiente</option>
                <option value="pagado">Pagado</option>
                <option value="atrasado">Atrasado</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="payment" class="form-label">Pago</label>
              <input type="number" class="form-control" id="payment" name="payment" step="0.01" required>
            </div>

            <div class="mb-3">
              <label for="notes" class="form-label">Notas</label>
              <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Crear Orden</button>
        </div>
      </div>
    </div>
  </form>
</div>
@endsection
