document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('click', function (e) {
        if (e.target.closest('.view-lead-details')) {
            const leadCard = e.target.closest('.kanban-item');
            const leadId = leadCard.dataset.id;
            const lead = leads.find(l => l.id == leadId);

            if (lead) {
                showLeadDetailsModal(lead);
                document.querySelectorAll('.kanban-item').forEach(item => {
                    item.classList.remove('active');
                });
                leadCard.classList.add('active');
            }
        }
    });

    // Asegúrate de que el evento se adjunta solo una vez
    const addAssignmentBtn = document.getElementById('add-assignment-btn');
    if (addAssignmentBtn) {
        addAssignmentBtn.addEventListener('click', function() {
            const userId = document.getElementById('user-select').value;
            const leadId = this.getAttribute('data-lead-id');

            if (!userId || !leadId) {
                console.error('Faltan datos necesarios');
                return;
            }

            // Verificar si el usuario ya está asignado
            const assignmentsList = document.querySelector('.assignments-list');
            const existingAssignment = assignmentsList.querySelector(`[data-user-id="${userId}"]`);
            if (existingAssignment) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Usuario ya asignado',
                    text: 'Este usuario ya está asignado al lead.'
                });
                return;
            }

            $.ajax({
                url: `leads/${leadId}/assign-user`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken
                },
                data: { user_id: userId },
                success: function(response) {
                    if (response.success) {
                        const index = leads.findIndex(l => l.id === leadId);
                        if (index !== -1) {
                            leads[index] = response.lead;
                        }

                        // Actualizar la lista de asignaciones
                        const noAssignmentsMessage = assignmentsList.querySelector('.text-muted');
                        if (noAssignmentsMessage) {
                            assignmentsList.innerHTML = '';
                        }

                        const newAssignment = response.lead.assignments.find(a => a.user_id === parseInt(userId));
                        const assignmentElement = document.createElement('div');
                        assignmentElement.className = 'assignment-item';
                        assignmentElement.dataset.userId = userId;
                        assignmentElement.innerHTML = `
                            ${newAssignment.user.name}
                            <button class="btn btn-sm btn-danger remove-assignment-btn" 
                                    data-user-id="${userId}" 
                                    data-lead-id="${leadId}">
                                <i class="bx bx-x"></i>
                            </button>
                        `;
                        assignmentsList.appendChild(assignmentElement);

                        // Remover el usuario del select
                        document.querySelector(`#user-select option[value="${userId}"]`).remove();
                    }
                }
            });
        });
    }

    // Eliminar asignación
    document.querySelector('.assignments-list').addEventListener('click', function(e) {
        if (e.target.closest('.remove-assignment-btn')) {
            const button = e.target.closest('.remove-assignment-btn');
            const userId = button.dataset.userId;
            const leadId = button.dataset.leadId;

            if (!leadId) {
                console.error('No se pudo encontrar el ID del lead');
                return;
            }

            $.ajax({
                url: `leads/${leadId}/remove-assignment/${userId}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken
                },
                success: function(response) {
                    if (response.success) {
                        e.target.closest('.assignment-item').remove();
                    }
                }
            });
        }
    });

    document.getElementById('save-lead').addEventListener('click', function() {
        const activeLeadCard = document.querySelector('.kanban-item.active');
        if (!activeLeadCard) {
            console.error('No se encontró el lead activo');
            return;
        }

        const leadId = activeLeadCard.dataset.id;
        const formData = {
            name: document.getElementById('name').value,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value,
            description: document.getElementById('description').value,
            amount_of_money: document.getElementById('amount_of_money').value,
            category_id: document.getElementById('category_id').value,
            position: document.getElementById('position').value,
            type: document.getElementById('type').value,
        };

        if (!formData.name) {
            $('#leadModal').modal('hide'); 
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'El nombre es requerido'
            });
            return;
        }

        $.ajax({
            url: `leads/${leadId}`,
            type: 'PUT',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': window.csrfToken
            },
            success: function (response) {
                $('#leadModal').modal('hide');
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: response.message
                    }).then(() => {
                        location.reload(); 
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function (xhr) {
                $('#leadModal').modal('hide'); 
                console.error('Error:', xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al intentar guardar los cambios'
                });
            }
        });
    });

    let currentLeadId = null;

    document.getElementById('companyButton').addEventListener('click', function() {
        const leadModal = bootstrap.Modal.getInstance(document.getElementById('leadModal'));
        leadModal.hide();
        
        // Hacer una petición AJAX para obtener los datos actualizados del lead
        $.ajax({
            url: `leads/${currentLeadId}`,
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': window.csrfToken
            },
            success: function(response) {
                if (response.success) {
                    const lead = response.lead;
                    const companyModal = new bootstrap.Modal(document.getElementById('companyModal'));
                    companyModal.show();
                    
                    // Cargar datos de la compañía si existen
                    if (lead.company_information) {
                        document.getElementById('company_name').value = lead.company_information.name || '';
                        document.getElementById('company_address').value = lead.company_information.address || '';
                        document.getElementById('company_city').value = lead.company_information.city || '';
                        document.getElementById('company_state').value = lead.company_information.state || '';
                        document.getElementById('company_postal_code').value = lead.company_information.postal_code || '';
                        document.getElementById('company_country').value = lead.company_information.country || '';
                        document.getElementById('company_webpage').value = lead.company_information.webpage || '';
                    } else {
                        // Limpiar el formulario si no hay información de la compañía
                        document.getElementById('company_name').value = '';
                        document.getElementById('company_address').value = '';
                        document.getElementById('company_city').value = '';
                        document.getElementById('company_state').value = '';
                        document.getElementById('company_postal_code').value = '';
                        document.getElementById('company_country').value = '';
                        document.getElementById('company_webpage').value = '';
                    }
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al cargar la información de la compañía'
                });
            }
        });
    });

    document.getElementById('save-company').addEventListener('click', function() {
        const formData = {
            name: document.getElementById('company_name').value,
            address: document.getElementById('company_address').value,
            city: document.getElementById('company_city').value,
            state: document.getElementById('company_state').value,
            postal_code: document.getElementById('company_postal_code').value,
            country: document.getElementById('company_country').value,
            webpage: document.getElementById('company_webpage').value
        };

        $.ajax({
            url: `leads/${currentLeadId}/company-information`,
            type: 'PUT',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': window.csrfToken
            },
            success: function(response) {
                if (response.success) {
                    const companyModal = bootstrap.Modal.getInstance(document.getElementById('companyModal'));
                    companyModal.hide();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Información de la compañía actualizada correctamente'
                    }).then(() => {
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al actualizar la información de la compañía'
                });
            }
        });
    });

    function showLeadDetailsModal(lead) {
        // Obtener el lead actualizado del servidor
        $.ajax({
            url: `leads/${lead.id}`,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    // Actualizar el lead en el array leads
                    const index = leads.findIndex(l => l.id === lead.id);
                    if (index !== -1) {
                        leads[index] = response.lead;
                    }
                    
                    currentLeadId = lead.id;  
                    const modal = document.getElementById('leadModal');
                    const modalTitle = modal.querySelector('#leadModalLabel');
                    const clientButton = modal.querySelector('#clientButton');
                    const addAssignmentBtn = modal.querySelector('#add-assignment-btn');

                    modalTitle.innerHTML = `<i class="bx bx-user me-2"></i>Detalles del Lead`;

                    // Cargar las asignaciones existentes
                    const assignmentsList = document.querySelector('.assignments-list');
                    if (lead.assignments && lead.assignments.length > 0) {
                        assignmentsList.innerHTML = lead.assignments.map(assignment => `
                            <div class="assignment-item" data-user-id="${assignment.user_id}">
                                ${assignment.user.name}
                                <button class="btn btn-sm btn-danger remove-assignment-btn" 
                                        data-user-id="${assignment.user_id}" 
                                        data-lead-id="${lead.id}">
                                    <i class="bx bx-x"></i>
                                </button>
                            </div>
                        `).join('');
                    } else {
                        assignmentsList.innerHTML = '<p class="text-muted text-center">No hay usuarios asignados.</p>';
                    }

                    // Actualizar el select eliminando usuarios ya asignados
                    const userSelect = document.getElementById('user-select');
                    userSelect.innerHTML = users
                        .filter(user => !lead.assignments.some(assignment => assignment.user_id === user.id))
                        .map(user => `<option value="${user.id}">${user.name}</option>`)
                        .join('');

                    // Actualizar el data-lead-id del botón de asignación
                    addAssignmentBtn.setAttribute('data-lead-id', lead.id);

                    // Usar el lead actualizado del servidor
                    const updatedLead = response.lead;

                    // Configurar el botón de cliente con la información actualizada
                    if (updatedLead.client_id) {
                        clientButton.innerHTML = `<i class='bx bx-user-check'></i><span class="ms-1">Cliente Actual</span>`;
                        clientButton.disabled = true;
                        clientButton.classList.add('btn-secondary');
                        clientButton.classList.remove('btn-light');
                    } else {
                        clientButton.innerHTML = `<i class='bx bx-user-plus'></i><span class="ms-1">Guardar Cliente</span>`;
                        clientButton.disabled = false;
                        clientButton.classList.add('btn-light');
                        clientButton.classList.remove('btn-secondary');
                    }

                    // Cargar datos básicos del lead
                    const creatorName = updatedLead.creator ? updatedLead.creator.name : 'No disponible';
                    const storeName = updatedLead.store ? updatedLead.store.name : 'No disponible';

                    modal.querySelector('#creator_name').value = creatorName;
                    modal.querySelector('#store_name').value = storeName;
                    modal.querySelector('#name').value = updatedLead.name || '';
                    modal.querySelector('#email').value = updatedLead.email || '';
                    modal.querySelector('#phone').value = updatedLead.phone || '';
                    modal.querySelector('#description').value = updatedLead.description || '';
                    modal.querySelector('#amount_of_money').value = updatedLead.amount_of_money || '';
                    modal.querySelector('#category_id').value = updatedLead.category_id;
                    modal.querySelector('#position').value = updatedLead.position;
                    modal.querySelector('#type').value = updatedLead.type || 'individual';

                    const bsModal = new bootstrap.Modal(modal);
                    bsModal.show();
                }
            }
        });
    }

    // Agregar el evento click para el botón de cliente
    document.getElementById('clientButton').addEventListener('click', function() {
        const leadModal = bootstrap.Modal.getInstance(document.getElementById('leadModal'));
        
        // Obtener el lead actualizado del servidor antes de hacer la validación
        $.ajax({
            url: `leads/${currentLeadId}`,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const updatedLead = response.lead;
                    
                    // Verificar campos requeridos con el lead actualizado
                    const missingFields = [];
                    
                    if (!updatedLead.name) missingFields.push('Nombre');
                    if (!updatedLead.email) missingFields.push('Email');
                    if (!updatedLead.phone) missingFields.push('Teléfono');
                    if (!updatedLead.company_information?.address) missingFields.push('Dirección');
                    
                    if (missingFields.length > 0) {
                        leadModal.hide();
                        
                        Swal.fire({
                            icon: 'warning',
                            title: 'Campos Requeridos',
                            html: `Para convertir el lead a cliente, primero complete los siguientes campos:<br><br>
                                  <ul style="text-align: left; display: inline-block;">
                                      ${missingFields.map(field => `<li>${field}</li>`).join('')}
                                  </ul>`,
                            confirmButtonText: 'Entendido'
                        });
                        return;
                    }

                    // Si pasa la validación, cerrar el modal y mostrar confirmación
                    leadModal.hide();

                    Swal.fire({
                        title: '¿Convertir a cliente?',
                        text: "¿Estás seguro de que deseas convertir este lead en cliente?",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, convertir',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: `leads/${currentLeadId}/convert-to-client`,
                                type: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': window.csrfToken
                                },
                                success: function(response) {
                                    if (response.success) {
                                        // Actualizar el lead en el array leads
                                        const index = leads.findIndex(l => l.id === currentLeadId);
                                        if (index !== -1) {
                                            leads[index] = response.lead;
                                        }

                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Éxito',
                                            text: 'Lead convertido a cliente correctamente'
                                        }).then(() => {
                                            location.reload();
                                        });
                                    }
                                },
                                error: function(xhr) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: xhr.responseJSON?.message || 'Ocurrió un error al convertir el lead a cliente'
                                    });
                                }
                            });
                        }
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al obtener la información actualizada del lead'
                });
            }
        });
    });

    const companyModal = document.querySelector('#companyModal');
    const companyModalHeader = companyModal.querySelector('.modal-header');
    const backButton = document.createElement('button');
    backButton.className = 'btn btn-icon btn-light btn-sm me-2';
    backButton.innerHTML = '<i class="bx bx-arrow-back"></i>';
    companyModalHeader.insertBefore(backButton, companyModalHeader.firstChild);

    let currentLeadData = null;

    // Guardado de la info del LEAD para luego cuando se vaya para atras con la flechita del modal de compañia,
    // se pueda volver a cargar la info del LEAD.
    document.querySelector('#companyButton').addEventListener('click', function() {
        currentLeadData = {
            id: document.querySelector('#leadId').value,
            name: document.querySelector('#name').value,
            email: document.querySelector('#email').value,
            phone: document.querySelector('#phone').value,
            description: document.querySelector('#description').value,
            amount: document.querySelector('#amount_of_money').value,
            type: document.querySelector('#type').value,
            category_id: document.querySelector('#category_id').value,
            position: document.querySelector('#position').value
        };
    });

    // Volver a cargar la info del LEAD cuando se vuelve para atras con la flechita del modal de compañia
    backButton.addEventListener('click', function() {
        // Ocultar modal de compañía
        const companyModalInstance = bootstrap.Modal.getInstance(companyModal);
        companyModalInstance.hide();

        // Mostrar modal de LEAD
        if (currentLeadData) {
            const leadModal = document.querySelector('#leadModal');
            const leadModalInstance = new bootstrap.Modal(leadModal);
            
            // Cargar los datos del LEAD
            document.querySelector('#leadId').value = currentLeadData.id;
            document.querySelector('#name').value = currentLeadData.name;
            document.querySelector('#email').value = currentLeadData.email;
            document.querySelector('#phone').value = currentLeadData.phone;
            document.querySelector('#description').value = currentLeadData.description;
            document.querySelector('#amount_of_money').value = currentLeadData.amount;
            document.querySelector('#type').value = currentLeadData.type;
            document.querySelector('#category_id').value = currentLeadData.category_id;
            document.querySelector('#position').value = currentLeadData.position;

            leadModalInstance.show();
        }
    });
});