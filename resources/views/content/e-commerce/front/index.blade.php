@extends('content.e-commerce.front.layouts.ecommerce-layout')

@section('title', 'Chelato')

@section('content')


<div class="ecommerce-background vh-100">
  <div class="vendors-container container mt-5">
    <div class="row text-center justify-content-center">
      <h4>Selecciona tu local más cercano</h4>
      <form action="{{ route('cart.selectStore', ['storeId' => 'STORE_ID_PLACEHOLDER']) }}" method="POST" id="selectStoreForm">
      @csrf
      <div class="d-flex justify-content-center">
        <div class="row gy-3 mt-0 col-12 col-md-8 justify-content-center">
            @foreach ($stores as $store)
              <div class="col-xl-3 col-md-5 col-sm-6 col-6">
                <div class="form-check custom-option custom-option-icon">
                  <label class="form-check-label custom-option-content" for="store{{ $store->id }}">
                    <span class="custom-option-body">
                      <i class="fa-solid fa-store"></i>
                      <span class="custom-option-title">{{$store->name}}</span>
                      <small>{{$store->address}}</small>
                    </span>
                    <input name="storeId" class="form-check-input" type="radio" value="{{ $store->id }}" id="store{{ $store->id }}" {{ $loop->first ? 'checked' : '' }} />
                  </label>
                </div>
              </div>
            @endforeach
        </div>
      </div>
      <button class="btn btn-primary col-md-3 col-6 mt-5">Continuar</button>
      </form>
    </div>
  </div>

  <div class="d-flex quienes-somos-home container-fluid mt-3">
    <div class="col-6 text-center quienes-somos-container container">
      <h2>¿Quiénes somos?</h2>
      <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam tempor, orci eu lacinia rutrum, mauris metus malesuada neque, a maximus erat turpis quis dolor. In efficitur iaculis feugiat. Sed eget facilisis justo, vel auctor lacus. Donec in velit non orci facilisis aliquet at sed ante. Cras elementum ipsum metus, nec porta lectus porta sit amet. Nunc et tristique arcu, vel tempor neque. Proin placerat, lacus ut consequat vestibulum, lorem ex.</p>
    </div>

    <div class="col-6 p-5 text-end homepage-img-container">
      <img class="homepage-quienes-somos-img" src="{{ asset('assets\img\front-pages\homepage\img-01.svg') }}" alt="">
    </div>
  </div>

  <div class="d-flex servicios-home container-fluid">
    <div class="col-6 p-5 text-center">
      <img class="homepage-servicios-img" src="{{ asset('assets\img\front-pages\homepage\eventos-1.jpg') }}" alt="">
    </div>

    <div class="col-6 text-center servicios-container container">
      <h2>Servicios</h2>
      <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam tempor, orci eu lacinia rutrum, mauris metus malesuada neque, a maximus erat turpis quis dolor. In efficitur iaculis feugiat. Sed eget facilisis justo, vel auctor lacus. Donec in velit non orci facilisis aliquet at sed ante. Cras elementum ipsum metus, nec porta lectus porta sit amet. Nunc et tristique arcu, vel tempor neque. Proin placerat, lacus ut consequat vestibulum, lorem ex.</p>
    </div>
  </div>

  <div class="d-flex quienes-somos-home container-fluid">
    <div class="col-6 text-center quienes-somos-container container">
      <h2>Locales</h2>
      <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam tempor, orci eu lacinia rutrum, mauris metus malesuada neque, a maximus erat turpis quis dolor. In efficitur iaculis feugiat. Sed eget facilisis justo, vel auctor lacus. Donec in velit non orci facilisis aliquet at sed ante. Cras elementum ipsum metus, nec porta lectus porta sit amet. Nunc et tristique arcu, vel tempor neque. Proin placerat, lacus ut consequat vestibulum, lorem ex.</p>
    </div>

    <div class="col-6 p-5 text-end homepage-img-container">
      <img class="homepage-quienes-somos-img" src="{{ asset('assets\img\front-pages\homepage\locales-1.jpg') }}" alt="">
    </div>
  </div>


</div>




@endsection
