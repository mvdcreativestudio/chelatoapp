@extends('layouts/layoutMaster')

@section('title', 'Detalle de Orden Interna')

@section('content')

@php
  $selected = old('status', $order->status?->value);
@endphp


<div class="d-flex align-items-center justify-content-between bg-white p-4 mb-3 rounded shadow-lg border-bottom border-light">
  <h4 class="mb-0 page-title">
    <i class="bx bx-package me-2"></i> Orden #{{ $order->id }} desde {{ $order->fromStore->name }}
  </h4>
  <div class="col-3 justify-content-between d-flex align-items-center">
    <a href="{{ route('internal-orders.pdf', $order->id) }}" class="btn btn-success btn-sm">
      <i class="bx bx-download"></i> Exportar PDF
    </a>
  </div>
</div>

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif



<form action="{{ route('internal-orders.update', $order->id) }}" method="POST">
  @csrf
  @method('PUT')

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <h5 class="card-title">Datos de la Orden</h5>
      <small class="mb-5 pb-5">Creada el: <b>{{ $order->created_at->format('d/m/Y H:i') }}</b></small>
      <div class="row mb-3 mt-4">
        <div class="col-md-4">
          <label class="form-label">Estado</label>
          <select name="status" class="form-select" required>
            @php $selected = old('status', $order->status); @endphp
            <option value="pending" @selected($selected === 'pending')>Pendiente</option>
            <option value="accepted" @selected($selected === 'accepted')>Aceptada</option>
            <option value="rejected" @selected($selected === 'rejected')>Rechazada</option>
            <option value="delivered" @selected($selected === 'delivered')>Entregada</option>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label">Fecha de Entrega</label>
          <input type="date" name="delivery_date" class="form-control"
            value="{{ old('delivery_date', optional($order->delivery_date)->format('Y-m-d')) }}">
        </div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <h5 class="card-title">Productos</h5>

      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead>
            <tr>
              <th>Producto</th>
              <th>Cantidad Solicitada</th>
              <th>Cantidad a Entregar</th>
            </tr>
          </thead>
          <tbody>
            @foreach($order->items as $item)
              <tr>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>
                  <input type="number"
                    name="items[{{ $item->id }}][deliver_quantity]"
                    value="{{ old('items.' . $item->id . '.deliver_quantity', $item->deliver_quantity ?? $item->quantity) }}"
                    class="form-control" min="0">
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

    </div>
  </div>

  <div class="text-end">
    <button type="submit" class="btn btn-primary">
      <i class="bx bx-save"></i> Guardar Cambios
    </button>
  </div>
</form>
@endsection
