<div class="integration-card" id="store-content-{{ $store->id }}">
    <div class="card">
        <div class="card-header text-center bg-light">
            <div class="integration-icon mx-auto">
                <img src="{{ asset('assets/img/integrations/email-config-logo.png') }}"
                    alt="Email Config Logo" class="img-fluid">
            </div>
            @if ($store->emailConfig)
            <span class="status-indicator">
                <i class="bx bx-check text-white"></i>
            </span>
            @endif
        </div>
        <div class="card-body text-center d-flex flex-column justify-content-between">
            <div>
                <h3 class="card-title mb-1">Configuración de Correo</h3>
                <small class="d-block mb-3">Gestiona la configuración de correo de tu tienda</small>
            </div>
            <div class="form-check form-switch d-flex justify-content-center">
                <input type="hidden" name="stores_email_config" value="0">
                <input class="form-check-input" type="checkbox"
                    id="emailConfigSwitch-{{ $store->id }}"
                    name="stores_email_config"
                    value="1"
                    {{ $store->emailConfig ? 'checked' : '' }}
                    {{ !$store->emailConfig ? 'data-bs-toggle=modal data-bs-target=#emailConfigModal-'.$store->id : '' }}>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="emailConfigModal-{{ $store->id }}" tabindex="-1"
    aria-labelledby="emailConfigModalLabel-{{ $store->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="emailConfigModalLabel-{{ $store->id }}">Configuración de Correo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="emailConfigForm-{{ $store->id }}">
                    <input type="hidden" name="stores_email_config" value="1">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="mailHost-{{ $store->id }}">Host</label>
                                <input type="text" class="form-control"
                                    id="mailHost-{{ $store->id }}"
                                    name="mail_host"
                                    placeholder="Host de correo"
                                    value="{{ $store->emailConfig->mail_host ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="mailPort-{{ $store->id }}">Puerto</label>
                                <input type="text" class="form-control"
                                    id="mailPort-{{ $store->id }}"
                                    name="mail_port"
                                    placeholder="Puerto de correo"
                                    value="{{ $store->emailConfig->mail_port ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="mailUsername-{{ $store->id }}">Usuario</label>
                                <input type="text" class="form-control"
                                    id="mailUsername-{{ $store->id }}"
                                    name="mail_username"
                                    placeholder="Usuario de correo"
                                    value="{{ $store->emailConfig->mail_username ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="mailPassword-{{ $store->id }}">Contraseña</label>
                                <input type="password" class="form-control"
                                    id="mailPassword-{{ $store->id }}"
                                    name="mail_password"
                                    placeholder="Contraseña de correo"
                                    value="{{ $store->emailConfig->mail_password ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="mailEncryption-{{ $store->id }}">Encriptación</label>
                                <input type="text" class="form-control"
                                    id="mailEncryption-{{ $store->id }}"
                                    name="mail_encryption"
                                    placeholder="Encriptación (e.g., tls, ssl)"
                                    value="{{ $store->emailConfig->mail_encryption ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="mailFromAddress-{{ $store->id }}">Correo Remitente</label>
                                <input type="email" class="form-control"
                                    id="mailFromAddress-{{ $store->id }}"
                                    name="mail_from_address"
                                    placeholder="Correo remitente"
                                    value="{{ $store->emailConfig->mail_from_address ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="mailFromName-{{ $store->id }}">Nombre Remitente</label>
                                <input type="text" class="form-control"
                                    id="mailFromName-{{ $store->id }}"
                                    name="mail_from_name"
                                    placeholder="Nombre remitente"
                                    value="{{ $store->emailConfig->mail_from_name ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="mailReplyToAddress-{{ $store->id }}">Correo de Respuesta</label>
                                <input type="email" class="form-control"
                                    id="mailReplyToAddress-{{ $store->id }}"
                                    name="mail_reply_to_address"
                                    placeholder="Correo de respuesta"
                                    value="{{ $store->emailConfig->mail_reply_to_address ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="mailReplyToName-{{ $store->id }}">Nombre de Respuesta</label>
                                <input type="text" class="form-control"
                                    id="mailReplyToName-{{ $store->id }}"
                                    name="mail_reply_to_name"
                                    placeholder="Nombre de respuesta"
                                    value="{{ $store->emailConfig->mail_reply_to_name ?? '' }}">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="saveEmailConfig-{{ $store->id }}">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>