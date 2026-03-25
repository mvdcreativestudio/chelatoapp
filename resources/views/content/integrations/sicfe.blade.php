<div class="integration-card">
    <div class="card">
        <div class="card-header text-center bg-light">
            <div class="integration-icon mx-auto">
                <h4 class="mb-0">SICFE</h4>
            </div>
            @if ($store->billing_provider_id == 2)
            <span class="status-indicator">
                <i class="bx bx-check text-white"></i>
            </span>
            <button type="button" class="btn btn-icon btn-sm position-absolute top-0 end-0 mt-2 me-2"
                data-store-id="{{ $store->id }}"
                onclick="checkSicfeConnection({{ $store->id }})">
                <i class="bx bx-show"></i>
            </button>
            @endif
        </div>
        <div class="card-body text-center d-flex flex-column justify-content-between">
            <div>
                <h3 class="card-title mb-1">SICFE</h3>
                <small class="d-block mb-3">Facturación Electrónica a través de SICFE</small>
            </div>
            <div class="form-check form-switch d-flex justify-content-center">
              <input type="hidden" name="invoices_enabled_sicfe" value="0">
              <input class="form-check-input" type="checkbox"
                  id="sicfeSwitch-{{ $store->id }}"
                  name="invoices_enabled_sicfe"
                  value="1"
                  data-store-id="{{ $store->id }}"
                  {{ $store->invoices_enabled && $store->billing_provider_id == 2 && $store->billingCredential ? 'checked' : '' }}>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Configuración SICFE -->
<div class="modal fade" id="sicfeConfigModal-{{ $store->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Configuración de SICFE</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" for="sicfeTenant-{{ $store->id }}">Tenant</label>
                    <input type="text" class="form-control" id="sicfeTenant-{{ $store->id }}" name="sicfe_tenant"
                        value="{{ optional($store->billingCredential)->tenant }}">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="sicfeUser-{{ $store->id }}">Usuario SICFE</label>
                    <input type="text" class="form-control" id="sicfeUser-{{ $store->id }}" name="sicfe_user"
                        value="{{ optional($store->billingCredential)->user }}">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="sicfePassword-{{ $store->id }}">Contraseña SICFE</label>
                    <input type="password" class="form-control" id="sicfePassword-{{ $store->id }}" name="sicfe_password">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="sicfeBranchOffice-{{ $store->id }}">Sucursal SICFE</label>
                    <input type="number" class="form-control" id="sicfeBranchOffice-{{ $store->id }}" name="sicfe_branch_office"
                        value="{{ optional($store->billingCredential)->branch_office }}">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary save-sicfe-config" data-store-id="{{ $store->id }}">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Información de Conexión -->
<div class="modal fade" id="sicfeConnectionModal-{{ $store->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Información de Conexión SICFE</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="sicfeConnectionLoader-{{ $store->id }}" class="text-center my-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
                <div id="sicfeConnectionData-{{ $store->id }}" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div id="sicfeConnectionError-{{ $store->id }}" class="alert alert-danger" style="display: none;">
                </div>
            </div>
        </div>
    </div>
</div>
