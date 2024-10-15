<div class="col-sm-6 col-lg-3 mb-4">
  <div class="card animated-card card-border-shadow-primary h-100">
    <div class="card-body">
      <div class="d-flex align-items-center mb-2 pb-1">
        <div class="avatar me-2">
          <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-check"></i></span>
        </div>
        <h4 class="ms-1 mb-0">{{ $expenses['total'] }}</h4>
      </div>
      <p class="mb-1 fw-medium me-1">Total de Gastos</p>
      <p class="mb-0"></p>
    </div>
  </div>
</div>

<div class="col-sm-6 col-lg-3 mb-4">
  <div class="card animated-card card-border-shadow-success h-100">
    <div class="card-body">
      <div class="d-flex align-items-center mb-2 pb-1">
        <div class="avatar me-2">
          <span class="avatar-initial rounded bg-label-success"><i class="bx bx-check-circle"></i></span>
        </div>
        <h4 class="ms-1 mb-0">{{ $expenses['paid'] }}</h4>
      </div>
      <p class="mb-1 fw-medium me-1">Gastos Pagados</p>
      <p class="mb-0"></p>
    </div>
  </div>
</div>

<div class="col-sm-6 col-lg-3 mb-4">
  <div class="card animated-card card-border-shadow-warning h-100">
    <div class="card-body">
      <div class="d-flex align-items-center mb-2 pb-1">
        <div class="avatar me-2">
          <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-time"></i></span>
        </div>
        <h4 class="ms-1 mb-0">{{ $expenses['partial'] }}</h4>
      </div>
      <p class="mb-1 fw-medium me-1">Gastos Parciales</p>
      <p class="mb-0"></p>
    </div>
  </div>
</div>

<div class="col-sm-6 col-lg-3 mb-4">
  <div class="card animated-card card-border-shadow-danger h-100">
    <div class="card-body">
      <div class="d-flex align-items-center mb-2 pb-1">
        <div class="avatar me-2">
          <span class="avatar-initial rounded bg-label-danger"><i class="bx bx-error-circle"></i></span>
        </div>
        <h4 class="ms-1 mb-0">{{ $expenses['unpaid'] }}</h4>
      </div>
      <p class="mb-1 fw-medium me-1">Gastos No Pagados</p>
      <p class="mb-0"></p>
    </div>
  </div>
</div>