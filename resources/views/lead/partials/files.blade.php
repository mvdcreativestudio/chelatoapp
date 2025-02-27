<!-- Modal de Archivos Multimedia -->
<div class="modal fade" id="filesModal" tabindex="-1" aria-labelledby="filesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-white">
                <h5 class="modal-title text-black" id="filesModalLabel">
                    <i class="bx bx-images me-2"></i>
                    Archivos Multimedia del Lead
                </h5>
                <button type="button" class="btn-close btn-close-black" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start mb-3">
                    <div class="lead-info mb-3 mb-md-0">
                        <h6 class="lead-name fw-semibold mb-2"></h6>
                        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center text-muted small">
                            <div class="me-0 me-sm-3 mb-1 mb-sm-0">
                                <i class="bx bx-envelope me-2"></i>
                                <span class="lead-email"></span>
                            </div>
                            <div>
                                <i class="bx bx-phone me-2"></i>
                                <span class="lead-phone"></span>
                            </div>
                        </div>
                    </div>
                    <div class="file-upload">
                        <label for="fileInput" class="btn btn-primary">
                            <i class="bx bx-upload me-2"></i>
                            Cargar Archivo
                        </label>
                        <input type="file" id="fileInput" class="d-none">
                    </div>
                </div>
                <div id="filesList">
                    <!-- Los archivos se organizarán en filas aquí -->
                </div>
            </div>
        </div>
    </div>
</div>