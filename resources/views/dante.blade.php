<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Dante</title>
    <style>
        body {
            background-image: url("{{ asset('/assets/img/noimage.jpg') }}");
            margin: 0;
            font-family: Arial, sans-serif;
        }

        h1 {
            text-align: center;
            margin-top: 20px;
            font-size: 4rem;
            color: blue;
            text-transform: uppercase;
        }

        .contenedor-foto {
            width: 50%;
            margin: 0 auto;
            border: 10vmin solid;
            border-image: conic-gradient(from var(--angle), red, yellow, lime, aqua, blue, magenta, red) 1;
            animation: rotate 10s linear infinite;
            --angle: 0deg;
        }

        .foto-dante {
            display: block;
            width: 100%;
            height: 70vh;
            object-fit: fit;
        }

        @keyframes rotate {
            to {
                --angle: 360deg;
            }
        }

        @property --angle {
            syntax: '<angle>';
            initial-value: 0deg;
            inherits: false;
        }

        .dibujo {
            max-width: 80%;
            display: block;
            margin: 0 auto;
            margin-top: 30px;
            border-radius: 30px;
          }
    </style>
</head>
<body>

    <h1>Página Dante</h1>

    <div class="contenedor-foto">
        <img src="{{ asset('/assets/img/dante.jpeg') }}" alt="Foto de Dante" class="foto-dante">
    </div>

    <div class="contenedor-dibujo">
        <img src="{{ asset('/assets/img/dibujo.png') }}" alt="Dibujo" class="dibujo">
    </div>

</body>
</html>
