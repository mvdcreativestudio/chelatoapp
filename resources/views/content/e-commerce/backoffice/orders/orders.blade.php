@extends('layouts/layoutMaster')

@section('title', 'Ventas')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/select2/select2.scss',
])
@endsection
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/es.min.js"></script>

@section('vendor-script')
@vite([
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
'resources/assets/js/app-orders-list.js'
])
@endsection

@section('content')
@if (Auth::user()->can('access_datacenter'))
<div class="row">
  <!-- Total de Ventas -->
  <div class="col-sm-6 col-lg-2 mb-4">
    <div class="card animated-card card-border-shadow-primary h-100 cursor-pointer">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2 pb-1">
          <div class="avatar me-2">
            <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-receipt"></i></span>
          </div>
          <h4 class="ms-1 mb-0">{{ $totalOrders }}</h4>
        </div>
        <p class="mb-1 fw-medium me-1">Total de Ventas</p>
      </div>
    </div>
  </div>

  <!-- Ventas Pagas -->
  <div class="col-sm-6 col-lg-2 mb-4">
    <div class="card animated-card card-border-shadow-success h-100 cursor-pointer">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2 pb-1">
          <div class="avatar me-2">
            <span class="avatar-initial rounded bg-label-success"><i class="bx bx-dollar"></i></span>
          </div>
          <h4 class="ms-1 mb-0">{{ $paidOrders }}</h4>
        </div>
        <p class="mb-1 fw-medium me-1">Ventas Pagas</p>
      </div>
    </div>
  </div>

  <!-- Ventas Impagas -->
  <div class="col-sm-6 col-lg-2 mb-4">
    <div class="card animated-card card-border-shadow-warning h-100 cursor-pointer">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2 pb-1">
          <div class="avatar me-2">
            <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-minus-circle"></i></span>
          </div>
          <h4 class="ms-1 mb-0">{{ $unpaidOrders }}</h4>
        </div>
        <p class="mb-1 fw-medium me-1">Ventas Impagas</p>
      </div>
    </div>
  </div>

  <!-- Ventas Impagas -->
  <div class="col-sm-6 col-lg-2 mb-4">
    <div class="card animated-card card-border-shadow-danger h-100 cursor-pointer">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2 pb-1">
          <div class="avatar me-2">
            <span class="avatar-initial rounded bg-label-danger"><i class="bx bx-time"></i></span>
          </div>
          <h4 class="ms-1 mb-0">{{ $failedOrders }}</h4>
        </div>
        <p class="mb-1 fw-medium me-1">Ventas Fallidas</p>
      </div>
    </div>
  </div>

  @if($bestClient)
<div class="col-sm-6 col-lg-4 mb-4">
  <a href="{{ route('clients.show', ['client' => $bestClient->client->id]) }}" class="text-decoration-none">
    <div class="card animated-card card-border-shadow-primary h-100">
      <div class="card-body d-flex flex-column justify-content-between">
        <!-- Encabezado con Icono y Título -->
        <div class="d-flex align-items-center mb-3">
          <div class="avatar me-2">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="bx bx-user"></i>
            </span>
          </div>
          <div>
            <h6 class="mb-0">Mejor Cliente</h6>
            <small class="text-muted">
              @if($bestClient->client->type == 'individual')
                {{ $bestClient->client->name ?? 'N/A' }} {{ $bestClient->client->lastname }}
              @elseif($bestClient->client->type == 'company')
                {{ $bestClient->client->company_name }}
              @endif
            </small>
          </div>
        </div>

        <!-- Datos de Total Comprado y Compras Realizadas -->
        <div class="d-flex justify-content-between">
          <div class="text-center">
            <h5 class="mb-0">{{ $settings->currency_symbol }}{{ number_format($bestClient->total_spent, 2) }}</h5>
            <small class="text-muted">Total Comprado</small>
          </div>
          <div class="text-center">
            <h5 class="mb-0">{{ $bestClient->purchase_count }}</h5>
            <small class="text-muted">Compras</small>
          </div>
        </div>
      </div>
    </div>
  </a>
</div>
@endif

</div>
@endif

<div
  class="d-flex align-items-center justify-content-between bg-white p-4 mb-3 rounded shadow-lg sticky-top border-bottom border-light">
  <div class="d-flex flex-column justify-content-center">
    <h4 class="mb-0 page-title">
      <i class="bx bx-package me-2"></i> Ventas
    </h4>
  </div>


  <div class="d-flex align-items-center justify-content-center flex-grow-1 gap-3">
    <div class="input-group w-50 shadow-sm">
      <span class="input-group-text bg-white">
        <i class="bx bx-search"></i>
      </span>
      <input type="text" id="searchOrder" class="form-control" placeholder="Buscar orden por ID o nombre del cliente..."
        aria-label="Buscar Orden">
    </div>
  </div>

  <div class="text-end d-flex gap-2">
    <button id="openFilters" class="btn btn-outline-primary btn-sm shadow-sm d-flex align-items-center gap-1">
      <i class="bx bx-filter-alt"></i> Filtros
    </button>

    <div class="dropdown">
      <button class="btn btn-primary btn-sm shadow-sm d-flex align-items-center gap-1 dropdown-toggle" type="button"
        id="dropdownImportExport" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bx bx-download"></i> Acciones
      </button>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownImportExport">
        <li><a class="dropdown-item" href="#" id="exportExcel"><i class="bx bx-export"></i> Exportar Excel</a></li>
      </ul>
    </div>
  </div>
</div>

<div id="alert-container"></div>

<!-- Order List Cards -->
<div class="row row-cols-1" id="order-list-container" data-ajax-url="{{ route('orders.datatable') }}">
  <!-- Aquí se generarán las tarjetas de órdenes mediante JS -->
</div>

<!-- Modal de Filtros -->
<div id="filterModal" class="filter-modal">
  <div class="filter-modal-content">
    <button id="closeFilterModal" class="close-filter-modal">
      <i class="bx bx-x"></i>
    </button>

    <h5 class="mb-4">Filtros</h5>
    <div class="mb-3">
      <label for="clientFilter">Cliente</label>
      <select id="clientFilter" class="form-select">
      </select>
    </div>

    <div class="mb-3">
      <label for="storeFilter">Tienda</label>
      <select id="storeFilter" class="form-select">
      </select>
    </div>
    <!-- Filtro por estado de pago -->
    <div class="mb-3">
      <label for="paymentStatusFilter" class="form-label">Estado de Pago</label>
      <select id="paymentStatusFilter" class="form-select">
        <option value="">Todos los estados</option>
        <option value="paid">Pagado</option>
        <option value="pending">Pendiente</option>
        <option value="failed">Fallido</option>
      </select>
    </div>

    <!-- Filtro por estado de envío -->
    <div class="mb-3">
      <label for="shippingStatusFilter" class="form-label">Estado de Envío</label>
      <select id="shippingStatusFilter" class="form-select">
        <option value="">Todos los estados</option>
        <option value="pending">No enviado</option>
        <option value="shipped">Enviado</option>
        <option value="delivered">Entregado</option>
      </select>
    </div>

    <!-- Filtro por fecha -->
    <div class="mb-3">
      <label for="startDate">Desde:</label>
      <input type="date" class="form-control date-range-filter" id="startDate" placeholder="Fecha de inicio">
    </div>
    <div class="mb-3">
      <label for="endDate">Hasta:</label>
      <input type="date" class="form-control date-range-filter" id="endDate" placeholder="Fecha de fin">
    </div>

    {{-- clear filters --}}
    <div class="mb-3">
      <button id="clearFilters" class="btn btn-outline-danger">
        Limpiar Filtros
      </button>
    </div>
  </div>
</div>

<style>
  .order-card {
    display: flex;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    transition: transform 0.2s ease-in-out;
  }

  .order-card:hover {
    transform: translateY(-3px);
  }

  .order-card-body {
    padding: 10px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    width: 100%;
  }

  .order-title {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 5px;
  }

  .order-status {
    font-size: 0.75rem;
    margin-bottom: 5px;
  }

  .order-total {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 5px;
  }

  .order-date {
    font-size: 0.75rem;
    margin-bottom: 5px;
  }

  .order-card-actions {
    text-align: right;
    margin-top: auto;
  }

  .badge {
    padding: 3px 8px;
    font-size: 0.75rem;
  }

  /* Modal de Filtros */
  .filter-modal {
    position: fixed;
    top: 0;
    right: -300px;
    width: 300px;
    height: 100%;
    background: #fff;
    box-shadow: -2px 0 10px rgba(0, 0, 0, 0.2);
    z-index: 2000;
    transition: right 0.3s ease-in-out;
    overflow-y: auto;
  }

  .filter-modal.open {
    right: 0;
  }

  .filter-modal-content {
    padding: 20px;
  }

  .close-filter-modal {
    position: absolute;
    top: 15px;
    right: 15px;
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
  }

  @media (max-width: 768px) {
    .d-flex {
      flex-direction: column;
    }

    .page-title {
      margin-bottom: 10px !important;
    }

    .input-group {
      width: 100% !important;
    }

    .text-end {
      margin-top: 1rem;
      width: 100%;
      justify-content: center;
    }

    .dropdown-menu-end {
      right: 0;
      left: auto;
    }

    .dropdown-toggle {
      width: 100%;
      text-align: center;
    }

    .btn {
      width: 100%;
    }
  }
</style>
@endsection