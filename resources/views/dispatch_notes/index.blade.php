@extends('layouts/layoutMaster')

@section('title', 'Remitos')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/select2/select2.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/jquery-repeater/jquery-repeater.js'
])
@endsection

@section('page-script')
<script type="text/javascript">
    window.baseUrl = "{{ url('') }}/";
    window.csrfToken = "{{ csrf_token() }}";
    window.hasEditDeliveryData = {{ auth()->user()->can('access_edit_delivery_data') ? 'true' : 'false' }};
</script>
@vite(['resources/assets/js/app-dispatch-notes-list.js'])
@endsection

@section('content')
<div class="container-fluid p-4">
    <!-- Alerts -->
    @if (session('success'))
    <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
    @foreach ($errors->all() as $error)
    <div class="alert alert-danger">{{ $error }}</div>
    @endforeach
    @endif

    <div class="mb-4">
        <a href="{{ url('admin/orders/' . $order->uuid) }}">
            <i class="bx bx-arrow-back"></i> Volver a la orden
        </a>
    </div>

    <!-- Products Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="m-0">Productos de la orden</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach ($products as $product)
                <div class="col-md-4 mb-4"> 
                    <div class="card h-100 shadow-sm border-0 transition-transform hover:scale-105"> 
                        <div class="card-body d-flex flex-column p-4"> 
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title text-primary fw-bold m-0">{{ $product['name'] }}</h5>
                                <span class="badge bg-light text-dark">{{ $product['quantity'] }} metro(s)</span>
                            </div>

                            <hr class="my-3">

                            <button class="btn btn-primary w-100 mt-auto create-dispatch-note"
                                data-product-id="{{ $product['id'] }}"
                                data-product-name="{{ $product['name'] }}"
                                data-product-quantity="{{ $product['quantity'] }}">
                                <i class="fas fa-file-alt me-2"></i> 
                                Crear remito
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Dispatch Notes Section -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="m-0">Remitos</h5>
            @if($dispatchNotes->count() > 0)
            <a href="{{ route('dispatch-notes.pdf', $order->uuid) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-download me-1"></i>Descargar remitos
            </a>
            @endif
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach ($dispatchNotes as $dispatchNote)
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <h6 class="mb-0">Remito #{{ $dispatchNote->id }}</h6>
                                    <a href="{{ route('dispatch-notes.single.pdf', $dispatchNote->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bx bx-download"></i>
                                    </a>
                                </div>
                                <span class="badge bg-light text-dark">{{ \Carbon\Carbon::parse($dispatchNote->date)->format('d/m/y') }}</span>
                            </div>
                            <div class="mb-2">
                                <strong class="text-muted">Producto:</strong>
                                {{ $dispatchNote->product->name }}
                            </div>
                            <div class="mb-2">
                                <strong class="text-muted">Cantidad:</strong>
                                {{ $dispatchNote->quantity }}
                            </div>
                            <div class="mb-2">
                                <strong class="text-muted">Bombeo:</strong>
                                @if($dispatchNote->bombing_type == 'Drag')
                                Arrastre
                                @elseif($dispatchNote->bombing_type == 'Throw')
                                Lanza
                                @else
                                No aplicable
                                @endif
                            </div>
                            <div>
                                <strong class="text-muted">Entrega:</strong>
                                @if($dispatchNote->delivery_method == 'Dumped')
                                Volcado
                                @elseif($dispatchNote->delivery_method == 'Pumped')
                                Bombeado
                                @endif
                            </div>
                            <div class="mt-3">
                                <strong class="text-muted">Envío:</strong>
                                @if($dispatchNote->noteDelivery->count() > 0)
                                <button class="btn btn-sm btn-info show-delivery"
                                    data-dispatch-note-id="{{ $dispatchNote->noteDelivery->first()->id }}">
                                    <i class="bx bx-show"></i>
                                </button>
                                <button class="btn btn-sm btn-warning edit-dispatch-note"
                                    data-dispatch-note-id="{{ $dispatchNote->noteDelivery->first()->id}}">
                                    <i class="bx bx-pencil"></i>
                                </button>
                                @else
                                <button class="btn btn-sm btn-primary create-delivery"
                                    data-dispatch-note-id="{{ $dispatchNote->id }}">
                                    Realizar envío
                                </button>
                                @endif
                                <button class="btn btn-sm btn-danger delete-dispatch-note"
                                    data-dispatch-note-id="{{ $dispatchNote->id }}">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Create Dispatch Note Modal -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasCreateDispatchNote">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Nuevo Remito</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="createDispatchNoteForm" class="needs-validation" novalidate>
            @csrf
            <input type="hidden" name="product_id" id="product_id">
            <input type="hidden" name="order_id" value="{{ $order->id }}">

            <div class="mb-3">
                <label class="form-label">Producto</label>
                <input type="text" class="form-control bg-light" id="product_name" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label" for="quantity">Cantidad</label>
                <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="date">Fecha</label>
                <input type="date" class="form-control" id="date" name="date" required>
            </div>

            <div class="mb-3">
                <label class="form-label" for="bombing_type">Tipo de Bombeo</label>
                <select class="form-select" id="bombing_type" name="bombing_type" required>
                    <option value="">Seleccionar...</option>
                    <option value="Drag">Arrastre</option>
                    <option value="Throw">Lanza</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label" for="delivery_method">Método de Entrega</label>
                <select class="form-select" id="delivery_method" name="delivery_method" required>
                    <option value="">Seleccionar...</option>
                    <option value="Dumped">Volcado</option>
                    <option value="Pumped">Bombeado</option>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">Guardar</button>
                <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancelar</button>
            </div>
        </form>
    </div>
</div>


<!-- Delivery modal -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasCreateDelivery">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Nuevo Envío</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="createDeliveryForm" class="needs-validation" novalidate>
            @csrf
            <input type="hidden" name="dispatch_note_id" id="dispatch_note_id">

            <div class="mb-3">
                <label class="form-label">Vehículo</label>
                <select class="form-select" name="vehicle_id" id="vehicle_id" required>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Conductor</label>
                <select class="form-select" name="driver_id" id="driver_id" required>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Producido en</label>
                <select class="form-select" name="store_id" id="store_id" required>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Salida</label>
                <input type="datetime-local" class="form-control" name="departuring" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Llegada</label>
                <input type="datetime-local" class="form-control" name="arriving" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Inicio de descarga</label>
                <input type="datetime-local" class="form-control" name="unload_starting" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Fin de descarga</label>
                <input type="datetime-local" class="form-control" name="unload_finishing" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Salida del sitio</label>
                <input type="datetime-local" class="form-control" name="departure_from_site" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Regreso a la planta</label>
                <input type="datetime-local" class="form-control" name="return_to_plant" required>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">Guardar</button>
                <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Delivery Details Modal -->
<div class="modal fade" id="deliveryDetailsModal" tabindex="-1" aria-labelledby="deliveryDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deliveryDetailsModalLabel">Detalles del Envío</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEditDelivery">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Editar Envío</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="editDeliveryForm" class="needs-validation" novalidate>
            @csrf
            @method('PUT')
            <input type="hidden" name="dispatch_note_id" id="edit_dispatch_note_id">
            <input type="hidden" name="note_delivery_id" id="note_delivery_id">

            <div class="mb-3">
                <label class="form-label">Vehículo</label>
                <input type="text" class="form-control bg-light" id="edit_vehicle" readonly>
                <input type="hidden" name="vehicle_id" id="edit_vehicle_id">
            </div>

            <div class="mb-3">
                <label class="form-label">Conductor</label>
                <input type="text" class="form-control bg-light" id="edit_driver" readonly>
                 <input type="hidden" name="driver_id" id="edit_driver_id">
            </div>

            <div class="mb-3">
                <label class="form-label">Producido en</label>
                <input type="text" class="form-control bg-light" id="edit_store" readonly>
                <input type="hidden" name="store_id" id="edit_store_id">
            </div>

            <div class="mb-3">
                <label class="form-label">Salida</label>
                <input type="datetime-local" 
                    class="form-control" 
                    name="departuring" 
                    id="edit_departuring" 
                    {{ auth()->user()->cannot('access_edit_delivery_data') ? 'readonly' : '' }}
                    required>
            </div>

            <div class="mb-3">
                <label class="form-label">Llegada</label>
                <input type="datetime-local" 
                    class="form-control" 
                    name="arriving" 
                    id="edit_arriving" 
                    {{ auth()->user()->cannot('access_edit_delivery_data') ? 'readonly' : '' }}
                    required>
            </div>

            <div class="mb-3">
                <label class="form-label">Inicio de descarga</label>
                <input type="datetime-local" 
                    class="form-control" 
                    name="unload_starting" 
                    id="edit_unload_starting" 
                    {{ auth()->user()->cannot('access_edit_delivery_data') ? 'readonly' : '' }}
                    required>
            </div>

            <div class="mb-3">
                <label class="form-label">Fin de descarga</label>
                <input type="datetime-local" 
                    class="form-control" 
                    name="unload_finishing" 
                    id="edit_unload_finishing" 
                    {{ auth()->user()->cannot('access_edit_delivery_data') ? 'readonly' : '' }}
                    required>
            </div>

            <div class="mb-3">
                <label class="form-label">Salida del sitio</label>
                <input type="datetime-local" 
                    class="form-control" 
                    name="departure_from_site" 
                    id="edit_departure_from_site" 
                    {{ auth()->user()->cannot('access_edit_delivery_data') ? 'readonly' : '' }}
                    required>
            </div>

            <div class="mb-3">
                <label class="form-label">Regreso a la planta</label>
                <input type="datetime-local" 
                    class="form-control" 
                    name="return_to_plant" 
                    id="edit_return_to_plant" 
                    {{ auth()->user()->cannot('access_edit_delivery_data') ? 'readonly' : '' }}
                    required>
            </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">Guardar</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cerrar</button>
                </div>
        </form>
    </div>
</div>
@endsection