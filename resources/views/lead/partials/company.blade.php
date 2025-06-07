<!-- Modal Compañía -->
<div class="modal fade" id="companyModal" tabindex="-1" aria-labelledby="companyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-white">
                <h5 class="modal-title text-black" id="companyModalLabel">
                    <i class="bx bx-buildings me-2"></i>
                    Información de la Compañía
                </h5>
                <button type="button" class="btn-close btn-close-black" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="companyForm">
                    <div class="mb-3">
                        <label class="form-label">Nombre de la Compañía</label>
                        <input type="text" class="form-control" id="company_name" name="name" placeholder="Ingrese el nombre de la compañía">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ciudad</label>
                            <input type="text" class="form-control" id="company_city" name="city" placeholder="Ingrese la ciudad">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estado</label>
                            <input type="text" class="form-control" id="company_state" name="state" placeholder="Ingrese el estado">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Código Postal</label>
                            <input type="text" class="form-control" id="company_postal_code" name="postal_code" placeholder="Ingrese el código postal">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">País</label>
                            <input type="text" class="form-control" id="company_country" name="country" placeholder="Ingrese el país">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Página Web</label>
                        <input type="text" class="form-control" id="company_webpage" name="webpage" placeholder="Ingrese la página web">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="save-company">Guardar</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos para el botón de compañía */
    .btn-company,
    .btn-client {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    }

    .btn-company i,
    .btn-client i {
    font-size: 1rem;
    }

    /* En pantallas pequeñas */
    @media (max-width: 768px) {
    .btn-company,
    .btn-client {
        padding: 0.25rem;
        min-width: 32px;
        height: 32px;
    }
    
    .btn-company span,
    .btn-client span {
        display: none;
    }
    
    .btn-company i,
    .btn-client i {
        margin: 0;
    }
    }

    @media (max-width: 768px) {
    #companyButton .d-none {
        display: none !important;
    }
    #companyButton {
        padding: 0.25rem 0.5rem;
    }
    }
</style>