@extends('layouts/layoutMaster')

@section('title', 'Facturación Electrónica - Configuración')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
])
@endsection

@section('vendor-script')
@vite(['resources/assets/js/accounting/accounting-settings.js'])
@vite(['resources/assets/js/accounting/upload-caes.js'])
<script>
  window.csrfToken = "{{ csrf_token() }}";
</script>
@endsection

@section('content')
<div class="row">
  <!-- Mensaje principal -->
  <div class="col-12 mb-4">
    <div class="card border-0 shadow-sm">
        <div class="row g-0 align-items-center">
            <div class="col-md-8">
                <div class="card-body">
                    @php
                        // Verifica si al menos una tienda tiene invoices_enabled = 1
                        $isInvoicesEnabled = $stores->contains('invoices_enabled', 1);
                    @endphp
                    @if($isInvoicesEnabled)
                        <h5 class="text-primary mb-3">
                            La Facturación Electrónica se encuentra
                            <strong class="text-success">activa</strong>
                        </h5>
                        <p class="mb-0">Ya puedes emitir comprobantes fiscales desde la aplicación.</p>
                    @else
                        <h5 class="text-primary mb-3">
                            La Facturación Electrónica se encuentra
                            <strong class="text-danger">inactiva</strong>
                        </h5>
                        <p class="mb-3">No puedes emitir comprobantes fiscales. Verifica la configuración de tus tiendas.</p>
                        <a href="{{ route('integrations.index') }}" class="btn btn-primary btn-sm mb-0">Gestionar conexión</a>
                      @endif
                </div>
            </div>
            <div class="col-md-4 text-center">
                <img src="{{ asset('assets/img/illustrations/man-with-laptop.png') }}"
                    height="140"
                    alt="View Badge User"
                    data-app-light-img="illustrations/man-with-laptop-light.png"
                    data-app-dark-img="illustrations/man-with-laptop-dark.png">
            </div>
        </div>
    </div>
  </div>

    <!-- Configuración de sucursales -->
    @foreach ($stores as $store)
        <div class="col-12 col-md-6 col-lg-4 mb-4 store-card" data-store-id="{{ $store->id }}">
            <div class="card border-0 shadow-sm h-100 d-flex flex-column justify-content-between">
                <div class="card-body flex-grow-1 d-flex flex-column">
                    <h5 class="card-title text-primary fw-bold mb-3">Sucursal: {{ $store->name }}</h5>

                    <!-- Contenedor de datos o mensaje de error -->
                    <div class="flex-grow-1">
                        <!-- Contenedor de datos de Pymo -->
                        <div class="pymo-data" style="display: none;">
                            <p class="mb-1"><strong>Empresa:</strong> <span class="company-name">N/A</span></p>
                            <p class="mb-1"><strong>RUT:</strong> <span class="company-rut">N/A</span></p>
                            <p class="mb-1"><strong>Email:</strong> <span class="company-email">N/A</span></p>
                            <p class="mb-1"><strong>Sucursal:</strong> <span class="company-branch">N/A</span></p>
                        </div>

                        <!-- Contenedor de errores -->
                        <div class="pymo-error text-danger text-center" style="display: none;">
                            <i class="bx bx-error-circle fs-4"></i>
                            <p class="mb-0">La tienda no tiene configurada la integración</p>
                        </div>
                    </div>
                </div>

                <!-- Tabla de CAES dentro de la tarjeta -->
                <div class="caes-container" style="display: none;">
                    <table class="caes-table w-100">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Próximo Número</th>
                                <th>Rango</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Aquí se llenarán los datos dinámicamente -->
                        </tbody>
                    </table>
                </div>


                <!-- Loader mientras se cargan los datos -->
                <div class="pymo-loader text-center">
                  <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                  </div>
                  <p class="mt-2 text-muted">Cargando información...</p>
                </div>

                @if($store->invoices_enabled)
                <div class="col-12 text-center">
                  <button
                  class="btn btn-primary btn-sm mb-3 open-upload-caes-modal"
                  data-store-rut="{{ $store->rut }}"
                  data-store-id="{{ $store->id }}">
                  Subir CAEs
                  </button>
                </div>
                @endif
            </div>
        </div>
    @endforeach
</div>

<!-- Modal para cargar CAEs -->
@include('content.accounting._partials.upload-caes')
<style>
/* General */
.store-card {
    background-color: #fff;
    border-radius: 8px;
    overflow: hidden;
}

.card {
    transition: box-shadow 0.3s ease-in-out;
}

.card:hover {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

/* Tabla de CAES */
.caes-container {
    margin-top: 15px;
    padding: 10px 0;
}

.caes-table {
    width: 100%;
    border-spacing: 0;
}

.caes-table th, .caes-table td {
    padding: 10px;
    text-align: left;
}

.caes-table th {
    font-weight: 600;
    color: #333;
    border-bottom: 2px solid #e9ecef;
    background-color: #f8f9fa;
}

.caes-table td {
    font-size: 14px;
    color: #555;
    border-bottom: 1px solid #f1f1f1;
}

.caes-table tr:last-child td {
    border-bottom: none;
}

.caes-table tr:hover {
    background-color: #f5f8fa;
}

/* Loader */
.pymo-loader .spinner-border {
    width: 30px;
    height: 30px;
    margin: auto;
}

.pymo-loader p {
    margin-top: 10px;
    font-size: 14px;
    color: #6c757d;
}

/* Botón */
.fetch-caes {
    font-size: 14px;
    transition: all 0.3s ease;
}

.fetch-caes:hover {
    background-color: #0d6efd;
    color: white;
}
</style>
@endsection



