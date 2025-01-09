@extends('layouts/layoutMaster')

@section('title', 'Integraciones')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/sortablejs/sortable.js'
])
@endsection

@section('page-script')
<script type="text/javascript">
  window.baseUrl = "{{ url('/') }}";
  window.csrfToken = "{{ csrf_token() }}";
  var stores = @json($stores);
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@vite(['resources/assets/js/integrations/app-integration-mercado-pago.js'])
@vite(['resources/assets/js/integrations/app-integration-peya.js'])
@vite(['resources/assets/js/integrations/app-integration-pymo.js'])
@vite(['resources/assets/js/integrations/app-integration-email.js'])
@vite(['resources/assets/js/integrations/app-integration-ecommerce.js'])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Empresa /</span> Integraciones
</h4>

@if (session('success'))
<div class="alert alert-success mt-3 mb-3">
  {{ session('success') }}
</div>
@endif

@if (session('error'))
<div class="alert alert-danger mt-3 mb-3">
  {{ session('error') }}
</div>
@endif

@if ($errors->any())
@foreach ($errors->all() as $error)
<div class="alert alert-danger">
  {{ $error }}
</div>
@endforeach
@endif

<div class="nav-tabs-container position-relative">
  <ul class="nav nav-tabs" id="storeTabs" role="tablist">
    @foreach($stores as $store)
    <li class="nav-item" role="presentation">
      <button class="nav-link {{ $loop->first ? 'active' : '' }}"
        id="store-tab-{{ $store->id }}"
        data-bs-toggle="tab"
        data-bs-target="#store-content-{{ $store->id }}"
        type="button"
        role="tab"
        aria-controls="store-content-{{ $store->id }}"
        aria-selected="{{ $loop->first ? 'true' : 'false' }}">
        <i class='bx bx-store-alt me-1'></i>
        {{ $store->name }}
        <small class="ms-2 text-muted">#{{ $store->id }}</small>
      </button>
    </li>
    @endforeach
  </ul>

  <div class="tab-content mt-3 overflow-hidden" id="storeTabsContent">
    @foreach($stores as $store)
    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
      id="store-content-{{ $store->id }}"
      data-store-id="{{ $store->id }}"
      role="tabpanel">
      <div class="integration-grid">
        @include('stores.partials.integracion-ecommerce')
        @include('stores.partials.configuracion-correo')
        @include('stores.partials.pedidos-ya')
        @include('stores.partials.mercado-pago-pagos-presenciales', [
        'store' => $store,
        'mercadoPagoPresencial' => $store->mercadoPagoAccount->firstWhere('type',
        \App\Enums\MercadoPago\MercadoPagoApplicationTypeEnum::PAID_PRESENCIAL),
        'mercadoPagoAccountStore' => $store->mercadoPagoAccountStore
        ])
        @include('stores.partials.mercado-pago-pagos-online', [
        'store' => $store,
        'mercadoPagoOnline' => $store->mercadoPagoAccount->firstWhere('type',
        \App\Enums\MercadoPago\MercadoPagoApplicationTypeEnum::PAID_ONLINE)
        ])
        @include('stores.partials.pymo', ['companyInfo' => $store->companyInfo])
      </div>
    </div>
    @endforeach
  </div>
</div>

<style>
  .nav-tabs {
    border-bottom: none;
    gap: 0.5rem;
    padding: 1rem 1rem 0;
    background: #f8f9fa;
    border-radius: 0.8rem 0.8rem 0 0;
  }

  .nav-tabs .nav-link {
    border-radius: 1rem 1rem 0 0;
    padding: 0.75rem 1.5rem;
    background: #e9ecef;
    border: 1px solid #dee2e6;
    border-bottom: none;
    margin-bottom: 0;
    transition: all 0.2s ease-in-out;
    position: relative;
    top: 1px;
  }

  .nav-tabs .nav-link:hover {
    background: #f8f9fa;
    transform: translateY(-2px);
  }

  .nav-tabs .nav-link.active {
    background: white;
    border-color: #dee2e6;
    border-bottom: none;
    box-shadow: 0 -4px 8px rgba(0, 0, 0, 0.05);
  }

  .tab-content {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 0 0 1rem 1rem;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  }

  .config-wrapper {
    position: relative;
    display: flex;
    align-items: flex-start;
  }

  .main-card {
    width: 100%;
  }


  .main-card.active {
    transform: translateX(20px);
  }

  .config-panel {
    max-width: 0;
    overflow: hidden;
    opacity: 0;
    transition: all 0.3s ease;
  }

  .config-panel.active {
    max-width: 500px;
    opacity: 1;
  }

  .config-panel .card {
    width: 450px;
  }

  .card .integration-fields {
    max-height: 100%;
    overflow-y: auto;
  }

  .card .form-label {
    margin-bottom: 0.25rem;
  }

  .integration-grid {
    display: grid;
    gap: 1rem;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  }

  @media (max-width: 768px) {
    .integration-grid {
      grid-template-columns: 1fr;
    }

    .card {
      margin-bottom: 1rem;
    }
  }

  .integration-grid>* {
    width: 100%;
    min-width: 0;
  }

  .integration-card {
    width: 100%;
  }

  .integration-card .card {
    height: 220px;
    margin: 0;
  }

  .integration-card .card-header {
    height: 100px;
    position: relative;
    padding: 1rem;
  }

  .integration-card .integration-icon {
    width: 80px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .integration-card .integration-icon img {
    width: 70px;
    height: auto;
  }

  .integration-card .status-indicator {
    position: absolute;
    top: 0;
    right: 0;
    transform: translate(50%, -50%);
    padding: 0.25rem;
    background-color: #198754;
    border-radius: 50%;
  }

  .integration-card .card-body {
    padding: 1.5rem;
  }

  .integration-card .card-body {
    padding: 1.0rem;
  }

  .integration-card .card-title {
    margin-top: 0.6rem;
  }

  .integration-card small {
    margin-top: 0.25rem;
    display: block;
  }
</style>

@endsection