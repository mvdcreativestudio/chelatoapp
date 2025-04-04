<!-- Modal para Importar Productos -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="importModalLabel">Importar Proveedores</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="importForm" action="{{ route('suppliers.import') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="mb-3">
            <label for="importFile" class="form-label">Subir archivo Excel (.xlsx)</label>
            <input class="form-control" type="file" id="importFile" name="file" accept=".xlsx" required>
          </div>
          <div class="mb-3">
            <a href="{{ route('suppliers.download-template') }}" class="text-muted text-sm" id="download-template">Haz click aquí para descargar la plantilla</a>
          </div>
          <button type="submit" class="btn btn-primary">Subir</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
    $(document).ready(function() {
        // Manejador para el botón de importar
        $('#openImportModal').on('click', function(e) {
            e.preventDefault();
            $('#importModal').modal('show');
        });
        // Manejador para el formulario de importación
        $('#importForm').on('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#importModal').modal('hide');
                    toastr.success('Proveedores importados correctamente');
                    location.reload(); // Actualizar la lista de proveedores
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON;
                    if (errors && errors.errors) {
                        // Mostrar errores de validación
                        toastr.error(Object.values(errors.errors).join('\n'));
                    } else {
                        toastr.error('Error al importar proveedores');
                    }
                }
            });
        });
    });
</script>