<nav class="navbar container-fluid">
    <div class="logo">
        <img src="{{ asset($companySettings->logo_black) }}" alt="Anjos Colchones y Sofas" class="">
    </div>
    <ul class="nav-links">
        <li><a href="{{ route('landing-page') }}">Inicio</a></li>
        <li><a href="{{ route('landing-page.products') }}">Productos</a></li>
        <li><a href="{{ route('landing-page.about-us') }}">¿Quienes Somos?</a></li>
        <li><a href="{{ route('landing-page.contact') }}">Contacto</a></li>
    </ul>
</nav>
