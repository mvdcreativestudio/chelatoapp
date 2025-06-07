document.addEventListener('DOMContentLoaded', function() {
    // Corregir la ruta base eliminando la barra adicional
    const baseUrl = window.baseUrl.endsWith('/') 
        ? window.baseUrl.slice(0, -1) // Elimina la barra final si existe
        : window.baseUrl;
    const apiBaseUrl = `${baseUrl}/admin`;
    
    console.log('API Base URL:', apiBaseUrl); // Para debug

    // Verificar que el modal existe
    const columnModalElement = document.getElementById('columnModal');
    if (!columnModalElement) {
        console.error('Modal element not found');
        return;
    }

    // Inicializar el modal
    const columnModal = new bootstrap.Modal(columnModalElement);
    
    // Añadir nueva columna
    const addColumnBtn = document.getElementById('add-column-btn');
    if (addColumnBtn) {
        addColumnBtn.addEventListener('click', function() {
            document.getElementById('columnModalTitle').textContent = 'Nueva Columna';
            document.getElementById('column-id').value = '';
            document.getElementById('column-name').value = '';
            document.getElementById('column-color').value = '#0d6efd';
            columnModal.show();
        });
    }

    // Editar columna
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-column')) {
            const btn = e.target.closest('.edit-column');
            const id = btn.dataset.id;
            
            fetch(`${apiBaseUrl}/lead-categories/${id}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    document.getElementById('columnModalTitle').textContent = 'Editar Columna';
                    document.getElementById('column-id').value = data.category.id;
                    document.getElementById('column-name').value = data.category.name;
                    document.getElementById('column-color').value = data.category.color;
                    columnModal.show();
                })
                .catch(error => {
                    console.error('Error fetching category:', error);
                    alert('Error al cargar la categoría');
                });
        }
    });

    // Guardar columna
    const saveColumnBtn = document.getElementById('save-column');
    if (saveColumnBtn) {
        saveColumnBtn.addEventListener('click', function() {
            console.log('Save button clicked');
            
            // Obtener los valores del formulario
            const id = document.getElementById('column-id').value;
            const name = document.getElementById('column-name').value;
            const color = document.getElementById('column-color').value;
            
            // Validar datos
            if (!name || !color) {
                alert('Por favor complete todos los campos');
                return;
            }
            
            const data = {
                name: name,
                color: color,
                order: id ? undefined : 999 // Nueva columna va al final
            };
            
            console.log('Sending data:', data);

            // Determinar URL y método
            const url = id 
                ? `${apiBaseUrl}/lead-categories/${id}` 
                : `${apiBaseUrl}/lead-categories`;
            const method = id ? 'PUT' : 'POST';
            
            // Enviar solicitud
            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        console.error('Error response:', text);
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Success:', data);
                columnModal.hide();
                
                // Determinar si es creación o edición
                const isNew = !document.getElementById('column-id').value;
                const actionText = isNew ? 'creada' : 'actualizada';
                
                // Mostrar alerta de éxito
                Swal.fire({
                    title: 'Éxito',
                    text: `La columna ha sido ${actionText} correctamente`,
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    window.location.reload();
                });
            })
            .catch(error => {
                console.error('Error saving category:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Error al guardar la categoría',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            });
        });
    }

    // Eliminar columna
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-column')) {
            const btn = e.target.closest('.delete-column');
            const id = btn.dataset.id;
            
            // Verificar si hay leads en esta columna
            const leadsInCategory = leads.filter(lead => lead.category_id == id).length;
            
            fetch(`${apiBaseUrl}/lead-categories`)
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    const categories = data.categories.filter(cat => cat.id != id);
                    
                    // Caso 1: Es la única columna pero no tiene leads - permitir eliminar
                    if (categories.length === 0 && leadsInCategory === 0) {
                        Swal.fire({
                            title: '¿Eliminar columna?',
                            text: 'Esta es la última columna del tablero. Si la eliminas, deberás crear una nueva columna para añadir leads.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Sí, eliminar',
                            cancelButtonText: 'Cancelar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                deleteColumn(id);
                            }
                        });
                        return;
                    }
                    
                    // Caso 2: Es la única columna y tiene leads - no permitir eliminar
                    if (categories.length === 0 && leadsInCategory > 0) {
                        Swal.fire({
                            title: 'No se puede eliminar',
                            text: 'Esta es la única columna y contiene leads. Crea otra columna primero antes de eliminar esta.',
                            icon: 'error',
                            confirmButtonText: 'Entendido'
                        });
                        return;
                    }
                    
                    // Caso 3: No tiene leads - permitir eliminar directamente
                    if (leadsInCategory === 0) {
                        Swal.fire({
                            title: '¿Eliminar columna?',
                            text: 'Esta columna no contiene leads y se eliminará inmediatamente.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Sí, eliminar',
                            cancelButtonText: 'Cancelar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                deleteColumn(id);
                            }
                        });
                        return;
                    }
                    
                    // Caso 4: Tiene leads y hay otras columnas - preguntar a dónde migrar
                    const options = categories.map(cat => 
                        `<option value="${cat.id}">${cat.name}</option>`
                    ).join('');
                    
                    Swal.fire({
                        title: '¿Eliminar columna?',
                        html: `
                            <p>Esta columna contiene ${leadsInCategory} lead(s).</p>
                            <p>Selecciona la columna a donde mover los leads existentes:</p>
                            <select class="form-select" id="target-category">
                                ${options}
                            </select>
                        `,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const targetId = document.getElementById('target-category').value;
                            deleteColumn(id, targetId);
                        }
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Error al cargar las categorías',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                });
        }
        
        // Función auxiliar para eliminar columna
        function deleteColumn(id, targetId = null) {
            const requestData = targetId ? { target_category_id: targetId } : {};
            
            fetch(`${apiBaseUrl}/lead-categories/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                },
                body: JSON.stringify(requestData)
            })
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Eliminada',
                        text: 'La columna ha sido eliminada correctamente',
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        window.location.reload();
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Error al eliminar la categoría',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            });
        }
    });

    // Manejo de la selección de colores
    const colorOptions = document.querySelectorAll('.color-option');
    const moreColorsBtn = document.getElementById('more-colors-btn');
    const customColorPicker = document.getElementById('custom-color-picker');
    const columnColorInput = document.getElementById('column-color');
    const selectedColorBox = document.getElementById('selected-color-box');
    const selectedColorHex = document.getElementById('selected-color-hex');

    // Función para actualizar la vista previa del color seleccionado
    function updateSelectedColor(color) {
        // Actualizar el input de color oculto
        columnColorInput.value = color;
        
        // Actualizar la vista previa del color
        selectedColorBox.style.backgroundColor = color;
        selectedColorHex.textContent = color;
        
        // Actualizar la selección visual
        colorOptions.forEach(option => {
            if (option.dataset.color === color) {
                option.classList.add('selected');
            } else {
                option.classList.remove('selected');
            }
        });
    }

    // Event listeners para las opciones de color predefinidas
    colorOptions.forEach(option => {
        option.addEventListener('click', function() {
            const color = this.dataset.color;
            updateSelectedColor(color);
        });
    });

    // Event listener para el botón "Más colores"
    moreColorsBtn.addEventListener('click', function() {
        // Mostrar siempre el selector de color cuando se hace clic en "Más colores"
        if (customColorPicker.style.display === 'none') {
            customColorPicker.style.display = 'block';
            this.innerHTML = 'Ocultar selector <i class="bx bx-palette"></i>';
        } else {
            customColorPicker.style.display = 'none';
            this.innerHTML = 'Más colores <i class="bx bx-palette"></i>';
        }
        
        // Enfocar el selector de color cuando se muestra
        if (customColorPicker.style.display === 'block') {
            columnColorInput.focus();
            // Simular un clic para abrir el selector de color nativo
            columnColorInput.click();
        }
    });

    // Event listener para el input de color personalizado
    columnColorInput.addEventListener('input', function() {
        updateSelectedColor(this.value);
    });

    // Cuando se muestra el modal de columna, actualizar la vista previa del color
    document.getElementById('columnModal').addEventListener('show.bs.modal', function() {
        // Si estamos editando una columna existente
        if (document.getElementById('column-id').value) {
            updateSelectedColor(columnColorInput.value);
        } else {
            // Para una nueva columna, usar el valor por defecto
            updateSelectedColor('#0d6efd');
        }
    });
});