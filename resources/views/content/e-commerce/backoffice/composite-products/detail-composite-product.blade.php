@extends('layouts/layoutMaster')

@section('title', 'Detalle del Producto Compuesto')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'
])
@endsection

@section('page-script')
@vite([
'resources/assets/js/composite-products/app-composite-product-details.js'
])
@endsection

@section('content')
<h4 class="py-3 mb-4">
    <span class="text-muted fw-light"></span> Detalle del Producto Compuesto
</h4>

<!-- Botones Volver y Editar -->
<div class="mb-4 d-flex justify-content-between">
    <a href="{{ route('composite-products.index') }}" class="btn btn-secondary">
        <i class="bx bx-left-arrow-alt"></i> Volver Atrás
    </a>
    <a href="{{ route('composite-products.edit', $compositeProduct->id) }}" class="btn btn-primary">
        <i class="bx bx-edit"></i> Editar Producto Compuesto
    </a>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title">Detalles del Producto Compuesto</h5>
    </div>
    <div class="card-body">
        <div class="row gy-4">
            <div class="col-md-6">
                <h6 class="mb-2">Nombre:</h6>
                <p>{{ $compositeProduct->name }}</p>
            </div>
            <div class="col-md-6">
                <h6 class="mb-2">Empresa:</h6>
                <p>{{ $compositeProduct->store->name }}</p>
            </div>
            <div class="col-md-6">
                <h6 class="mb-2">Precio:</h6>
                <p>{{ $settings->currency_symbol }} {{ number_format($compositeProduct->price, 2) }}</p>
            </div>
            <div class="col-md-6">
                <h6 class="mb-2">Precio Recomendado:</h6>
                <p>{{ $settings->currency_symbol }} {{ number_format($compositeProduct->recommended_price, 2) }}</p>
            </div>
            <div class="col-md-6">
                <h6 class="mb-2">Fecha de Creación:</h6>
                <p>{{ $compositeProduct->created_at->format('d/m/Y') }}</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title">Detalles de los Productos Incluidos</h5>
    </div>
    <div class="card-datatable table-responsive">
        <table class="table datatables-composite-product-details">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Stock</th>
                    <th>Precio Unitario</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($compositeProduct->details as $index => $detail)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $detail->product->name }}</td>
                    <td>{{ $detail->quantity_composite_product }}</td>
                    <td>{{ $detail->product->stock ?? 'Sin Stock' }}</td>
                    <td>{{ $settings->currency_symbol }} {{ number_format($detail->product->build_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
