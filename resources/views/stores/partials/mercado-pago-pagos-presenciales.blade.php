<div class="integration-card">
    <div class="card">
        <div class="card-header text-center bg-light">
            <div class="integration-icon mx-auto">
                <img src="{{ asset('assets/img/integrations/mercadopago-logo.png') }}" alt="MercadoPago Logo"
                    class="img-fluid">
            </div>
            {{-- Si hay credenciales/datos de MercadoPagoPresencial, se muestra el ícono de check y el botón "ojo" --}}
            @if ($mercadoPagoPresencial !== null)
                <span class="status-indicator">
                    <i class="bx bx-check text-white"></i>
                </span>
                <button type="button" 
                        class="btn btn-icon btn-sm position-absolute top-0 end-0 mt-2 me-2"
                        data-store-id="{{ $store->id }}"
                        onclick="checkMercadoPagoPresencialConnection({{ $store->id }})">
                    <i class="bx bx-show"></i>
                </button>
            @endif
        </div>
        <div class="card-body text-center d-flex flex-column justify-content-between">
            <div>
                <h3 class="card-title mb-1">MercadoPago Presencial</h3>
                <small class="d-block mb-3">Acepta pagos presenciales con QR</small>
            </div>
            <div class="form-check form-switch d-flex justify-content-center">
                <input type="hidden" name="accepts_mercadopago_presencial" value="0">
                <input class="form-check-input" type="checkbox" id="mercadoPagoSwitchPresencial-{{ $store->id }}"
                    name="accepts_mercadopago_presencial" value="1" {{ $mercadoPagoPresencial !==null ? 'checked' : ''
                    }}>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade mercadoPagoPresencialModal"
     id="mercadoPagoPresencialModal-{{ $store->id }}"
     tabindex="-1"
     aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Configuración MercadoPago Presencial</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <form id="mercadoPagoPresencialForm-{{ $store->id }}"
              class="mercadoPagoPresencialForm">

          <div id="mercadoPagoFieldsPresencial-{{ $store->id }}"
               class="integration-fields"
               style="display: {{ $mercadoPagoPresencial !== null ? 'block' : 'none' }}">

            <!-- Credentials Section -->
            <div class="card mb-4">
              <div class="card-header">
                <h6 class="mb-0">Credenciales de MercadoPago</h6>
              </div>
              <div class="card-body">
                <div class="mb-3">
                  <label class="form-label"
                         for="mercadoPagoPublicKeyPresencial-{{ $store->id }}">
                    Public Key
                  </label>
                  <input type="text"
                         id="mercadoPagoPublicKeyPresencial-{{ $store->id }}"
                         class="form-control"
                         name="mercadoPagoPublicKeyPresencial"
                         value="{{ $mercadoPagoPresencial->public_key ?? '' }}"
                         required>
                </div>

                <div class="mb-3">
                  <label class="form-label"
                         for="mercadoPagoAccessTokenPresencial-{{ $store->id }}">
                    Access Token
                  </label>
                  <input type="text"
                         id="mercadoPagoAccessTokenPresencial-{{ $store->id }}"
                         class="form-control"
                         name="mercadoPagoAccessTokenPresencial"
                         value="{{ $mercadoPagoPresencial->access_token ?? '' }}"
                         required>
                </div>

                <div class="mb-3">
                  <label class="form-label"
                         for="mercadoPagoSecretKeyPresencial-{{ $store->id }}">
                    Secret Key
                  </label>
                  <input type="text"
                         id="mercadoPagoSecretKeyPresencial-{{ $store->id }}"
                         class="form-control"
                         name="mercadoPagoSecretKeyPresencial"
                         value="{{ $mercadoPagoPresencial->secret_key ?? '' }}"
                         required>
                </div>

                <div class="mb-3">
                  <label class="form-label"
                         for="mercadoPagoUserIdPresencial-{{ $store->id }}">
                    User ID
                  </label>
                  <input type="text"
                         id="mercadoPagoUserIdPresencial-{{ $store->id }}"
                         class="form-control"
                         name="mercadoPagoUserIdPresencial"
                         value="{{ $mercadoPagoPresencial->user_id_mp ?? '' }}"
                         required>
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
                  <label class="form-label"
                         for="branch-name-{{ $store->id }}">
                    Nombre de la Sucursal
                  </label>
                  <input type="text"
                         id="branch-name-{{ $store->id }}"
                         class="form-control"
                         name="branch_name"
                         disabled
                         value="{{ $mercadoPagoAccountStore->name ?? $store->name }}">
                </div>
                <div class="mb-3">
                  <label class="form-label"
                         for="external-id-{{ $store->id }}">
                    ID Externo
                  </label>
                  <input type="text"
                         id="external-id-{{ $store->id }}"
                         class="form-control"
                         name="external_id"
                         disabled
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

                <!-- Campo para autocompletado -->
                <div class="mb-3">
                  <label class="form-label" for="autocomplete-{{ $store->id }}">
                    Dirección
                  </label>
                  <input type="text"
                         id="autocomplete-{{ $store->id }}"
                         class="form-control"
                         placeholder="Escribe la dirección">
                </div>

                <!-- Google Maps con ID dinámico -->
                <div id="map-{{ $store->id }}"
                     style="height: 300px; width: 100%;">
                </div>

                <div class="row mt-3">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label"
                             for="street_name-{{ $store->id }}">
                        Calle
                      </label>
                      <input type="text"
                             id="street_name-{{ $store->id }}"
                             class="form-control"
                             name="street_name"
                             value="{{ $mercadoPagoAccountStore->street_name ?? '' }}"
                             >
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label"
                             for="street_number-{{ $store->id }}">
                        Número
                      </label>
                      <input type="text"
                             id="street_number-{{ $store->id }}"
                             class="form-control"
                             name="street_number"
                             value="{{ $mercadoPagoAccountStore->street_number ?? '' }}"
                             >
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label"
                             for="city_name-{{ $store->id }}">
                        Ciudad
                      </label>
                      <input type="text"
                             id="city_name-{{ $store->id }}"
                             class="form-control"
                             name="city_name"
                             value="{{ $mercadoPagoAccountStore->city_name ?? '' }}"
                             >
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label"
                             for="state_name-{{ $store->id }}">
                        Estado
                      </label>
                      <input type="text"
                             id="state_name-{{ $store->id }}"
                             class="form-control"
                             name="state_name"
                             value="{{ $mercadoPagoAccountStore->state_name ?? '' }}"
                             >
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label"
                             for="latitude-{{ $store->id }}">
                        Latitud
                      </label>
                      <input type="text"
                             id="latitude-{{ $store->id }}"
                             class="form-control"
                             name="latitude"
                             value="{{ $mercadoPagoAccountStore->latitude ?? '' }}"
                             >
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label"
                             for="longitude-{{ $store->id }}">
                        Longitud
                      </label>
                      <input type="text"
                             id="longitude-{{ $store->id }}"
                             class="form-control"
                             name="longitude"
                             value="{{ $mercadoPagoAccountStore->longitude ?? '' }}"
                             >
                    </div>
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label"
                         for="reference-{{ $store->id }}">
                    Referencia
                  </label>
                  <input type="text"
                         id="reference-{{ $store->id }}"
                         class="form-control"
                         name="reference"
                         value="{{ $mercadoPagoAccountStore->reference ?? '' }}">
                </div>

              </div>
            </div>

          </div> <!-- /#mercadoPagoFieldsPresencial -->

          <div class="modal-footer">
            <button type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">
              Cerrar
            </button>
            <button type="submit"
                    class="btn btn-primary"
                    id="btnCredencialesMercadoPagoPresencial-{{ $store->id }}">
              Guardar cambios
            </button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>


<!-- Modal para 'Información de Conexión' (similar a PYMO) -->
<div class="modal fade mercadoPagoPresencialModalViewData" id="mercadoPagoPresencialConnectionModal-{{ $store->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Información de Conexión MercadoPago Presencial</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="connection-info">
                    <!-- Loader -->
                    <div class="text-center mb-3" id="mercadoPagoPresencialConnectionLoader-{{ $store->id }}">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>

                    <!-- Datos -->
                    <div id="mercadoPagoPresencialConnectionData-{{ $store->id }}" style="display: none;">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td><strong>Public Key:</strong></td>
                                    <td class="mp-public-key text-break"></td>
                                </tr>
                                <tr>
                                    <td><strong>Access Token:</strong></td>
                                    <td class="mp-access-token text-break"></td>
                                </tr>
                                <tr>
                                    <td><strong>Secret Key:</strong></td>
                                    <td class="mp-secret-key text-break"></td>
                                </tr>
                                <tr>
                                    <td><strong>User ID:</strong></td>
                                    <td class="mp-user-id text-break"></td>
                                </tr>
                                <tr>
                                    <td><strong>Sucursal:</strong></td>
                                    <td class="mp-branch-name text-break"></td>
                                </tr>
                                <!-- Agrega las filas que quieras mostrar en la vista -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Error -->
                    <div id="mercadoPagoPresencialConnectionError-{{ $store->id }}" 
                         class="alert alert-danger" 
                         style="display: none;">
                    </div>

                    <div class="text-center">
                        <button type="button" class="btn btn-sm btn-primary" data-bs-dismiss="modal">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>