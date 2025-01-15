<div class="modal fade" id="uploadCaesModal" tabindex="-1" aria-labelledby="uploadCaesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="uploadCaesModalLabel">Cargar nuevos CAEs</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="uploadCaesForm">
          <div class="mb-3">
            <h6>RUT: <span id="storeRut" class="text-primary"></span></h6>
            <p class="text-muted">Selecciona un archivo para cada tipo de CAE que desees cargar.</p>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Tipo de CAE</th>
                  <th>Archivo</th>
                  <th>Estado</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>eTicket</td>
                  <td><input type="file" name="caes[101]" class="form-control cae-file" data-type="101"></td>
                  <td class="status-101 text-muted">Pendiente</td>
                </tr>
                <tr>
                  <td>eTicket - Nota de Crédito</td>
                  <td><input type="file" name="caes[102]" class="form-control cae-file" data-type="102"></td>
                  <td class="status-102 text-muted">Pendiente</td>
                </tr>
                <tr>
                  <td>eTicket - Nota de Débito</td>
                  <td><input type="file" name="caes[103]" class="form-control cae-file" data-type="103"></td>
                  <td class="status-103 text-muted">Pendiente</td>
                </tr>
                <tr>
                  <td>eFactura</td>
                  <td><input type="file" name="caes[111]" class="form-control cae-file" data-type="111"></td>
                  <td class="status-111 text-muted">Pendiente</td>
                </tr>
                <tr>
                  <td>eFactura - Nota de Crédito</td>
                  <td><input type="file" name="caes[112]" class="form-control cae-file" data-type="112"></td>
                  <td class="status-112 text-muted">Pendiente</td>
                </tr>
                <tr>
                  <td>eFactura - Nota de Débito</td>
                  <td><input type="file" name="caes[113]" class="form-control cae-file" data-type="113"></td>
                  <td class="status-113 text-muted">Pendiente</td>
                </tr>
              </tbody>
            </table>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="uploadCaesBtn">Subir CAEs</button>
      </div>
    </div>
  </div>
</div>
