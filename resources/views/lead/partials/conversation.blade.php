<!-- Modal de Conversaciones -->
<div class="modal fade" id="conversationsModal" tabindex="-1" aria-labelledby="conversationsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-white">
                <h5 class="modal-title text-black" id="conversationsModalLabel">
                    <i class="bx bx-chat me-2"></i>
                    Conversación del Lead
                </h5>
                <button type="button" class="btn-close btn-close-black" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="lead-info mb-3">
                    <h6 class="lead-name fw-semibold mb-2"></h6>
                    <div class="d-flex align-items-center text-muted small">
                        <i class="bx bx-envelope me-2"></i>
                        <span class="lead-email me-3"></span>
                        <i class="bx bx-phone ms-2 me-2"></i>
                        <span class="lead-phone"></span>
                    </div>
                </div>
                <div class="chat-messages p-3" style="height: 350px; overflow-y: auto;">
                    <!-- Los mensajes se cargarán dinámicamente aquí -->
                </div>
                <div class="chat-input mt-3">
                    <form id="chatForm" class="d-flex gap-2">
                        <input type="text" class="form-control" name="message" placeholder="Escribe un mensaje..." required>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-send"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos para los mensajes */
    .chat-message {
    max-width: 70%;
    margin-bottom: 1rem;
    position: relative;
    }

    .chat-message.sent {
    margin-left: auto;
    }

    .chat-message.received {
    margin-right: auto;
    }

    .message-content {
    padding: 0.75rem 1rem;
    border-radius: 1rem;
    position: relative;
    }

    .chat-message.sent .message-content {
    background-color: #dcf8c6;
    border-top-right-radius: 0;
    }

    .chat-message.received .message-content {
    background-color: #f0f0f0;
    border-top-left-radius: 0;
    }

    .message-time {
    font-size: 0.75rem;
    color: #666;
    margin-top: 0.25rem;
    }

    .message-deleted {
    font-style: italic;
    color: #666;
    }

    .delete-message {
    position: absolute;
    top: 0;
    right: 0;
    padding: 0.25rem;
    cursor: pointer;
    opacity: 0;
    transition: opacity 0.2s;
    }

    .chat-message:hover .delete-message {
    opacity: 1;
    }

    /* Contenedor de mensajes */
    .chat-messages {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 1rem;
    }

    .message-sender {
    font-size: 0.85rem;
    margin-bottom: 2px;
    }

    .chat-message.sent .message-sender {
    text-align: right;
    }

    .chat-message.received .message-sender {
    text-align: left;
    }
</style>