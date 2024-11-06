@extends('layouts/layoutMaster')

@section('title', 'Elaboraciones')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/select2/select2.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/assets/vendor/libs/select2/select2.js'
])
@endsection

@section('page-script')
<script type="text/javascript">
  window.packagings = @json($packagings);
  window.csrfToken = "{{ csrf_token() }}";
</script>
@vite(['resources/assets/js/app-packagings-list.js'])
@endsection

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

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Producción /</span> Envasado
</h4>

<div class="card">
  <div class="card-body">

    <div class="card mb-4">
      <div class="card-widget-separator-wrapper">
        <div class="card-body card-widget-separator">
          <div class="row gy-4 gy-sm-1">
            <div class="col-sm-6 col-lg-4">
              <div class="d-flex justify-content-between align-items-start card-widget-1 border-end pb-3 pb-sm-0">
                <div>
                  <h6 class="mb-2">Total de envasados</h6>
                  <h4 class="mb-2">{{ $packagings->count() }}</h4>
                  <p class="mb-0"><span class="text-muted me-2">Total</span></p>
                </div>
                <div class="avatar me-sm-4">
                  <span class="avatar-initial rounded bg-label-secondary">
                    <i class="bx bx-list-ul bx-sm"></i>
                  </span>
                </div>
              </div>
              <hr class="d-none d-sm-block d-lg-none me-4">
            </div>
            <div class="col-sm-6 col-lg-4">
              <div class="d-flex justify-content-between align-items-start card-widget-2 border-end pb-3 pb-sm-0">
                <div>
                  <h6 class="mb-2">Envasados recientes</h6>
                  <h4 class="mb-2">{{ $packagings->where('created_at', '>=', now()->subMonth())->count() }}</h4>
                  <p class="mb-0"><span class="text-muted me-2">Último mes</span></p>
                </div>
                <div class="avatar me-lg-4">
                  <span class="avatar-initial rounded bg-label-secondary">
                    <i class="bx bx-calendar bx-sm"></i>
                  </span>
                </div>
              </div>
              <hr class="d-none d-sm-block d-lg-none">
            </div>
            <div class="col-sm-12 col-lg-4">
              <div class="d-flex justify-content-between align-items-start pb-3 pb-sm-0 card-widget-3">
                <div>
                  <h6 class="mb-2">Envasados del día</h6>
                  <h4 class="mb-2">{{ $packagings->where('created_at', '>=', now()->startOfDay())->count() }}</h4>
                  <p class="mb-0 text-muted">Hoy</p>
                </div>
                <div class="avatar me-sm-4">
                  <span class="avatar-initial rounded bg-label-secondary">
                    <i class="bx bx-time-five bx-sm"></i>
                  </span>
                </div>
              </div>
              <hr class="d-none d-sm-block d-lg-none">
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="d-flex justify-content-end mb-4">
      <button type="button" id="openProductionModal" class="btn btn-primary" data-toggle="modal" data-target="#productionModal">
        Envasar Materia Prima
      </button>
      <button class="btn btn-primary ms-2" id="loadPackagesButton">
        Stock Envase
      </button>
    </div>

    <div class="container mt-5">
      <div class="row g-4">
        @foreach($packagings as $packaging)
        <div class="col-md-6">
          <div class="card h-100 border-0 shadow-sm hover-shadow transition">
            <div class="card-body p-4 position-relative">
              <h5 class="card-title text-primary mb-3">Packaging #{{ $packaging->id }}</h5>
              <ul class="list-unstyled mb-0">
                <li class="mb-2">
                  <i class="fas fa-box text-secondary me-2"></i>
                  <strong>Cantidad envasada:</strong> {{ $packaging->quantity_packaged }}
                </li>
                <li class="mb-2">
                  <i class="fas fa-industry text-secondary me-2"></i>
                  <strong>De la producción </strong> #{{ $packaging->id }} - {{ $packaging->formula_name }}
                </li>
                <li>
                  <i class="fas fa-archive text-secondary me-2"></i>
                  <strong>En el envase:</strong> {{ $packaging->package_name }}
                </li>
              </ul>
              <button class="btn btn-outline-danger btn-delete-packaging position-absolute" data-id="{{ $packaging->id }}">
                <i class="fas fa-trash-alt fa"></i>
              </button>
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </div>


    <!-- Modal de Bootstrap -->
    <div class="modal fade" id="productionModal" tabindex="-1" role="dialog" aria-labelledby="productionModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="productionModalLabel">Iniciar Envasado</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </button>
          </div>
          <div class="modal-body">
            <form id="productionForm">
              <label for="bulk_production">Producciones a envasar:</label>
              <select id="bulk_production" name="bulk_production" class="form-control">
              </select>
              <label for="package_id">Envase:</label>
              <select id="package_id" name="package_id" class="form-control">
              </select>
              <label for="label_id">Etiqueta:</label>
              <select id="label_id" name="label_id" class="form-control">
              </select>
              <label for="tap_id">Tapa:</label>
              <select id="tap_id" name="tap_id" class="form-control">
              </select>
              <label for="quantity_packaged">Cantidad a envasar:</label>
              <input type="number" id="quantity_packaged" name="quantity_packaged" class="form-control" placeholder="Ingrese una cantidad a envasar" required />
            </form>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary" id="submitProductionForm">Iniciar Envasado</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<style>
  .card {
    overflow: hidden;
  }

  .btn-delete-packaging {
    top: 50%;
    right: 1rem;
    transform: translateY(5%);
    padding: 0.75rem;
    line-height: 1;
    border-radius: 50%;
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .hover-shadow:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
  }
</style>


@endsection