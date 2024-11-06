@extends('layouts/layoutMaster')

@section('title', 'Configuración Empresa')

@section('content')
<div class="d-flex align-items-center justify-content-between bg-white p-4 mb-3 rounded shadow-lg sticky-top border-bottom border-light">

  <!-- Title aligned to the left -->
  <div class="d-flex flex-column justify-content-center">
    <h4 class="mb-0 page-title">
      <i class="bx bx-cog me-2"></i> Configuración de la Aplicación
    </h4>
  </div>

  <!-- Action buttons aligned to the right -->
  <div class="text-end d-flex gap-2">
    <!-- Add any additional action buttons if needed -->
  </div>
</div>

@include('components.alerts')

<div class="col-xl">
  <div class="card mb-5 shadow-sm border-0">
    <div class="card-body p-4">
      <form action="{{ route('company-settings.update', ['company_setting' => 1]) }}" method="POST" id="myForm" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Información General -->
        <h5 class="mb-4">Información General</h5>
        <div class="row">
          <div class="mb-3 col-md-6">
            <label class="form-label" for="name">Nombre de la Empresa</label>
            <input type="text" id="name" name="name" class="form-control" placeholder="Ingrese el nombre de su empresa" value="{{ $companySettings->name }}" required />
          </div>
          <div class="mb-3 col-md-6">
            <label class="form-label" for="rut">RUT</label>
            <input type="text" id="rut" name="rut" class="form-control" placeholder="215645876452139" value="{{ $companySettings->rut }}" />
          </div>
        </div>

        <h6 class="text-muted mt-4">Dirección de la Empresa</h6>
        <div class="row mt-2">
          <div class="mb-3 col-md-6">
            <label class="form-label" for="address">Dirección</label>
            <input type="text" id="address" name="address" class="form-control" placeholder="Ingrese la dirección" value="{{ $companySettings->address }}" />
          </div>
          <div class="mb-3 col-md-3">
            <label class="form-label" for="city">Ciudad</label>
            <input type="text" id="city" name="city" class="form-control" placeholder="Ingrese la ciudad" value="{{ $companySettings->city }}" />
          </div>
          <div class="mb-3 col-md-3">
            <label class="form-label" for="state">Departamento</label>
            <input type="text" id="state" name="state" class="form-control" placeholder="Ingrese el departamento" value="{{ $companySettings->state }}" />
          </div>
        </div>
        
        <div class="row mt-2">
          <div class="mb-3 col-md-6">
            <label class="form-label" for="country">País</label>
            <input type="text" id="country" name="country" class="form-control" placeholder="Ingrese el país" value="{{ $companySettings->country }}" />
          </div>
          <div class="mb-3 col-md-6">
            <label class="form-label" for="phone">Teléfono</label>
            <input type="text" id="phone" name="phone" class="form-control" placeholder="099 111 222" value="{{ $companySettings->phone }}" />
          </div>
        </div>

        <h6 class="text-muted mt-4">Información de Contacto</h6>
        <div class="row mt-2">
          <div class="mb-3 col-md-6">
            <label class="form-label" for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control" placeholder="contacto@suempresa.com" value="{{ $companySettings->email }}" />
          </div>
          <div class="mb-3 col-md-6">
            <label class="form-label" for="website">Sitio Web</label>
            <input type="text" id="website" name="website" class="form-control" placeholder="www.suempresa.com" value="{{ $companySettings->website }}" />
          </div>
        </div>

        <!-- Redes Sociales -->
        <h5 class="mt-5 mb-3">Redes Sociales</h5>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label" for="facebook">Facebook</label>
            <input type="text" id="facebook" name="facebook" class="form-control" placeholder="URL de Facebook" value="{{ $companySettings->facebook }}" />
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label" for="instagram">Instagram</label>
            <input type="text" id="instagram" name="instagram" class="form-control" placeholder="URL de Instagram" value="{{ $companySettings->instagram }}" />
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label" for="twitter">Twitter</label>
            <input type="text" id="twitter" name="twitter" class="form-control" placeholder="URL de Twitter" value="{{ $companySettings->twitter }}" />
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label" for="linkedin">LinkedIn</label>
            <input type="text" id="linkedin" name="linkedin" class="form-control" placeholder="URL de LinkedIn" value="{{ $companySettings->linkedin }}" />
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label" for="youtube">YouTube</label>
            <input type="text" id="youtube" name="youtube" class="form-control" placeholder="URL de YouTube" value="{{ $companySettings->youtube }}" />
          </div>
        </div>

        <!-- Configuración Adicional -->
        <h5 class="mt-5">Configuración Adicional</h5>
        <div class="form-check form-switch mb-3">
          <input type="hidden" name="allow_registration" value="0"/>
          <input class="form-check-input" type="checkbox" value="1" id="allow_registration" name="allow_registration" {{ $companySettings->allow_registration ? 'checked' : '' }}>
          <label class="form-check-label" for="allow_registration">Permitir registro en página de Login</label>
        </div>

        <!-- Sección Marca -->
        <h5 class="mt-5">Marca</h5>
        <div class="card mb-4">
          <div class="card-body text-center">
            <h6 class="text-muted mb-3">Logo Actual</h6>
            <div id="currentLogo" class="mb-4">
              @if($companySettings->logo_black)
                <img src="{{ asset($companySettings->logo_black) }}" alt="Logo actual de la empresa" class="img-fluid" style="max-height: 50px;">
              @else
                <p>No hay un logo cargado actualmente.</p>
              @endif
            </div>
            
            <h6 class="text-muted mb-3" id="newLogoText" style="display: none;">Nuevo Logo (sin guardar)</h6>
            <div id="newLogoPreview" class="mb-4"></div>
            
            <div class="dropzone dz-clickable" id="logoDropzone">
              <div class="dz-message needsclick">
                <p class="fs-6 text-primary">Arrastra el logo aquí o haz clic para buscar</p>
              </div>
            </div>
            <input type="file" name="logo_black" id="logoBlackInput" class="d-none">
          </div>
          
        </div>

        <div class="text-end mt-5">
          <button type="submit" class="btn btn-primary btn-sm">Guardar Cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>

@section('vendor-script')
@vite(['resources/assets/vendor/libs/dropzone/dropzone.js'])
@endsection

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const logoDropzoneElement = document.querySelector('#logoDropzone');
    const hiddenLogoInput = document.getElementById('logoBlackInput');
    const newLogoPreview = document.getElementById('newLogoPreview'); // Contenedor para el nuevo logo
    const newLogoText = document.getElementById('newLogoText'); // Texto para el nuevo logo

    if (logoDropzoneElement) {
      const logoDropzone = new Dropzone(logoDropzoneElement, {
        url: '#', 
        autoProcessQueue: false,
        maxFiles: 1,
        clickable: true,
        maxFilesize: 2,
        acceptedFiles: '.jpg,.jpeg,.png,.gif',
        previewTemplate: '<span></span>', // Disable Dropzone's default preview by providing an empty template

        init: function () {
          const dz = this;

          dz.on('addedfile', function (file) {
            // Limpiar la previsualización del nuevo logo y mostrar el texto
            newLogoPreview.innerHTML = '';
            newLogoText.style.display = 'block';

            // Crear una previsualización de la nueva imagen seleccionada
            const reader = new FileReader();
            reader.onload = function (e) {
              const previewImage = document.createElement('img');
              previewImage.src = e.target.result;
              previewImage.className = 'img-fluid';
              previewImage.style.maxHeight = '50px';
              newLogoPreview.appendChild(previewImage);
            };
            reader.readAsDataURL(file);

            // Crear un DataTransfer para asignar el archivo al input oculto
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            hiddenLogoInput.files = dataTransfer.files;
          });

          dz.on('removedfile', function () {
            newLogoPreview.innerHTML = ''; // Clear the preview if the file is removed
            newLogoText.style.display = 'none'; // Hide the new logo text if no file is selected
          });

          const form = document.getElementById('myForm');
          form.addEventListener('submit', function (event) {
            if (dz.getAcceptedFiles().length) {
              event.preventDefault();
              dz.processQueue();
              dz.on('success', function () {
                form.submit();
              });
            } else {
              form.submit();
            }
          });
        }
      });
    }
  });
</script>
@endsection
