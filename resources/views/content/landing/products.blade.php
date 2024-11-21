@extends('content/landing/layouts/landing-layout')

@section('title', 'Productos - Anjos')

@section('page-script')
    @vite(['resources/assets/js/app-ecommerce-landing-products.js'])
@endsection

<!-- Contenido -->

@section('content')
<div id="global-spinner" class="spinner-container" style="display: none;">
    <div class="spinner"></div>
</div>

<div class="colchones-wrapper">

    <header class="page-header-colchones" style="background-image: url('{{ asset('assets/img/landing/hero_colchones.jpg') }}');">
        <h1>Productos</h1>
    </header>

    <!-- Encabezado con botones de categorías -->
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <!-- Botón para mostrar todos los productos -->
                    <button class="btn btn-outline-primario filter-button" data-category-id="">Todos</button>
        
                    <!-- Generar botones dinámicos de categorías -->
                    @foreach($categories as $category)
                        <button class="btn btn-outline-primario filter-button" data-category-id="{{ $category->id }}">
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>


    <!-- Contenedor dinámico de productos -->
    <div id="products-container" class="container-fluid my-5">
        <div class="row row-cols-1 row-cols-xl-5 row-cols-md-4 row-cols-sm-2 g-4">
            @foreach($products as $product)
                <div class="col">
                    <div class="card h-100 text-center custom-card-colchones">
                        <img src="{{ asset($product->image) }}" class="card-img-top custom-image-colchones" alt="{{ $product->name }}">
                        <div class="card-body">
                            <h5 class="card-title text-primary font-weight-bold custom-title-colchones">{{ strtoupper($product->name) }}</h5>
                        </div>
                        <div class="card-footer bg-white border-0 custom-footer-colchones">
                            <a href="{{ route('landing-page.producto', $product->id) }}" class="btn custom-button-colchones">
                                VER PRODUCTO
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>


@endsection
    

<!--/ Contenido -->