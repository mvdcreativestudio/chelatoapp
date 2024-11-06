<!-- Modal de Envío de Correo -->
<div class="modal fade" id="sendEmailModal" tabindex="-1" aria-labelledby="sendEmailModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="sendEmailModalLabel">Enviar Factura por Correo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="sendEmailForm" method="POST" action="{{ route('invoices.sendEmail') }}">
        @csrf
        <input type="hidden" name="invoice_id" id="invoice_id" value="{{ $invoice->id }}">
        <div class="modal-body">
          <div class="mb-3">
            <label for="email" class="form-label">Correo electrónico del cliente</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ $order->client->email ?? '' }}" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Enviar</button>
        </div>
      </form>
    </div>
  </div>
</div>
