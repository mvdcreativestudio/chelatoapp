document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', function(e) {
        const leadCard = e.target.closest('.kanban-item');
        if (leadCard && !e.target.closest('.dropdown')) {
            const leadId = leadCard.dataset.id;
            const lead = leads.find(l => l.id == leadId);
            
            if (lead) {
                showTasksModal(lead);
            }
        }
    });

    document.getElementById('add-task-btn').addEventListener('click', function() {
        const modal = document.getElementById('tasksModal');
        const leadName = modal.querySelector('.lead-name').textContent;
        const leadId = leads.find(l => l.name === leadName).id;

        const taskForm = `
            <div class="task-form mb-3" style="width: 100%;">
                <div class="mb-3">
                    <label class="form-label">Descripción <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="task-description" rows="2" required></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Prioridad</label>
                        <select class="form-select" id="task-priority">
                            <option value="0">🔵 Baja</option>
                            <option value="1">🟡 Media</option>
                            <option value="2">🔴 Alta</option>
                            <option value="3">⚡ Urgente</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Fecha y hora límite</label>
                        <input type="datetime-local" class="form-control" id="task-due-datetime">
                    </div>
                </div>
                <div class="text-end">
                    <button type="button" class="btn btn-primary btn-sm" id="save-task">Guardar</button>
                </div>
            </div>
        `;

        const tasksList = modal.querySelector('.tasks-list');
        
        const noTasksMessage = tasksList.querySelector('.text-muted');
        if (noTasksMessage) {
            tasksList.innerHTML = '';
        }
        
        tasksList.insertAdjacentHTML('afterbegin', taskForm);

        document.getElementById('add-task-btn').style.display = 'none';

        document.getElementById('save-task').addEventListener('click', function() {
            const description = document.getElementById('task-description').value;
            const priority = document.getElementById('task-priority').value;
            const dueDateTime = document.getElementById('task-due-datetime').value;

            if (!description) {
                const tasksModal = document.getElementById('tasksModal');
                const bsTasksModal = bootstrap.Modal.getInstance(tasksModal);
                bsTasksModal.hide();

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Por favor ingrese la descripción de la tarea.'
                }).then(() => {
                    location.reload();
                });
                return;
            }

            const tasksModal = document.getElementById('tasksModal');
            const bsTasksModal = bootstrap.Modal.getInstance(tasksModal);
            bsTasksModal.hide();

            $.ajax({
                url: 'lead-tasks',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken
                },
                data: {
                    leads_id: leadId,
                    description: description,
                    priority: priority,
                    due_date: dueDateTime,
                    status: '0'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: 'Tarea creada correctamente'
                        }).then(() => {
                            location.reload();
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Hubo un error al crear la tarea'
                    }).then(() => {
                        location.reload();
                    });
                }
            });
        });
    });

    $('#tasksModal').on('hidden.bs.modal', function () {
        document.getElementById('add-task-btn').style.display = 'block';
    });
});

function showTasksModal(lead) {
    const modal = document.getElementById('tasksModal');
    const tasksList = modal.querySelector('.tasks-list');
    
    modal.querySelector('.lead-name').textContent = lead.name;
    modal.querySelector('.lead-email').textContent = lead.email || 'No disponible';
    modal.querySelector('.lead-phone').textContent = lead.phone || 'No disponible';
    
    const emailIcon = modal.querySelector('.bx-envelope').parentElement;
    const phoneIcon = modal.querySelector('.bx-phone').parentElement;
    
    emailIcon.style.display = lead.email ? 'inline' : 'none';
    phoneIcon.style.display = lead.phone ? 'inline' : 'none';
    
    tasksList.innerHTML = '';
    
    tasksList.style.maxHeight = '300px';
    tasksList.style.overflowY = 'auto';
    tasksList.style.overflowX = 'hidden';
    tasksList.style.scrollbarWidth = 'thin';
    tasksList.style.scrollbarColor = '#6c757d #f8f9fa';
    
    if (lead.tasks && lead.tasks.length > 0) {
        updateProgressBar(lead.tasks);
        
        lead.tasks.forEach(task => {
            const taskElement = createTaskElement(task);
            tasksList.appendChild(taskElement);
        });
    } else {
        tasksList.innerHTML = '<p class="text-muted text-center">No hay tareas asociadas a este lead.</p>';
    }
    
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
}

function updateProgressBar(tasks) {
    const modal = document.getElementById('tasksModal');
    const completedTasks = tasks.filter(task => task.status === '1').length;
    const totalTasks = tasks.length;
    const progressPercentage = (completedTasks / totalTasks) * 100;
    
    const existingProgressBar = modal.querySelector('.progress');
    if (existingProgressBar) {
        existingProgressBar.remove();
    }
    
    const progressBar = `
        <div class="progress mb-3" style="height: 20px;">
            <div class="progress-bar bg-success position-relative" role="progressbar" 
                 style="width: ${progressPercentage}%;" 
                 aria-valuenow="${progressPercentage}" 
                 aria-valuemin="0" 
                 aria-valuemax="100">
                <span class="position-absolute w-100 text-center" style="left: 0; color: black;">
                    Tareas finalizadas (${completedTasks}/${totalTasks})
                </span>
            </div>
        </div>
    `;
    
    const cardBody = modal.querySelector('.card-body');
    cardBody.insertBefore(
        document.createRange().createContextualFragment(progressBar),
        cardBody.querySelector('.d-flex')
    );
}

function createTaskElement(task) {
    const taskDiv = document.createElement('div');
    taskDiv.className = 'task-item card mb-2';
    taskDiv.dataset.taskId = task.id;
    
    const priorityIcons = {
        '0': '🔵',
        '1': '🟡',
        '2': '🔴',
        '3': '⚡'
    };
    
    const priorityClasses = {
        '0': 'bg-label-primary',  // Baja - Azul
        '1': 'bg-label-warning',  // Media - Amarillo
        '2': 'bg-label-danger',   // Alta - Rojo
        '3': 'bg-label-danger'    // Urgente - Rojo
    };
    
    const priorityLabels = {
        '0': 'Baja',
        '1': 'Media',
        '2': 'Alta',
        '3': 'Urgente'
    };

    taskDiv.innerHTML = `
        <div class="card-body p-3 d-flex justify-content-between align-items-center">
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge ${priorityClasses[task.priority]}">${priorityIcons[task.priority]} ${priorityLabels[task.priority]}</span>
                    <button class="btn btn-sm btn-icon delete-task" data-task-id="${task.id}">
                        <i class="bx bx-trash text-danger"></i>
                    </button>
                </div>
                <p class="mb-1">${task.description}</p>
                <small class="text-muted">
                    <i class="bx bx-calendar me-1"></i>
                    Fecha de vencimiento: ${task.due_date ? new Date(task.due_date).toLocaleString() : '--'}
                </small>
            </div>
            <div class="ms-3 d-flex align-items-center">
                <div class="task-status-circle" 
                     style="width: 25px; height: 25px; border-radius: 50%; border: 2px solid #ddd; cursor: pointer; display: flex; align-items: center; justify-content: center; background-color: ${task.status === '1' ? '#28a745' : '#fff'}"
                     onclick="toggleTaskStatus(${task.id}, this)">
                    ${task.status === '1' ? '<i class="bx bx-check text-white"></i>' : ''}
                </div>
            </div>
        </div>
    `;

    const deleteBtn = taskDiv.querySelector('.delete-task');
    deleteBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        const taskId = this.dataset.taskId;
        
        const tasksModal = document.getElementById('tasksModal');
        const bsTasksModal = bootstrap.Modal.getInstance(tasksModal);
        bsTasksModal.hide();
        
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `lead-tasks/${taskId}`,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': window.csrfToken
                    },
                    success: function(response) {
                        if (response.success) {
                            const leadId = response.lead_id;
                            const lead = leads.find(l => l.id === leadId);
                            if (lead) {
                                lead.tasks = lead.tasks.filter(t => t.id !== taskId);
                            }
                            
                            taskDiv.remove();
                            
                            const tasksList = document.querySelector('.tasks-list');
                            if (!tasksList.children.length) {
                                tasksList.innerHTML = '<p class="text-muted text-center">No hay tareas asociadas a este lead.</p>';
                            }

                            Swal.fire(
                                '¡Eliminada!',
                                'La tarea ha sido eliminada.',
                                'success'
                            ).then(() => {
                                const bsModal = bootstrap.Modal.getInstance(document.getElementById('tasksModal'));
                                bsModal.hide();
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        Swal.fire(
                            'Error',
                            'Hubo un error al eliminar la tarea',
                            'error'
                        ).then(() => {
                            const bsModal = bootstrap.Modal.getInstance(document.getElementById('tasksModal'));
                            bsModal.hide();
                            location.reload();
                        });
                    }
                });
            }
        });
    });
    
    return taskDiv;
}

function toggleTaskStatus(taskId, element) {
    const currentStatus = element.innerHTML.includes('bx-check') ? '0' : '1';
    $.ajax({
        url: `lead-tasks/${taskId}/${currentStatus}`,
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': window.csrfToken
        },
        success: function(response) {
            if (currentStatus === '1') {
                element.style.backgroundColor = '#28a745';
                element.innerHTML = '<i class="bx bx-check text-white"></i>';
            } else {
                element.style.backgroundColor = '#fff';
                element.innerHTML = '';
            }

            // Actualizar la barra de progreso
            const modal = document.getElementById('tasksModal');
            const tasksList = modal.querySelector('.tasks-list');
            const allTasks = Array.from(tasksList.querySelectorAll('.task-item')).map(taskDiv => ({
                id: parseInt(taskDiv.dataset.taskId),
                status: taskDiv.querySelector('.task-status-circle').innerHTML.includes('bx-check') ? '1' : '0'
            }));
            
            updateProgressBar(allTasks);
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Hubo un error al actualizar el estado de la tarea'
            });
        }
    });
}

window.toggleTaskStatus = toggleTaskStatus;