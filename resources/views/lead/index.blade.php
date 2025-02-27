@extends('layouts/layoutMaster')

@section('title', 'CRM')

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
  var leads = @json($leads);
  var users = @json($users);
  var currentUserId = {{ auth()->id() }};
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@vite(['resources/assets/js/crm/app-lead-list.js'])
@vite(['resources/assets/js/crm/app-lead-details.js'])
@vite(['resources/assets/js/crm/app-lead-tasks.js'])
@vite(['resources/assets/js/crm/app-lead-files.js'])
@vite(['resources/assets/js/crm/app-lead-conversations.js'])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">CRM /</span> Tablero
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

<!-- Kanban tablero contenedor -->
<div class="kanban-container">
  <div class="row g-4">
    <!-- Columna nuevos -->
    <div class="col-12 col-md-6 col-lg-3">
      <div class="kanban-column card">
        <div class="card-header border-bottom sticky-top bg-white" style="border-top: 3px solid #696cff;">
          <h5 class="card-title mb-0">Nuevo</h5>
          <div class="d-flex align-items-center">
            <span class="badge bg-label-primary rounded-pill me-2">{{ $leads->where('category_id', '0')->count() }}</span>
            <button class="btn btn-icon btn-sm btn-primary add-lead-btn" data-category_id="0">
              <i class="bx bx-plus"></i>
            </button>
          </div>
        </div>
        <div class="card-body kanban-items pt-3" data-category="0">
          @foreach($leads->where('category_id', '0') as $lead)
          <div class="kanban-item card mb-3 cursor-move w-95" data-id="{{ $lead->id }}" data-position="{{ $lead->position }}" data-email="{{ $lead->email }}" data-phone="{{ $lead->phone }}">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="card-title mb-0">{{ $lead->name }}</h6>
                <div class="dropdown">
                  <button class="btn btn-icon btn-sm text-muted dropdown-toggle hide-arrow p-0" data-bs-toggle="dropdown">
                    <i class="bx bx-dots-vertical-rounded"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a class="dropdown-item view-lead-details" href="javascript:void(0);">
                        <i class="bx bx-info-circle me-1"></i>
                        Ver detalles
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item view-lead-conversations" href="javascript:void(0);" data-id="{{ $lead->id }}">
                        <i class="bx bx-chat me-1"></i>
                        Ver conversación
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item view-lead-files" href="javascript:void(0);">
                        <i class="bx bx-images me-1"></i>
                        Ver multimedia
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item text-danger delete-lead" href="javascript:void(0);">
                        <i class="bx bx-trash me-1"></i>
                        Eliminar
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
              @if($lead->email)
              <p class="card-text small text-muted mb-1">{{ $lead->email }}</p>
              @endif
              @if($lead->phone)
              <p class="card-text small text-muted mb-0">{{ $lead->phone }}</p>
              @endif
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>

    <!-- Columna contactado -->
    <div class="col-12 col-md-6 col-lg-3">
      <div class="kanban-column card">
        <div class="card-header border-bottom sticky-top bg-white" style="border-top: 3px solid #03c3ec;">
          <h5 class="card-title mb-0">Contactado</h5>
          <div class="d-flex align-items-center">
            <span class="badge bg-label-info rounded-pill me-2">{{ $leads->where('category_id', '1')->count() }}</span>
            <button class="btn btn-icon btn-sm btn-info add-lead-btn" data-category_id="1">
              <i class="bx bx-plus"></i>
            </button>
          </div>
        </div>
        <div class="card-body kanban-items pt-3" data-category="1">
          @foreach($leads->where('category_id', '1') as $lead)
          <div class="kanban-item card mb-3 cursor-move w-95" data-id="{{ $lead->id }}" data-position="{{ $lead->position }}" data-email="{{ $lead->email }}" data-phone="{{ $lead->phone }}">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="card-title mb-0">{{ $lead->name }}</h6>
                <div class="dropdown">
                  <button class="btn btn-icon btn-sm text-muted dropdown-toggle hide-arrow p-0" data-bs-toggle="dropdown">
                    <i class="bx bx-dots-vertical-rounded"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a class="dropdown-item view-lead-details" href="javascript:void(0);">
                        <i class="bx bx-info-circle me-1"></i>
                        Ver detalles
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item view-lead-conversations" href="javascript:void(0);" data-id="{{ $lead->id }}">
                        <i class="bx bx-chat me-1"></i>
                        Ver conversación
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item view-lead-files" href="javascript:void(0);">
                        <i class="bx bx-images me-1"></i>
                        Ver multimedia
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item text-danger delete-lead" href="javascript:void(0);">
                        <i class="bx bx-trash me-1"></i>
                        Eliminar
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
              @if($lead->email)
              <p class="card-text small text-muted mb-1">{{ $lead->email }}</p>
              @endif
              @if($lead->phone)
              <p class="card-text small text-muted mb-0">{{ $lead->phone }}</p>
              @endif
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>

    <!-- Propuesta enviada columna -->
    <div class="col-12 col-md-6 col-lg-3">
      <div class="kanban-column card">
        <div class="card-header border-bottom sticky-top bg-white" style="border-top: 3px solid #ffab00;">
          <h5 class="card-title mb-0">Propuesta enviada</h5>
          <div class="d-flex align-items-center">
            <span class="badge bg-label-warning rounded-pill me-2">{{ $leads->where('category_id', '2')->count() }}</span>
            <button class="btn btn-icon btn-sm btn-warning add-lead-btn" data-category_id="2">
              <i class="bx bx-plus"></i>
            </button>
          </div>
        </div>
        <div class="card-body kanban-items pt-3" data-category="2">
          @foreach($leads->where('category_id', '2') as $lead)
          <div class="kanban-item card mb-3 cursor-move w-95" data-id="{{ $lead->id }}" data-position="{{ $lead->position }}" data-email="{{ $lead->email }}" data-phone="{{ $lead->phone }}">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="card-title mb-0">{{ $lead->name }}</h6>
                <div class="dropdown">
                  <button class="btn btn-icon btn-sm text-muted dropdown-toggle hide-arrow p-0" data-bs-toggle="dropdown">
                    <i class="bx bx-dots-vertical-rounded"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a class="dropdown-item view-lead-details" href="javascript:void(0);">
                        <i class="bx bx-info-circle me-1"></i>
                        Ver detalles
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item view-lead-conversations" href="javascript:void(0);" data-id="{{ $lead->id }}">
                        <i class="bx bx-chat me-1"></i>
                        Ver conversación
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item view-lead-files" href="javascript:void(0);">
                        <i class="bx bx-images me-1"></i>
                        Ver multimedia
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item text-danger delete-lead" href="javascript:void(0);">
                        <i class="bx bx-trash me-1"></i>
                        Eliminar
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
              @if($lead->email)
              <p class="card-text small text-muted mb-1">{{ $lead->email }}</p>
              @endif
              @if($lead->phone)
              <p class="card-text small text-muted mb-0">{{ $lead->phone }}</p>
              @endif
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>

    <!-- Columna avanzado -->
    <div class="col-12 col-md-6 col-lg-3">
      <div class="kanban-column card">
        <div class="card-header border-bottom sticky-top bg-white" style="border-top: 3px solid #71dd37;">
          <h5 class="card-title mb-0">Avanzado</h5>
          <div class="d-flex align-items-center">
            <span class="badge bg-label-success rounded-pill me-2">{{ $leads->where('category_id', '3')->count() }}</span>
            <button class="btn btn-icon btn-sm btn-success add-lead-btn" data-category_id="3">
              <i class="bx bx-plus"></i>
            </button>
          </div>
        </div>
        <div class="card-body kanban-items pt-3" data-category="3">
          @foreach($leads->where('category_id', '3') as $lead)
          <div class="kanban-item card mb-3 cursor-move w-95" data-id="{{ $lead->id }}" data-position="{{ $lead->position }}" data-email="{{ $lead->email }}" data-phone="{{ $lead->phone }}">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="card-title mb-0">{{ $lead->name }}</h6>
                <div class="dropdown">
                  <button class="btn btn-icon btn-sm text-muted dropdown-toggle hide-arrow p-0" data-bs-toggle="dropdown">
                    <i class="bx bx-dots-vertical-rounded"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a class="dropdown-item view-lead-details" href="javascript:void(0);">
                        <i class="bx bx-info-circle me-1"></i>
                        Ver detalles
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item view-lead-conversations" href="javascript:void(0);" data-id="{{ $lead->id }}">
                        <i class="bx bx-chat me-1"></i>
                        Ver conversación
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item view-lead-files" href="javascript:void(0);">
                        <i class="bx bx-images me-1"></i>
                        Ver multimedia
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item text-danger delete-lead" href="javascript:void(0);">
                        <i class="bx bx-trash me-1"></i>
                        Eliminar
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
              @if($lead->email)
              <p class="card-text small text-muted mb-1">{{ $lead->email }}</p>
              @endif
              @if($lead->phone)
              <p class="card-text small text-muted mb-0">{{ $lead->phone }}</p>
              @endif
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  .kanban-container {
    overflow-x: auto;
    padding: 1rem;
  }

  .kanban-column {
    height: calc(100vh - 277px);
    display: flex;
    flex-direction: column;
  }

  .kanban-items {
    min-height: 100px;
    overflow-y: auto;
    flex: 1;
    padding: 0.5rem;
  }

  .cursor-move {
    cursor: move;
  }

  .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    z-index: 1;
  }

  .kanban-item {
    box-shadow: 0 1px 3px rgba(67, 89, 113, 0.15);
    border: 1px solid rgba(67, 89, 113, 0.1);
    width: 95%;
    margin-left: auto;
    margin-right: auto;
  }

  .card-body {
    padding: 0.75rem 1rem;
  }

  .card-title {
    font-size: 0.9rem;
    line-height: 1.2;
    margin-right: 0.5rem;
  }

  .dropdown {
    margin-top: -2px;
  }

  .dropdown-toggle::after {
    display: none;
  }

  .card-text {
    margin-bottom: 0.25rem;
    line-height: 1.3;
  }

  .card-text:last-child {
    margin-bottom: 0;
  }

  .bx-dots-vertical-rounded {
    font-size: 1.2rem;
    line-height: 1;
  }

  .add-lead-btn {
    width: 24px;
    height: 24px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .add-lead-btn i {
    font-size: 1rem;
  }
</style>

@include('lead.partials.conversation')
@include('lead.partials.company')
@include('lead.partials.files')
@include('lead.partials.tasks')
@include('lead.partials.details')
@endsection