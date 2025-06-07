<!-- Modal Add New Current Account Setting -->
<div class="modal fade" id="addCurrentAccountSettingModal" tabindex="-1" aria-labelledby="addCurrentAccountSettingModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addCurrentAccountSettingModalLabel">Agregar Nueva Configuración de Cuenta Corriente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addNewCurrentAccountSettingForm">
          <div class="mb-3">
            <label for="transaction_type" class="form-label">Tipo de Transacción</label>
            <select class="form-select" id="transaction_type" name="transaction_type" required>
              <option value="" selected disabled>Seleccione el tipo de transacción</option>
              @foreach($transactionTypes as $key => $value)
                <option value="{{ $key }}">{{ $value }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label for="late_fee" class="form-label">Recargo por Mora</label>
            <input type="number" class="form-control" id="late_fee" name="late_fee" required placeholder="Ingrese el recargo por mora">
          </div>
          <div class="mb-3">
            <label for="payment_terms" class="form-label">Términos de Pago (Días)</label>
            <input type="text" class="form-control" id="payment_terms" name="payment_terms" required placeholder="Ingrese los términos de pago">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="submitCurrentAccountSettingBtn" data-route="{{ route('current-account-settings.store') }}">Guardar Configuración</button>
      </div>
    </div>
  </div>
</div>
