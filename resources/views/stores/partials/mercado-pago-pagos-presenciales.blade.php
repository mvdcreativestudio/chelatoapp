<div class="integration-card">
    <div class="card">
        <div class="card-header text-center bg-light">
            <div class="integration-icon mx-auto">
                <img src="{{ asset('assets/img/integrations/mercadopago-logo.png') }}"
                    alt="MercadoPago Logo" class="img-fluid">
            </div>
            @if ($mercadoPagoPresencial !== null)
            <span class="status-indicator">
                <i class="bx bx-check text-white"></i>
            </span>
            @endif
        </div>
        <div class="card-body text-center d-flex flex-column justify-content-between">
            <div>
                <h3 class="card-title mb-1">MercadoPago Presencial</h3>
                <small class="d-block mb-3">Acepta pagos presenciales con QR</small>
            </div>
            <div class="form-check form-switch d-flex justify-content-center">
                <input type="hidden" name="accepts_mercadopago_presencial" value="0">
                <input class="form-check-input" type="checkbox" 
                       id="mercadoPagoSwitchPresencial-{{ $store->id }}"
                       name="accepts_mercadopago_presencial" 
                       value="1" 
                       {{ $mercadoPagoPresencial !== null ? 'checked' : '' }}>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="mercadoPagoPresencialModal-{{ $store->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Configuración MercadoPago Presencial</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="mercadoPagoPresencialForm-{{ $store->id }}" class="mercadoPagoPresencialForm">

                    <div id="mercadoPagoFieldsPresencial-{{ $store->id }}" class="integration-fields"
                        style="display: {{ $mercadoPagoPresencial !== null ? 'block' : 'none' }}">

                        <!-- Credentials Section -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Credenciales de MercadoPago</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label" for="mercadoPagoPublicKeyPresencial">Public Key</label>
                                    <input type="text" class="form-control" name="mercadoPagoPublicKeyPresencial"
                                        value="{{ $mercadoPagoPresencial->public_key ?? '' }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="mercadoPagoAccessTokenPresencial">Access Token</label>
                                    <input type="text" class="form-control" name="mercadoPagoAccessTokenPresencial"
                                        value="{{ $mercadoPagoPresencial->access_token ?? '' }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="mercadoPagoSecretKeyPresencial">Secret Key</label>
                                    <input type="text" class="form-control" name="mercadoPagoSecretKeyPresencial"
                                        value="{{ $mercadoPagoPresencial->secret_key ?? '' }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="mercadoPagoUserIdPresencial">User ID</label>
                                    <input type="text" class="form-control" name="mercadoPagoUserIdPresencial"
                                        value="{{ $mercadoPagoPresencial->user_id_mp ?? '' }}" required>
                                </div>
                            </div>
                        </div>

                        <!-- Store Details Section -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Detalles de la Sucursal</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label" for="branch-name">Nombre de la Sucursal</label>
                                    <input type="text" class="form-control" id="branch-name" name="branch_name" disabled
                                        value="{{ $mercadoPagoAccountStore->name ?? $store->name }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="external-id">ID Externo</label>
                                    <input type="text" class="form-control" id="external-id" name="external_id" disabled
                                        value="{{ $mercadoPagoAccountStore->external_id ?? 'SUC' . $store->id }}">
                                </div>
                            </div>
                        </div>

                        <!-- Location Details Section -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Ubicación de la Sucursal</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="street_name">Calle</label>
                                            <input type="text" class="form-control" id="street_name" name="street_name"
                                                value="{{ $mercadoPagoAccountStore->street_name ?? '' }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="street_number">Número</label>
                                            <input type="text" class="form-control" id="street_number" name="street_number"
                                                value="{{ $mercadoPagoAccountStore->street_number ?? '' }}" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="city_name">Ciudad</label>
                                            <input type="text" class="form-control" id="city_name" name="city_name"
                                                value="{{ $mercadoPagoAccountStore->city_name ?? '' }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="state_name">Estado</label>
                                            <input type="text" class="form-control" id="state_name" name="state_name"
                                                value="{{ $mercadoPagoAccountStore->state_name ?? '' }}" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="latitude">Latitud</label>
                                            <input type="text" class="form-control" id="latitude" name="latitude" readonly
                                                value="{{ $mercadoPagoAccountStore->latitude ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="longitude">Longitud</label>
                                            <input type="text" class="form-control" id="longitude" name="longitude" readonly
                                                value="{{ $mercadoPagoAccountStore->longitude ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="reference">Referencia</label>
                                    <input type="text" class="form-control" id="reference" name="reference"
                                        value="{{ $mercadoPagoAccountStore->reference ?? '' }}">
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