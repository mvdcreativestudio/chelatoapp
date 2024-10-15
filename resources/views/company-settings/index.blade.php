@extends('layouts/layoutMaster')

@section('title', 'Configuración Empresa')

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Configuración /</span> Empresa
</h4>

@include('components.alerts')

<div class="col-xl">
  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Editar datos de la empresa</h5>
    </div>
    <div class="card-body">
      <form action="{{ route('company-settings.update', ['company_setting' => 1]) }}" method="POST" id="myForm">
        @csrf
        @method('PUT')
        <div class="mb-3">
          <label class="form-label" for="name">Nombre de la empresa</label>
          <input type="text" id="name" name="name" class="form-control" placeholder="Ingrese el nombre de su empresa" value="{{$companySettings->name}}" required />
        </div>
        <div class="row col-12">
          <div class="mb-3 col-md-3 col-12">
            <label class="form-label" for="address">Dirección</label>
            <input type="text" id="address" name="address" class="form-control" placeholder="Ingrese la dirección de su empresa" value="{{$companySettings->address}}" />
          </div>
          <div class="mb-3 col-md-3 col-12">
            <label class="form-label" for="city">Ciudad</label>
            <input type="text" id="city" name="city" class="form-control" placeholder="Ingrese la ciudad de su empresa" value="{{$companySettings->city}}"/>
          </div>
          <div class="mb-3 col-md-3 col-12">
            <label class="form-label" for="state">Departamento</label>
            <input type="text" id="state" name="state" class="form-control" placeholder="Ingrese el departamento de su empresa" value="{{$companySettings->state}}"/>
          </div>
          <div class="mb-3 col-md-3 col-12">
            <label class="form-label" for="country">País</label>
            <input type="text" id="country" name="country" class="form-control" placeholder="Ingrese el país de su empresa" value="{{$companySettings->country}}"/>
          </div>
        </div>
        <div class="row col-12">
          <div class="mb-3 col-md-6 col-12">
            <label class="form-label" for="phone">Teléfono</label>
            <input type="text" id="phone" name="phone" class="form-control phone-mask" placeholder="099 111 222" value="{{$companySettings->phone}}"/>
          </div>
          <div class="mb-3 col-md-6 col-12">
            <label class="form-label" for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control" placeholder="contacto@suempresa.com" value="{{$companySettings->email}}"/>
          </div>
        </div>
        <div class="row col-12">
          <div class="mb-3 col-md-6 col-12">
            <label class="form-label" for="website">Sitio Web</label>
            <input type="text" id="website" name="website" class="form-control" placeholder="www.suempresa.com" value="{{$companySettings->website}}"/>
          </div>
          <div class="mb-3 col-md-6 col-12">
            <label class="form-label" for="rut">RUT</label>
            <input type="text" id="rut" name="rut" class="form-control" placeholder="215645876452139" value="{{$companySettings->rut}}"/>
          </div>
        </div>

        <!-- Redes Sociales -->
        <h5 class="mb-3 mt-5">Redes Sociales</h5>
        <div class="row col-12">
          <div class="mb-3 col-md-6 col-12">
            <label class="form-label" for="facebook">Facebook</label>
            <input type="text" id="facebook" name="facebook" class="form-control" placeholder="URL de Facebook" value="{{$companySettings->facebook}}"/>
          </div>
          <div class="mb-3 col-md-6 col-12">
            <label class="form-label" for="instagram">Instagram</label>
            <input type="text" id="instagram" name="instagram" class="form-control" placeholder="URL de Instagram" value="{{$companySettings->instagram}}"/>
          </div>
        </div>
        <div class="row col-12">
          <div class="mb-3 col-md-6 col-12">
            <label class="form-label" for="twitter">Twitter</label>
            <input type="text" id="twitter" name="twitter" class="form-control" placeholder="URL de Twitter" value="{{$companySettings->twitter}}"/>
          </div>
          <div class="mb-3 col-md-6 col-12">
            <label class="form-label" for="linkedin">LinkedIn</label>
            <input type="text" id="linkedin" name="linkedin" class="form-control" placeholder="URL de LinkedIn" value="{{$companySettings->linkedin}}"/>
          </div>
        </div>
        <div class="row col-12">
          <div class="mb-3 col-md-6 col-12">
            <label class="form-label" for="youtube">YouTube</label>
            <input type="text" id="youtube" name="youtube" class="form-control" placeholder="URL de YouTube" value="{{$companySettings->youtube}}"/>
          </div>
        </div>

        <div class="d-flex gap-2 mb-3 col-12 mt-3">
          <input type="hidden" name="allow_registration" value="0"/>
          <input class="form-check-input" type="checkbox" value="1" id="allow_registration" name="allow_registration" {{ $companySettings->allow_registration ? 'checked' : '' }}>
          <label class="form-check-label" for="allow_registration">Permitir registro en página de Login</label>
        </div>

        <button type="submit" class="btn btn-primary">Guardar</button>
      </form>
    </div>
  </div>
</div>

<script>
  document.getElementById('myForm').onsubmit = function() {
      console.log('Form submitted');
  };
</script>

@endsection
