@extends('layouts/layoutMaster')

@section('title', 'Editar Empresa')

@section('page-script')
@vite(['resources/assets/js/edit-store.js'])
<script
    src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places&callback=initAutocomplete"
    async defer></script>
@endsection

@section('content')

<style>
    /* Estilos para mejorar la visualización de las cards de Pymo */
    .card {
        border-radius: 10px;
        transition: all 0.3s ease-in-out;
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 2px solid #e9ecef;
        padding: 15px;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 10px;
    }
</style>


<h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Empresas /</span> Editar Empresa
</h4>

@if (session('success'))
<div class="alert alert-success mt-3 mb-3">
    {{ session('success') }}
</div>
@endif

@if ($errors->any())
@foreach ($errors->all() as $error)
<div class="alert alert-danger">
    {{ $error }}
</div>
@endforeach
@endif

<div class="app-ecommerce">
    <!-- Formulario para editar tienda -->
    <form action="{{ route('stores.update', $store->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Información de la Tienda</h5>
                    </div>
                    <div class="card-body">
                        <!-- Nombre -->
                        <div class="mb-3">
                            <label class="form-label" for="store-name">Nombre</label>
                            <input type="text" class="form-control" id="store-name" name="name" required
                                placeholder="Nombre de la tienda" value="{{ $store->name }}">
                        </div>

                        <!-- Dirección -->
                        <div class="mb-3">
                            <label class="form-label" for="store-address">Dirección</label>
                            <input type="text" class="form-control" id="store-address" name="address"
                                placeholder="Calle, esquina, número de puerta" onFocus="geolocate()" role="presentation"
                                autocomplete="off" value="{{ $store->address }}">
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label class="form-label" for="store-email">Email</label>
                            <input type="email" class="form-control" id="store-email" name="email" required
                                placeholder="Email de la Empresa" value="{{ $store->email }}">
                        </div>

                        <!-- RUT -->
                        <div class="mb-3">
                            <label class="form-label" for="store-rut">RUT</label>
                            <input type="text" class="form-control" id="store-rut" name="rut" required
                                placeholder="RUT de la Empresa" value="{{ $store->rut }}">
                        </div>

                        <!-- Estado -->
                        <div class="mb-3">
                            <label class="form-label" for="store-status">Estado</label>
                            <select class="form-select" id="store-status" name="status" required>
                                <option value="1" {{ $store->status == 'activo' ? 'selected' : '' }}>Activo
                                </option>
                                <option value="0" {{ $store->status == 'inactivo' ? 'selected' : '' }}>Inactivo
                                </option>
                            </select>
                        </div>

                        <!-- Tarjetas de Integraciones -->
                        <div class="row pt-3">
                            <!-- Integración Ecommerce -->
                            <div class="col-lg-3 col-sm-6 mb-4">
                                <div class="card position-relative border">
                                    <div class="card-header text-center bg-light">
                                        <div class="border-0 rounded-circle mx-auto">
                                            <img src="{{ asset('assets/img/integrations/ecommerce-logo.png') }}"
                                                alt="E-Commerce Logo" class="img-fluid" style="width: 80px;">
                                        </div>
                                        <!-- Icono de check para mostrar la vinculación activa -->
                                        @if ($store->ecommerce)
                                        <span
                                            class="position-absolute top-0 end-0 translate-middle p-2 bg-success rounded-circle">
                                            <i class="bx bx-check text-white"></i>
                                        </span>
                                        @endif

                                    </div>
                                    <div class="card-body text-center">
                                        <h3 class="card-title mb-1 me-2">E-Commerce</h3>
                                        <small class="d-block mb-2">Activa la venta en línea para tu tienda</small>
                                        <div class="form-check form-switch d-flex justify-content-center">
                                            <input type="hidden" name="ecommerce" value="0">
                                            <input class="form-check-input" type="checkbox" id="ecommerceSwitch"
                                                name="ecommerce" value="1" {{ $store->ecommerce ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Integración MercadoPago -->
                            <div class="col-lg-3 col-sm-6 mb-4">
                                <div class="card position-relative border">
                                    <div class="card-header text-center bg-light">
                                        <div class="border-0 rounded-circle mx-auto">
                                            <img src="{{ asset('assets/img/integrations/mercadopago-logo.png') }}"
                                                alt="MercadoPago Logo" class="img-fluid" style="width: 80px;">
                                        </div>
                                        <!-- Icono de check para mostrar la vinculación activa -->
                                        @if ($store->accepts_mercadopago)
                                        <span
                                            class="position-absolute top-0 end-0 translate-middle p-2 bg-success rounded-circle">
                                            <i class="bx bx-check text-white"></i>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="card-body text-center">
                                        <h3 class="card-title mb-1 me-2">MercadoPago</h3>
                                        <small class="d-block mb-2">Acepta pagos a través de MercadoPago en tu
                                            E-Commerce</small>
                                        <div class="form-check form-switch d-flex justify-content-center">
                                            <input type="hidden" name="accepts_mercadopago" value="0">
                                            <input class="form-check-input" type="checkbox" id="mercadoPagoSwitch"
                                                name="accepts_mercadopago" value="1" {{ $store->mercadoPagoAccount ?
                                            'checked' : '' }}>
                                        </div>
                                        <!-- Campos MercadoPago (ocultos por defecto) -->
                                        <div id="mercadoPagoFields" class="integration-fields" style="display: none;">
                                            <div class="mb-3">
                                                <label class="form-label mt-2" for="mercadoPagoPublicKey">Public
                                                    Key</label>
                                                <input type="text" class="form-control" id="mercadoPagoPublicKey"
                                                    name="mercadoPagoPublicKey" placeholder="Public Key de MercadoPago"
                                                    value="{{ $store->mercadoPagoAccount->public_key ?? '' }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="mercadoPagoAccessToken">Access
                                                    Token</label>
                                                <input type="text" class="form-control" id="mercadoPagoAccessToken"
                                                    name="mercadoPagoAccessToken"
                                                    placeholder="Access Token de MercadoPago"
                                                    value="{{ $store->mercadoPagoAccount->access_token ?? '' }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="mercadoPagoSecretKey">Access
                                                    Token</label>
                                                <input type="text" class="form-control" id="mercadoPagoSecretKey"
                                                    name="mercadoPagoSecretKey" placeholder="Secret Key de MercadoPago"
                                                    value="{{ $store->mercadoPagoAccount->secret_key ?? '' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Integración Pedidos Ya Envíos -->
                            <div class="col-lg-3 col-sm-6 mb-4">
                                <div class="card position-relative border">
                                    <div class="card-header text-center bg-light">
                                        <div class="border-0 rounded-circle mx-auto">
                                            <img src="{{ asset('assets/img/integrations/peya-logo.png') }}"
                                                alt="Pedidos Ya Envíos Logo" class="img-fluid" style="width: 80px;">
                                        </div>
                                        <!-- Icono de check para mostrar la vinculación activa -->
                                        @if ($store->accepts_peya_envios)
                                        <span
                                            class="position-absolute top-0 end-0 translate-middle p-2 bg-success rounded-circle">
                                            <i class="bx bx-check text-white"></i>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="card-body text-center">
                                        <h3 class="card-title mb-1 me-2">Pedidos Ya Envíos</h3>
                                        <small class="d-block mb-2">Ofrece envíos a través de Pedidos Ya</small>
                                        <div class="form-check form-switch d-flex justify-content-center">
                                            <!-- Campo oculto para asegurar que se envíe el valor '0' si el checkbox no está marcado -->
                                            <input type="hidden" name="accepts_peya_envios" value="0">
                                            <input class="form-check-input" type="checkbox" id="peyaEnviosSwitch"
                                                name="accepts_peya_envios" value="1" {{ $store->accepts_peya_envios ?
                                            'checked' : '' }}>
                                        </div>
                                        <!-- Campos Pedidos Ya (ocultos por defecto) -->
                                        <div id="peyaEnviosFields" class="integration-fields" style="display: none;">
                                            <div class="mb-3">
                                                <label class="form-label mt-2" for="peyaEnviosKey">API Key de Pedidos
                                                    Ya Envíos</label>
                                                <input type="text" class="form-control" id="peyaEnviosKey"
                                                    name="peya_envios_key" placeholder="API Key de Pedidos Ya"
                                                    value="{{ $store->peya_envios_key ?? '' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Integración Pymo -->
                            <div class="col-lg-3 col-sm-6 mb-4">
                                <div class="card position-relative border">
                                    <div class="card-header text-center bg-light">
                                        <div class="border-0 rounded-circle mx-auto">
                                            <img src="{{ asset('assets/img/integrations/pymo-logo.png') }}"
                                                alt="Pymo Logo" class="img-fluid" style="width: 80px;">
                                        </div>

                                        <!-- Icono de check para mostrar la vinculación activa -->
                                        @if ($store->invoices_enabled)
                                        <span
                                            class="position-absolute top-0 end-0 translate-middle p-2 bg-success rounded-circle">
                                            <i class="bx bx-check text-white"></i>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="card-body text-center">
                                        <h3 class="card-title mb-1 me-2">Pymo</h3>
                                        <small class="d-block mb-2">Facturación Electrónica a través de Pymo</small>
                                        <div class="form-check form-switch d-flex justify-content-center">
                                            <!-- Campo oculto para asegurar que se envíe el valor '0' si el checkbox no está marcado -->
                                            <input type="hidden" name="invoices_enabled" value="0">
                                            <input class="form-check-input" type="checkbox" id="invoicesEnabledSwitch"
                                                name="invoices_enabled" value="1" {{ $store->invoices_enabled ?
                                            'checked' : '' }}>
                                        </div>

                                        @if ($store->invoices_enabled == 0)
                                        <div class="mt-4">
                                            <small class="">¿Aún no tienes cuenta? <a href="https://pymo.uy/"
                                                    target="_blank">Registrate
                                                    aquí</a></small>
                                        </div>
                                        @endif


                                        <!-- Campos de Configuración de PyMo (ocultos por defecto) -->
                                        <div id="pymoFields" style="display: none;">
                                            <div class="mb-3">
                                                <label class="form-label mt-2" for="pymoUser">Usuario PyMo</label>
                                                <input type="text" class="form-control" id="pymoUser" name="pymo_user"
                                                    value="{{ $store->pymo_user }}">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label" for="pymoPassword">Contraseña PyMo</label>
                                                <input type="password" class="form-control" id="pymoPassword"
                                                    name="pymo_password" value="{{ $store->pymo_password }}">
                                            </div>

                                            @if ($branchOffices)
                                            <div class="mb-3">
                                                <label class="form-label" for="pymoBranchOfficeSelect">Seleccionar
                                                    Sucursal</label>
                                                <select id="pymoBranchOfficeSelect" name="pymo_branch_office"
                                                    class="form-select">
                                                    <option value="">Selecciona una sucursal</option>
                                                    @foreach ($branchOffices as $branchOffice)
                                                    <option value="{{ $branchOffice['number'] }}"
                                                        data-callback-url="{{ $branchOffice['callbackNotificationUrl'] }}"
                                                        {{ $store->pymo_branch_office == $branchOffice['number'] ?
                                                        'selected' : '' }}>
                                                        {{ $branchOffice['fiscalAddress'] }}, {{ $branchOffice['city']
                                                        }}, {{ $branchOffice['state'] }} | Sucursal: {{
                                                        $branchOffice['number'] }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="mb-3 d-none">
                                                <label class="form-label" for="callbackNotificationUrl">URL de
                                                    notificaciones de la Sucursal</label>
                                                <input type="text" class="form-control" id="callbackNotificationUrl"
                                                    name="callbackNotificationUrl" value="">
                                            </div>
                                            @endif
                                        </div>

                                        @if ($store->invoices_enabled && $store->pymo_user && $store->pymo_password &&
                                        !empty($companyInfo))
                                        <div class="col-12 mt-4">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h4 class="card-title">Logo de la empresa en Pymo</h4>
                                                </div>
                                                <div class="card-body">
                                                    @if ($logoUrl)
                                                    <div class="mb-3">
                                                        <img src="{{ asset($logoUrl) }}" alt="Company Logo"
                                                            class="img-thumbnail" style="max-width: 200px;">
                                                    </div>
                                                    @endif
                                                    <form action="{{ route('accounting.uploadLogo') }}" method="POST"
                                                        enctype="multipart/form-data">
                                                        @csrf
                                                        <div class="form-group">
                                                            <input type="file" class="form-control-file" id="logo"
                                                                name="logo">
                                                        </div>
                                                        <button type="submit" class="btn btn-primary mt-3">Actualizar
                                                            Logo</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 mt-4">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h4 class="card-title">Información de la empresa en Pymo
                                                    </h4>
                                                </div>
                                                <div class="card-body">
                                                    <form>
                                                        @if (!empty($companyInfo['name']))
                                                        <div class="form-group">
                                                            <label for="companyName">Nombre de la
                                                                Empresa</label>
                                                            <input type="text" class="form-control my-3"
                                                                id="companyName" value="{{ $companyInfo['name'] }}"
                                                                disabled>
                                                        </div>
                                                        @endif

                                                        @if (!empty($companyInfo['rut']))
                                                        <div class="form-group">
                                                            <label for="companyRUT">RUT</label>
                                                            <input type="text" class="form-control my-3" id="companyRUT"
                                                                value="{{ $companyInfo['rut'] }}" disabled>
                                                        </div>
                                                        @endif

                                                        @if (!empty($companyInfo['socialPurpose']))
                                                        <div class="form-group">
                                                            <label for="socialPurpose">Propósito
                                                                Social</label>
                                                            <input type="text" class="form-control my-3"
                                                                id="socialPurpose"
                                                                value="{{ $companyInfo['socialPurpose'] }}" disabled>
                                                        </div>
                                                        @endif

                                                        @if (!empty($companyInfo['resolutionNumber']))
                                                        <div class="form-group">
                                                            <label for="resolutionNumber">Número de
                                                                Resolución</label>
                                                            <input type="text" class="form-control my-3"
                                                                id="resolutionNumber"
                                                                value="{{ $companyInfo['resolutionNumber'] }}" disabled>
                                                        </div>
                                                        @endif

                                                        @if (!empty($companyInfo['email']))
                                                        <div class="form-group">
                                                            <label for="companyEmail">Correo
                                                                Electrónico</label>
                                                            <input type="email" class="form-control my-3"
                                                                id="companyEmail" value="{{ $companyInfo['email'] }}"
                                                                disabled>
                                                        </div>
                                                        @endif

                                                        @if (!empty($companyInfo['createdAt']))
                                                        <div class="form-group">
                                                            <label for="createdAt">Fecha de
                                                                Creación</label>
                                                            <input type="text" class="form-control my-3" id="createdAt"
                                                                value="{{ $companyInfo['createdAt'] }}" disabled>
                                                        </div>
                                                        @endif

                                                        @if (!empty($companyInfo['updatedAt']))
                                                        <div class="form-group">
                                                            <label for="updatedAt">Fecha de
                                                                Actualización</label>
                                                            <input type="text" class="form-control my-3" id="updatedAt"
                                                                value="{{ $companyInfo['updatedAt'] }}" disabled>
                                                        </div>
                                                        @endif
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        @if ($errors->any())
                                        @foreach ($errors->all() as $error)
                                        <div class="alert alert-danger">
                                            {{ $error }}
                                        </div>
                                        @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>

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
                            <!-- Botones -->
                            <div class="d-flex justify-content-end mt-5">
                                <button type="submit" class="btn btn-primary">Actualizar Tienda</button>
                            </div>
                        </div>
    </form>
</div>
<script>
    let autocomplete;

        function initAutocomplete() {
            autocomplete = new google.maps.places.Autocomplete(document.getElementById('store-address'), {
                types: ['geocode']
            });
            autocomplete.setFields(['address_component']);
        }

        function geolocate() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const geolocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    const circle = new google.maps.Circle({
                        center: geolocation,
                        radius: position.coords.accuracy
                    });
                    autocomplete.setBounds(circle.getBounds());
                });
            }
        }
</script>
@endsection