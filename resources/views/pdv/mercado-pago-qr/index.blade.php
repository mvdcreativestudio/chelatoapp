<!-- Modal de opciones de QR -->
<div class="modal fade" id="modalMercadopago" tabindex="-1" aria-labelledby="modalMercadopagoLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMercadopagoLabel">Opciones de QR</h5>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p>Seleccione el método para procesar el pago con Mercado Pago:</p>
                <div class="d-flex justify-content-around">
                    <button id="btnModeloAtendido" data-method="qr_attended" class="btn btn-outline-primary">
                        QR Modelo Atendido
                    </button>
                    <button id="btnModeloDinamico" data-method="qr_dynamic" class="btn btn-outline-primary">
                        QR Modelo Dinámico
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal "Esperando Pago" (Modelo Atendido) -->
<div class="modal fade" id="modalQRAtendido" tabindex="-1" aria-labelledby="modalQRAtendidoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalQRAtendidoLabel">Esperando Pago</h5>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p>Esperando confirmación de pago...</p>
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal "QR Dinámico" -->
<div class="modal fade" id="modalQRDinamico" tabindex="-1" aria-labelledby="modalQRDinamicoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalQRDinamicoLabel">Código QR Dinámico</h5>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p>Escanea el código QR con Mercado Pago para realizar el pago.</p>
                <div id="qrImageDinamicoContainer" class="d-flex justify-content-center align-items-center"></div>
                <div class="mt-3">
                    <button class="btn btn-outline-secondary" data-bs-target="#modalMercadopago">
                        Volver a opciones
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
