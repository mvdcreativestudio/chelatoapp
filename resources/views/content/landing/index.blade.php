@extends('content/landing/layouts/landing-layout')

@section('title', 'Landing Page')

@section('page-script')
@vite(['resources/assets/js/app-ecommerce-landing-index.js'])
@endsection

<!-- Contenido -->
@section('content')

<section id="hero" class="hero-carousel">
    <div class="carousel-container">
        <div class="carousel-slide">
            <img src="{{ asset($companySettings->hero_image) }}" alt="Sofá Confortable">
            <div class="slide-content">
            </div>
        </div>
    </div>
</section>

<section id="video-section" class="video-section-index">
    <video autoplay muted loop id="bg-video">
        <source src="assets/img/landing/BANNER-FRANQUEADO-DESKTOP.webm" type="video/webm">
    </video>
    </div>
</section>

{{-- <section id="catalog-section" class="catalog-section">
    <div class="catalog-content">
        <div class="catalog-header">
            <div class="catalog-title">
                <img src="assets/img/landing/guion.png" alt="guion decorativo" class="catalog-guion">
                <h2 class="text-white">BAIXE NOSSO CATÁLOGO E RECEBA UM DESCONTO ESPECIAL</h2>
            </div>
        </div>
        <form id="catalog-form">
            <input type="text" placeholder="Seu nome" required>
            <input type="email" placeholder="Seu e-mail" required>
            <input type="tel" placeholder="Seu telefone" required>
            <input type="text" placeholder="Sua cidade" required>
            <button type="submit">QUERO MEU DESCONTO</button>
        </form>
    </div>
</section> --}}

<section id="especialista-section" class="especialista-section">
    <div class="especialista-content">
        <h2 class="font-white">Converse con un especialista</h2>
        <p>¿Tiene alguna consulta? <br>
        ¡Haga click para hablar con un vendedor!</p>
        <div class="whatsapp-button-container">
            @php
            // Elimina todos los caracteres no numéricos
            $phoneNumber = preg_replace('/\D/', '', $companySettings->phone);

            // Si el número comienza con "0", quítalo
            if (substr($phoneNumber, 0, 1) === '0') {
                $phoneNumber = substr($phoneNumber, 1);
            }
            @endphp
            <a href="https://wa.me/598{{ $phoneNumber }}" target="_blank" class="btn btn-primary btn-wpp text-white mx-2">
                <i class="fab fa-whatsapp fa-2x mx-2"></i> Iniciar Conversación
            </a>
        </div>
    </div>
</section>

<section id="produtos-section" class="produtos-section">
    <div class="produtos-carousel-container">
        <div class="produtos-carousel">
            <div class="produtos-slide">
                <img src="assets/img/landing/carrousel_producto.png" alt="Produto 1">
            </div>
            <div class="produtos-slide">
                <img src="assets/img/landing/carrousel_producto.png" alt="Produto 2">
            </div>
            <div class="produtos-slide">
                <img src="assets/img/landing/carrousel_producto.png" alt="Produto 3">
            </div>
            <div class="produtos-slide">
                <img src="assets/img/landing/carrousel_producto.png" alt="Produto 4">
            </div>
            <div class="produtos-slide">
                <img src="assets/img/landing/carrousel_producto.png" alt="Produto 5">
            </div>
            <div class="produtos-slide">
                <img src="assets/img/landing/carrousel_producto.png" alt="Produto 6">
            </div>
        </div>
    </div>
    <div class="produtos-content">
        <h2 class="font-white">Conozca nuestros productos</h2>
        <p>Comodidad y funcionalidad para quienes aman vivir bien.</p>
        <a href="{{ route('landing-page.products') }}" class="btn-encante">Ver Productos</a>
    </div>
</section>

<section class="excelencia-satisfacao" style="background-image: url('{{ asset('assets/img/landing/seccion5.jpg') }}');">
    <div class="content-wrapper-excelencia">
        <img class="guion-excelencia" src="assets/img/landing/guion3.png" alt="">
        <h2 class="font-white">Excelencia y Satisfacción</h2>
        <p>
            Para el dormitorio, camas confeccionadas con tejidos de alta decoración, tecnología y extremo confort. Para el salón, un portafolio de sofás en diferentes estilos y formatos que garantizan comodidad y funcionalidad a los ambientes. Además, Anjos trabaja con socios seleccionados para la línea de complementos, que sorprenden por su buen gusto.
        </p>
        <a href="{{ route('landing-page.products') }}" class="cta-button">Ver más</a>
    </div>
</section>

<section class="anjos-testimonios">
    <div class="anjos-container">
        <h2 class="anjos-title text-dark">Opiniones de Clientes</h2>
        <div id="testimoniosCarousel" class="anjos-carousel">
            <div class="anjos-carousel-item d-flex">
                <div class="col-1"> <button id="anjos-prevBtn" class="anjos-carousel-control anjos-prev">&lt;</button></div>
                <div class="anjos-card col-10">
                    <div class="anjos-user-info">
                        <img src="assets/img/landing/person-circle-1.png" alt="Pauliene Mesquita Carvalho" class="anjos-user-avatar">
                        <div>
                            <h3 class="anjos-user-name">Pauliene Mesquita Carvalho</h3>
                            <p class="anjos-user-location">Alta Floresta/MT</p>
                        </div>
                    </div>
                    <p class="anjos-testimonial-text">"Quando cheguei na Anjos Colchões e Sofás - Alta Floresta, fui super bem recepcionada. O atendimento foi impecável, adquiri alguns produtos como colchão, travesseiros e roupa de cama. Não tenho palavras para descrever como minhas noites de sono e da minha família mudaram depois que passamos a usar os produtos, que possuem altíssima qualidade. Estou super satisfeita com os Produtos e encantada com a Loja."</p>
                </div>
                <div class="col-1"> <button id="anjos-nextBtn" class="anjos-carousel-control anjos-next">&gt;</button></div>
            </div>
        </div>
    </div>
</section>



@endsection
<!--/ Contenido -->
