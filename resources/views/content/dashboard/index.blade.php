@extends('layouts/layoutMaster')

@section('title', 'Dashboard')

<meta name="csrf-token" content="{{ csrf_token() }}">

@section('page-script')
@vite(['resources/assets/js/cards-statistics.js', 'resources/assets/js/ui-cards-analytics.js', 'resources/assets/js/extended-ui-tour.js', 'resources/assets/js/toggle-store-status.js'])
<script>
    window.baseUrl = "{{ url('/') }}";
</script>
@endsection

@section('vendor-style')
@vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
@vite('resources/assets/vendor/libs/shepherd/shepherd.scss')
@endsection

@section('page-style')
@vite('resources/assets/vendor/scss/pages/card-analytics.scss')
@endsection

@section('vendor-script')
@vite('resources/assets/vendor/libs/apex-charts/apexcharts.js',)
@vite('resources/assets/vendor/libs/shepherd/shepherd.js')
@endsection

@section('content')
@if (session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  {{ session('error') }}
</div>
@endif

<!-- Tarjetas primera línea -->
<div class="row g-3">
  <div class="col-12 col-md-7">
    <div class="card p-3">
        <h5>Vencen hoy:</h5>
        <div class="row g-3 align-items-stretch">
            <!-- Tarjeta Factura Impaga -->
            <div class="col-12 col-md-6">
                <div class="card card-border-shadow-danger low-opacity-bg d-flex flex-row align-items-center p-3 h-100">
                    <div class="expenses-card-icon me-3">
                        <img src="{{ asset('assets/img/ux-new/Ellipse-14.png') }}" alt="Movistar Logo" class="img-fluid" style="width: 40px; height: auto;">
                    </div>
                    <div class="expenses-card-content flex-grow-1">
                        <p class="m-0 text-danger bold">Factura Impaga</p>
                        <p class="m-0">Contrato corporativo<br>Movistar</p>
                    </div>
                    <h5 class="m-0">$790.68</h5>
                </div>
            </div>
            
            <!-- Tarjeta Cobro -->
            <div class="col-12 col-md-6">
                <div class="card card-border-shadow-success d-flex flex-row align-items-center p-3 h-100">
                    <div class="expenses-card-icon me-3">
                        <img src="{{ asset('assets/img/ux-new/Ellipse-15.png') }}" alt="Juan Rodriguez" class="img-fluid" style="width: 40px; height: auto;">
                    </div>
                    <div class="expenses-card-content flex-grow-1">
                        <p class="m-0 text-success bold">Último cobro realizado</p>
                        <p class="m-0">Sandwiche Carne<br>Juan Rodriguez</p>
                    </div>
                    <h5 class="m-0">$790.68</h5>
                </div>
            </div>
        </div>
    </div>
  </div>


  <div class="col-12 col-md-5">
    <div class="card h-100 bg-success-custom text-white">
        <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center">
            <div class="mb-3 mb-md-0">
                <p class="mb-1">Tu balance diario</p>
                <h3 class="m-0 text-white">+ $18.765</h3>
            </div>
            <div class="text-end">
                <p class="m-0">Ingresos: <span class="fw-bold">$38.765</span></p>
                <p class="m-0">Egresos: <span class="fw-bold">$20.765</span></p>
                <p class="m-0">Total: <span class="fw-bold">+ $18.765</span></p>
            </div>
        </div>
    </div>
  </div>

</div>

<!-- Tarjetas segunda línea -->
<div class="row mt-3 g-3 align-items-stretch">
  <div class="col-12 col-lg-8">
      <div class="card h-100 p-3 justify-content-center text-center">
          <h5 class="mb-4">Conectá tus cuentas</h5>
          <div class="d-flex flex-wrap justify-content-center">
              @foreach(['pedidos-ya', 'rappi', 'mercadopago', 'handy', 'fiserv', 'oca'] as $integration)
                  <div class="me-3 mb-2">
                      <img src="{{ asset("assets/img/ux-new/integraciones/$integration.png") }}" alt="Logo {{ ucfirst($integration) }}" class="img-fluid" style="width: 70px; height: auto;">
                  </div>
              @endforeach
          </div>
      </div>
  </div>

  <div class="col-12 col-sm-6 col-md-2 col-lg-2">
    <div class="card h-100">
      <div class="card-body">
        <h5 class="d-block fw-medium mb-2">Ventas Realizadas</h5>
        <h5 class="mb-2 bold">514</h5>
        <span class="badge bg-label-info mb-3">+34%</span>
        <small class="text-muted d-block">Objetivo de ventas</small>
        <div class="d-flex align-items-center">
          <div class="progress w-75 me-2" style="height: 8px;">
            <div class="progress-bar bg-info" style="width: 78%" role="progressbar" aria-valuenow="78" aria-valuemin="0" aria-valuemax="100"></div>
          </div>
          <span>78%</span>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-md-2 col-lg-2">
    <div class="card h-100">
      <div class="card-body">
        <h5 class="d-block fw-medium mb-2">Gastos</h5>
        <h5 class="mb-2 bold">$18.487</h5>
        <span class="badge bg-label-danger mb-3">+18%</span>
        <div class="d-flex align-items-center">
          <span>34%</span>
        </div>
        <small class="text-muted d-block">más que el mes pasado.</small>
      </div>
    </div>
  </div>

</div>


<!-- Tarjetas tercera línea -->
<div class="row mt-3 g-3">
  <!-- Tarjeta de productos más vendidos con col-lg-4 para mejor distribución en pantallas grandes -->
  <div class="col-12 col-md-6 col-lg-4">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Productos más vendidos <i class="fa-solid fa-chevron-down"></i></h5>
      </div>
      <div class="card-body p-0">
        <div class="report-list">
          @foreach([['Remera Azul L', '42,845', '7.835'], ['Remera Gris L', '39,789', '6.352'], ['Gorra Azul', '29,378', '2.191']] as $index => $product)
            <div class="report-list-item rounded-2 mb-3 p-2">
              <div class="d-flex align-items-start">
                <div class="report-list-icon shadow-sm me-2">
                  <h4 class="m-0 p-0">{{ $index + 1 }}.</h4>
                </div>
                <div class="d-flex justify-content-between align-items-end w-100 flex-wrap gap-2">
                  <div class="d-flex flex-column">
                    <span>{{ $product[0] }}</span>
                    <h5 class="mb-0">${{ $product[1] }}</h5>
                  </div>
                  <div class="text-end">
                    <small class="text-success bold">{{ $product[2] }}</small>
                    <small class="text-success">Unidades</small>
                  </div>
                </div>
              </div>
            </div>
          @endforeach
          <div class="text-center mb-3">
            <button class="btn btn-sm btn-primary">Ver Todos</button>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Gráfica de Ingresos Totales -->
  <div class="col-12 col-md-6 col-lg-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between">
        <div>
          <h5 class="mb-0">Ingresos Totales</h5>
          <small class="card-subtitle">Reporte Mensual</small>
        </div>
        <div class="btn-group">
          <button type="button" class="btn btn-sm btn-outline-primary text-muted dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Oct.</button>
          <ul class="dropdown-menu">
            @foreach(['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'] as $month)
              <li><a class="dropdown-item{{ $month == 'Octubre' ? ' active' : '' }}" href="javascript:void(0);">{{ $month }}</a></li>
            @endforeach
          </ul>
        </div>
      </div>
      <div class="card-body">
        <div id="totalIncomeChart"></div>
      </div>
    </div>
  </div>
</div>

<!-- Tarjetas cuarta línea -->
<div class="row mt-3 g-3">
  @foreach([['primary', 'bx-check'], ['warning', 'bx-time'], ['danger', 'bx-error-circle'], ['info', 'bx-line-chart']] as $card)
    <div class="col-12 col-sm-6 col-lg-3">
      <div class="card h-100 card-border-shadow-{{ $card[0] }}">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2 pb-1">
            <div class="avatar me-2">
              <span class="avatar-initial rounded bg-label-{{ $card[0] }}"><i class="bx {{ $card[1] }}"></i></span>
            </div>
            <h4 class="ms-1 mb-0"></h4>
          </div>
          <p class="mb-1 fw-medium me-1"></p>
        </div>
      </div>
    </div>
  @endforeach
</div>
@endsection