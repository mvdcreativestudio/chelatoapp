<div class="integration-card fiserv-card" id="fiserv-card-{{ $store->id }}">
  <div class="card">
    <div class="card-header text-center bg-light">
      <div class="integration-icon mx-auto">
        <img src="{{ asset('assets/img/integrations/fiserv-logo.png') }}"
             alt="Fiserv Logo"
             class="img-fluid">
      </div>
      <span class="status-indicator {{ $store->pos_provider_id === 2 ? '' : 'd-none' }}">
        <i class="bx bx-check text-white"></i>
      </span>
    </div>
    <div class="card-body text-center d-flex flex-column justify-content-between">
      <div>
        <h3 class="card-title mb-1">Fiserv</h3>
        <small class="d-block mb-1">Gestiona terminales POS con Fiserv</small>
      </div>
      <div class="form-check form-switch pt-0 d-flex justify-content-center">
        <input type="hidden" name="fiserv" value="0">
        <input
          class="form-check-input"
          type="checkbox"
          id="fiservSwitch-{{ $store->id }}"
          name="fiserv"
          value="1"
          {{ $store->pos_provider_id === 2 ? 'checked' : '' }}
          data-store-id="{{ $store->id }}"
        >
      </div>
      <div>
        <button
          type="button"
          class="btn btn-primary btn-sm pt-0 pb-0 manage-terminals {{ $store->pos_provider_id === 2 ? '' : 'd-none' }}"
          id="manageTerminalsFiserv-{{ $store->id }}"
          data-bs-toggle="modal"
          data-bs-target="#fiservModal-{{ $store->id }}"
          {{ $store->pos_provider_id !== 2 ? 'disabled' : '' }}>
          Gestionar Terminales
        </button>
      </div>
    </div>
  </div>

  <!-- Modal Fiserv -->
  <div class="modal fade" id="fiservModal-{{ $store->id }}" tabindex="-1" data-provider="fiserv" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="fiservModalLabel">Terminales Fiserv</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="fiservModalMessage-{{ $store->id }}" class="alert d-none" role="alert"></div>
          <form id="fiservForm-{{ $store->id }}" autocomplete="off">
            <table class="table table-striped terminals-table" data-provider="fiserv" data-store-id="{{ $store->id }}">
              <thead>
                <tr>
                  <th>Nombre</th>
                  <th>Identificador</th>
                  <th>Usuario</th>
                  <th>Caja</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($store->fiservDevices as $device)
                <tr data-id="{{ $device->id }}">
                  <td><input type="text" class="form-control terminal-name" value="{{ $device->name }}" autocomplete="off" /></td>
                  <td><input type="text" class="form-control terminal-identifier" value="{{ $device->identifier }}" autocomplete="off" /></td>
                  <td><input type="text" class="form-control terminal-user" value="{{ $device->user }}" autocomplete="off" /></td>
                  <td><input type="text" class="form-control terminal-cash-register" value="{{ $device->cash_register }}" autocomplete="off" /></td>
                  <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-terminal">
                      <i class="bx bx-trash"></i>
                    </button>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </form>
          <button
            type="button"
            class="btn btn-success mt-3 add-terminal-row col-12"
            data-provider="fiserv"
            data-store-id="{{ $store->id }}">
            Agregar Nueva Terminal
          </button>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button type="button"
            class="btn btn-primary save-terminals"
            data-provider="fiserv"
            data-store-id="{{ $store->id }}">
            Guardar Cambios
          </button>
          </div>
      </div>
    </div>
  </div>
</div>
