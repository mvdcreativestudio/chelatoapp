<div class="integration-card" id="store-content-{{ $store->id }}">
    <div class="card">
        <div class="card-header text-center bg-light">
            <div class="integration-icon mx-auto">
                <img src="{{ asset('assets/img/integrations/peya-logo.png') }}"
                    alt="Pedidos Ya Envíos Logo" class="img-fluid">
            </div>
            @if ($store->accepts_peya_envios)
            <span class="status-indicator">
                <i class="bx bx-check text-white"></i>
            </span>
            @endif
        </div>
        <div class="card-body text-center d-flex flex-column justify-content-between">
            <div>
                <h3 class="card-title mb-1">Pedidos Ya Envíos</h3>
                <small class="d-block mb-3">Ofrece envíos a través de Pedidos Ya</small>
            </div>
            <div class="form-check form-switch d-flex justify-content-center">
                <input type="hidden" name="accepts_peya_envios" value="0">
                <input 
                    class="form-check-input" 
                    type="checkbox" 
                    id="peyaEnviosSwitch-{{ $store->id }}"
                    name="accepts_peya_envios" 
                    value="1" 
                    {{ $store->accepts_peya_envios ? 'checked' : '' }}
                    data-store-id="{{ $store->id }}"
                >
            </div>
        </div>
    </div>
</div>

<!-- Modal para configuración de PedidosYa -->
<div class="modal fade" id="peyaConfigModal-{{ $store->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Configuración de Pedidos Ya</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" for="peyaEnviosKey-{{ $store->id }}">API Key de Pedidos Ya Envíos</label>
                    <input type="text" class="form-control" id="peyaEnviosKey-{{ $store->id }}"
                        name="peya_envios_key" placeholder="API Key de Pedidos Ya"
                        value="{{ $store->peya_envios_key ?? '' }}">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary save-peya-config" data-store-id="{{ $store->id }}">Guardar</button>
            </div>
        </div>
    </div>
</div>