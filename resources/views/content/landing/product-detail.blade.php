@extends('content/landing/layouts/landing-layout')

@section('title', $product->name)

@section('page-script')
    @vite(['resources/assets/js/app-ecommerce-landing-product-page.js'])
@endsection

@section('content')
<div class="ficha-producto-container">

    <div class="product-container">
        <img src="{{ asset($product->image) }}" alt="Colchão Admirable" class="product-image">
        <h1 class="product-title">{{ $product->name }}</h1>
    </div>

    @if($product->description != '<p><br></p>')
    <div class="product-description">
        <div class="content-wrapper-ficha landing-product-description">
            {!! $product->description !!}
        </div>
    </div>
    @endif


    <div class="content-wrapper-ficha">
        <div class="product-gallery mb-5">
            <div class="gallery-container">
                @foreach($product->gallery as $image)
                    <img src="{{ asset($image->image) }}" alt="Imagen del producto" class="gallery-image">
                @endforeach
            </div>
            <button class="gallery-button prev">&lt;</button>
            <button class="gallery-button next">&gt;</button>
        </div>
        
        @if($product->features->count() > 0)
            <div class="technical-specs mt-5">
                <h2 class="specs-title">Ficha técnica</h2>
                <div class="specs-grid">
                    @foreach($product->features as $feature)
                        <div class="spec-item">{{ strtoupper($feature->value) }}</div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @if($product->sizes->count() > 0)
    <div class="measures-table-container mb-4">
        <table class="measures-table">
            <h3 class="measures-title">Medidas Disponibles</h3>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Ancho</th>
                    <th>Largo</th>
                    <th>Alto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($product->sizes as $size)
                    <tr>
                        <td>{{ ucfirst($size->size) }}</td>
                        <td>{{ $size->width }}cm</td>
                        <td>{{ $size->height }}cm</td>
                        <td>{{ $size->length }}cm</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif


    <div class="additional-info text-center">
        <p>*La casa se reserva el derecho a modificar características de los productos sin previo aviso al consumidor.</p>
    </div>

</div>
@endsection
