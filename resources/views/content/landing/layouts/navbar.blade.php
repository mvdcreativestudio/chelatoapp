<nav class="navbar container-fluid">
    <div class="logo">
        <img src="{{ asset($companySettings->logo_black) }}" alt="Anjos Colchones y Sofas" class="">
    </div>
    <ul class="nav-links">
        <li><a href="{{ route('landing-page') }}">Inicio</a></li>
        <li><a href="{{ route('landing-page.colchones') }}">Colchones</a></li>
        <li><a href="sofas.html">Sofás</a></li>
        <li><a href="sobre_tienda.html">Sobre a loja</a></li>
        <li><a href="contacto.html">Contato</a></li>
    </ul>
</nav>
