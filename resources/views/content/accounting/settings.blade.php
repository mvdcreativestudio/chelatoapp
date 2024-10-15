@extends('layouts/layoutMaster')

@section('title', 'Configuración de Contabilidad')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss'
])
@endsection

@section('content')
<div class="container">
  <div class="row">
      <div class="col-12">
          <div class="card">
              <div class="card-header">
                  <h4 class="card-title">Configuración del RUT</h4>
              </div>
              <div class="card-body">
                  <form action="{{ route('accounting.saveRut') }}" method="POST">
                      @csrf
                      <div class="form-group">
                          <label for="rut">RUT de la Empresa</label>
                          <input type="text" class="form-control my-3" id="rut" name="rut" value="{{ $pymoSetting->settingValue ?? '' }}" required>
                      </div>
                      <button type="submit" class="btn btn-primary">Guardar</button>
                  </form>
                  @if(session('success_rut'))
                  <div class="alert alert-success mt-3">
                      {{ session('success_rut') }}
                  </div>
                  @endif
                  @if(session('error_rut'))
                  <div class="alert alert-danger mt-3">
                      {{ session('error_rut') }}
                  </div>
                  @endif
              </div>
          </div>
      </div>

      @if($companyInfo)
      <div class="col-12 mt-4">
          <div class="card">
              <div class="card-header">
                  <h4 class="card-title">Logo de la Empresa</h4>
              </div>
              <div class="card-body">
                  @if($logoUrl)
                      <div class="mb-3">
                          <img src="{{ asset($logoUrl) }}" alt="Company Logo" class="img-thumbnail" style="max-width: 200px;">
                      </div>
                  @endif
                  <form action="{{ route('accounting.uploadLogo') }}" method="POST" enctype="multipart/form-data">
                      @csrf
                      <div class="form-group">
                          <input type="file" class="form-control-file" id="logo" name="logo" required>
                      </div>
                      <button type="submit" class="btn btn-primary mt-3">Actualizar Logo</button>
                  </form>
                  @if(session('success_logo'))
                  <div class="alert alert-success mt-3">
                      {{ session('success_logo') }}
                  </div>
                  @endif
                  @if(session('error_logo'))
                  <div class="alert alert-danger mt-3">
                      {{ session('error_logo') }}
                  </div>
                  @endif
              </div>
          </div>
      </div>

      <div class="col-12 mt-4">
          <div class="card">
              <div class="card-header">
                  <h4 class="card-title">Información de la Empresa</h4>
              </div>
              <div class="card-body">
                <form>
                  @if(!empty($companyInfo['name']))
                      <div class="form-group">
                          <label for="companyName">Nombre de la Empresa</label>
                          <input type="text" class="form-control my-3" id="companyName" value="{{ $companyInfo['name'] }}" disabled>
                      </div>
                  @endif

                  @if(!empty($companyInfo['rut']))
                      <div class="form-group">
                          <label for="companyRUT">RUT</label>
                          <input type="text" class="form-control my-3" id="companyRUT" value="{{ $companyInfo['rut'] }}" disabled>
                      </div>
                  @endif

                  @if(!empty($companyInfo['socialPurpose']))
                      <div class="form-group">
                          <label for="socialPurpose">Propósito Social</label>
                          <input type="text" class="form-control my-3" id="socialPurpose" value="{{ $companyInfo['socialPurpose'] }}" disabled>
                      </div>
                  @endif

                  @if(!empty($companyInfo['resolutionNumber']))
                      <div class="form-group">
                          <label for="resolutionNumber">Número de Resolución</label>
                          <input type="text" class="form-control my-3" id="resolutionNumber" value="{{ $companyInfo['resolutionNumber'] }}" disabled>
                      </div>
                  @endif

                  @if(!empty($companyInfo['email']))
                      <div class="form-group">
                          <label for="companyEmail">Correo Electrónico</label>
                          <input type="email" class="form-control my-3" id="companyEmail" value="{{ $companyInfo['email'] }}" disabled>
                      </div>
                  @endif

                  @if(!empty($companyInfo['createdAt']))
                      <div class="form-group">
                          <label for="createdAt">Fecha de Creación</label>
                          <input type="text" class="form-control my-3" id="createdAt" value="{{ $companyInfo['createdAt'] }}" disabled>
                      </div>
                  @endif

                  @if(!empty($companyInfo['updatedAt']))
                      <div class="form-group">
                          <label for="updatedAt">Fecha de Actualización</label>
                          <input type="text" class="form-control my-3" id="updatedAt" value="{{ $companyInfo['updatedAt'] }}" disabled>
                      </div>
                  @endif
              </form>

              </div>
          </div>
      </div>
      @endif
  </div>
</div>
@endsection
