@extends('layouts/layoutMaster')

@section('title', 'Transacciones')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
'resources/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.scss',
])
@endsection
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/es.min.js"></script>

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/moment/moment.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js',
])
@endsection

@section('content')
<div class="container-fluid mb-3">
  <div class="card">
      <div class="card-header">Consulta de Transacciones</div>
      <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
              <div class="col-md-12 col-lg-8">
                  <div class="mt-0">
                      <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#transactionModal">
                          Consultar Transacciones
                      </button>
                  </div>
              </div>
          </div>
      </div>
  </div>
</div>

<!-- Tabla de Resultados -->
<div class="card mt-3">
    <div class="card-header">
        Resultados de Transacciones
    </div>
    <div class="card-body">
        <div id="transactionResults" class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>TransactionId</th>
                        <th>Acquirer</th>
                        <th>AcquirerTerminal</th>
                        <th>AuthorizationCode</th>
                        <th>Batch</th>
                        <th>CardNumber</th>
                        <th>ClientAppId</th>
                        <th>Currency</th>
                        <th>PosId</th>
                        <th>PosResponseCode</th>
                        <th>PosResponseCodeExtension</th>
                        <th>State</th>
                        <th>Ticket</th>
                        <th>TotalAmount</th>
                        <th>TransactionDate</th>
                        <th>TransactionType</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Los datos se llenarán dinámicamente -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1" aria-labelledby="transactionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title" id="transactionModalLabel">Consulta de Transacciones</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <form id="transactionHistoryForm">
                  <div class="row mb-3">
                      <div class="col-md-6">
                          <label for="storeSelector">Seleccionar Tienda</label>
                          <select id="storeSelector" class="form-select">
                              <option value="">Seleccionar una tienda</option>
                              @foreach ($stores as $store)
                                  <option value="{{ $store->id }}">{{ $store->name }}</option>
                              @endforeach
                          </select>
                      </div>
                      <div class="col-md-6">
                          <label for="pos_device">Seleccionar POS</label>
                          <select id="pos_device" class="form-select" name="pos_device_id">
                              <option value="">Todos los POS</option>
                          </select>
                      </div>
                  </div>
                  <div class="row mb-3">
                      <div class="col-md-6">
                          <label for="from_date">Desde</label>
                          <input type="text" id="from_date" name="from_date" class="form-control datepicker" placeholder="Selecciona fecha y hora">
                      </div>
                      <div class="col-md-6">
                          <label for="to_date">Hasta</label>
                          <input type="text" id="to_date" name="to_date" class="form-control datepicker" placeholder="Selecciona fecha y hora">
                      </div>
                  </div>
                  <div class="row mb-3">
                      <div class="col-md-12">
                          <input type="checkbox" id="only_confirmed" name="only_confirmed" value="1">
                          <label for="only_confirmed">Solo transacciones confirmadas</label>
                      </div>
                  </div>
              </form>
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
              <button type="button" class="btn btn-primary" id="submitTransactionForm">Consultar</button>
          </div>
      </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const storeSelector = document.getElementById('storeSelector');
    const posDeviceSelect = document.getElementById('pos_device');
    const transactionResults = document.getElementById('transactionResults');
    const datepickers = document.querySelectorAll('.datepicker');

    // Inicializar Flatpickr
    if (datepickers.length > 0) {
        flatpickr(datepickers, {
            enableTime: true,
            time_24hr: true,
            dateFormat: "YmdHiss",
            locale: "es",
        });
    }

    // Cargar POS dinámicamente
    storeSelector.addEventListener('change', function () {
        const storeId = this.value;

        if (!storeId) {
            posDeviceSelect.innerHTML = '<option value="">Seleccionar POS</option>';
            return;
        }

        fetch(`/api/pos/devices/${storeId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    posDeviceSelect.innerHTML = '<option value="">Seleccionar POS</option>';
                    data.devices.forEach(device => {
                        const option = document.createElement('option');
                        option.value = device.id;
                        option.textContent = device.identifier;
                        posDeviceSelect.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Error al cargar dispositivos POS:', error));
    });

    // Enviar formulario
    document.getElementById('submitTransactionForm').addEventListener('click', function () {
        const formData = new FormData(document.getElementById('transactionHistoryForm'));
        const data = Object.fromEntries(formData.entries());

        const storeId = document.getElementById('storeSelector').value;
        if (!storeId) {
            Swal.fire({
                title: 'Error',
                text: 'Por favor selecciona una tienda antes de consultar.',
                icon: 'error',
                confirmButtonText: 'OK',
            });
            return;
        }
        data.store_id = storeId;
        data.only_confirmed = document.getElementById('only_confirmed').checked ? 1 : 0;

        Swal.fire({
            title: 'Consultando...',
            text: 'Por favor espera mientras obtenemos los datos.',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
        });

        fetch('/api/pos/fetchTransactionHistory', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify(data),
        })
            .then(response => response.json())
            .then(result => {
                Swal.close();
                const modal = bootstrap.Modal.getInstance(document.getElementById('transactionModal'));
                modal.hide();

                if (result.success) {
                    renderTransactionResults(result.data);
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: result.message,
                        icon: 'error',
                        confirmButtonText: 'OK',
                    });
                }
            })
            .catch(error => {
                Swal.close();
                Swal.fire({
                    title: 'Error',
                    text: 'Ocurrió un error al consultar las transacciones.',
                    icon: 'error',
                    confirmButtonText: 'OK',
                });
                console.error('Error al consultar transacciones:', error);
            });
    });

    function renderTransactionResults(transactions) {
        const tbody = transactionResults.querySelector('tbody');
        tbody.innerHTML = transactions.map(transaction => `
            <tr>
                <td>${transaction.TransactionId}</td>
                <td>${transaction.Acquirer || 'N/A'}</td>
                <td>${transaction.AcquirerTerminal || 'N/A'}</td>
                <td>${transaction.AuthorizationCode || 'N/A'}</td>
                <td>${transaction.Batch || 'N/A'}</td>
                <td>${transaction.CardNumber || 'N/A'}</td>
                <td>${transaction.ClientAppId || 'N/A'}</td>
                <td>${transaction.Currency || 'N/A'}</td>
                <td>${transaction.PosId || 'N/A'}</td>
                <td>${transaction.PosResponseCode || 'N/A'}</td>
                <td>${transaction.PosResponseCodeExtension || 'N/A'}</td>
                <td>${transaction.State || 'N/A'}</td>
                <td>${transaction.Ticket || 'N/A'}</td>
                <td>${transaction.TotalAmount || 'N/A'}</td>
                <td>${formatTransactionDate(transaction.TransactionDateTimeyyyyMMddHHmmssSSS)}</td>
                <td>${transaction.TransactionType || 'N/A'}</td>
            </tr>
        `).join('');
    }

    function formatTransactionDate(dateTimeString) {
        return moment(dateTimeString, "YYYYMMDDHHmmssSSS").format("DD/MM/YYYY HH:mm:ss");
    }
});
</script>
@endsection
