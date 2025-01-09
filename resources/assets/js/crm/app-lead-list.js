document.addEventListener('DOMContentLoaded', function () {

  function updateColumnCounters(fromCategory, toCategory) {
    const fromBadge = document.querySelector(`[data-category="${fromCategory}"]`)
      .closest('.kanban-column')
      .querySelector('.badge');
    const fromCount = parseInt(fromBadge.textContent) - 1;
    fromBadge.textContent = fromCount;

    const toBadge = document.querySelector(`[data-category="${toCategory}"]`)
      .closest('.kanban-column')
      .querySelector('.badge');
    const toCount = parseInt(toBadge.textContent) + 1;
    toBadge.textContent = toCount;
  }

  const columns = document.querySelectorAll('.kanban-items');
  columns.forEach(column => {
    new Sortable(column, {
      group: 'leads',
      animation: 150,
      onEnd: function (evt) {
        const leadId = evt.item.dataset.id;
        const newCategoryId = evt.to.dataset.category;
        const newPosition = Array.from(evt.to.children).indexOf(evt.item);

        // Obtener todos los elementos en la nueva columna
        const itemsInNewColumn = Array.from(evt.to.children).map((item, index) => ({
          id: item.dataset.id,
          position: index
        }));

        updateColumnCounters(evt.from.dataset.category, evt.to.dataset.category);

        $.ajax({
          url: `${baseUrl}admin/leads/${leadId}/update-category`,
          method: 'PUT',
          headers: {
            'X-CSRF-TOKEN': window.csrfToken
          },
          data: {
            category_id: newCategoryId,
            position: newPosition,
            items_order: itemsInNewColumn
          },
          error: function (error) {
            console.error('Error:', error);
            evt.from.appendChild(evt.item);
          }
        });
      }
    });
  });

  // Agregar nuevo lead
  const addLeadBtns = document.querySelectorAll('.add-lead-btn');

  addLeadBtns.forEach(btn => {
    btn.addEventListener('click', function () {
      const category_id = this.dataset.category_id;
      const column = this.closest('.kanban-column').querySelector('.kanban-items');

      const tempCard = document.createElement('div');
      tempCard.className = 'kanban-item card cursor-move mb-3';
      tempCard.innerHTML = `
        <div class="card-body">
          <div class="mb-2">
            <label class="form-label small mb-1">Nombre *</label>
            <input type="text" class="form-control form-control-sm lead-name" placeholder="Ingrese nombre">
          </div>
          <div class="mb-2">
            <label class="form-label small mb-1">Email</label>
            <input type="email" class="form-control form-control-sm lead-email" placeholder="Ingrese email">
          </div>
          <div class="mb-3">
            <label class="form-label small mb-1">Teléfono</label>
            <input type="tel" class="form-control form-control-sm lead-phone" placeholder="Ingrese teléfono">
          </div>
          <div class="d-flex justify-content-between">
            <button class="btn btn-outline-secondary btn-sm cancel-temp-lead">
              <i class="bx bx-x"></i> 
            </button>
            <button class="btn btn-primary btn-sm save-temp-lead">
              <i class="bx bx-check"></i> 
            </button>
          </div>
        </div>
      `;

      column.insertAdjacentElement('beforeend', tempCard);

      const nameInput = tempCard.querySelector('.lead-name');
      nameInput.focus();

      const cancelBtn = tempCard.querySelector('.cancel-temp-lead');
      cancelBtn.addEventListener('click', () => tempCard.remove());

      const saveBtn = tempCard.querySelector('.save-temp-lead');
      saveBtn.addEventListener('click', function () {
        const name = tempCard.querySelector('.lead-name').value.trim();
        const email = tempCard.querySelector('.lead-email').value.trim();
        const phone = tempCard.querySelector('.lead-phone').value.trim();

        if (!name) {
          nameInput.classList.add('is-invalid');
          return;
        }

        $.ajax({
          url: 'leads',
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': window.csrfToken
          },
          data: {
            name: name,
            email: email,
            phone: phone,
            category_id: category_id
          },
          success: function (data) {
            if (data.success) {
              Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: 'Lead creado correctamente'
              }).then(() => {
                location.reload();
              });
            }
          },
          error: function (error) {
            console.error('Error:', error);
            tempCard.remove();
          }
        });
      });

      document.addEventListener('click', function removeTemp(e) {
        if (!tempCard.contains(e.target) && !btn.contains(e.target)) {
          tempCard.remove();
          document.removeEventListener('click', removeTemp);
        }
      });
    });
  });

  document.addEventListener('click', function (e) {
    if (e.target.closest('.delete-lead')) {
      const card = e.target.closest('.kanban-item');
      const leadId = card.dataset.id;

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
            url: `leads/${leadId}`,
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': window.csrfToken
            },
            success: function (response) {
              card.remove();
              Swal.fire(
                '¡Eliminado!',
                'El lead ha sido eliminado.',
                'success'
              ).then(() => {
                location.reload();
              });
            },
            error: function (error) {
              console.error('Error:', error);
              Swal.fire(
                'Error',
                'No se pudo eliminar el lead.',
                'error'
              );
            }
          });
        }
      });
    }
  });
});
