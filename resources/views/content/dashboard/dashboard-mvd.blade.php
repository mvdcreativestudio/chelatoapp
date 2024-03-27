@extends('layouts/layoutMaster')

@section('title', 'Dashboard')

@section('vendor-style')
@vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
@endsection

@section('page-style')
@vite('resources/assets/vendor/scss/pages/card-analytics.scss')
@endsection

@section('vendor-script')
@vite('resources/assets/vendor/libs/apex-charts/apexcharts.js',)
@endsection

@section('page-script')
@vite(['resources/assets/js/cards-statistics.js'])
@vite(['resources/assets/js/ui-cards-analytics.js'])

@endsection


@section('content')

@if (session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  {{ session('error') }}
</div>
@endif

<div class="row">
{{--
  <div class="col-12 mb-4 text-end">
    <div class="text-light small fw-medium">Filtrar período de tiempo</div>
    <div>
      <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
        <input type="radio" class="btn-check" name="btnradio" id="btnradio1" checked>
        <label class="btn btn-outline-primary" for="btnradio1">Este año</label>
        <input type="radio" class="btn-check" name="btnradio" id="btnradio2">
        <label class="btn btn-outline-primary" for="btnradio2">Este mes</label>
        <input type="radio" class="btn-check" name="btnradio" id="btnradio3">
        <label class="btn btn-outline-primary" for="btnradio3">Esta semana</label>
      </div>
    </div>
  </div> --}}

  <!-- single card  -->
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-widget-separator-wrapper">
        <div class="card-body card-widget-separator">
          <div class="row gy-4 gy-sm-1">
            <div class="col-sm-6 col-lg-3">
              <div class="d-flex justify-content-between align-items-start card-widget-1 border-end pb-3 pb-sm-0">
                <div>
                  <h3 class="mb-1">7</h3>
                  <p class="mb-0">Locales</p>
                </div>
                <span class="badge bg-label-secondary rounded p-2 me-sm-4">
                  <i class="bx bx-user bx-sm"></i>
                </span>
              </div>
              <hr class="d-none d-sm-block d-lg-none me-4">
            </div>
            <div class="col-sm-6 col-lg-3">
              <div class="d-flex justify-content-between align-items-start card-widget-2 border-end pb-3 pb-sm-0">
                <div>
                  <h3 class="mb-1">1492</h3>
                  <p class="mb-0">Clientes registrados</p>
                </div>
                <span class="badge bg-label-secondary rounded p-2 me-lg-4">
                  <i class="bx bx-file bx-sm"></i>
                </span>
              </div>
              <hr class="d-none d-sm-block d-lg-none">
            </div>
            <div class="col-sm-6 col-lg-3">
              <div class="d-flex justify-content-between align-items-start border-end pb-3 pb-sm-0 card-widget-3">
                <div>
                  <h3 class="mb-1">$24.600</h3>
                  <p class="mb-0">Ingresos este mes</p>
                </div>
                <span class="badge bg-label-secondary rounded p-2 me-sm-4">
                  <i class="bx bx-check-double bx-sm"></i>
                </span>
              </div>
            </div>
            <div class="col-sm-6 col-lg-3">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <h3 class="mb-1">$1.498</h3>
                  <p class="mb-0">Ingresos perdidos este mes</p>
                </div>
                <span class="badge bg-label-secondary rounded p-2">
                  <i class="bx bx-error-circle bx-sm"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- /single card  -->

  <!-- Card Border Shadow -->
  <div class="col-sm-6 col-lg-3 mb-4">
    <div class="card card-border-shadow-primary h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2 pb-1">
          <div class="avatar me-2">
            <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-check"></i></span>
          </div>
          <h4 class="ms-1 mb-0">42</h4>
        </div>
        <p class="mb-1 fw-medium me-1">Pedidos completados</p>
        <p class="mb-0">
          <span class="fw-medium me-1 text-success">+18.2%</span>
        </p>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3 mb-4">
    <div class="card card-border-shadow-warning h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2 pb-1">
          <div class="avatar me-2">
            <span class="avatar-initial rounded bg-label-warning"><i class='bx bx-time'></i></span>
          </div>
          <h4 class="ms-1 mb-0">8</h4>
        </div>
        <p class="mb-1 fw-medium me-1">Pedidos pendientes</p>
        <p class="mb-0">
          <span class="fw-medium me-1 text-danger">-8.7%</span>
        </p>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3 mb-4">
    <div class="card card-border-shadow-danger h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2 pb-1">
          <div class="avatar me-2">
            <span class="avatar-initial rounded bg-label-danger"><i class='bx bx-error-circle'></i></span>
          </div>
          <h4 class="ms-1 mb-0">2</h4>
        </div>
        <p class="mb-1">Pedidos cancelados</p>
        <p class="mb-0">
          <span class="fw-medium me-1 text-success">+4.3%</span>
        </p>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3 mb-4">
    <div class="card card-border-shadow-info h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2 pb-1">
          <div class="avatar me-2">
            <span class="avatar-initial rounded bg-label-info"><i class='bx bx-line-chart'></i></span>
          </div>
          <h4 class="ms-1 mb-0">$847</h4>
        </div>
        <p class="mb-1">Ticket medio</p>
        <p class="mb-0">
          <span class="fw-medium me-1 text-danger">-2.5%</span>
        </p>
      </div>
    </div>
  </div>


  <!-- Total Income -->
  <div class="col-12 mb-4">
    <div class="card">
      <div class="row row-bordered g-0">
        <div class="col-md-8">
          <div class="card-header">
            <h5 class="card-title mb-0">Ingresos totales</h5>
            <small class="card-subtitle">Reporte anual</small>
          </div>
          <div class="card-body">
            <div id="totalIncomeChart"></div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card-header d-flex justify-content-between">
            <div>
              <h5 class="card-title mb-0">Reporte</h5>
              <small class="card-subtitle">Media mensual: $26.398</small>
            </div>
            <div class="dropdown">
              <button class="btn p-0" type="button" id="totalIncome" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="bx bx-dots-vertical-rounded"></i>
              </button>
              <div class="dropdown-menu dropdown-menu-end" aria-labelledby="totalIncome">
                <a class="dropdown-item" href="javascript:void(0);">Última semana</a>
                <a class="dropdown-item" href="javascript:void(0);">Último mes</a>
                <a class="dropdown-item" href="javascript:void(0);">Último año</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="report-list">
              <div class="report-list-item rounded-2 mb-3">
                <div class="d-flex align-items-start">
                  <div class="avatar me-2">
                    <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-store"></i></span>
                  </div>
                  <div class="d-flex justify-content-between align-items-end w-100 flex-wrap gap-2">
                    <div class="d-flex flex-column">
                      <span>Físico</span>
                      <h5 class="mb-0">$42.845</h5>
                    </div>
                    <small class="text-success">+2.34%</small>
                  </div>
                </div>
              </div>
              <div class="report-list-item rounded-2 mb-3">
                <div class="d-flex align-items-start">
                  <div class="avatar me-2">
                    <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-laptop"></i></span>
                  </div>
                  <div class="d-flex justify-content-between align-items-end w-100 flex-wrap gap-2">
                    <div class="d-flex flex-column">
                      <span>E-Commerce</span>
                      <h5 class="mb-0">$74.875</h5>
                    </div>
                    <small class="text-danger">-1.15%</small>
                  </div>
                </div>
              </div>
              <div class="report-list-item rounded-2">
                <div class="d-flex align-items-start">
                  <div class="avatar me-2">
                    <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-shape-square"></i></span>
                  </div>
                  <div class="d-flex justify-content-between align-items-end w-100 flex-wrap gap-2">
                    <div class="d-flex flex-column">
                      <span>Total</span>
                      <h5 class="mb-0">$117.720</h5>
                    </div>
                    <small class="text-success">+1.35%</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--/ Total Income -->
  </div>
  <!--/ Total Income -->

  <!-- pill table -->
  <div class="col-8 mb-4 order-2 order-xl-0">
    <div class="card h-100 text-center">
      <div class="card-header">
        <ul class="nav nav-pills nav- card-header-pills" role="tablist">
          <li class="nav-item">
            <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-browser" aria-controls="navs-pills-browser" aria-selected="true">Locales</button>
          </li>
          <li class="nav-item">
            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-os" aria-controls="navs-pills-os" aria-selected="false">Productos</button>
          </li>
          <li class="nav-item">
            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-country" aria-controls="navs-pills-country" aria-selected="false">Categorías</button>
          </li>
        </ul>
      </div>
      <div class="tab-content pt-0">
        <div class="tab-pane fade show active" id="navs-pills-browser" role="tabpanel">
          <div class="table-responsive text-start text-nowrap">
            <table class="table table-borderless">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Local</th>
                  <th>Ventas</th>
                  <th class="w-50">Porcentaje del total</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>1</td>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="avatar avatar-xm me-2">
                        <span class="avatar-initial rounded-circle bg-label-primary">po</span>
                      </div>
                      <span>Pocitos</span>
                    </div>
                  </td>
                  <td>8.92k</td>
                  <td>
                    <div class="d-flex justify-content-between align-items-center gap-3">
                      <div class="progress w-100" style="height:10px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 64.75%" aria-valuenow="64.75" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                      <small class="fw-medium">64.75%</small>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td>2</td>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="avatar avatar-m me-2">
                        <span class="avatar-initial rounded-circle bg-label-primary">ca</span>
                      </div>
                      <span>Carrasco</span>
                    </div>
                  </td>
                  <td>1.29k</td>
                  <td>
                    <div class="d-flex justify-content-between align-items-center gap-3">
                      <div class="progress w-100" style="height:10px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 18.43%" aria-valuenow="18.43" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                      <small class="fw-medium">18.43%</small>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td>3</td>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="avatar avatar-m me-2">
                        <span class="avatar-initial rounded-circle bg-label-primary">tc</span>
                      </div>
                      <span>Tres Cruces</span>
                    </div>
                  </td>
                  <td>328</td>
                  <td>
                    <div class="d-flex justify-content-between align-items-center gap-3">
                      <div class="progress w-100" style="height:10px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: 8.37%" aria-valuenow="8.37" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                      <small class="fw-medium">8.37%</small>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td>4</td>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="avatar avatar-m me-2">
                        <span class="avatar-initial rounded-circle bg-label-primary">pc</span>
                      </div>
                      <span>Punta Carretas</span>
                    </div>
                  </td>
                  <td>142</td>
                  <td>
                    <div class="d-flex justify-content-between align-items-center gap-3">
                      <div class="progress w-100" style="height:10px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 6.12%" aria-valuenow="6.12" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                      <small class="fw-medium">6.12%</small>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td>5</td>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="avatar avatar-m me-2">
                        <span class="avatar-initial rounded-circle bg-label-primary">pi</span>
                      </div>
                      <span>Piriapolis</span>
                    </div>
                  </td>
                  <td>82</td>
                  <td>
                    <div class="d-flex justify-content-between align-items-center gap-3">
                      <div class="progress w-100" style="height:10px;">
                        <div class="progress-bar bg-danger" role="progressbar" style="width: 1.94%" aria-valuenow="1.94" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                      <small class="fw-medium">1.94%</small>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="tab-pane fade" id="navs-pills-os" role="tabpanel">
          <div class="table-responsive text-start text-nowrap">
            <table class="table table-borderless">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Producto</th>
                  <th>Ventas</th>
                  <th class="w-50">Porcentaje del total</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>1</td>
                  <td>
                    <div class="d-flex align-items-center">
                      <span>Helado 2 Litros</span>
                    </div>
                  </td>
                  <td>875.24k</td>
                  <td>
                    <div class="d-flex justify-content-between align-items-center gap-3">
                      <div class="progress w-100" style="height:10px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 61.50%" aria-valuenow="61.50" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                      <small class="fw-medium">61.50%</small>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td>2</td>
                  <td>
                    <div class="d-flex align-items-center">
                      <span>Paleta Helada Chanchito</span>
                    </div>
                  </td>
                  <td>89.68k</td>
                  <td>
                    <div class="d-flex justify-content-between align-items-center gap-3">
                      <div class="progress w-100" style="height:10px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 16.67%" aria-valuenow="16.67" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                      <small class="fw-medium">16.67%</small>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td>3</td>
                  <td>
                    <div class="d-flex align-items-center">
                      <span>Helado 1/2 Litro</span>
                    </div>
                  </td>
                  <td>37.68k</td>
                  <td>
                    <div class="d-flex justify-content-between align-items-center gap-3">
                      <div class="progress w-100" style="height:10px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: 12.82%" aria-valuenow="12.82" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                      <small class="fw-medium">12.82%</small>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td>4</td>
                  <td>
                    <div class="d-flex align-items-center">
                      <span>Paleta Helada Oreo</span>
                    </div>
                  </td>
                  <td>8.34k</td>
                  <td>
                    <div class="d-flex justify-content-between align-items-center gap-3">
                      <div class="progress w-100" style="height:10px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 6.25%" aria-valuenow="6.25" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                      <small class="fw-medium">6.25%</small>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td>5</td>
                  <td>
                    <div class="d-flex align-items-center">
                      <span>Helado 1 Litro</span>
                    </div>
                  </td>
                  <td>2.25k</td>
                  <td>
                    <div class="d-flex justify-content-between align-items-center gap-3">
                      <div class="progress w-100" style="height:10px;">
                        <div class="progress-bar bg-danger" role="progressbar" style="width: 2.76%" aria-valuenow="2.76" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                      <small class="fw-medium">2.76%</small>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="tab-pane fade" id="navs-pills-country" role="tabpanel">
          <div class="table-responsive text-start text-nowrap">
            <table class="table table-borderless">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Categoría</th>
                  <th>Ventas</th>
                  <th class="w-50">Porcentaje del total</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>1</td>
                  <td>
                    <div class="d-flex align-items-center">
                      <span>Helados</span>
                    </div>
                  </td>
                  <td>87.24k</td>
                  <td>
                    <div class="d-flex justify-content-between align-items-center gap-3">
                      <div class="progress w-100" style="height:10px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 38.12%" aria-valuenow="38.12" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                      <small class="fw-medium">38.12%</small>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td>2</td>
                  <td>
                    <div class="d-flex align-items-center">
                      <span>Paletas</span>
                    </div>
                  </td>
                  <td>42.68k</td>
                  <td>
                    <div class="d-flex justify-content-between align-items-center gap-3">
                      <div class="progress w-100" style="height:10px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 28.23%" aria-valuenow="28.23" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                      <small class="fw-medium">28.23%</small>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td>3</td>
                  <td>
                    <div class="d-flex align-items-center">
                      <span>Postres</span>
                    </div>
                  </td>
                  <td>12.58k</td>
                  <td>
                    <div class="d-flex justify-content-between align-items-center gap-3">
                      <div class="progress w-100" style="height:10px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: 14.82%" aria-valuenow="14.82" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                      <small class="fw-medium">14.82%</small>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Reasons for delivery exceptions -->
  <div class="col-md-6 col-xxl-4 mb-4 order-4">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div class="card-title mb-0">
          <h5 class="m-0 me-2">Ventas por local</h5>
        </div>
        <div class="dropdown">
          <button class="btn p-0" type="button" id="deliveryExceptions" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="bx bx-dots-vertical-rounded"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="deliveryExceptions">
            <a class="dropdown-item" href="javascript:void(0);">Recargar</a>
            <a class="dropdown-item" href="javascript:void(0);">Compartir</a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div id="deliveryExceptionsChart"></div>
      </div>
    </div>
  </div>
  <!--/ Reasons for delivery exceptions -->

  <!--/ pill table -->


</div>
@endsection
