@extends('content/landing/layouts/landing-layout')

@section('title', '¿Quienes Somos?')

@section('content')

    <header class="page-header-about"
        style="background-image: url('{{ asset('assets/img/landing/hero_sobre_tienda.jpg') }}');">
        <h1>¿Quienes Somos?</h1>
    </header>

    <section class="history-section">
        <div class="container-fluid">
            <h2 class="text-white text-center mb-5">Nuestra Historia</h2>
            <p class="text-center">
                Hace más de 30 años, en un galpón de apenas 250 m², el fundador de la red, Claudinei dos Anjos, y
                otros cuatro empleados comenzaron a fabricar sofás y a crecer poco a poco. En 1999,
                mientras el negocio tomaba forma, nuestra fábrica fue sorprendida por un incendio de grandes proporciones.
                <br>

                A pesar de las dificultades, Claudinei no dejó de luchar por sus sueños y, dos años después del
                incidente, la empresa ya comenzaba a crecer nuevamente. <br>

                Pronto, buscando mayor calidad en los productos, comenzamos a fabricar nuestra propia espuma para
                satisfacer la demanda. En ese mismo período, iniciamos la fabricación de colchones, los cuales, rápidamente,
                prosperaron y transformaron por completo el negocio.
            </p>

        </div>
    </section>


    <section class="values-section">
        <div class="values-container">
            <div class="value-item">
                <i class="fas fa-heartbeat"></i>
                <h3>Salud</h3>
                <p>
                    La calidad de nuestros productos está directamente ligada a la salud del cuerpo y la mente.
                    Nuestros productos están especialmente desarrollados para proporcionar el mejor descanso.
                </p>
            </div>
            <div class="value-item">
                <i class="fas fa-check-circle"></i>
                <h3>Calidad</h3>
                <p>
                    Anjos siempre tiene un producto para tu hogar. Desarrollado en base a las tendencias globales,
                    Los colchones y sofás Anjos aportan belleza, confort y tecnología a los ambientes.
                </p>
            </div>
            <div class="value-item">
                <i class="fas fa-headset"></i>
                <h3>Atención</h3>
                <p>
                    En Anjos, los consultores son dueños de su propio negocio para satisfacer las necesidades de cada
                    cliente.
                    El servicio es personalizado y de alta calidad, entregando soluciones valiosas.
                </p>
            </div>
        </div>
    </section>

    <section class="video-section-about">
        <div class="video-container-about ">
            <div class="video-text-about">
                <h3>Detalles de nuestra operación</h3>
                <p>
                    Desde las materias primas hasta los empleados, para garantizar cada día aún más salud y comodidad para
                    tu familia.
                </p>
            </div>
            <div class="video-player">

                <iframe src="https://player.vimeo.com/video/713782582" width="640" height="360"
                    frameborder="0"></iframe>

            </div>
        </div>
    </section>

@endsection
