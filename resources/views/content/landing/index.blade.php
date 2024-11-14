@extends('content/landing/layouts/landing-layout')

@section('title', 'Landing Page')



<!-- Contenido -->
@section('content')

<section id="hero" class="hero-carousel">
    <div class="carousel-container">
        <div class="carousel-slide">
            <img src="assets/img/landing/2550x735px-BONUCCI-1536x443.jpg" alt="Sofá Confortable 1">
            <div class="slide-content">
            </div>
        </div>
        <div class="carousel-slide">
            <img src="assets/img/landing/2550x735px-BONUCCI-1536x443.jpg" alt="Sofá Confortable 2">
            <div class="slide-content">
            </div>
        </div>
        <div class="carousel-slide">
            <img src="assets/img/landing/2550x735px-BONUCCI-1536x443.jpg"  alt="Sofá Confortable 3">
            <div class="slide-content">
            </div>
        </div>
    </div>
</section>

<section id="video-section" class="video-section">
    <video autoplay muted loop id="bg-video">
        <source src="assets/img/landing/BANNER-FRANQUEADO-DESKTOP.webm" type="video/webm">
    </video>
    </div>
</section>

<section id="catalog-section" class="catalog-section">
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
</section>

<section id="especialista-section" class="especialista-section">
    <div class="especialista-content">
        <h2 class="font-white">Converse com nosso especialista</h2>
        <p>Tem alguma dúvida e deseja tirar com um especialista e não um robô? <br>
        Clique no botão abaixo e faça todas as perguntas que desejar!</p>
        <div class="whatsapp-button-container">
            <img src="assets/img/landing/botonwp.png" alt="Fale com um especialista">
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
        <h2 class="font-white">Conheça <br> Nossos produtos</h2> 
        <p>Conforto e funcionalidade<br>para quem ama viver bem.</p>
        <a href="#" class="btn-encante">ENCANTE-SE</a>
    </div>
</section>

<section class="excelencia-satisfacao" style="background-image: url('{{ asset('assets/img/landing/seccion5.jpg') }}');">
    <div class="content-wrapper-excelencia">
        <img class="guion-excelencia" src="assets/img/landing/guion3.png" alt="">
        <h2 class="font-white">Excelência e Satisfação</h2>
        <p>
            Para o quarto, camas produzidas com tecidos de alta decoração, tecnologia e extremo conforto. Para a sala, um portfólio de sofás em diferentes estilos e formatos que garantem conforto e funcionalidade aos ambientes. Além disso, a Anjos trabalha com parceiros selecionados para a linha de complementos, que surpreendem pelo bom gosto.
        </p>
        <a href="#" class="cta-button">Saiba Mais</a>
    </div>
</section>

<section class="anjos-testimonios">
    <div class="anjos-container">
        <h2 class="anjos-title text-dark">Depoimentos</h2>
        <div id="anjos-testimoniosCarousel" class="anjos-carousel">
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
        <button class="anjos-cta-button">Fale com um consultor do sono</button>
    </div>
</section>

@endsection
<!--/ Contenido -->