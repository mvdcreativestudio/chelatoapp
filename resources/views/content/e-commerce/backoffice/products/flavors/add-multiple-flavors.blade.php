<!-- Enlace al CSS de Tagify -->
<link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet">

<!-- Enlace al JS de Tagify -->
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>

<!-- Modal para Agregar Múltiples Variaciones -->
<div class="modal fade" id="addMultipleFlavorsModal" tabindex="-1" aria-labelledby="addMultipleFlavorsModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addMultipleFlavorsModalLabel">Agregar Múltiples Variaciones</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addMultipleFlavorsForm">
          <div class="mb-3">
            <label for="tagifyFlavorNames" class="form-label">Nombres de Variaciones</label>
            <input type="text" class="form-control tagify" id="tagifyFlavorNames" name="flavorNames" placeholder="Añade variaciones" required>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" onclick="submitMultipleFlavors()">Guardar Variaciones</button>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function () {
      var input = document.getElementById('tagifyFlavorNames'),
          tagify = new Tagify(input);

      window.submitMultipleFlavors = function() {
          var tags = tagify.value.map(tag => tag.value.trim()).filter(Boolean);

          fetch('{{ route("product-flavors.store-multiple") }}', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              },
              body: JSON.stringify({ name: tags })
          })
          .then(response => response.json())
          .then(data => {
              if (data.success) {
                  // Cerrar el modal primero
                  $('#addMultipleFlavorsModal').modal('hide');

                  // Esperar a que el modal se cierre completamente antes de mostrar SweetAlert
                  $('#addMultipleFlavorsModal').on('hidden.bs.modal', function () {
                      Swal.fire({
                          icon: 'success',
                          title: '¡Éxito!',
                          text: data.message,
                          confirmButtonText: 'Cerrar'
                      }).then((result) => {
                          if (result.isConfirmed) {
                              location.reload(); // Recargar la página completa, o puedes recargar solo la tabla si es necesario
                          }
                      });
                  });
              } else {
                  throw new Error(data.message || 'Error al guardar los variaciones.');
              }
          })
          .catch((error) => {
              Swal.fire({
                  icon: 'error',
                  title: '¡Error!',
                  text: error.toString(),
                  confirmButtonText: 'Cerrar'
              });
          });
      };
  });
</script>



