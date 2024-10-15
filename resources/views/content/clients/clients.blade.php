@extends('layouts/layoutMaster')

@section('title', 'Clientes')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.scss',
'resources/assets/vendor/libs/@form-validation/form-validation.scss',
'resources/assets/vendor/libs/select2/select2.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/moment/moment.js',
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/@form-validation/popular.js',
'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
'resources/assets/vendor/libs/@form-validation/auto-focus.js',
'resources/assets/vendor/libs/cleavejs/cleave.js',
'resources/assets/vendor/libs/cleavejs/cleave-phone.js'
])
@endsection

@section('page-script')
@vite('resources/assets/js/clients-list.js')
@endsection

@section('content')
<div class="d-flex flex-column flex-md-row align-items-center justify-content-between bg-white p-4 mb-3 rounded shadow-lg sticky-top border-bottom border-light">
  <div class="d-flex flex-column justify-content-center mb-3 mb-md-0">
    <h4 class="mb-0 page-title">
      <i class="bx bx-user-circle me-2"></i> Clientes
    </h4>
  </div>

  <div class="d-flex align-items-center justify-content-center flex-grow-1 gap-3 mb-3 mb-md-0 mx-md-4">
    <div class="input-group w-100 w-md-75 shadow-sm">
      <span class="input-group-text bg-white">
        <i class="bx bx-search"></i>
      </span>
      <input type="text" id="searchClient" class="form-control" placeholder="Buscar cliente..." aria-label="Buscar Cliente">
    </div>
  </div>

  <div class="text-end d-flex gap-2">
    <button type="button" class="btn btn-primary btn-sm shadow-sm d-flex align-items-center gap-1 w-100" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEcommerceCustomerAdd">
      <i class="bx bx-plus"></i> Nuevo Cliente
    </button>
  </div>
</div>

@if(session('success'))
<div class="alert alert-success d-flex" role="alert">
  <span class="badge badge-center rounded-pill bg-success border-label-success p-3 me-2"><i class="bx bx-user fs-6"></i></span>
  <div class="d-flex flex-column ps-1">
    <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">¡Correcto!</h6>
    <span>{{ session('success') }}</span>
  </div>
</div>
@elseif(session('error'))
<div class="alert alert-danger d-flex" role="alert">
  <span class="badge badge-center rounded-pill bg-danger border-label-danger p-3 me-2"><i class="bx bx-user fs-6"></i></span>
  <div class="d-flex flex-column ps-1">
    <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">¡Error!</h6>
    <span>{{ session('error') }}</span>
  </div>
</div>
@endif

<!-- Clients List Container -->
<div class="row client-list-container" data-ajax-url="{{ route('clients.datatable') }}">
  <!-- Las tarjetas de clientes se generarán aquí dinámicamente -->
</div>

<!-- Offcanvas to add new customer -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEcommerceCustomerAdd" aria-labelledby="offcanvasEcommerceCustomerAddLabel">
  <div class="offcanvas-header">
    <h5 id="offcanvasEcommerceCustomerAddLabel" class="offcanvas-title">Crear cliente</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body mx-0 flex-grow-0">
    <form class="ecommerce-customer-add pt-0" id="eCommerceCustomerAddForm" method="POST" action="{{ route('clients.store') }}" onsubmit="return false">
      @csrf
      <div class="ecommerce-customer-add-basic mb-3">
        <h6 class="mb-3">Información básica</h6>
        <div class="col-md mb-3">
          <small class="text-light fw-medium d-block">Tipo de cliente</small>
          <div class="form-check form-check-inline mt-1">
            <input class="form-check-input" type="radio" name="type" id="individualType" value="individual" checked />
            <label class="form-check-label" for="individualType">Consumidor Final</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="type" id="companyType" value="company" />
            <label class="form-check-label" for="companyType">Empresa</label>
          </div>
        </div>

        <!-- Campos requeridos para Persona y Empresa -->
        <div class="mb-3">
          <label class="form-label" for="ecommerce-customer-add-name">Nombre <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="ecommerce-customer-add-name" placeholder="Ingrese el nombre" name="name" required />
        </div>
        <div class="mb-3">
          <label class="form-label" for="ecommerce-customer-add-lastname">Apellido <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="ecommerce-customer-add-lastname" placeholder="Ingrese el apellido" name="lastname" required />
        </div>

        <!-- Campo CI para Persona -->
        <div class="mb-3" id="ciField">
          <label class="form-label" for="ci">CI <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="ci" placeholder="Ingrese el CI" name="ci" required />
        </div>

        <!-- Campo Razón Social y RUT para Empresa -->
        <div class="mb-3" id="razonSocialField" style="display: none;">
          <label class="form-label" for="company_name">Razón Social <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="company_name" placeholder="Ingrese la razón social" name="company_name" />
        </div>

        <div class="mb-3" id="rutField" style="display: none;">
          <label class="form-label" for="rut">RUT <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="rut" placeholder="Ingrese el RUT" name="rut" />
        </div>

        <!-- Campo Email (requerido para ambos) -->
        <div class="mb-3">
          <label class="form-label" for="ecommerce-customer-add-email">Email <span class="text-danger">*</span></label>
          <input type="email" id="ecommerce-customer-add-email" class="form-control" placeholder="mail@empresa.com" name="email" required />
        </div>

        <!-- Campo Teléfono (opcional) -->
        <div>
          <label class="form-label" for="ecommerce-customer-add-contact">Teléfono</label>
          <input type="text" id="ecommerce-customer-add-contact" class="form-control" placeholder="Ingrese el teléfono" name="phone" />
        </div>
      </div>

      <!-- Campos adicionales compartidos -->
      <div class="ecommerce-customer-add-shiping mb-3 pt-2">
        <div class="mb-3">
          <label class="form-label" for="address">Dirección <span id="direccionAsterisk" class="text-danger">*</span></label>
          <input type="text" id="ecommerce-customer-add-address" class="form-control" placeholder="Ingrese la dirección" name="address" required/>
        </div>
        <div class="mb-3">
          <label class="form-label" for="city">Ciudad <span id="ciudadAsterisk" class="text-danger" style="display: none;">*</span></label>
          <input type="text" id="ecommerce-customer-add-town" class="form-control" placeholder="Ingrese la ciudad" name="city" />
        </div>
        <div class="mb-3">
          <label class="form-label" for="state">Departamento <span id="departamentoAsterisk" class="text-danger" style="display: none;">*</span></label>
          <input type="text" id="ecommerce-customer-add-state" class="form-control" placeholder="Ingrese el departamento" name="state" />
        </div>
        <div>
          <label for="country" class="form-label">País</label>
          <select id="country" class="form-select form-select" name="country">
            <option value="Uruguay" selected>Uruguay</option>
          </select>
        </div>
        <div class="mb-3 mt-3">
          <label class="form-label" for="website">Sitio Web</label>
          <input type="text" id="website" class="form-control" placeholder="Ingrese el sitio web" name="website" />
        </div>
      </div>

      <div class="pt-3">
        <button type="button" class="btn btn-primary me-sm-3 me-1 data-submit" id="guardarCliente">Crear cliente</button>
        <button type="reset" class="btn bg-label-danger" data-bs-dismiss="offcanvas">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<style>

</style>
@endsection
