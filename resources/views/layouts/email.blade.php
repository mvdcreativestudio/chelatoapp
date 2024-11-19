<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Notificación de ' . config('app.name'))</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>

<body style="background-color: #f4f4f4; font-family: Arial, sans-serif; padding: 20px; color: #333;">

    <div class="container"
        style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; padding: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
        <!-- Header -->
        <div class="text-center mb-4">
            <h1 class="h4 text-primary">@yield('header', 'Notificación de ' . config('app.name'))</h1>
        </div>

        <!-- Main Content -->
        <div class="content mb-4" style="font-size: 16px; color: #666;">
            @yield('content')
        </div>

        <!-- Footer -->
        <div class="footer text-center"
            style="font-size: 12px; color: #888; border-top: 1px solid #ddd; padding-top: 15px;">
            @yield('footer', 'Este es un mensaje generado automáticamente. Por favor, no responda a este correo.')
        </div>
    </div>

</body>

</html>