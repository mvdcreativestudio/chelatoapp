document.addEventListener('DOMContentLoaded', function() {
    let currentLeadId = null;
    const chatMessages = document.querySelector('.chat-messages');
    const chatForm = document.getElementById('chatForm');
    const conversationsModal = document.getElementById('conversationsModal');

    // Función para formatear la fecha
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleTimeString('es-ES', { 
            hour: '2-digit', 
            minute: '2-digit'
        });
    }

    // Función para renderizar un mensaje
    function renderMessage(message) {
        const isMine = message.user_id === window.currentUserId;
        const messageHtml = `
            <div class="chat-message ${isMine ? 'sent' : 'received'} mb-3">
                <div class="message-content position-relative ${message.is_deleted ? 'message-deleted' : ''}">
                    <div class="message-sender mb-1">
                        <small class="text-muted">${message.creator.name}</small>
                    </div>
                    ${!message.is_deleted ? `
                        ${isMine ? `
                            <button class="delete-message btn btn-link text-danger p-0 position-absolute top-0 end-0" 
                                    data-id="${message.id}" 
                                    style="font-size: 0.8rem;">
                                <i class="bx bx-trash"></i>
                            </button>
                        ` : ''}
                        <div class="message-text">
                            ${message.message}
                        </div>
                    ` : `
                        <div class="message-text">
                            <em>Este mensaje ha sido eliminado</em>
                        </div>
                    `}
                    <div class="message-time text-end mt-1">
                        <small class="text-muted">${formatDate(message.created_at)}</small>
                    </div>
                </div>
            </div>
        `;
        return messageHtml;
    }

    // Cargar mensajes
    function loadMessages(leadId) {
        $.ajax({
            url: `leads/${leadId}/conversations`,
            type: 'GET',
            success: function(data) {
                if (data.success) {
                    chatMessages.innerHTML = data.conversations
                        .sort((a, b) => new Date(a.created_at) - new Date(b.created_at))
                        .map(message => renderMessage(message))
                        .join('');
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            }
        });
    }

    // Event listener para abrir el modal de conversaciones
    document.querySelectorAll('.view-lead-conversations').forEach(button => {
        button.addEventListener('click', function() {
            currentLeadId = this.dataset.id;
            
            // Actualizar la información del lead en el modal
            const leadName = this.closest('.kanban-item').querySelector('.card-title').textContent;
            const leadEmail = this.closest('.kanban-item').dataset.email;
            const leadPhone = this.closest('.kanban-item').dataset.phone;

            // Actualizar los elementos en el modal
            conversationsModal.querySelector('.lead-name').textContent = leadName;
            conversationsModal.querySelector('.lead-email').textContent = leadEmail || 'No disponible';
            conversationsModal.querySelector('.lead-phone').textContent = leadPhone || 'No disponible';

            loadMessages(currentLeadId);
            const modal = new bootstrap.Modal(conversationsModal);
            modal.show();
        });
    });

    // Event listener para enviar mensajes
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        $.ajax({
            url: `leads/${currentLeadId}/conversations`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.csrfToken,
                'Content-Type': 'application/json',
            },
            data: JSON.stringify({
                message: formData.get('message')
            }),
            success: function(data) {
                if (data.success) {
                    chatMessages.insertAdjacentHTML('beforeend', renderMessage(data.conversation));
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                    chatForm.reset();
                }
            }
        });
    });

    // Event listener para eliminar mensajes
    chatMessages.addEventListener('click', function(e) {
        const deleteButton = e.target.closest('.delete-message');
        if (!deleteButton) return;

        const messageId = deleteButton.dataset.id;
        
        $.ajax({
            url: `leads/${currentLeadId}/conversations/${messageId}`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': window.csrfToken,
                'Content-Type': 'application/json',
            },
            success: function(data) {
                if (data.success) {
                    const messageElement = deleteButton.closest('.chat-message');
                    if (messageElement) {
                        // Mantener la estructura completa pero solo cambiar el contenido del mensaje
                        const messageContent = messageElement.querySelector('.message-content');
                        const messageText = messageContent.querySelector('.message-text');
                        messageContent.classList.add('message-deleted');
                        messageText.innerHTML = '<em>Este mensaje ha sido eliminado</em>';
                        // Eliminar el botón de borrar
                        deleteButton.remove();
                    }
                } else {
                    alert('Error al eliminar el mensaje');
                }
            },
            error: function(error) {
                console.error('Error:', error);
                alert('Error al eliminar el mensaje');
            }
        });
    });
});
