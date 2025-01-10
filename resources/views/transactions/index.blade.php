@extends('layouts/layoutMaster')

@section('title', 'Gestión de Transacciones')

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

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">Gestión de Transacciones</h4>
        </div>
        <div class="card-body">
            <!-- Barra de navegación con pestañas -->
            <ul class="nav nav-tabs" id="transactionTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="transacciones-tab" data-bs-toggle="tab" href="#transacciones" role="tab"
                        aria-controls="transacciones" aria-selected="true">Transacciones</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="historial-tab" data-bs-toggle="tab" href="#historial" role="tab"
                        aria-controls="historial" aria-selected="false">Historial de Transacciones</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="cierres-tab" data-bs-toggle="tab" href="#cierres" role="tab"
                        aria-controls="cierres" aria-selected="false">Cierres de Lote</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="lotes-abiertos-tab" data-bs-toggle="tab" href="#lotes-abiertos" role="tab"
                        aria-controls="lotes-abiertos" aria-selected="false">Lotes Abiertos</a>
                </li>
            </ul>

            <!-- Contenido de las pestañas -->
            <div class="tab-content mt-3" id="transactionTabContent">
                <!-- Transacciones (Tabla de Base de Datos) -->
                <div class="tab-pane fade show active" id="transacciones" role="tabpanel"
                    aria-labelledby="transacciones-tab">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Venta #</th>
                                    <th>Tipo</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                              @foreach ($transactions as $transaction)
                              <tr>
                                  <td>{{ $transaction->TransactionId }}</td>
                                  <td>
                                      <a href="/admin/orders/{{ $transaction->order->uuid }}">
                                          {{ $transaction->order_id }}
                                      </a>
                                  </td>
                                  <td>
                                      @switch($transaction->type)
                                          @case('sale')
                                              Venta
                                              @break
                                          @case('refund')
                                              Devolución
                                              @break
                                          @case('cancellation')
                                              Cancelación
                                              @break
                                          @case('void')
                                              Anulación
                                              @break
                                          @default
                                              Desconocido
                                      @endswitch
                                  </td>
                                  <td>
                                      @switch($transaction->status)
                                          @case('pending')
                                              Pendiente
                                              @break
                                          @case('void_request')
                                              Anulación Pendiente
                                              @break
                                          @case('voided')
                                              Anulación
                                              @break
                                          @case('failed')
                                              Fallida
                                              @break
                                          @case('completed')
                                              Completada
                                              @break
                                          @case('reversed')
                                              Reversada
                                              @break
                                          @default
                                              Desconocido
                                      @endswitch
                                  </td>
                                  <td>{{ \Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y H:i') }}</td>
                                </tr>
                              @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Historial de Transacciones (Consulta API) -->
                <div class="tab-pane fade" id="historial" role="tabpanel" aria-labelledby="historial-tab">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#transactionModal">
                          Consultar Historial de Transacciones
                      </button>
                  </div>
                  <div id="transactionResultsContainer">
                      <div id="noResultsMessage" class="alert alert-info text-center">
                          Debe realizar una consulta para obtener los datos.
                      </div>
                      <div id="transactionResults" class="table-responsive" style="display: none;">
                          <table class="table table-bordered">
                              <thead>
                                  <tr>
                                      <th>Fecha</th>
                                      <th>Tipo</th>
                                      <th>ID</th>
                                      <th>N° Ticket</th>
                                      <th>Respuesta</th>
                                      <th>Ext. Respuesta</th>
                                      <th>Estado</th>
                                      <th>Financiera</th>
                                      <th>Lote</th>
                                      <th>Moneda</th>
                                      <th>Monto</th>
                                      <th>N° Terminal</th>
                                      <th>Autorización</th>
                                      <th>N° Tarjeta</th>
                                      <th>ClientAppId</th>
                                      <th>POS ID</th>
                                  </tr>
                              </thead>
                              <tbody>
                                  <!-- Resultados dinámicos de la consulta API -->
                              </tbody>
                          </table>
                      </div>
                  </div>
                </div>

                <!-- Cierres de Lote -->
                <div class="tab-pane fade" id="cierres" role="tabpanel" aria-labelledby="cierres-tab">
                  <div class="d-flex justify-content-between mb-3">
                      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#batchCloseQueryModal">
                          <i class="bi bi-search"></i> Realizar Consulta
                      </button>
                  </div>
                  <div id="batchClosesContainer">
                      <!-- Mensaje cuando no hay resultados -->
                      <div id="noBatchResultsMessage" class="alert alert-warning text-center" style="display: none;">
                          No se encontraron cierres de lote para los parámetros seleccionados.
                      </div>
                      <!-- Resultados de cierres -->
                      <div id="batchClosesResults" style="display: none;">
                          <!-- Listado de Acquirers -->
                          <div class="accordion" id="batchClosesAccordion">
                              <!-- Contenido dinámico renderizado por JavaScript -->
                          </div>
                      </div>
                  </div>
                </div>




                <!-- Modal para realizar consulta -->
                <div class="modal fade" id="batchCloseQueryModal" tabindex="-1" aria-labelledby="batchCloseQueryModalLabel" aria-hidden="true">
                  <div class="modal-dialog modal-lg">
                      <div class="modal-content">
                          <div class="modal-header bg-primary text-white">
                              <h5 class="modal-title" id="batchCloseQueryModalLabel">Consulta de Cierres de Lote</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                              <form id="batchCloseQueryForm">
                                  <div class="row mb-3">
                                      <div class="col-md-6">
                                          <label for="storeSelectorBatch" class="form-label">Seleccionar Tienda</label>
                                          <select id="storeSelectorBatch" class="form-select">
                                              <option value="">Seleccionar una tienda</option>
                                              @foreach ($stores as $store)
                                              <option value="{{ $store->id }}">{{ $store->name }}</option>
                                              @endforeach
                                          </select>
                                      </div>
                                      <div class="col-md-6">
                                          <label for="posDeviceBatch" class="form-label">Seleccionar POS</label>
                                          <select id="posDeviceBatch" class="form-select" name="pos_device_id">
                                              <option value="">Todos los POS</option>
                                          </select>
                                      </div>
                                  </div>
                                  <div class="row mb-3">
                                      <div class="col-md-6">
                                          <label for="fromDateBatch" class="form-label">Desde</label>
                                          <input type="text" id="fromDateBatch" name="from_date" class="form-control datepicker" placeholder="Selecciona fecha y hora">
                                      </div>
                                      <div class="col-md-6">
                                          <label for="toDateBatch" class="form-label">Hasta</label>
                                          <input type="text" id="toDateBatch" name="to_date" class="form-control datepicker" placeholder="Selecciona fecha y hora">
                                      </div>
                                  </div>
                                  <div class="row mb-3">
                                      <div class="col-md-12">
                                          <label for="userIdBatch" class="form-label">Usuario (Opcional)</label>
                                          <input type="text" id="userIdBatch" name="user_id" class="form-control" placeholder="ID del usuario (Opcional)">
                                      </div>
                                  </div>
                              </form>
                          </div>
                          <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                              <button type="button" class="btn btn-primary" id="submitBatchCloseQueryForm">
                                  <i class="bi bi-send"></i> Consultar
                              </button>
                          </div>
                      </div>
                  </div>
                </div>




                <!-- Lotes Abiertos -->
                <div class="tab-pane fade" id="lotes-abiertos" role="tabpanel" aria-labelledby="lotes-abiertos-tab">
                  <div class="d-flex justify-content-between mb-3">
                      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#openBatchQueryModal">
                          <i class="bi bi-search"></i> Realizar Consulta
                      </button>
                  </div>

                  <!-- Contenedor de Lotes Abiertos -->
                  <div id="openBatchesContainer">
                      <!-- Mensaje cuando no hay resultados -->
                      <div id="noOpenBatchResultsMessage" class="alert alert-warning text-center" style="display: none;">
                          No se encontraron lotes abiertos para los parámetros seleccionados.
                      </div>

                      <!-- Resultados de lotes abiertos -->
                      <div id="openBatchesResults" style="display: none;">
                          <!-- Acordeón dinámico -->
                          <div id="openBatchesAccordion" class="accordion">
                              <!-- Contenido dinámico generado por JavaScript -->
                          </div>
                      </div>
                  </div>
                </div>


                <!-- Modal para realizar consulta de lotes abiertos -->
                <div class="modal fade" id="openBatchQueryModal" tabindex="-1" aria-labelledby="openBatchQueryModalLabel" aria-hidden="true">
                  <div class="modal-dialog modal-lg">
                      <div class="modal-content">
                          <div class="modal-header bg-primary text-white">
                              <h5 class="modal-title" id="openBatchQueryModalLabel">Consulta de Lotes Abiertos</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                              <form id="openBatchQueryForm">
                                  <div class="row mb-3">
                                      <div class="col-md-6">
                                          <label for="storeSelectorOpenBatch" class="form-label">Seleccionar Tienda</label>
                                          <select id="storeSelectorOpenBatch" class="form-select">
                                              <option value="">Seleccionar una tienda</option>
                                              @foreach ($stores as $store)
                                              <option value="{{ $store->id }}">{{ $store->name }}</option>
                                              @endforeach
                                          </select>
                                      </div>
                                      <div class="col-md-6">
                                          <label for="posDeviceOpenBatch" class="form-label">Seleccionar POS</label>
                                          <select id="posDeviceOpenBatch" class="form-select" name="pos_device_id">
                                              <option value="">Todos los POS</option>
                                          </select>
                                      </div>
                                  </div>
                              </form>
                          </div>
                          <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                              <button type="button" class="btn btn-primary" id="submitOpenBatchQueryForm">
                                  <i class="bi bi-send"></i> Consultar
                              </button>
                          </div>
                      </div>
                  </div>
                </div>



                <!-- Detalle de Cierres de Lote -->
                <div class="tab-pane fade" id="detalle-cierres" role="tabpanel" aria-labelledby="detalle-cierres-tab">
                    <div class="alert alert-info">Próximamente: Implementación de Detalle de Cierres de Lote.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Consultar Transacciones -->
<div class="modal fade" id="transactionModal" tabindex="-1" aria-labelledby="transactionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transactionModalLabel">Consulta de Historial de Transacciones</h5>
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
                            <input type="text" id="from_date" name="from_date" class="form-control datepicker"
                                placeholder="Selecciona fecha y hora">
                        </div>
                        <div class="col-md-6">
                            <label for="to_date">Hasta</label>
                            <input type="text" id="to_date" name="to_date" class="form-control datepicker"
                                placeholder="Selecciona fecha y hora">
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

<!-- Modal para consultar cierres de lotes -->


<script>
  document.addEventListener('DOMContentLoaded', function () {
      const storeSelector = document.getElementById('storeSelector');
      const posDeviceSelect = document.getElementById('pos_device');
      const transactionResults = document.getElementById('transactionResults');
      const transactionResultsContainer = document.getElementById('transactionResultsContainer');
      const noResultsMessage = document.getElementById('noResultsMessage');
      const datepickers = document.querySelectorAll('.datepicker');

      // Traducciones de respuestas
      const transactionTypeMapping = @json($transactionType);
      const posResponseCodeMapping = @json($posResponseCode);
      const posResponseCodeExtensionMapping = @json($posResponseCodeExtension);
      const transactionStateMapping = @json($transactionState);
      const aquirer = @json($aquirer);
      const currency = @json($currency);

      // Inicializar Flatpickr
      if (datepickers.length > 0) {
          flatpickr(datepickers, {
              enableTime: true,
              time_24hr: true,
              dateFormat: "YmdHiss",
              altInput: true,
              altFormat: "d/m/Y",
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
                          option.textContent = `${device.name} (${device.identifier})`;
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
                      transactionResults.style.display = 'block';
                      noResultsMessage.style.display = 'none';
                  } else {
                      transactionResults.style.display = 'none';
                      noResultsMessage.style.display = 'block';
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

      function formatAmount(amount) {
          // Eliminar ceros a la izquierda
          const numericAmount = parseInt(amount, 10);
          // Dividir el valor por 100 para obtener decimales
          return (numericAmount / 100).toFixed(2);
      }

      function renderTransactionResults(transactions) {
          const tbody = transactionResults.querySelector('tbody');
          tbody.innerHTML = transactions.map(transaction => {
              // Determinar el valor del ticket
              const ticketValue = transaction.Ticket && transaction.Ticket !== 'N/A'
                  ? transaction.Ticket
                  : transaction.OriginalTicket && transaction.OriginalTicket !== 'N/A'
                  ? transaction.OriginalTicket
                  : 'N/A';

              return `
                  <tr>
                      <td>${formatTransactionDate(transaction.TransactionDate)}</td>
                      <td>${transactionTypeMapping[transaction.TransactionType] || 'Desconocido'}</td>
                      <td>${transaction.TransactionId}</td>
                      <td>${ticketValue}</td>
                      <td>${posResponseCodeMapping[transaction.PosResponseCode] || 'N/A'}</td>
                      <td>${posResponseCodeExtensionMapping[transaction.PosResponseCodeExtension] || 'N/A'}</td>
                      <td>${transactionStateMapping[transaction.State] || 'Estado desconocido'}</td>
                      <td>${aquirer[transaction.Acquirer] || 'N/A'}</td>
                      <td>${transaction.Batch || 'N/A'}</td>
                      <td>${currency[transaction.Currency] || 'N/A'}</td>
                      <td>${formatAmount(transaction.TotalAmount) || 'N/A'}</td>
                      <td>${transaction.AcquirerTerminal || 'N/A'}</td>
                      <td>${transaction.AuthorizationCode || 'N/A'}</td>
                      <td>${transaction.CardNumber || 'N/A'}</td>
                      <td>${transaction.ClientAppId || 'N/A'}</td>
                      <td>${transaction.PosId || 'N/A'}</td>
                  </tr>
              `;
          }).join('');
      }

      function formatTransactionDate(dateString) {
        // Ajustar el formato de entrada YYMMDD y convertirlo a DD/MM/YY
        return moment(dateString, "YYMMDD").format("DD/MM/YY");
      }

  });
</script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
      const transactionTypeMapping = @json($transactionType);
      const posResponseCodeMapping = @json($posResponseCode);
      const posResponseCodeExtensionMapping = @json($posResponseCodeExtension);
      const transactionStateMapping = @json($transactionState);
      const aquirer = @json($aquirer);
      const currency = @json($currency);
      const issuer = @json($issuer);

      const batchCloseQueryForm = document.getElementById('batchCloseQueryForm');
      const storeSelectorBatch = document.getElementById('storeSelectorBatch');
      const posDeviceBatch = document.getElementById('posDeviceBatch');
      const fromDateBatch = document.getElementById('fromDateBatch');
      const toDateBatch = document.getElementById('toDateBatch');
      const userIdBatch = document.getElementById('userIdBatch');
      const batchClosesResults = document.getElementById('batchClosesResults');
      const noBatchResultsMessage = document.getElementById('noBatchResultsMessage');
      const batchClosesAccordion = document.getElementById('batchClosesAccordion');

      // Inicializar los datepickers
      flatpickr(fromDateBatch, {
          enableTime: true,
          time_24hr: true,
          dateFormat: "YmdHis",
          altInput: true,
          altFormat: "d/m/Y H:i",
          locale: "es",
      });

      flatpickr(toDateBatch, {
          enableTime: true,
          time_24hr: true,
          dateFormat: "YmdHis",
          altInput: true,
          altFormat: "d/m/Y H:i",
          locale: "es",
      });

      // Cargar los dispositivos POS al cambiar el selector de tienda
      storeSelectorBatch.addEventListener('change', function () {
          const storeId = storeSelectorBatch.value;

          // Limpiar el selector de dispositivos POS si no se selecciona ninguna tienda
          if (!storeId) {
              posDeviceBatch.innerHTML = '<option value="">Seleccionar POS</option>';
              return;
          }

          // Consultar los dispositivos POS para la tienda seleccionada
          fetch(`/api/pos/devices/${storeId}`)
              .then(response => response.json())
              .then(data => {
                  if (data.success) {
                      // Llenar el selector de dispositivos POS con los datos obtenidos
                      posDeviceBatch.innerHTML = '<option value="">Seleccionar POS</option>';
                      data.devices.forEach(device => {
                          const option = document.createElement('option');
                          option.value = device.identifier; // Usar el identificador del dispositivo
                          option.textContent = `${device.name} (${device.identifier})`;
                          posDeviceBatch.appendChild(option);
                      });
                  } else {
                      // Mostrar un mensaje de error si no hay dispositivos POS disponibles
                      Swal.fire({
                          title: 'Error',
                          text: data.message || 'No se pudieron cargar los dispositivos POS.',
                          icon: 'error',
                          confirmButtonText: 'OK',
                      });
                  }
              })
              .catch(error => {
                  console.error('Error al cargar dispositivos POS:', error);
                  Swal.fire({
                      title: 'Error',
                      text: 'Ocurrió un error al cargar los dispositivos POS.',
                      icon: 'error',
                      confirmButtonText: 'OK',
                  });
              });
      });

      // Enviar el formulario de consulta de cierres
      document.getElementById('submitBatchCloseQueryForm').addEventListener('click', function () {
          const formData = new FormData(batchCloseQueryForm);
          const data = Object.fromEntries(formData.entries());
          const storeId = storeSelectorBatch.value;

          if (!storeId) {
              Swal.fire({
                  title: 'Error',
                  text: 'Por favor selecciona una tienda antes de realizar la consulta.',
                  icon: 'error',
                  confirmButtonText: 'OK',
              });
              return;
          }

          // Validar y formatear las fechas
          if (fromDateBatch.value && toDateBatch.value) {
              data.FromDateyyyyMMddHHmmss = fromDateBatch.value.padEnd(14, '0'); // Completar con ceros si faltan caracteres
              data.ToDateyyyyMMddHHmmss = toDateBatch.value.padEnd(14, '0'); // Completar con ceros si faltan caracteres
          } else {
              Swal.fire({
                  title: 'Error',
                  text: 'Debes seleccionar las fechas de inicio y fin para realizar la consulta.',
                  icon: 'error',
                  confirmButtonText: 'OK',
              });
              return;
          }

          if (!posDeviceBatch.value) {
              Swal.fire({
                  title: 'Error',
                  text: 'Por favor selecciona un dispositivo POS antes de realizar la consulta.',
                  icon: 'error',
                  confirmButtonText: 'OK',
              });
              return;
          }

          // Agregar valores obligatorios
          data.SystemId = "E62FC666-5E4A-5E1D-B80A-EAB805050505";
          data.Branch = "Sucursal1";
          data.ClientAppId = "Caja1";
          data.store_id = storeId;

          Swal.fire({
              title: 'Consultando...',
              text: 'Por favor espera mientras realizamos la consulta.',
              icon: 'info',
              allowOutsideClick: false,
              showConfirmButton: false,
          });

          fetch('/api/pos/fetchBatchCloses', {
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

                  // Cerrar el modal después de realizar la consulta
                  const modal = bootstrap.Modal.getInstance(document.getElementById('batchCloseQueryModal'));
                  if (modal) {
                      modal.hide();
                  }

                  if (result.success && Array.isArray(result.data)) {
                      renderBatchCloses(result.data);
                      noBatchResultsMessage.style.display = 'none';
                      batchClosesResults.style.display = 'block';
                  } else {
                      noBatchResultsMessage.style.display = 'block';
                      batchClosesResults.style.display = 'none';
                  }
              })
              .catch(error => {
                  Swal.close();
                  Swal.fire({
                      title: 'Error',
                      text: 'Ocurrió un error al realizar la consulta.',
                      icon: 'error',
                      confirmButtonText: 'OK',
                  });
                  console.error('Error al realizar consulta:', error);
              });
      });

      function renderBatchCloses(acquirers) {
    if (!acquirers.length) {
        batchClosesAccordion.innerHTML = '<p class="text-center text-muted">No se encontraron datos de cierres de lote.</p>';
        return;
    }

    batchClosesAccordion.innerHTML = ''; // Limpiar contenido previo

    acquirers.forEach((acquirer, index) => {
        const acquirerCode = acquirer.AcquirerCode || 'N/A';
        const acquirerName = aquirer[acquirerCode] || `Código: ${acquirerCode}`;
        const acquirerId = `acquirer-${index}`;
        const acquirerHtml = `
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading-${acquirerId}">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-${acquirerId}" aria-expanded="false" aria-controls="collapse-${acquirerId}">
                        ${acquirerName}
                    </button>
                </h2>
                <div id="collapse-${acquirerId}" class="accordion-collapse collapse" aria-labelledby="heading-${acquirerId}" data-bs-parent="#batchClosesAccordion">
                    <div class="accordion-body">
                        ${renderBatchTable(acquirer.AcquirersClose)}
                    </div>
                </div>
            </div>
        `;
        batchClosesAccordion.insertAdjacentHTML('beforeend', acquirerHtml);
    });
  }

  function renderBatchTable(batches) {
      if (!batches || batches.length === 0) {
          return '<p class="text-center text-muted">No se encontraron lotes para este adquirente.</p>';
      }

      let tableHtml = `
          <table class="table table-bordered table-hover">
              <thead class="table-light">
                  <tr>
                      <th>Lote</th>
                      <th>Fecha de Cierre</th>
                      <th>Detalles</th>
                  </tr>
              </thead>
              <tbody>
      `;

      batches.forEach(batch => {
          const batchDetails = batch.CloseTotals?.length
              ? batch.CloseTotals.map(total => `
                  <ul class="list-unstyled">
                      <li><strong>Moneda:</strong> ${currency[total.Currency] || 'N/A'}</li>
                      <li><strong>Emisor:</strong> ${issuer[total.Issuer] || 'N/A'}</li>
                      <li><strong>Transacciones:</strong> ${total.TransactionsCount || 0}</li>
                      <li><strong>Monto Total:</strong> ${formatNumberWithThousandsSeparator(total.TotalAmount)}</li>
                      <li><strong>Tipo:</strong> ${transactionTypeMapping[total.TransactionType] || 'N/A'}</li>
                  </ul>
              `).join('')
              : '<p class="text-muted">Sin detalles disponibles.</p>';

          tableHtml += `
              <tr>
                  <td>${batch.Batch || 'N/A'}</td>
                  <td>${formatDateTime(batch.CloseDate)}</td>
                  <td>${batchDetails}</td>
              </tr>
          `;
      });

      tableHtml += `
              </tbody>
          </table>
      `;

      return tableHtml;
  }



        function formatDateTime(dateTimeString) {
            return moment(dateTimeString, "YYYYMMDDHHmmss").format("DD/MM/YYYY HH:mm:ss");
        }

        function formatAmount(amount) {
            return (amount / 100).toFixed(2); // Convertir centavos a formato decimal
        }

        function formatNumberWithThousandsSeparator(number) {
          return new Intl.NumberFormat('es-ES', {
              minimumFractionDigits: 0,
              maximumFractionDigits: 0,
              useGrouping: true
          }).format(Math.round(number)); // Asegúrate de redondear si hay decimales
        }

    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const storeSelectorOpenBatch = document.getElementById('storeSelectorOpenBatch');
    const posDeviceOpenBatch = document.getElementById('posDeviceOpenBatch');
    const openBatchesResults = document.getElementById('openBatchesResults');
    const noOpenBatchResultsMessage = document.getElementById('noOpenBatchResultsMessage');
    const openBatchesAccordion = document.getElementById('openBatchesAccordion');
    const transactionStateMapping = @json($transactionState);

    // Cargar dispositivos POS dinámicamente al seleccionar una tienda
    storeSelectorOpenBatch.addEventListener('change', function () {
        const storeId = storeSelectorOpenBatch.value;

        // Limpiar el selector de POS antes de realizar una nueva carga
        posDeviceOpenBatch.innerHTML = '<option value="">Seleccionar POS</option>';

        if (!storeId) {
            return; // Si no hay tienda seleccionada, no hacemos nada
        }

        // Realizar la consulta para obtener los POS asociados a la tienda
        fetch(`/api/pos/devices/${storeId}`)
            .then(response => response.json())
            .then(data => {
                console.log('Respuesta de la API para dispositivos POS:', data); // Mostrar respuesta en la consola
                if (data.success && data.devices.length > 0) {
                    // Poblar el selector de POS con los datos obtenidos
                    data.devices.forEach(device => {
                        const option = document.createElement('option');
                        option.value = device.identifier; // Usar el identificador del dispositivo POS
                        option.textContent = `${device.name} (${device.identifier})`;
                        posDeviceOpenBatch.appendChild(option);
                    });
                } else {
                    Swal.fire({
                        title: 'No se encontraron POS',
                        text: 'No hay dispositivos POS asociados a la tienda seleccionada.',
                        icon: 'warning',
                        confirmButtonText: 'OK',
                    });
                }
            })
            .catch(error => {
                console.error('Error al cargar dispositivos POS:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Ocurrió un error al cargar los dispositivos POS.',
                    icon: 'error',
                    confirmButtonText: 'OK',
                });
            });
    });

    // Enviar formulario para consultar lotes abiertos
    document.getElementById('submitOpenBatchQueryForm').addEventListener('click', function () {
        const storeId = storeSelectorOpenBatch.value;
        const posId = posDeviceOpenBatch.value;

        if (!storeId) {
            Swal.fire({
                title: 'Error',
                text: 'Por favor selecciona una tienda antes de realizar la consulta.',
                icon: 'error',
                confirmButtonText: 'OK',
            });
            return;
        }

        if (!posId) {
            Swal.fire({
                title: 'Error',
                text: 'Por favor selecciona un dispositivo POS antes de realizar la consulta.',
                icon: 'error',
                confirmButtonText: 'OK',
            });
            return;
        }

        const data = {
            store_id: storeId,
            pos_device_id: posId,
        };

        Swal.fire({
            title: 'Consultando...',
            text: 'Por favor espera mientras realizamos la consulta.',
            icon: 'info',
            allowOutsideClick: false,
            showConfirmButton: false,
        });

        fetch('/api/pos/fetchOpenBatches', {
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
                console.log('Respuesta de la API para lotes abiertos:', result); // Mostrar respuesta en la consola

                // Cerrar el modal después de recibir la respuesta
                const modal = bootstrap.Modal.getInstance(document.getElementById('openBatchQueryModal'));
                if (modal) {
                    modal.hide();
                }

                if (result.success && result.data && Array.isArray(result.data)) {
                    if (result.data.length > 0) {
                        renderOpenBatches(result.data);
                        noOpenBatchResultsMessage.style.display = 'none';
                        openBatchesResults.style.display = 'block';
                    } else {
                        openBatchesResults.style.display = 'none';
                        noOpenBatchResultsMessage.style.display = 'block';
                    }
                } else {
                    noOpenBatchResultsMessage.style.display = 'block';
                    openBatchesResults.style.display = 'none';
                }
            })
            .catch(error => {
                Swal.close();
                Swal.fire({
                    title: 'Error',
                    text: 'Ocurrió un error al realizar la consulta.',
                    icon: 'error',
                    confirmButtonText: 'OK',
                });
                console.error('Error al realizar consulta de lotes abiertos:', error);
            });
    });

    function renderOpenBatches(transactions) {
        if (!openBatchesAccordion) {
            console.error('El elemento openBatchesAccordion no existe en el DOM.');
            return;
        }

        openBatchesAccordion.innerHTML = ''; // Limpiar contenido previo

        if (transactions.length === 0) {
            openBatchesAccordion.innerHTML = '<p class="text-center text-muted">No se encontraron transacciones abiertas.</p>';
            return;
        }

        transactions.forEach(transaction => {
            const transactionHtml = `
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading-${transaction.TransactionId}">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-${transaction.TransactionId}" aria-expanded="false" aria-controls="collapse-${transaction.TransactionId}">
                            Lote: ${transaction.Batch} | Ticket: ${transaction.Ticket} | Monto: ${formatAmount(transaction.TotalAmount)}
                        </button>
                    </h2>
                    <div id="collapse-${transaction.TransactionId}" class="accordion-collapse collapse" aria-labelledby="heading-${transaction.TransactionId}" data-bs-parent="#openBatchesAccordion">
                        <div class="accordion-body">
                            <ul class="list-group">
                                <li class="list-group-item"><strong>Transaction ID:</strong> ${transaction.TransactionId}</li>
                                <li class="list-group-item"><strong>Lote:</strong> ${transaction.Batch}</li>
                                <li class="list-group-item"><strong>Ticket:</strong> ${transaction.Ticket}</li>
                                <li class="list-group-item"><strong>Monto:</strong> ${formatAmount(transaction.TotalAmount)}</li>
                                <li class="list-group-item"><strong>Autorización:</strong> ${transaction.AuthorizationCode}</li>
                                <li class="list-group-item"><strong>Fecha y Hora:</strong> ${formatDateTime(transaction.TransactionDateTimeyyyyMMddHHmmssSSS)}</li>
                                <li class="list-group-item"><strong>PosID:</strong> ${transaction.PosID}</li>
                                <li class="list-group-item"><strong>Estado:</strong> ${transactionStateMapping[transaction.State] || 'Estado desconocido'}</li>
                                <li class="list-group-item"><strong>Número de Tarjeta:</strong> ${transaction.CardNumber}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            `;

            openBatchesAccordion.insertAdjacentHTML('beforeend', transactionHtml);
        });
    }

    // Formatear montos con separador de miles
    function formatAmount(amount) {
        return new Intl.NumberFormat('es-ES', {
            style: 'currency',
            currency: 'UYU', // Cambia según la moneda
        }).format(amount / 100); // Convertir de centavos a la moneda completa
    }

    // Formatear fecha y hora
    function formatDateTime(dateTimeString) {
        return moment(dateTimeString, 'YYYYMMDDHHmmssSSS').format('DD/MM/YYYY HH:mm:ss');
    }
});

</script>

@endsection
