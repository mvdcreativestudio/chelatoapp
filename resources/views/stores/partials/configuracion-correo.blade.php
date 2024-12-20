<!-- Integración Configuración de Correo -->
<div class="col-lg-3 col-sm-6 mb-4">
    <div class="card position-relative border">
        <div class="card-header text-center bg-light">
            <div class="border-0 rounded-circle mx-auto">
                <img src="{{ asset('assets/img/integrations/email-config-logo.png') }}"
                    alt="Email Config Logo" class="img-fluid" style="width: 80px;">
            </div>
            <!-- Icono de check para mostrar la vinculación activa -->
            @if ($store->emailConfig)
            <span
                class="position-absolute top-0 end-0 translate-middle p-2 bg-success rounded-circle">
                <i class="bx bx-check text-white"></i>
            </span>
            @endif
        </div>
        <div class="card-body text-center">
            <h3 class="card-title mb-1 me-2">Configuración de Correo</h3>
            <small class="d-block mb-2">Gestiona la configuración de correo de tu
                tienda</small>
            <div class="form-check form-switch d-flex justify-content-center">
                <!-- Campo oculto para enviar un valor de '0' si el checkbox no está marcado -->
                <input type="hidden" name="stores_email_config" value="0">
                <input class="form-check-input" type="checkbox" id="emailConfigSwitch"
                    name="stores_email_config" value="1" {{ $store->emailConfig ?
                'checked' : '' }}>
            </div>
            <!-- Campos de Configuración de Correo (ocultos por defecto) -->
            <div id="emailConfigFields" class="integration-fields" style="display: none;">
                <div class="mb-3">
                    <label class="form-label mt-2" for="mailHost">Host</label>
                    <input type="text" class="form-control" id="mailHost" name="mail_host"
                        placeholder="Host de correo"
                        value="{{ $store->emailConfig->mail_host ?? '' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="mailPort">Puerto</label>
                    <input type="text" class="form-control" id="mailPort" name="mail_port"
                        placeholder="Puerto de correo"
                        value="{{ $store->emailConfig->mail_port ?? '' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="mailUsername">Usuario</label>
                    <input type="text" class="form-control" id="mailUsername"
                        name="mail_username" placeholder="Usuario de correo"
                        value="{{ $store->emailConfig->mail_username ?? '' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="mailPassword">Contraseña</label>
                    <input type="password" class="form-control" id="mailPassword"
                        name="mail_password" placeholder="Contraseña de correo"
                        value="{{ $store->emailConfig->mail_password ?? '' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="mailEncryption">Encriptación</label>
                    <input type="text" class="form-control" id="mailEncryption"
                        name="mail_encryption" placeholder="Encriptación (e.g., tls, ssl)"
                        value="{{ $store->emailConfig->mail_encryption ?? '' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="mailFromAddress">Correo Remitente</label>
                    <input type="email" class="form-control" id="mailFromAddress"
                        name="mail_from_address" placeholder="Correo remitente"
                        value="{{ $store->emailConfig->mail_from_address ?? '' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="mailFromName">Nombre Remitente</label>
                    <input type="text" class="form-control" id="mailFromName"
                        name="mail_from_name" placeholder="Nombre remitente"
                        value="{{ $store->emailConfig->mail_from_name ?? '' }}">
                </div>

                {{-- Reply-To --}}
                <div class="mb-3">
                    <label class="form-label" for="mailReplyToAddress">Correo de
                        Respuesta</label>
                    <input type="email" class="form-control" id="mailReplyToAddress"
                        name="mail_reply_to_address" placeholder="Correo de respuesta"
                        value="{{ $store->emailConfig->mail_reply_to_address ?? '' }}">
                </div>

                <div class="mb-3">
                    <label class="form-label" for="mailReplyToName">Nombre de
                        Respuesta</label>
                    <input type="text" class="form-control" id="mailReplyToName"
                        name="mail_reply_to_name" placeholder="Nombre de respuesta"
                        value="{{ $store->emailConfig->mail_reply_to_name ?? '' }}">
                </div>
            </div>
        </div>
    </div>
</div>