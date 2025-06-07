<!-- Modal Edit Current Account Setting -->
<div class="modal fade" id="editCurrentAccountSettingModal" tabindex="-1" aria-labelledby="editCurrentAccountSettingModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editCurrentAccountSettingModalLabel">Editar Configuración de Cuenta Corriente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editCurrentAccountSettingForm">
          <div class="mb-3">
            <label for="transaction_type_edit" class="form-label">Tipo de Transacción</label>
            <select class="form-select" id="transaction_type_edit" name="transaction_type" required>
              <option value="" selected disabled>Seleccione el tipo de transacción</option>
              @foreach($transactionTypes as $key => $value)
                <option value="{{ $key }}">{{ $value }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label for="late_fee_edit" class="form-label">Recargo por Mora</label>
            <input type="number" class="form-control" id="late_fee_edit" name="late_fee" required placeholder="Ingrese el recargo por mora">
          </div>
          <div class="mb-3">
            <label for="payment_terms_edit" class="form-label">Términos de Pago (Días)</label>
            <input type="text" class="form-control" id="payment_terms_edit" name="payment_terms" required placeholder="Ingrese los términos de pago">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="updateCurrentAccountSettingBtn" data-route="{{ route('current-account-settings.update', ':id') }}">Actualizar Configuración</button>
      </div>
    </div>
  </div>
</div>
