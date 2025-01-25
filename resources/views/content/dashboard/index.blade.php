@extends('layouts/layoutMaster')

@section('title', 'Dashboard')

<meta name="csrf-token" content="{{ csrf_token() }}">

@section('page-script')
@vite([
'resources/assets/js/extended-ui-tour.js',
'resources/assets/js/toggle-store-status.js',
'resources/assets/js/dashboard/integrations.js',
'resources/assets/js/dashboard/total-incomes.js'])
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
  <div class="col-12 col-md-6 mt-0">
    <div class="card p-3">
      <h5>Vencen hoy:</h5>
      <div class="row g-3 align-items-stretch">
        <!-- Tarjeta Factura Impaga -->
        <div class="col-12 col-md-6">
          <div class="card {{ $expenses['amount'] > 0 ? 'card-border-shadow-danger low-opacity-bg' : 'card-border-shadow-success' }} d-flex flex-row align-items-center p-3 h-100">
            <div class="expenses-card-content flex-grow-1">
              @if($expenses['amount'] > 0)
              <p class="m-0 text-danger bold">Factura Impaga</p>
              <p class="m-0">Vencen hoy {{ $expenses['amount'] }} facturas<br>formando un total de</p>
              <h5 class="m-0">${{ number_format($expenses['total'], 2) }}</h5>
              @else
              <p class="m-0 text-success bold">¡Felicidades!</p>
              <p class="m-0">No tienes facturas impagas que vencen hoy</p>
              @endif
            </div>
          </div>
        </div>

        <!-- Tarjeta Cobro -->
        <div class="col-12 col-md-6 mt-0">
          <div class="card card-border-shadow-success d-flex flex-row align-items-center p-3 h-100">
            <div class="expenses-card-content flex-grow-1">
              <p class="m-0 text-success bold">Último cobro realizado</p>
              @if(!empty($amountOfOrders['last_order']))
              <p class="m-0">
                {{ $amountOfOrders['last_order']['product_name'] }}
                @if($amountOfOrders['last_order']['other_products'] > 0)
                <br>+ {{ $amountOfOrders['last_order']['other_products'] }} producto(s) más
                @endif
              </p>
              <h5 class="m-0">${{ number_format($amountOfOrders['last_order']['total'], 2) }}</h5>
              @else
              <p class="m-0">No hay órdenes registradas</p>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-6 mt-0">
    <div class="card h-100
       @if($dailyBalance['balance'] > 0)
            bg-success bg-opacity-50 text-dark
        @elseif($dailyBalance['balance'] < 0)
            bg-danger bg-opacity-50 text-dark
        @else
            bg-white text-black
        @endif
    ">
      <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center">
        <div class="mb-3 mb-md-0">
          <p class="mb-1">Tu balance diario</p>
          <h3 class="m-0 ">
            <span class="text-dark">
              {{ $dailyBalance['balance'] > 0 ? '+' : ($dailyBalance['balance'] < 0 ? '-' : '') }}
              ${{ number_format(abs($dailyBalance['balance']), 2) }}
            </span>
          </h3>
        </div>
        <div class="text-end">
          <p class="m-0">Ingresos: <span class="fw-bold">${{ number_format($dailyBalance['income'], 2) }}</span></p>
          <p class="m-0">Egresos: <span class="fw-bold">${{ number_format($dailyBalance['expenses'], 2) }}</span></p>
          <p class="m-0">Total: <span class="fw-bold">
              {{ $dailyBalance['balance'] > 0 ? '+' : ($dailyBalance['balance'] < 0 ? '-' : '') }}
              ${{ number_format(abs($dailyBalance['balance']), 2) }}
            </span></p>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- Tarjetas segunda línea -->
<div class="row mt-3 g-3 align-items-stretch">
  <div class="col-12 col-lg-6">
    <div class="card h-100 p-3 justify-content-center text-center">
        <h5 class="mb-4">Conectá tus cuentas</h5>
        <div class="d-flex flex-wrap justify-content-center">
            @foreach(['pymo', 'mercadopago'] as $integration)
            <a href="{{ route('integrations.index') }}" class="me-3 mb-2" style="text-decoration: none;">
                <img src="{{ global_asset("assets/img/ux-new/integraciones/$integration.png") }}" alt="Logo {{ ucfirst($integration) }}" class="img-fluid" style="width: 70px; height: auto;">
            </a>
            @endforeach
        </div>
    </div>
  </div>


  <div class="col-12 col-md-6 col-lg-3">
    <div class="card h-100">
      <div class="card-body">
        <h5 class="d-block fw-medium mb-2">Ventas Realizadas</h5>
        <h5 class="mb-2 bold">{{ $amountOfOrders['orders'] }}</h5>
        @if($amountOfOrders['percentage'] > 0)
        <span class="badge bg-label-success mb-3">+{{ $amountOfOrders['percentage'] }}%</span>
        @elseif($amountOfOrders['percentage'] == 0)
        <span class="badge bg-label-info mb-3">0%</span>
        @else
        <span class="badge bg-label-danger mb-3">{{ $amountOfOrders['percentage'] }}%</span>
        @endif
        <small class="text-muted d-block">comparado al mes anterior</small>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-6 col-lg-3">
    <div class="card h-100">
      <div class="card-body">
        <h5 class="d-block fw-medium mb-2">Gastos Realizados</h5>
        <h5 class="mb-2 bold">${{ number_format($monthlyExpenses['total'], 2) }}</h5>
        @if($monthlyExpenses['percentage'] > 0)
        <span class="badge bg-label-danger mb-3">+{{ $monthlyExpenses['percentage'] }}%</span>
        @elseif($monthlyExpenses['percentage'] == 0)
        <span class="badge bg-label-info mb-3">0%</span>
        @else
        <span class="badge bg-label-success mb-3">{{ $monthlyExpenses['percentage'] }}%</span>
        @endif
        <small class="text-muted d-block">
          @if($monthlyExpenses['percentage'] > 0)
          más que el mes pasado
          @elseif($monthlyExpenses['percentage'] < 0)
            menos que el mes pasado
            @else
            igual que el mes pasado
            @endif
            </small>
      </div>
    </div>
  </div>

</div>


<!-- Tarjetas tercera línea -->
<div class="row mt-3 g-3">
  <!-- Tarjeta de productos más vendidos con col-lg-4 para mejor distribución en pantallas grandes -->
  <div class="col-12 col-md-6 col-lg-4">
    <div class="card h-100">
      <div class="card-header py-3">
        <h5 class="mb-0">Productos más vendidos</h5>
      </div>
      <div class="card-body d-flex flex-column p-0">
        <div class="report-list flex-grow-1">
          @foreach(array_slice($products, 0, 5) as $index => $product)
          <div class="report-list-item rounded-2 mb-3 p-2">
            <div class="d-flex align-items-start">
              <div class="report-list-icon shadow-sm me-2">
                <h4 class="m-0 p-0">{{ $index + 1 }}.</h4>
              </div>
              <div class="d-flex justify-content-between align-items-end w-100 flex-wrap gap-2">
                <div class="d-flex flex-column">
                  <span>{{ $product['name'] }}</span>
                  <h5 class="mb-0">${{ number_format($product['total_sales'], 2) }}</h5>
                </div>
                <div class="text-end">
                  <small class="text-success bold">{{ $product['quantity'] }}</small>
                  <small class="text-success">Unidades</small>
                </div>
              </div>
            </div>
          </div>
          @endforeach
        </div>
        <!-- Botón alineado al fondo de la card -->
        <div class="text-center mt-auto p-3">
          <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#topProductsModal">
            Ver Top 10
          </button>
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
          @php
          $months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
          $currentMonth = date('n') - 1; // 0-based index for array
          @endphp
          <button type="button" class="btn btn-sm btn-outline-primary text-muted dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            {{ $months[$currentMonth] }}
          </button>
          <ul class="dropdown-menu">
            @foreach($months as $index => $month)
            <li><a class="dropdown-item{{ $index == $currentMonth ? ' active' : '' }}" href="javascript:void(0);">{{ $month }}</a></li>
            @endforeach
          </ul>
        </div>
      </div>
      <div class="card-body">
        <div id="totalIncomeChart"></div>
      </div>
    </div>
  </div>


  {{-- <!-- Tarjetas cuarta línea -->
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
  </div> --}}


  <div class="modal fade" id="topProductsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Top 10 Productos más vendidos</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Producto</th>
                  <th>Unidades Vendidas</th>
                  <th>Total Ventas</th>
                </tr>
              </thead>
              <tbody>
                @foreach(array_slice($products, 0, 10) as $index => $product)
                <tr>
                  <td>{{ $index + 1 }}</td>
                  <td>{{ $product['name'] }}</td>
                  <td>{{ $product['quantity'] }}</td>
                  <td>${{ number_format($product['total_sales'], 2) }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


@endsection
