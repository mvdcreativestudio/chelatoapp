<div class="col-12 mb-4">
  <div class="card" data-aos="flip-right">
    <div class="row row-bordered g-0">
      <div class="col-md-8">
        <div class="card-header">
          <h5 class="card-title mb-0">Gastos totales</h5>
          <small class="card-subtitle">Reporte anual</small>
        </div>
        <div class="card-body">
          <div id="totalExpensesChart"></div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card-header d-flex justify-content-between">
          <div>
            <h5 class="card-title mb-0">Reporte</h5>
            <small class="card-subtitle">Promedio mensual histórico: {{ $settings->currency_symbol }}{{ $averageMonthlyExpenses }}</small>
          </div>
          <div class="dropdown">
            <button class="btn p-0" type="button" id="totalExpenses" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="bx bx-dots-vertical-rounded"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="totalExpenses">
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
                  <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-check-circle"></i></span>
                </div>
                <div class="d-flex justify-content-between align-items-end w-100 flex-wrap gap-2">
                  <div class="d-flex flex-column">
                    <span>Pagados</span>
                    <h5 class="mb-0">{{ $settings->currency_symbol }}{{ $expenses['paid'] }}</h5>
                  </div>
                </div>
              </div>
            </div>
            <div class="report-list-item rounded-2 mb-3">
              <div class="d-flex align-items-start">
                <div class="avatar me-2">
                  <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-time"></i></span>
                </div>
                <div class="d-flex justify-content-between align-items-end w-100 flex-wrap gap-2">
                  <div class="d-flex flex-column">
                    <span>Parciales</span>
                    <h5 class="mb-0">{{ $settings->currency_symbol }}{{ $expenses['partial'] }}</h5>
                  </div>
                </div>
              </div>
            </div>
            <div class="report-list-item rounded-2 mb-3">
              <div class="d-flex align-items-start">
                <div class="avatar me-2">
                  <span class="avatar-initial rounded bg-label-danger"><i class="bx bx-error-circle"></i></span>
                </div>
                <div class="d-flex justify-content-between align-items-end w-100 flex-wrap gap-2">
                  <div class="d-flex flex-column">
                    <span>No Pagados</span>
                    <h5 class="mb-0">{{ $settings->currency_symbol }}{{ $expenses['unpaid'] }}</h5>
                  </div>
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
                    <h5 class="mb-0">{{ $settings->currency_symbol }}{{ $expenses['total'] }}</h5>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>