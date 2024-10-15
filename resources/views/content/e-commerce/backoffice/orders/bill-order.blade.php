<div class="modal fade" id="emitirFacturaModal" tabindex="-1" aria-labelledby="emitirFacturaLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('orders.emitCFE', ['order' => $order->id]) }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="emitirFacturaLabel">Emitir Factura</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="amountToBill" class="form-label">Monto a Facturar</label>
            <input type="number" class="form-control mb-2" id="amountToBill" name="amountToBill" min="0" max="{{ $order->total }}" step="0.01" value="{{ $order->total }}" required>
            <small class="text-muted mt-2">El monto máximo que puede facturar es de ${{ $order->total }}.</small>
          </div>
          <div class="mb-3">
            <label for="payType" class="form-label">Forma de Pago</label>
            <select class="form-control" id="payType" name="payType">
              <option value="1">Contado</option>
              <option value="2">Crédito</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Emitir Factura</button>
        </div>
      </form>
    </div>
  </div>
</div>
