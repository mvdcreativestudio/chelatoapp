@extends('layouts/layoutMaster')

@section('title', 'Órdenes Recibidas')

@section('content')
<div class="d-flex align-items-center justify-content-between bg-white p-4 mb-3 rounded shadow-lg border-bottom border-light">
  <h4 class="mb-0 page-title">
    <i class="bx bx-package me-2"></i> Órdenes Internas Recibidas
  </h4>
</div>

<!-- Resumen -->
<div class="row mb-4">
  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-body text-center">
        <h6 class="text-muted">Total</h6>
        <h3>{{ $totals['all'] }}</h3>
      </div>
    </div>
  </div>
  <div class="col-md-2">
    <div class="card shadow-sm">
      <div class="card-body text-center">
        <h6 class="text-warning">Pendientes</h6>
        <h3>{{ $totals['pending'] }}</h3>
      </div>
    </div>
  </div>
  <div class="col-md-2">
    <div class="card shadow-sm">
      <div class="card-body text-center">
        <h6 class="text-success">Aceptadas</h6>
        <h3>{{ $totals['accepted'] }}</h3>
      </div>
    </div>
  </div>
  <div class="col-md-2">
    <div class="card shadow-sm">
      <div class="card-body text-center">
        <h6 class="text-info">Entregadas</h6>
        <h3>{{ $totals['delivered'] }}</h3>
      </div>
    </div>
  </div>
  <div class="col-md-2">
    <div class="card shadow-sm">
      <div class="card-body text-center">
        <h6 class="text-danger">Canceladas</h6>
        <h3>{{ $totals['cancelled'] }}</h3>
      </div>
    </div>
  </div>
</div>

<!-- Lista de órdenes -->
<div class="row">
  @forelse($orders as $order)
    <div class="col-md-6 mb-3">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h5>Orden #{{ $order->id }}</h5>
            <span class="badge bg-{{ $order->status_color }}">{{ $order->status_label }}</span>
          </div>
          <p class="mb-1"><strong>Desde:</strong> {{ $order->fromStore->name }}</p>
          <p class="mb-1"><strong>Para:</strong> {{ $order->toStore->name }}</p>
          <p class="mb-1"><strong>Creada:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
          <p class="mb-2"><strong>Entrega:</strong> {{ $order->delivery_date ? $order->delivery_date->format('d/m/Y') : 'No definida' }}</p>

          <div class="d-flex justify-content-end">
            <a href="{{ route('internal-orders.show', $order->id) }}" class="btn btn-outline-primary btn-sm">
              Ver Detalle
            </a>
          </div>
        </div>
      </div>
    </div>
  @empty
    <div class="col-12">
      <div class="alert alert-info text-center">
        No hay órdenes internas recibidas.
      </div>
    </div>
  @endforelse
</div>
@endsection
