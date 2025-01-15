@if($order->client)
<div class="offcanvas offcanvas-end" tabindex="-1" id="updateClientDataOffcanvas">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Datos del cliente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <form id="updateClientDataForm" action="{{ route('clients.update', $order->client_id) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="type" value="{{ $order->client->type }}">

            <!-- Current Client Info Section -->
            <div class="mb-4">
                <h6 class="fw-bold">Información Actual</h6>
                @if($order->client->type === 'individual')
                <div class="mb-3">
                    <label class="form-label text-muted">Nombre</label>
                    <input type="text" class="form-control" value="{{ $order->client->name }}" disabled readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">Apellido</label>
                    <input type="text" class="form-control" value="{{ $order->client->lastname }}" disabled readonly>
                </div>
                @else
                <div class="mb-3">
                    <label class="form-label text-muted">Razón Social</label>
                    <input type="text" class="form-control" value="{{ $order->client->company_name }}" disabled readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">RUT</label>
                    <input type="text" class="form-control" value="{{ $order->client->rut }}" disabled readonly>
                </div>
                @endif
            </div>

            <hr class="my-4">

            <!-- Missing Fields Section -->
            <div class="mb-4">
                <h6 class="fw-bold">Campos Faltantes</h6>
                @if($order->client->type === 'individual')
                @if(empty($order->client->ci))
                <div class="mb-3">
                    <label class="form-label">CI</label>
                    <input type="text" class="form-control" name="ci" required>
                </div>
                @endif
                @else
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

                @php
                $translations = [
                'address' => 'Dirección',
                'city' => 'Ciudad',
                'state' => 'Departamento',
                'country' => 'País'
                ];
                @endphp

                @foreach(['address', 'city', 'state', 'country'] as $field)
                @if(empty($order->client->$field))
                <div class="mb-3">
                    <label class="form-label">{{ $translations[$field] }}</label>
                    <input type="text" class="form-control" name="{{ $field }}" required>
                </div>
                @endif
                @endforeach
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Cancelar</button>
            </div>
        </form>
    </div>
</div>
@endif