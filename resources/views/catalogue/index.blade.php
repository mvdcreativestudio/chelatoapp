@extends('catalogue/layouts/catalogue-layout')

@section('title', 'Catálogo')

<!-- Page Styles -->
@section('page-style')
@vite(['resources/assets/css/catalogue.css'])
@endsection

<!-- Page Scripts -->
@section('page-script')
@vite([
  'resources/assets/js/catalogue/app-catalogue-index.js'
])
@endsection

@section('content')

<section class="hero" style="background-image: url('/assets/img/productCatalogue/test_hero.jpg');">
    <div class="container">
        <h1>{{$companySettings->name}}</h1>
        <h5 class="text-white">Catálogo de Productos</h3>
    </div>
</section>

<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="input-group mb-3">
                <input type="text" id="search-input" class="form-control" placeholder="Buscar">
            </div>         
        </div>
    </div>

    <div class="category-buttons row justify-content-center mb-4">
        <div class="col-auto categoria">
            <a href="{{ route('catalogue.index') }}" class="btn btn-outline-primary {{ request('category') ? '' : 'active' }}">Todo</a>
        </div>
        @foreach ($categories as $category)
            <div class="col-auto categoria">
                <a href="{{ route('catalogue.index', ['category' => $category['id']]) }}" class="btn btn-outline-primary {{ request('category') == $category['id'] ? 'active' : '' }}">
                    {{ $category['name'] }}
                </a>
            </div>
        @endforeach
    </div>

    <div class="row products" id="products-container">
        @foreach ($products as $product)
            <div class="col-xl-2 col-md-3 col-sm-6 col-12 mb-4">
                <div class="card product">
                    <img src="{{ file_exists(public_path($product['image'])) ? asset($product['image']) : asset('assets/img/ecommerce-images/placeholder.png') }}" class="card-img-top" alt="{{ $product['name'] }}">
                    <div class="card-body">
                        <h5 class="card-title text-center">{{ $product['name'] }}</h5>
        
                        {{-- Mostrar precios según las condiciones --}}
                        <p class="card-text text-center">
                            @if(isset($product['old_price']) && isset($product['price']))
                                <span class="text-muted text-decoration-line-through">${{ number_format($product['old_price'], 2) }}</span>
                                <span>${{ number_format($product['price'], 2) }}</span>
                            @elseif(isset($product['old_price']) && !isset($product['price']))
                                ${{ number_format($product['old_price'], 2) }}
                            @elseif(isset($product['price']))
                                <span class="fw-bold">${{ number_format($product['price'], 2) }}</span>
                            @endif
                        </p>
        
                        <div class="d-flex justify-content-center gap-2">
                            @php
                                // Elimina todos los caracteres no numéricos del teléfono
                                $phoneNumber = preg_replace('/\D/', '', $companySettings->phone);
                        
                                // Si el número comienza con "0", quítalo
                                if (substr($phoneNumber, 0, 1) === '0') {
                                    $phoneNumber = substr($phoneNumber, 1);
                                }
                            @endphp
                        
                            <!-- Enlace de WhatsApp con el mensaje personalizado -->
                            <a href="https://wa.me/598{{ $phoneNumber }}?text={{ urlencode('Quisiera pedir el producto ' . $product['name']) }}" target="_blank" class="btn btn-outline-primary btn-sm">Pedir</a>
                            <a href="/catalogue/{{ $product['id'] }}" class="btn btn-outline-primary btn-sm">Ver producto</a>
                        </div>
                                                                   
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@endsection