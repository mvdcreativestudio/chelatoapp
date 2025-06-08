@extends('layouts/layoutMaster')

@section('title', 'Detalle de Orden Interna')

@section('content')

@php
  $selected = old('status', $order->status?->value);
@endphp

<script>
  window.baseUrl = "{{ url('') }}/";
  window.cashRegisterId = "{{ Session::get('open_cash_register_id') }}";
</script>

<div class="d-flex align-items-center justify-content-between bg-white p-4 mb-3 rounded shadow-lg border-bottom border-light">
  <h4 class="mb-0 page-title">
    <i class="bx bx-package me-2"></i> Orden #{{ $order->id }} desde {{ $order->fromStore->name }}
  </h4>
  <div class="col-3 justify-content-between d-flex align-items-center">
    <a href="{{ route('internal-orders.pdf', $order->id) }}" class="btn btn-success btn-sm">
      <i class="bx bx-download"></i> Exportar PDF
    </a>
    @php
      $dataToPrefill = base64_encode(json_encode([
        'products' => $order->items->map(fn($item) => [
          'id' => $item->product_id,
          'quantity' => $item->deliver_quantity ?? $item->quantity,
        ])->values(),
        'client_id' => optional($order->client)->id // si la orden tiene un cliente asignado
      ]));
    @endphp

    <button onclick="loadOrderToCart({{ $order->id }})" class="btn btn-primary btn-sm ms-2">
      <i class="bx bx-cart"></i> Generar Venta
    </button>

  </div>
</div>

<script>
function loadOrderToCart(orderId) {
  // Obtener los productos de la orden
  const orderData = @json($order->items->map(fn($item) => [
    'id' => $item->product_id,
    'quantity' => $item->deliver_quantity ?? $item->quantity,
  ]));

  // Obtener los detalles de los productos
  fetch(`${window.baseUrl}admin/pdv/products/${window.cashRegisterId}`)
    .then(response => response.json())
    .then(data => {
      const products = data.products;
      const cart = [];

      // Mapear los productos de la orden al formato del carrito
      orderData.forEach(orderItem => {
        const product = products.find(p => p.id === orderItem.id);
        if (product) {
          cart.push({
            id: product.id,
            name: product.name,
            price: product.price || product.old_price,
            base_price: product.price || product.old_price,
            original_price: product.price || product.old_price,
            final_price: product.price || product.old_price,
            currency: product.currency || 'Peso',
            tax_rate: product.tax_rate,
            quantity: orderItem.quantity,
            image: product.image,
            sku: product.sku,
            description: product.description || '',
            isComposite: product.type === 'configurable'
          });
        }
      });

      // Guardar el carrito en la sesión
      fetch('/admin/pdv/api-cart', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ cart: cart })
      })
      .then(response => {
        console.log('Respuesta bruta de api-cart:', response);
        return response.text();
      })
      .then(text => {
        console.log('Texto recibido de api-cart:', text);
        let data;
        try {
          data = JSON.parse(text);
        } catch (e) {
          console.error('No se pudo parsear JSON:', e, text);
          alert('La respuesta de api-cart no es JSON válido');
          return;
        }

        // Obtener el client_id de la tienda destino (toStore)
        const clientId = @json($order->toStore->client_id);
        console.log('ID del cliente destino:', clientId);

        if (clientId) {
          fetch('/admin/pdv/clients/json')
            .then(response => response.json())
            .then(data => {
              const client = (data.clients || []).find(c => c.id == clientId);
              if (client) {
                // Guardar el cliente completo en la sesión
                return fetch(`/admin/pdv/client-session`, {
                  method: 'POST',
                  headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                  },
                  body: JSON.stringify({ client: client })
                });
              } else {
                throw new Error('Cliente no encontrado');
              }
            })
            .then(response => response.json())
            .then(data => {
              if (data.status === 'success') {
                window.location.href = '{{ route("pdv.cart") }}';
              } else {
                throw new Error(data.message || 'Error al guardar el cliente en la sesión');
              }
            })
            .catch(error => {
              console.error('Error:', error);
              alert('Error al procesar el cliente: ' + error.message);
              window.location.href = '{{ route("pdv.cart") }}';
            });
        } else {
          window.location.href = '{{ route("pdv.cart") }}';
        }
      })
      .catch(error => {
        console.error('Error en el fetch de api-cart:', error);
        alert('Error al guardar el carrito: ' + error);
      });
    });
}
</script>

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif



<form action="{{ route('internal-orders.update', $order->id) }}" method="POST">
  @csrf
  @method('PUT')

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <h5 class="card-title">Datos de la Orden</h5>
      <small class="mb-5 pb-5">Creada el: <b>{{ $order->created_at->format('d/m/Y H:i') }}</b></small>
      <div class="row mb-3 mt-4">
        <div class="col-md-4">
          <label class="form-label">Estado</label>
          <select name="status" class="form-select" required>
            @php $selected = old('status', $order->status->value); @endphp
            <option value="pending"   @selected($selected === 'pending')>Pendiente</option>
            <option value="accepted"  @selected($selected === 'accepted')>Aceptada</option>
            <option value="rejected"  @selected($selected === 'rejected')>Rechazada</option>
            <option value="delivered" @selected($selected === 'delivered')>Entregada</option>
            <option value="cancelled" @selected($selected === 'cancelled')>Cancelada</option>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label">Fecha de Entrega</label>
          <input type="date" name="delivery_date" class="form-control"
            value="{{ old('delivery_date', optional($order->delivery_date)->format('Y-m-d')) }}">
        </div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <h5 class="card-title">Productos</h5>

      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead>
            <tr>
              <th>Producto</th>
              <th>Cantidad Solicitada</th>
              <th>Cantidad a Entregar</th>
            </tr>
          </thead>
          <tbody>
            @foreach($order->items as $item)
              <tr>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>
                  <input type="number"
                    name="items[{{ $item->id }}][deliver_quantity]"
                    value="{{ old('items.' . $item->id . '.deliver_quantity', $item->deliver_quantity ?? $item->quantity) }}"
                    class="form-control" min="0">
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

    </div>
  </div>

  <div class="text-end">
    <button type="submit" class="btn btn-primary">
      <i class="bx bx-save"></i> Guardar Cambios
    </button>
  </div>
</form>
@endsection
