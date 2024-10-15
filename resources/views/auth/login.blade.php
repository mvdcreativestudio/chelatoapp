@php
$configData = Helper::appClasses();
$customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/blankLayout')

@section('title', 'Iniciar sesión')

@section('page-style')
<!-- Page -->
@vite('resources/assets/vendor/scss/pages/page-auth.scss')
@endsection

@section('content')
<div class="authentication-wrapper authentication-cover">
  <div class="authentication-inner row m-0">
    <!-- /Left Text -->
    <div class="d-none d-lg-flex col-lg-7 col-xl-8 align-items-center p-5">
      <div class="w-100 d-flex justify-content-center">
        <img src="{{asset('assets/img/illustrations/boy-with-rocket-'.$configData['style'].'.png')}}" class="img-fluid" alt="Imagen de inicio de sesión" width="700" data-app-dark-img="illustrations/boy-with-rocket-dark.png" data-app-light-img="illustrations/boy-with-rocket-light.png">
      </div>
    </div>
    <!-- /Left Text -->

    <!-- Login -->
    <div class="d-flex col-12 col-lg-5 col-xl-4 align-items-center authentication-bg p-sm-5 p-4">
      <div class="w-px-400 mx-auto">
        <!-- Logo -->
        <div class="row mb-5 text-center">
          <a href="{{url('/')}}" class="text-center justify-content-center">
            <img src="{{ asset($companySettings->logo_black) }}" alt="" class="navbar-logo justify-content-center">
          </a>
        </div>
        <!-- /Logo -->
        <h4 class="mb-2 text-center">¡Bienvenido a {{$companySettings->name}}!</h4>
        <p class="mb-4 text-center">Por favor, inicia sesión en tu cuenta</p>

        @if (session('status'))
        <div class="alert alert-success mb-1 rounded-0" role="alert">
          <div class="alert-body">
            {{ session('status') }}
          </div>
        </div>
        @endif

        <form id="formAuthentication" class="mb-3" action="{{ route('login') }}" method="POST">
          @csrf
          <div class="mb-3">
            <label for="login-email" class="form-label">Correo electrónico</label>
            <input type="text" class="form-control @error('email') is-invalid @enderror" id="login-email" name="email" placeholder="john@example.com" autofocus value="{{ old('email') }}">
            @error('email')
            <span class="invalid-feedback" role="alert">
              <span class="fw-medium">{{ $message }}</span>
            </span>
            @enderror
          </div>
          <div class="mb-3 form-password-toggle">
            <div class="d-flex justify-content-between">
              <label class="form-label" for="login-password">Contraseña</label>
              @if (Route::has('password.request'))
              <a href="{{ route('password.request') }}">
                <small>¿Olvidaste tu contraseña?</small>
              </a>
              @endif
            </div>
            <div class="input-group input-group-merge @error('password') is-invalid @enderror">
              <input type="password" id="login-password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" />
              <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
            </div>
            @error('password')
            <span class="invalid-feedback" role="alert">
              <span class="fw-medium">{{ $message }}</span>
            </span>
            @enderror
          </div>
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="remember-me" name="remember" {{ old('remember') ? 'checked' : '' }}>
              <label class="form-check-label" for="remember-me">
                Recuérdame
              </label>
            </div>
          </div>
          <button class="btn btn-primary d-grid w-100" type="submit">Iniciar sesión</button>
        </form>

        @if($companySettings->allow_registration)
        <p class="text-center">
          <span>¿Eres nuevo en {{$companySettings->name}}?</span>
          @if (Route::has('register'))
          <a href="{{ route('register') }}">
            <span>Crea una cuenta</span>
          </a>
          @endif
        </p>
        @endif

        <div class="divider my-4">
          <div class="divider-text">Sumeria</div>
        </div>

        <!-- Social login buttons removed -->
      </div>
    </div>
    <!-- /Login -->
  </div>
</div>
@endsection
