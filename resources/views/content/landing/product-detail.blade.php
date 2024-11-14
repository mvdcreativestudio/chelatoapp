@extends('content/landing/layouts/landing-layout')

@section('title', $product->name)

@section('content')
<div class="container my-5">
    <div class="row">
        <!-- Contenedor de la imagen del producto -->
        <div class="col-md-6">
            <div class="product-container">
                <img src="{{ asset($product->image) }}" class="product-image" alt="{{ $product->name }}">
                <h1 class="product-title">{{ strtoupper($product->name) }}</h1>
            </div>
        </div>

        <!-- Detalles del producto -->
        <div class="col-md-6">
            <div class="product-description">
                <h2>Descripción del Producto</h2>
                <p>{{ $product->description }}</p>
                <p><strong>Precio:</strong> ${{ number_format($product->price, 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Galería del producto -->
    <div class="row my-5">
        <div class="col-12 product-gallery">
            <div class="gallery-container">
                @php
                    $images_gallery = json_decode($product->specs->images_gallery ?? '[]', true);
                @endphp
                @foreach($images_gallery as $key => $image)
                    <img src="{{ asset($image) }}" class="gallery-image" alt="Imagen adicional de {{ $product->name }}">
                @endforeach
            </div>
            <button class="gallery-button prev">‹</button>
            <button class="gallery-button next">›</button>
        </div>
    </div>

    <!-- Especificaciones técnicas -->
    <div class="technical-specs">
        <h2 class="specs-title">Especificaciones Técnicas</h2>
        <div class="specs-grid">
            @php
                $features = json_decode($product->specs->features ?? '[]', true);
            @endphp
            @foreach($features as $feature => $value)
                <div class="spec-item">
                    <span><strong>{{ ucfirst($feature) }}:</strong> {{ $value }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Tabla de medidas -->
    <div class="measures-table-container">
        <h2 class="measures-title">Tabla de Medidas</h2>
        <table class="measures-table">
            <thead>
                <tr>
                    <th>Tamaño</th>
                    <th>Dimensiones</th>
                </tr>
            </thead>
            <tbody>
                @foreach(json_decode($product->specs->sizes ?? '[]', true) as $size => $dimensions)
                    <tr>
                        <td>{{ ucfirst($size) }}</td>
                        <td>{{ $dimensions }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Colores disponibles -->
    <div class="technical-specs">
        <h2 class="specs-title">Colores Disponibles</h2>
        <div class="specs-grid">
            @php
                $colors = json_decode($product->specs->colors ?? '[]', true);
            @endphp
            @foreach($colors as $colorKey => $colorName)
                <div class="spec-item">
                    <span>{{ ucfirst($colorName) }}</span>
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
