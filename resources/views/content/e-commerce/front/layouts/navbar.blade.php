<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
    <img src="{{ asset('assets/img/branding/chelato-white.png') }}" alt="" class="navbar-logo">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarScroll">
      <ul class="navbar-nav me-auto my-2 my-lg-0 navbar-nav-scroll" style="--bs-scroll-height: 100px;">
        @if(session('store'))
        <li class="nav-item">
          <a class="nav-link nav-link-menu" aria-current="page" href="{{ route('shop') }}"><i class="fa-solid fa-location-dot"></i>     {{ session('store')['name'] }}</a>
        </li>
        @endif
      </ul>
      <ul class="navbar-nav navbar-nav-scroll">
        <li>
          <a class="nav-link nav-link-menu" aria-current="page" href="#">Iniciar Sesión</a>
        </li>
        <li>
          <a href="" class="nav-link nav-link-menu"><i class="fa-regular fa-user"></i></a>
        </li>
        <li>
          <a href="" class="nav-link nav-link-menu" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEnd"><i class="fa-solid fa-cart-shopping"></i></a>
        </li>
        <li>
          <a href="" class="nav-link nav-link-menu"><i class="fa-solid fa-bars"></i></a>
        </li>
      </ul>
    </div>
  </div>
</nav>
