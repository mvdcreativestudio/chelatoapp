<div class="integration-card">
    <div class="card">
        <div class="card-header text-center bg-light">
            <div class="integration-icon mx-auto">
                <img src="{{ asset('assets/img/integrations/pymo-logo.png') }}"
                    alt="Pymo Logo" class="img-fluid">
            </div>
            @if ($store->invoices_enabled)
            <span class="status-indicator">
                <i class="bx bx-check text-white"></i>
            </span>
            @endif
            <button type="button" class="btn btn-icon btn-sm position-absolute top-0 end-0 mt-2 me-2"
                data-store-id="{{ $store->id }}"
                onclick="checkPymoConnection({{ $store->id }})">
                <i class="bx bx-show"></i>
            </button>
        </div>
        <div class="card-body text-center d-flex flex-column justify-content-between">
            <div>
                <h3 class="card-title mb-1">Pymo</h3>
                <small class="d-block mb-3">Facturación Electrónica a través de Pymo</small>
            </div>
            <div class="form-check form-switch d-flex justify-content-center">
                <input type="hidden" name="invoices_enabled" value="0">
                <input class="form-check-input" type="checkbox"
                    id="pymoSwitch-{{ $store->id }}"
                    name="invoices_enabled"
                    value="1"
                    data-store-id="{{ $store->id }}"
                    {{ $store->invoices_enabled ? 'checked' : '' }}>
            </div>
        </div>
    </div>
</div>


<!-- Modal -->
<div class="modal fade" id="pymoConfigModal-{{ $store->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Configuración de Pymo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" for="pymoUser">Usuario PyMo</label>
                    <input type="text" class="form-control" id="pymoUser" name="pymo_user"
                        value="{{ $store->pymo_user }}">
                </div>

                <div class="mb-3">
                    <label class="form-label" for="pymoPassword">Contraseña PyMo</label>
                    <input type="password" class="form-control" id="pymoPassword" name="pymo_password"
                        value="{{ $store->pymo_password }}">
                </div>

                <div class="mb-3">
                    <label class="form-label" for="pymoBranchOffice">Sucursal PyMo</label>
                    <input type="number" class="form-control" id="pymoBranchOffice" name="pymoBranchOffice"
                        value="{{ $store->pymo_branch_office }}">
                </div>

                @if (!empty($branchOffices))
                <div class="mb-3">
                    <label class="form-label" for="pymoBranchOffice">Sucursal</label>
                    <select class="form-select" id="pymoBranchOffice" name="pymo_branch_office">
                        <option value="">Seleccionar sucursal</option>
                        @foreach ($branchOffices as $office)
                        <option value="{{ $office['number'] }}"
                            {{ $store->pymo_branch_office == $office['number'] ? 'selected' : '' }}>
                            {{ $office['fiscalAddress'] }}, {{ $office['city'] }}, {{ $office['state'] }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif

                @if ($store->invoices_enabled && $store->pymo_user && !empty($companyInfo))
                <div class="mt-4">
                    <h6>Información de la empresa</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td>Nombre</td>
                                    <td>{{ $companyInfo['name'] ?? '' }}</td>
                                </tr>
                                <tr>
                                    <td>RUT</td>
                                    <td>{{ $companyInfo['rut'] ?? '' }}</td>
                                </tr>
                                <tr>
                                    <td>Email</td>
                                    <td>{{ $companyInfo['email'] ?? '' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary save-pymo-config" data-store-id="{{ $store->id }}">Guardar</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal for Connection Info -->
<div class="modal fade" id="pymoConnectionModal-{{ $store->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Información de Conexión PYMO</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="connection-info">
                    <div class="text-center mb-3" id="pymoConnectionLoader-{{ $store->id }}">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                    <div id="pymoConnectionData-{{ $store->id }}" style="display: none;">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td><strong>Nombre:</strong></td>
                                    <td class="company-name"></td>
                                </tr>
                                <tr>
                                    <td><strong>RUT:</strong></td>
                                    <td class="company-rut"></td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td class="company-email"></td>
                                </tr>
                                <tr>
                                    <td><strong>Sucursal:</strong></td>
                                    <td class="company-branch"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="pymoConnectionError-{{ $store->id }}" class="alert alert-danger" style="display: none;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>