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
  // Asegúrate de que no tenga una barra al final
  window.baseUrl = "{{ rtrim(url('/'), '/') }}";
  window.csrfToken = "{{ csrf_token() }}";
  var leads = @json($leads);
  var users = @json($users);
  var currentUserId = {{ auth()->id() }};
  
  // Debug
  console.log('Base URL:', window.baseUrl);
  console.log('CSRF Token:', window.csrfToken);
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@vite(['resources/assets/js/crm/app-lead-list.js'])
@vite(['resources/assets/js/crm/app-lead-details.js'])
@vite(['resources/assets/js/crm/app-lead-tasks.js'])
@vite(['resources/assets/js/crm/app-lead-files.js'])
@vite(['resources/assets/js/crm/app-lead-conversations.js'])
@vite(['resources/assets/js/crm/app-lead-columns.js'])
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
<div class="kanban-outer-container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Tablero CRM</h4>
    <button class="btn btn-primary" id="add-column-btn">
      <i class="bx bx-plus"></i> Nueva Columna
    </button>
  </div>
  
  <div class="kanban-scroll-container">
    <div class="kanban-columns-wrapper" id="kanban-columns">
      @foreach($categories as $category)
      <div class="kanban-column-container">
        <div class="kanban-column card">
          <div class="card-header border-bottom sticky-top bg-white" style="border-top: 3px solid {{ $category->color }};">
            <div class="d-flex justify-content-between align-items-center w-100">
              <h5 class="card-title mb-0">{{ $category->name }}</h5>

              <div class="d-flex align-items-center controls-container">
                <span class="badge bg-label-primary rounded-pill me-2">{{ $leads->where('category_id', $category->id)->count() }}</span>
             
                <!-- Restaurar el botón de "+" para agregar leads directamente a cada columna -->
                <button class="btn btn-icon btn-sm btn-primary add-lead-btn ms-2" data-category_id="{{ $category->id }}">
                  <i class="bx bx-plus"></i>
                </button>

                <div class="dropdown ms-2">
                  <button class="btn btn-icon btn-sm text-muted dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                    <i class="bx bx-dots-vertical-rounded"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a class="dropdown-item edit-column" href="javascript:void(0);" data-id="{{ $category->id }}">
                        <i class="bx bx-edit me-1"></i> Editar
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item text-danger delete-column" href="javascript:void(0);" data-id="{{ $category->id }}">
                        <i class="bx bx-trash me-1"></i> Eliminar
                      </a>
                    </li>
                  </ul>
                </div>

              </div>
            </div>
          </div>

          <div class="card-body kanban-items pt-3" data-category="{{ $category->id }}">
            @foreach($leads->where('category_id', $category->id) as $lead)
            <!-- Lead item template restaurado del old_index.blade.php -->
            <div class="kanban-item card mb-3 cursor-move" data-id="{{ $lead->id }}" data-position="{{ $lead->position }}" data-email="{{ $lead->email }}" data-phone="{{ $lead->phone }}">
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
      @endforeach
    </div>
  </div>
</div>

<!-- Modal para crear/editar columna -->
<div class="modal fade" id="columnModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="columnModalTitle">Nueva Columna</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="columnForm">
          @csrf <!-- Asegúrate de incluir el token CSRF -->
          <input type="hidden" id="column-id">
          <div class="mb-3">
            <label class="form-label">Nombre <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="column-name" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Color</label>
            
            <div class="color-picker-container mb-2">
              <div class="d-flex flex-wrap gap-2 mb-2">
                <!-- Colores primarios -->
                <div class="color-option" style="background-color: #0d6efd" data-color="#0d6efd"></div>
                <div class="color-option" style="background-color: #0dcaf0" data-color="#0dcaf0"></div>                
                <div class="color-option" style="background-color: #20c997" data-color="#20c997"></div>
                <div class="color-option" style="background-color: #198754" data-color="#198754"></div>                
                <div class="color-option" style="background-color: #ffc107" data-color="#ffc107"></div>
                <div class="color-option" style="background-color: #fd7e14" data-color="#fd7e14"></div>
                <div class="color-option" style="background-color: #dc3545" data-color="#dc3545"></div>
                <div class="color-option" style="background-color: #d63384" data-color="#d63384"></div>
                <div class="color-option" style="background-color: #696cff" data-color="#696cff"></div>
                <div class="color-option" style="background-color: #6f42c1" data-color="#6f42c1"></div>
                <div class="color-option" style="background-color: #6c757d" data-color="#6c757d"></div>
              </div>
              
              <button type="button" class="btn btn-outline-secondary btn-sm" id="more-colors-btn">
                Más colores <i class="bx bx-palette"></i>
              </button>
              
              <!-- Color picker oculto inicialmente -->
              <div class="mt-2" id="custom-color-picker" style="display: none;">
                <input type="color" class="form-control" id="column-color" value="#0d6efd" style="height: 40px; cursor: pointer;">
              </div>
            </div>
            
            <div class="selected-color-preview mt-2 d-flex align-items-center">
              <span class="me-2">Color seleccionado:</span>
              <div id="selected-color-box" style="width: 24px; height: 24px; border-radius: 4px; background-color: #0d6efd; border: 1px solid #dee2e6;"></div>
              <span id="selected-color-hex" class="ms-2">#0d6efd</span>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="save-column">Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal detalles-->
<div class="modal fade" id="leadModal" tabindex="-1" aria-labelledby="leadModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title text-white" id="leadModalLabel"></h5>
        <div class="d-flex align-items-center gap-2">
          <button type="button" 
                  class="btn btn-light btn-sm btn-client" 
                  id="clientButton">
            <i class='bx bx-user-plus'></i>
            <span class="ms-1">Convertir a Cliente</span>
          </button>
          <button type="button" 
                  class="btn btn-light btn-sm btn-company" 
                  id="companyButton">
            <i class='bx bx-buildings'></i>
            <span class="ms-1">Datos Compañía</span>
          </button>
          <button type="button" 
                  class="btn-close btn-close-white" 
                  data-bs-dismiss="modal" 
                  aria-label="Close"></button>
        </div>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label text-muted">Usuario Creador</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="bx bx-user"></i></span>
                <input type="text" class="form-control" id="creator_name" readonly>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label text-muted">Tienda</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="bx bx-store"></i></span>
                <input type="text" class="form-control" id="store_name" readonly>
              </div>
            </div>
          </div>
        </div>
        <form id="leadForm">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Tipo de Lead <span class="text-danger">*</span></label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="bx bx-user-circle"></i></span>
                <select class="form-select" id="type" name="type" required>
                  <option value="individual">Individual</option>
                  <option value="company">Compañía</option>
                  <option value="no-client">No Cliente</option>
                </select>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Nombre <span class="text-danger">*</span></label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="bx bx-user"></i></span>
                <input type="text" class="form-control" id="name" name="name" placeholder="Ingrese el nombre" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Email</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                <input type="email" class="form-control" id="email" name="email" placeholder="ejemplo@email.com">
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Teléfono</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="bx bx-phone"></i></span>
                <input type="tel" class="form-control" id="phone" name="phone" placeholder="(123) 456-7890">
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Descripción</label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="bx bx-comment-detail"></i></span>
              <textarea class="form-control" id="description" name="description" rows="3" placeholder="Ingrese una descripción"></textarea>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Monto</label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="bx bx-dollar"></i></span>
              <input type="number" class="form-control" id="amount_of_money" name="amount_of_money" placeholder="0.00" step="0.01">
            </div>
          </div>
          <input type="hidden" id="leadId" value="">
          <input type="hidden" id="category_id" name="category_id">
          <input type="hidden" id="position" name="position">
        </form>

        <!-- Sección de Asignaciones -->
        <div class="mt-4">
          <h6 class="mb-3">Usuarios Asignados</h6>
          <div class="mb-3">
            <div class="input-group">
              <select class="form-select" id="user-select">
                @foreach($users as $user)
                  <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
              </select>
              <button class="btn btn-primary" id="add-assignment-btn" data-lead-id="">
                <i class="bx bx-plus"></i> Asignar
              </button>
            </div>
          </div>
          <div class="assignments-list">
            <!-- Las asignaciones se agregarán aquí dinámicamente -->
          </div>
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="bx bx-x me-1"></i>
          Cancelar
        </button>
        <button type="button" class="btn btn-primary" id="save-lead">
          <i class="bx bx-check me-1"></i>
          Guardar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal de Tareas -->
<div class="modal fade" id="tasksModal" tabindex="-1" aria-labelledby="tasksModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title text-white" id="tasksModalLabel">
          <i class="bx bx-task me-2"></i>
          Tareas del Lead
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <div class="card shadow-none border-0">
          <div class="card-header bg-transparent py-3">
            <div class="lead-info">
              <h6 class="lead-name fw-semibold mb-2"></h6>
              <div class="d-flex align-items-center text-muted small">
                <i class="bx bx-envelope me-2"></i>
                <span class="lead-email me-3"></span>
                <i class="bx bx-phone ms-2 me-2"></i>
                <span class="lead-phone"></span>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h6 class="text-primary mb-0">
                <i class="bx bx-list-check me-2"></i>
                Lista de Tareas
              </h6>
              <button type="button" class="btn btn-primary btn-sm rounded-pill" id="add-task-btn">
                <i class="bx bx-plus me-1"></i>
                Agregar Tarea
              </button>
            </div>
            <div class="tasks-list">
              <!-- Las tareas se cargarán dinámicamente aquí -->
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="bx bx-x me-1"></i>
          Cerrar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal de Archivos Multimedia -->
<div class="modal fade" id="filesModal" tabindex="-1" aria-labelledby="filesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="filesModalLabel">
                    <i class="bx bx-images me-2"></i>
                    Archivos Multimedia del Lead
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start mb-3">
                    <div class="lead-info mb-3 mb-md-0">
                        <h6 class="lead-name fw-semibold mb-2"></h6>
                        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center text-muted small">
                            <div class="me-0 me-sm-3 mb-1 mb-sm-0">
                                <i class="bx bx-envelope me-2"></i>
                                <span class="lead-email"></span>
                            </div>
                            <div>
                                <i class="bx bx-phone me-2"></i>
                                <span class="lead-phone"></span>
                            </div>
                        </div>
                    </div>
                    <div class="file-upload">
                        <label for="fileInput" class="btn btn-primary">
                            <i class="bx bx-upload me-2"></i>
                            Cargar Archivo
                        </label>
                        <input type="file" id="fileInput" class="d-none">
                    </div>
                </div>
                <div id="filesList">
                    <!-- Los archivos se organizarán en filas aquí -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Compañía -->
<div class="modal fade" id="companyModal" tabindex="-1" aria-labelledby="companyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="companyModalLabel">
                    <i class="bx bx-buildings me-2"></i>
                    Información de la Compañía
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="companyForm">
                    <div class="mb-3">
                        <label class="form-label">Nombre de la Compañía</label>
                        <input type="text" class="form-control" id="company_name" name="name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dirección</label>
                        <input type="text" class="form-control" id="company_address" name="address">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ciudad</label>
                            <input type="text" class="form-control" id="company_city" name="city">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estado</label>
                            <input type="text" class="form-control" id="company_state" name="state">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Código Postal</label>
                            <input type="text" class="form-control" id="company_postal_code" name="postal_code">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">País</label>
                            <input type="text" class="form-control" id="company_country" name="country">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Página Web</label>
                        <input type="text" class="form-control" id="company_webpage" name="webpage">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="save-company">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Conversaciones -->
<div class="modal fade" id="conversationsModal" tabindex="-1" aria-labelledby="conversationsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="conversationsModalLabel">
                    <i class="bx bx-chat me-2"></i>
                    Conversación del Lead
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="lead-info mb-3">
                    <h6 class="lead-name fw-semibold mb-2"></h6>
                    <div class="d-flex align-items-center text-muted small">
                        <i class="bx bx-envelope me-2"></i>
                        <span class="lead-email me-3"></span>
                        <i class="bx bx-phone ms-2 me-2"></i>
                        <span class="lead-phone"></span>
                    </div>
                </div>
                <div class="chat-messages p-3" style="height: 350px; overflow-y: auto;">
                    <!-- Los mensajes se cargarán dinámicamente aquí -->
                </div>
                <div class="chat-input mt-3">
                    <form id="chatForm" class="d-flex gap-2">
                        <input type="text" class="form-control" name="message" placeholder="Escribe un mensaje..." required>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-send"></i>
                        </button>
                    </form>
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

  @media (max-width: 768px) {
    #companyButton .d-none {
        display: none !important;
    }
    #companyButton {
        padding: 0.25rem 0.5rem;
    }
  }

  /* Estilos para el botón de compañía */
  .btn-company,
  .btn-client {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
  }

  .btn-company i,
  .btn-client i {
    font-size: 1rem;
  }

  /* En pantallas pequeñas */
  @media (max-width: 768px) {
    .btn-company,
    .btn-client {
        padding: 0.25rem;
        min-width: 32px;
        height: 32px;
    }
    
    .btn-company span,
    .btn-client span {
        display: none;
    }
    
    .btn-company i,
    .btn-client i {
        margin: 0;
    }
  }

  /* Estilos para los mensajes */
  .chat-message {
    max-width: 70%;
    margin-bottom: 1rem;
    position: relative;
  }

  .chat-message.sent {
    margin-left: auto;
  }

  .chat-message.received {
    margin-right: auto;
  }

  .message-content {
    padding: 0.75rem 1rem;
    border-radius: 1rem;
    position: relative;
  }

  .chat-message.sent .message-content {
    background-color: #dcf8c6;
    border-top-right-radius: 0;
  }

  .chat-message.received .message-content {
    background-color: #f0f0f0;
    border-top-left-radius: 0;
  }

  .message-time {
    font-size: 0.75rem;
    color: #666;
    margin-top: 0.25rem;
  }

  .message-deleted {
    font-style: italic;
    color: #666;
  }

  .delete-message {
    position: absolute;
    top: 0;
    right: 0;
    padding: 0.25rem;
    cursor: pointer;
    opacity: 0;
    transition: opacity 0.2s;
  }

  .chat-message:hover .delete-message {
    opacity: 1;
  }

  /* Contenedor de mensajes */
  .chat-messages {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 1rem;
  }

  .message-sender {
    font-size: 0.85rem;
    margin-bottom: 2px;
  }

  .chat-message.sent .message-sender {
    text-align: right;
  }

  .chat-message.received .message-sender {
    text-align: left;
  }

  .assignments-list {
    margin-top: 1rem;
  }

  .assignment-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem;
    background-color: #f8f9fa;
    border-radius: 0.375rem;
    margin-bottom: 0.5rem;
  }

  .remove-assignment-btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
  }

  .remove-assignment-btn i {
    font-size: 1rem;
  }

  /* Estilos para la columna del kanban */
  .kanban-column .card-header {
    padding: 0.75rem 1rem;
  }

  .kanban-column .card-header .d-flex.align-items-center {
    margin-left: auto; /* Empuja los elementos más hacia la derecha */
  }
  
  .kanban-column .card-header .controls-container {
    display: flex;
    align-items: center;
  }
  
  .kanban-column .card-header .card-title {
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 60%;
  }

   /* Estilos para las opciones de color */
   .color-option {
    width: 30px;
    height: 30px;
    border-radius: 4px;
    cursor: pointer;
    border: 1px solid #dee2e6;
    transition: transform 0.2s;
  }

  .color-option:hover {
    transform: scale(1.1);
  }

  .color-option.selected {
    box-shadow: 0 0 0 2px #fff, 0 0 0 4px #000;
  }

  /* Estilos para el contenedor principal del Kanban */
  .kanban-outer-container {
    width: 100%;
    overflow: hidden;
    padding: 1rem;
  }

  /* Contenedor con scroll horizontal */
  .kanban-scroll-container {
    width: 100%;
    overflow-x: auto;
    overflow-y: hidden;
    padding-bottom: 1rem;
    -webkit-overflow-scrolling: touch; /* Para un desplazamiento suave en iOS */
  }

  /* Wrapper de las columnas */
  .kanban-columns-wrapper {
    display: flex;
    flex-wrap: nowrap;
    gap: 1rem;
    padding: 0.5rem;
    /* Quitamos el min-height de aquí */
  }

  /* Contenedor de cada columna */
  /* Contenedor de cada columna - alternativa con porcentaje */
  .kanban-column-container {
    flex: 0 0 calc(25% - 1rem); /* 25% menos el espacio del gap */
    min-width: 350px; /* Ancho mínimo para que sea usable */
    /* Aseguramos que el contenedor tome la altura completa disponible */
    height: calc(100vh - 277px);
  }

  /* Estilo para la columna Kanban */
  .kanban-column {
    /* Volvemos a poner la altura fija como estaba antes */
    height: calc(100vh - 277px);
    display: flex;
    flex-direction: column;
  }

  /* Personalización de la barra de desplazamiento */
  .kanban-scroll-container::-webkit-scrollbar {
    height: 8px;
  }

  .kanban-scroll-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
  }

  .kanban-scroll-container::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
  }

  .kanban-scroll-container::-webkit-scrollbar-thumb:hover {
    background: #555;
  }
</style>

@include('lead.partials.conversation')
@include('lead.partials.company')
@include('lead.partials.files')
@include('lead.partials.tasks')
@include('lead.partials.details')
@endsection