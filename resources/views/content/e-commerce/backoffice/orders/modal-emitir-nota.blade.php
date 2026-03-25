<div class="modal fade" id="emitirNotaModal" tabindex="-1" aria-labelledby="emitirNotaLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="#" method="POST" id="emitirNotaForm">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="emitirNotaLabel">Emitir Nota</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="noteType" class="form-label">Tipo de Nota</label>
            <select class="form-control" id="noteType" name="noteType" required>
              <option value="credit">Nota de Crédito</option>
              <option value="debit">Nota de Débito</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="noteAmount" class="form-label">Monto de la Nota</label>
            <input type="number" class="form-control" id="noteAmount" name="noteAmount" min="0" step="0.01" required>
            <small class="text-muted">Para nota de crédito, el monto no puede superar el saldo de la factura.</small>
          </div>
          <div class="mb-3">
            <label for="reason" class="form-label">Razón de la Nota</label>
            <textarea class="form-control" id="reason" name="reason" rows="2" required></textarea>
          </div>
          <div class="mb-3">
            <label for="emissionDate" class="form-label">Fecha y Hora de Emisión</label>
            <input type="datetime-local" class="form-control" id="emissionDate" name="emissionDate"
                   value="{{ now()->format('Y-m-d\TH:i') }}" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Emitir Nota</button>
        </div>
      </form>
    </div>
  </div>
</div>
