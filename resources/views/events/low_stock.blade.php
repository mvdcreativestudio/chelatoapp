@extends('layouts.email')

@section('title', 'Alerta de Bajo Stock')

@section('header')
    <h1 class="text-danger text-center">⚠️ Alerta de Bajo Stock ⚠️</h1>
@endsection

@section('content')
    <div class="text-muted">
        <p>Estimado administrador,</p>

        <p>El producto <strong>{{ $data['product']->name }}</strong> ha alcanzado el límite de stock definido.</p>

        <p>Detalles del producto:</p>
        <ul class="list-unstyled">
            <li><strong>ID del Producto:</strong> {{ $data['product']->id }}</li>
            <li><strong>Nombre:</strong> {{ $data['product']->name }}</li>
            <li><strong>Stock Actual:</strong> {{ $data['currentStock'] }}</li>
            <li><strong>Margen de Seguridad:</strong> {{ $data['product']->safety_margin }}</li>
        </ul>

        <p>Por favor, tome las medidas necesarias para reabastecer el inventario de este producto.</p>
    </div>
@endsection

@section('footer')
    <div class="text-center text-muted">
        <p>Si tienes alguna duda, contacta a soporte. Este mensaje fue enviado automáticamente.</p>
    </div>
@endsection
