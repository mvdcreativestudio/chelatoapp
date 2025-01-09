<div class="integration-card">
    <div class="card">
        <div class="card-header text-center bg-light">
            <div class="integration-icon mx-auto">
                <img src="{{ asset('assets/img/integrations/mercadopago-logo.png') }}"
                    alt="MercadoPago Logo" class="img-fluid">
            </div>
            @if ($mercadoPagoOnline !== null)
            <span class="status-indicator">
                <i class="bx bx-check text-white"></i>
            </span>
            @endif
        </div>
        <div class="card-body text-center d-flex flex-column justify-content-between">
            <div>
                <h3 class="card-title mb-1">MercadoPago Online</h3>
                <small class="d-block mb-3">Acepta pagos en línea a través de MercadoPago</small>
            </div>
            <div class="form-check form-switch d-flex justify-content-center">
                <input type="hidden" name="accepts_mercadopago_online" value="0">
                <input class="form-check-input" type="checkbox" 
                       id="mercadoPagoSwitchOnline-{{ $store->id }}"
                       name="accepts_mercadopago_online" 
                       value="1" 
                       {{ $mercadoPagoOnline !== null ? 'checked' : '' }}
                       >
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="mercadoPagoOnlineModal-{{ $store->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Configuración MercadoPago Online</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="mercadoPagoOnlineForm-{{ $store->id }}" class="mercadoPagoOnlineForm">
                    <div id="mercadoPagoFieldsOnline-{{ $store->id }}" class="integration-fields"
                        style="display: {{ $mercadoPagoOnline !== null ? 'block' : 'none' }}">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Credenciales de MercadoPago</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label" for="mercadoPagoPublicKeyOnline">Public Key</label>
                                    <input type="text" class="form-control" name="mercadoPagoPublicKeyOnline"
                                        value="{{ $mercadoPagoOnline->public_key ?? '' }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="mercadoPagoAccessTokenOnline">Access Token</label>
                                    <input type="text" class="form-control" name="mercadoPagoAccessTokenOnline"
                                        value="{{ $mercadoPagoOnline->access_token ?? '' }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="mercadoPagoSecretKeyOnline">Secret Key</label>
                                    <input type="text" class="form-control" name="mercadoPagoSecretKeyOnline"
                                        value="{{ $mercadoPagoOnline->secret_key ?? '' }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>