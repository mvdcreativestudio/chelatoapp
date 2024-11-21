@extends('content/landing/layouts/landing-layout')

@section('title', 'Contacto')

@section('content')

<div class="message-container mt-5">
  @if(session('success'))
  <div class="alert alert-success">
    {{ session('success') }}
  </div>
@endif

@if($errors->any())
  <div class="alert alert-danger">
    <ul>
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif
</div>



<div class="container-fluid mt-5 col-xl-8 col-md-9 col-10">
  <!-- Basic Layout -->
  <div class="col-xxl">
    <div class="card mb-4">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Contactanos</h5>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('landing-page.contact.send') }}">
          @csrf
          <div class="row mb-3">
            <label class="col-sm-2 col-form-label" for="basic-default-name">Nombre y Apellido</label>
            <div class="col-sm-10">
              <input type="text" class="form-control" id="basic-default-name" name="name" placeholder="Ingrese su nombre y Apellido" required />
            </div>
          </div>
          <div class="row mb-3">
            <label class="col-sm-2 col-form-label" for="basic-default-company">Empresa</label>
            <div class="col-sm-10">
              <input type="text" class="form-control" id="basic-default-company" name="company" placeholder="Ingrese su empresa (Opcional)" />
            </div>
          </div>
          <div class="row mb-3">
            <label class="col-sm-2 col-form-label" for="basic-default-email">Correo Electrónico</label>
            <div class="col-sm-10">
              <input type="email" id="basic-default-email" class="form-control" name="email" placeholder="Ingrese su correo electrónico " required />
            </div>
          </div>
          <div class="row mb-3">
            <label class="col-sm-2 col-form-label" for="basic-default-phone">Teléfono</label>
            <div class="col-sm-10">
              <input type="text" id="basic-default-phone" class="form-control phone-mask" name="phone" placeholder="Ingrese su número de teléfono" />
            </div>
          </div>
          <div class="row mb-3">
            <label class="col-sm-2 col-form-label" for="basic-default-message">Mensaje</label>
            <div class="col-sm-10">
              <textarea id="basic-default-message" class="form-control" name="message" placeholder="Ingrese su mensaje" required></textarea>
            </div>
          </div>
          <div class="row justify-content-end">
            <div class="col-sm-10 text-end">
              <button type="submit" class="btn btn-primary">Enviar</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
