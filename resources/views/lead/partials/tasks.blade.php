<!-- Modal de Tareas -->
<div class="modal fade" id="tasksModal" tabindex="-1" aria-labelledby="tasksModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title text-white" id="tasksModalLabel">
          <i class="bx bx-task me-2"></i>
          Tareas del Lead
        </h5>
        <button type="button" class="btn-close btn-close-black" data-bs-dismiss="modal" aria-label="Close"></button>
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