@extends('content/landing/layouts/landing-layout')

@section('title', 'Colchones - Anjos')



<!-- Contenido -->

@section('content')
<div class="colchones-wrapper">

    <header class="page-header-colchones" style="background-image: url('{{ asset('assets/img/landing/hero_colchones.jpg') }}');">
        <h1>Colchones</h1>
    </header>

    <!-- Encabezado con botones de categorías -->
    <div class="container-fluid my-4 justify-content-center">
        <div class="d-flex justify-content-center gap-3">
            <a href="" class="btn btn-outline-primario">Boxes</a>
            <a href="" class="btn btn-outline-primario">Colchones</a>
            <a href="" class="btn btn-outline-primario">Sofás</a>
            <a href="" class="btn btn-outline-primario">Complementos</a>
        </div>
    </div>

    <div class="container-fluid my-5">
        <div class="row row-cols-1 row-cols-xl-5 row-cols-md-4 row-cols-sm-2 g-4">
            @foreach($products as $product)
                <div class="col">
                    <div class="card h-100 text-center custom-card-colchones">
                        <!-- Imagen del producto -->
                        <img src="{{ asset($product->image) }}" class="card-img-top custom-image-colchones" alt="{{ $product->name }}">

                        <div class="card-body">
                            <!-- Nombre del producto -->
                            <h5 class="card-title text-primary font-weight-bold custom-title-colchones">{{ strtoupper($product->name) }}</h5>
                        </div>

                        <!-- Botón de "Saiba Mais" -->
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