@extends('catalogue/layouts/catalogue-layout')

@section('title', 'Catálogo')

<!-- Page Styles -->
@section('page-style')
@vite(['resources/assets/css/ficha_producto.css'])
@endsection

<!-- Page Scripts -->
@section('page-script')
@vite([
  'resources/assets/js/catalogue/app-catalogue-show-product.js'
])
@endsection

@section('content')
<div class="container-fluid py-5 mt-5">

    <div class="row mb-5">
        <div class="col-12">
            <a href="{{ route('catalogue.index') }}" class="btn btn-primary">
                ← Atras
            </a>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10 col-md-12">
            <div class="row">
                <!-- Imagen del Producto -->
                <div class="col-12 col-md-6 mb-4 mb-md-0 d-flex justify-content-center">
                    <img src="{{ asset($product->image) }}" class="img-fluid rounded w-100" alt="Producto">
                </div>

                <!-- Detalles del Producto -->
                <div class="col-12 col-md-6">
                    <h1 class="mb-3">{{ $product->name }}</h1>
                    @if($product->description !== '<p><br></p>')
                        <p>{{$product->description}}</p>
                    @endif

                    <div class="product-details">
                        <div class="mb-2">
                            <p><strong>Unidad:</strong> kg</p>
                        </div>
                        <div class="mb-2">
                            <p><strong>{{ $product->categories->count() > 1 ? 'Categorías:' : 'Categoría:' }}</strong> {{ $product->categories->pluck('name')->implode(', ') }}</p>
                        </div>
                        @if($product->sku)
                            <div class="mb-4">
                                    <p><strong>SKU:</strong> {{ $product->sku }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Precios -->
                    <div class="row align-items-center mb-4">
                        @if(isset($product->old_price) && isset($product->price))
                            <div class="col-auto">
                                <h5 class="text-muted mb-0"><del>${{ number_format($product->old_price, 2) }}</del></h5>
                            </div>
                            <div class="col-auto">
                                <h3 class="text-success mb-0"><strong>${{ number_format($product->price, 2) }}</strong></h3>
                            </div>
                        @else
                            <div class="col-auto">
                                <h3 class="mb-0 text-success"><strong>${{ number_format($product->old_price, 2) }}</strong></h3>
                            </div>
                        @endif
                    </div>
                    

                    <!-- Control de Cantidad y Botón de Añadir al Pedido -->
                    <div class="quantity-control d-flex align-items-center mb-4">
                        <button id="decrement-btn" class="btn btn-outline-secondary">-</button>
                        <input type="number" id="quantity-input" class="form-control mx-2 text-center" value="1" min="1" style="width: 60px;">
                        <button id="increment-btn" class="btn btn-outline-secondary">+</button>
                        <!-- Añade los datos necesarios en el botón -->
                        <button id="add-to-order-btn" 
                                class="btn btn-dark ms-3"
                                data-phone="{{ preg_replace('/\D/', '', $companySettings->phone) }}" 
                                data-product-name="{{ $product->name }}">
                            AÑADIR AL PEDIDO
                        </button>
                    </div>
                    
                    <!-- Disponibilidad -->
                    @if($product->stock > 0 && $product->stock !== null)
                        <p class="mb-4"><strong>Disponible:</strong> 
                            {{$product->stock}}
                        </p>
                    @elseif ($product->stock === 0)
                        <p class="bold text-danger">No hay stock disponible</p>
                    @elseif ($product->stock === null)
                        <p class="bold text-warning">Consultar disponibilidad</p>
                    @endif
                   

                    <!-- Botón de Pedido por WhatsApp -->
                    <button id="contact-whatsapp-btn" class="btn btn-outline-success w-50 mb-4">
                        <i class="fab fa-whatsapp me-2"></i> Contactar por WhatsApp
                    </button>
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
