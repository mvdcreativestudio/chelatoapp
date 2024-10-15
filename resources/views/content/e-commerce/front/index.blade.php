@extends('content.e-commerce.front.layouts.ecommerce-layout')

@section('title', 'Sumeria')

@section('content')

@if(session('store_closed_error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Error',
                text: '{{ session('store_closed_error') }}',
                icon: 'error',
                confirmButtonText: 'OK',
                onClose: () => {
                    window.location.href = "{{ route('session.clear') }}";
                }
            });
        });
        setTimeout(() => {
            window.location.href = "{{ route('session.clear') }}";
        }, 3000);

    </script>
@endif

<div class="video-container">
  <video autoplay muted loop id="myVideo" class="video-background">
      <source src="assets/img/videos/back-chelato.mp4" type="video/mp4">
  </video>
  <div class="video-overlay">
    <h2 class="header-title">Llegaste al paraíso del helado</h2>
    <img src="assets/img/branding/chelato-white.png" class="logo-header" alt="">
    <div class="animated-text-container">
      <a class="animated-text" href="#selectStore">Pedí Online <i class="fa fa-arrow-down arrow-animate"></i></a>
    </div>
  </div>
</div>



<div class="ecommerce-background vh-100"  id="selectStore">
  @if(session('error'))
    <div class="alert alert-danger d-flex text-center" role="alert">
      <div class="d-flex flex-column ps-1 text-center justify-content-center w-100">
        <h6 class="alert-heading d-flex align-items-center fw-bold mb-1 text-center m-auto">¡Error!</h6>
        <span>{{ session('error') }}</span>
      </div>
    </div>
  @endif
  @if(session('store') == null)
  <div class="vendors-container container mt-5">
    <div class="row text-center justify-content-center">
      <h4>Selecciona tu local más cercano</h4>
      <form action="{{ route('cart.selectStore') }}" method="POST" id="selectStoreForm">
      @csrf
      <div class="d-flex justify-content-center">
        <div class="row gy-3 mt-0 col-12 col-md-8 justify-content-center">
            @foreach ($stores as $store)
              @if ($store->ecommerce == 1)
                @if ($store->closed != 1)
                <div class="col-xl-3 col-md-5 col-sm-6 col-6">
                  <div class="form-check custom-option custom-option-icon">
                    <label class="form-check-label custom-option-content" for="store{{ $store->id }}">
                      <span class="custom-option-body">
                        <i class="fa-solid fa-store"></i>
                        <span class="custom-option-title">{{$store->name}}</span>
                        <small class="text-success">Empresa abierta</small>
                      </span>
                      <input name="slug" class="form-check-input" type="radio" value="{{ $store->slug }}" id="store{{ $store->id }}" {{ $loop->first ? 'checked' : '' }} />
                    </label>
                  </div>
                </div>
                @else
                <div class="col-xl-3 col-md-5 col-sm-6 col-6">
                  <div class="form-check custom-option custom-option-icon">
                    <label class="form-check-label custom-option-content" for="store{{ $store->id }}">
                      <span class="custom-option-body">
                        <i class="fa-solid fa-store"></i>
                        <span class="custom-option-title">{{$store->name}}</span>
                        <small class="text-danger">Empresa cerrada</small>
                      </span>
                    </label>
                  </div>
                </div>
                @endif
              @endif
            @endforeach
        </div>
      </div>
      <button class="btn btn-primary col-md-3 col-6 mt-5">Continuar</button>
      </form>
    </div>
  </div>
  @else
  <div class="vendors-container container mt-5">
    <div class="row text-center justify-content-center">
      <h4>Finaliza tu pedido</h4>
      <a href="{{ route('store', ['slug' => session('store')['slug']]) }}" class="btn btn-primary col-md-3 col-6 mt-2 mb-4">Ir a la tienda</a>

    </div>
  </div>
  @endif

  <div class="container-fluid mt-3">
    <div class="row align-items-center">
      <div class="col-lg-6 col-md-12 text-center quienes-somos-container mb-3 mb-lg-0">
        <h2>¿Quiénes somos?</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam tempor, orci eu lacinia rutrum, mauris metus malesuada neque, a maximus erat turpis quis dolor. In efficitur iaculis feugiat. Sed eget facilisis justo, vel auctor lacus. Donec in velit non orci facilisis aliquet at sed ante. Cras elementum ipsum metus, nec porta lectus porta sit amet. Nunc et tristique arcu, vel tempor neque. Proin placerat, lacus ut consequat vestibulum, lorem ex.</p>
      </div>
      <div class="col-lg-6 col-md-12 p-5 text-end homepage-img-container">
        <img class="img-fluid homepage-quienes-somos-img" src="{{ asset('assets/img/front-pages/homepage/img-01.svg') }}" alt="">
      </div>
    </div>
  </div>

  <div class="container-fluid">
    <div class="row align-items-center my-5">
      <div class="col-lg-6 col-md-12 p-5 text-center mb-3 mb-lg-0">
        <img class="img-fluid homepage-servicios-img" src="{{ asset('assets/img/front-pages/homepage/eventos-1.jpg') }}" alt="">
      </div>
      <div class="col-lg-6 col-md-12 text-center servicios-container">
        <h2>Servicios</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam tempor, orci eu lacinia rutrum, mauris metus malesuada neque, a maximus erat turpis quis dolor. In efficitur iaculis feugiat. Sed eget facilisis justo, vel auctor lacus. Donec in velit non orci facilisis aliquet at sed ante. Cras elementum ipsum metus, nec porta lectus porta sit amet. Nunc et tristique arcu, vel tempor neque. Proin placerat, lacus ut consequat vestibulum, lorem ex.</p>
      </div>
    </div>
  </div>

  <div class="container-fluid">
    <div class="row align-items-center">
      <div class="col-lg-6 col-md-12 text-center quienes-somos-container mb-3 mb-lg-0">
        <h2>Empresas</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam tempor, orci eu lacinia rutrum, mauris metus malesuada neque, a maximus erat turpis quis dolor. In efficitur iaculis feugiat. Sed eget facilisis justo, vel auctor lacus. Donec in velit non orci facilisis aliquet at sed ante. Cras elementum ipsum metus, nec porta lectus porta sit amet. Nunc et tristique arcu, vel tempor neque. Proin placerat, lacus ut consequat vestibulum, lorem ex.</p>
      </div>
      <div class="col-lg-6 col-md-12 p-5 text-end homepage-img-container">
        <img class="img-fluid homepage-quienes-somos-img" src="{{ asset('assets/img/front-pages/homepage/locales-1.jpg') }}" alt="">
      </div>
    </div>
  </div>



</div>




@endsection
