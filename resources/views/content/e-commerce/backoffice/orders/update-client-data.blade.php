<div class="offcanvas offcanvas-end" tabindex="-1" id="updateClientDataOffcanvas" aria-labelledby="updateClientDataOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="updateClientDataOffcanvasLabel">Completar datos faltantes</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="updateClientDataForm" action="{{ route('clients.update', $order->client_id) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="type" value="{{ $order->client->type }}">
            
            @if($order->client->type === 'individual')
                <!-- Individual fields -->
                @if(empty($order->client->name))
                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" class="form-control" name="name" required>
                </div>
                @endif
                
                @if(empty($order->client->lastname))
                <div class="mb-3">
                    <label class="form-label">Apellido</label>
                    <input type="text" class="form-control" name="lastname" required>
                </div>
                @endif
                
                @if(empty($order->client->ci))
                <div class="mb-3">
                    <label class="form-label">CI</label>
                    <input type="text" class="form-control" name="ci" required>
                </div>
                @endif
            @else
                <!-- Company fields -->
                @if(empty($order->client->company_name))
                <div class="mb-3">
                    <label class="form-label">Razón Social</label>
                    <input type="text" class="form-control" name="company_name" required>
                </div>
                @endif
                
                @if(empty($order->client->rut))
                <div class="mb-3">
                    <label class="form-label">RUT</label>
                    <input type="text" class="form-control" name="rut" required>
                </div>
                @endif
            @endif

            <!-- Common fields -->
            @if(empty($order->client->address))
            <div class="mb-3">
                <label class="form-label">Dirección</label>
                <input type="text" class="form-control" name="address" required>
            </div>
            @endif
            
            @if(empty($order->client->city))
            <div class="mb-3">
                <label class="form-label">Ciudad</label>
                <input type="text" class="form-control" name="city" required>
            </div>
            @endif
            
            @if(empty($order->client->state))
            <div class="mb-3">
                <label class="form-label">Departamento</label>
                <input type="text" class="form-control" name="state" required>
            </div>
            @endif
            
            @if(empty($order->client->country))
            <div class="mb-3">
                <label class="form-label">País</label>
                <input type="text" class="form-control" name="country" required value="Uruguay">
            </div>
            @endif

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Guardar y Facturar</button>
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
    $('#updateClientDataForm').on('submit', function(e) {
    e.preventDefault();
    const form = $(this);
    
    $.ajax({
        url: form.attr('action'),
        method: 'POST',
        data: form.serialize(),
        success: function(response) {
            location.reload();
        },
        error: function(xhr) {
            const errors = xhr.responseJSON.errors;
            Object.keys(errors).forEach(field => {
                const input = form.find(`[name="${field}"]`);
                input.addClass('is-invalid');
                input.after(`<div class="invalid-feedback">${errors[field][0]}</div>`);
            });
        }
    });
});
</script>