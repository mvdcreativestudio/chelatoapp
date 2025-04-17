@extends('layouts/layoutMaster')

@section('title', 'Detalle del Ingreso')

@if (request()->is('*/pdf'))
<!-- Cargar estilos CSS para PDF directamente -->
<link rel="stylesheet" href="{{ public_path('path/to/datatables-bootstrap5.css') }}">
<link rel="stylesheet" href="{{ public_path('path/to/datatables-responsive-bootstrap5.css') }}">
<link rel="stylesheet" href="{{ public_path('path/to/datatables-buttons-bootstrap5.css') }}">
<link rel="stylesheet" href="{{ public_path('path/to/sweetalert2.css') }}">
<link rel="stylesheet" href="{{ public_path('path/to/form-validation.css') }}">
<link rel="stylesheet" href="{{ public_path('path/to/select2.css') }}">
@else
@section('vendor-style')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/@form-validation/form-validation.scss',
'resources/assets/vendor/libs/select2/select2.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/cleavejs/cleave.js',
'resources/assets/vendor/libs/cleavejs/cleave-phone.js',
'resources/assets/vendor/libs/@form-validation/popular.js',
'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
'resources/assets/vendor/libs/@form-validation/auto-focus.js',
'resources/assets/vendor/libs/select2/select2.js'
])
@endsection

@section('page-script')
@vite([
'resources/assets/js/app-ecommerce-order-details.js',
])
<script>
    window.baseUrl = "{{ url('') }}/";
    window.currencySymbol = "{{ $settings->currency_symbol ?? '$' }}";
</script>
@endsection
@endif

@section('content')
@php
use Carbon\Carbon;
Carbon::setLocale('es');

$paymentStatusTranslations = [
    'paid' => 'Pagado',
    'pending' => 'Pendiente',
    'failed' => 'Fallido',
];
@endphp

<meta name="csrf-token" content="{{ csrf_token() }}">

<h4 class="py-3 mb-4">
    <span class="text-muted fw-light"></span> Detalles del Ingreso
</h4>
<x-errors />
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 card p-3">
    <div class="d-flex flex-column justify-content-center">
        <h6 class="mb-1 mt-3">Ingreso #{{ $income->id }}</h6>
        <h6 class="card-title mb-1 mt-1">{{ $income->income_name }}</h6>
        <h6 class="card-title mb-1 mt-1">
            Método de pago:
            <span class="badge bg-label-primary me-2 ms-2">
                {{ $income->paymentMethod->description ?? 'Desconocido' }}
            </span>
        </h6>
        <h6 class="card-title mb-1 mt-1">
            Categoría:
            @if($income->incomeCategory)
            <span class="badge bg-label-info me-2 ms-2">{{ $income->incomeCategory->income_name }}</span>
            @else
            <span class="badge bg-label-secondary me-2 ms-2">Sin Categoría</span>
            @endif
        </h6>
        <h6 class="card-title mb-1 mt-1">Estado de Facturación:
            @if($income->is_billed)
            <span class="badge bg-label-success me-2 ms-2">Facturado</span>
            @else
            <span class="badge bg-label-danger me-2 ms-2">No Facturado</span>
            @endif
        </h6>
        <p class="text-body mb-1">
            {{ $income->income_date ? $income->income_date->format('d/m/Y') : 'Sin fecha' }}
        </p>
    </div>
    <div class="d-flex align-content-center flex-wrap gap-2">
        <a href="{{ route('incomes.index') }}" class="btn btn-sm btn-label-secondary">
            <i class="bx bx-chevron-left"></i>
            <span class="align-middle">Volver</span>
        </a>
        <a href="{{ route('income.pdf', ['income' => $income->id]) }}?action=print" target="_blank"
            onclick="window.open(this.href, 'print_window', 'left=100,top=100,width=800,height=600').print(); return false;">
            <button class="btn btn-sm btn-primary">Imprimir</button>
        </a>
        <a href="{{ route('income.pdf', ['income' => $income->id]) }}" class="btn btn-sm btn-label-primary">
            Descargar PDF
        </a>
        @if($income?->store->invoices_enabled == 1)
            @if(!$income->is_billed)
            <!--
            <button type="button" class="btn btn-sm btn-label-info" data-bs-toggle="modal" data-bs-target="#emitirFacturaModal">
                Emitir Factura
            </button>
            -->
            @else
            <a target="_blank" href="{{ route('income.cfe.pdf', ['id' => $income->id]) }}" class="btn btn-sm btn-label-info">
                Ver Factura
            </a>
            @endif
        @endif


        @if(!$income->deleted_at)
        <button type="button" class="btn btn-sm btn-danger delete-income" data-income-id="{{ $income->id }}"
            data-bs-toggle="tooltip" data-bs-placement="left" title="Eliminar Ingreso">
            <i class="bx bx-trash"></i>
        </button>
        @else
        <span data-bs-toggle="tooltip" data-bs-placement="top" title="Este Ingreso ya fue eliminado">
            <button type="button" class="btn btn-sm btn-danger" disabled>
                <i class="bx bx-trash"></i>
            </button>
        </span>
        @endif
    </div>
</div>

<div class="row">
    <!-- Columna principal con tabla de items -->
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Detalle de Items</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Producto/Item</th>
                                <th>Precio</th>
                                <th>Cantidad</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(is_array($income->items) && count($income->items) > 0)
                                @php $sumSubtotal = 0; @endphp
                                @foreach($income->items as $item)
                                    @php
                                    $price = (float) ($item['price'] ?? 0);
                                    $quantity = (int) ($item['quantity'] ?? 0);
                                    $subtotal = $price * $quantity;
                                    $sumSubtotal += $subtotal;
                                    @endphp
                                    <tr>
                                        <td>{{ $item['name'] ?? 'Sin nombre' }}</td>
                                        <td>{{ $settings->currency_symbol ?? '$' }}{{ number_format($price, 2) }}</td>
                                        <td>{{ $quantity }}</td>
                                        <td class="text-end">{{ $settings->currency_symbol ?? '$' }}{{ number_format($subtotal, 2) }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="text-center">No hay items registrados</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-end">
                    <div class="income-calculations" style="min-width: 250px">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-semibold">Moneda:</span>
                            <span class="text-heading">{{ $income->currency }}</span>
                        </div>
                        @if($income->currency === 'Dólar')
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-semibold">Cotización:</span>
                            <span class="text-heading">{{ number_format($income->exchange_rate, 2) }}</span>
                        </div>
                        @endif
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-semibold">Subtotal:</span>
                            <span class="text-heading">{{ $settings->currency_symbol ?? '$' }}{{ number_format($sumSubtotal ?? 0, 2) }}</span>
                        </div>
                        @if($income->tax_rate_id)
                            @php
                                $tax = \App\Models\TaxRate::find($income->tax_rate_id);
                                $taxAmount = $sumSubtotal * ($tax->rate / 100);
                            @endphp
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-semibold">{{ $tax->name }} ({{ $tax->rate }}%):</span>
                                <span class="text-heading">{{ $settings->currency_symbol ?? '$' }}{{ number_format($taxAmount, 2) }}</span>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between pt-2 border-top mt-2">
                            <span class="fw-bold">Total:</span>
                            <span class="text-heading fw-bold">{{ $settings->currency_symbol ?? '$' }}{{ number_format($income->income_amount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Columna lateral con información de la entidad -->
    <div class="col-12 col-lg-4">
        @if($income->client_id || $income->supplier_id)
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title m-0">Datos de la Entidad</h6>
            </div>
            <div class="card-body">
                @if($income->client_id && $income->client)
                <h5 class="mb-2">Cliente</h5>
                <p class="mb-0"><strong>Nombre:</strong> {{ $income->client->name }} {{ $income->client->lastname }}</p>
                @elseif($income->supplier_id && $income->supplier)
                <h5 class="mb-2">Proveedor</h5>
                <p class="mb-0"><strong>Nombre:</strong> {{ $income->supplier->name }}</p>
                @endif
                @if($income->income_description)
                <hr>
                <p class="mb-0"><strong>Descripción:</strong> {{ $income->income_description }}</p>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
</div>


@endsection