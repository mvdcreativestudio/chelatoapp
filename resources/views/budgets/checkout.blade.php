@extends('layouts/layoutMaster')

@section('title', 'Finalizar Venta - Presupuesto')

@section('vendor-style')
@vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
    'resources/assets/vendor/libs/select2/select2.scss'
])
@endsection

@section('content')
<div class="p-1">
    <div id="errorContainer" class="alert alert-danger d-none" role="alert"></div>

    <div class="row">
        <!-- Header con botón volver -->
        <div class="col-12 d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">
                <a href="{{ route('budgets.detail', $budget->id) }}" class="btn m-0 p-0">
                    <i class="bx bx-chevron-left fs-2"></i>
                </a> 
                Volver al Presupuesto
            </h5>
        </div>

        <!-- Columna izquierda: Detalles y Observaciones -->
        <div class="col-md-8">
            <!-- Tabla de productos -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Detalles del Presupuesto #{{ $budget->id }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>Cliente</h6>
                        <p class="mb-1">{{ $budget->client->name ?? $budget->lead->name ?? 'Sin Cliente' }}</p>
                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-end">Precio Base</th>
                                    <th class="text-center">Descuento</th>
                                    <th class="text-end">Precio Final</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($budget->items as $item)
                                @php
                                    $basePrice = $item->price;
                                    $finalPrice = $basePrice;
                                    if ($item->discount_type === 'Percentage' && $item->discount_price > 0) {
                                        $finalPrice = $basePrice * (1 - ($item->discount_price / 100));
                                    } elseif ($item->discount_type === 'Fixed' && $item->discount_price > 0) {
                                        $finalPrice = $basePrice - $item->discount_price;
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $item->product->name }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">${{ number_format($basePrice, 2) }}</td>
                                    <td class="text-center">
                                        @if($item->discount_type === 'Percentage')
                                            {{ $item->discount_price }}%
                                        @elseif($item->discount_type === 'Fixed')
                                            ${{ number_format($item->discount_price, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end">${{ number_format($finalPrice, 2) }}</td>
                                    <td class="text-end">${{ number_format($finalPrice * $item->quantity, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Observaciones -->
            <div class="card shadow-sm p-3 mb-4">
                <h5>Observación</h5>
                <textarea name="notes" form="checkoutForm" class="form-control" placeholder="Digite la observación aquí"></textarea>
            </div>
        </div>

        <!-- Columna derecha: Resumen y Forma de pago -->
        <div class="col-md-4">
            <!-- Resumen de la venta -->
            <div class="card shadow-sm p-3 mb-3">
                <h5>Resumen de la venta</h5>
                <div class="d-flex justify-content-between">
                    <span>Subtotal</span>
                    <span class="subtotal">${{ number_format($budget->subtotal, 2) }}</span>
                </div>
                @if($budget->discount)
                <div class="d-flex justify-content-between">
                    <span>Descuento</span>
                    <span class="discount-amount">
                        @if($budget->discount_type === 'Percentage')
                            {{ $budget->discount }}%
                        @else
                            ${{ number_format($budget->discount, 2) }}
                        @endif
                    </span>
                </div>
                @endif
                <hr>
                <div class="d-flex justify-content-between">
                    <strong>Total</strong>
                    <strong class="total" id="totalAmount" data-total="{{ $budget->total }}">${{ number_format($budget->total, 2) }}</strong>
                </div>
            </div>

            <!-- Formulario de pago -->
            <div class="card shadow-sm p-4 bg-light">
                <form id="checkoutForm" action="{{ route('budgets.process-checkout', $budget->id) }}" method="POST">
                    @csrf
                    <h5 class="mb-3">Método de Pago</h5>
                    <div class="payment-options d-flex flex-wrap gap-2">
                        <div class="payment-option flex-grow-1">
                            <input type="radio" class="btn-check" name="payment_method" id="cash" value="cash" checked>
                            <label class="btn btn-outline-primary w-100" for="cash">
                                <i class="bx bx-money"></i> Efectivo
                            </label>
                        </div>
                        <div class="payment-option flex-grow-1">
                            <input type="radio" class="btn-check" name="payment_method" id="card" value="card">
                            <label class="btn btn-outline-primary w-100" for="card">
                                <i class="bx bx-credit-card"></i> Tarjeta
                            </label>
                        </div>
                        <div class="payment-option flex-grow-1">
                            <input type="radio" class="btn-check" name="payment_method" id="transfer" value="transfer">
                            <label class="btn btn-outline-primary w-100" for="transfer">
                                <i class="bx bx-transfer"></i> Transferencia
                            </label>
                        </div>
                    </div>

                    <div class="mt-3" id="cashFields">
                        <label class="form-label">Monto Recibido</label>
                        <input type="number" name="amount_received" class="form-control form-control-lg mb-2" step="0.01">
                        <div id="changeAmount" class="form-text"></div>
                    </div>

                    <div class="demo-inline-spacing d-flex justify-content-between mt-4">
                        <a href="{{ route('budgets.detail', $budget->id) }}" class="btn btn-outline-danger">
                            <i class="bx bx-x"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="bx bx-check"></i> Finalizar Venta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
@vite(['resources/assets/js/app-budgets-checkout.js'])
@endsection