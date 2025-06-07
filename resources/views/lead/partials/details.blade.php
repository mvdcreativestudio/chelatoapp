<!-- Modal detalles-->
<div class="modal fade" id="leadModal" tabindex="-1" aria-labelledby="leadModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-white">
        <h5 class="modal-title text-black" id="leadModalLabel"></h5>
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
                  class="btn-close btn-close-black" 
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
                        <label class="form-label">Dirección</label>
                        <input type="text" class="form-control" id="company_address" name="address" placeholder="Ingrese una dirección">
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

<style>
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
</style>