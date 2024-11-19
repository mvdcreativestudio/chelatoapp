@extends('layouts/layoutMaster')

@section('title', 'Cliente')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/@form-validation/form-validation.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/@form-validation/popular.js',
  'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
  'resources/assets/vendor/libs/@form-validation/auto-focus.js'
])
@endsection

@section('page-script')
@vite([
  'resources/assets/js/modal-edit-user.js',
])
<script>
  window.baseUrl = "{{ url('/') }}";
</script>
@endsection

<meta name="csrf-token" content="{{ csrf_token() }}">


@section('content')

<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <a href="{{ route('clients.index') }}" class="btn btn-sm btn-primary">
            <i class="bx bx-arrow-back me-1"></i>Volver a Clientes
          </a>
          <h2 class="card-title mb-0">
            @if($client->type == 'individual')
              <i class="bx bx-user me-2"></i>{{ $client->name }} {{ $client->lastname }}
            @elseif($client->type == 'company')
              <i class="bx bx-building me-2"></i>{{ $client->company_name }}
            @endif
          </h2>
        </div>
      </div>
    </div>
  </div>
</div>

@if(session('success'))
<div class="alert alert-success d-flex" role="alert">
  <span class="badge badge-center rounded-pill bg-success border-label-success p-3 me-2"><i
      class="bx bx-user fs-6"></i></span>
  <div class="d-flex flex-column ps-1">
    <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">¡Correcto!</h6>
    <span>{{ session('success') }}</span>
  </div>
</div>
@elseif(session('error'))
<div class="alert alert-danger d-flex" role="alert">
  <span class="badge badge-center rounded-pill bg-danger border-label-danger p-3 me-2"><i
      class="bx bx-user fs-6"></i></span>
  <div class="d-flex flex-column ps-1">
    <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">¡Error!</h6>
    <span>{{ session('error') }}</span>
  </div>
@endif

<!-- Mostrar errores generales si existen -->
@if ($errors->any())
  <div class="alert alert-danger">
    <ul>
      @foreach ($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<div class="col-12">
  <div class="row">
    <!-- Card Border Shadow -->
    <div class="col-sm-6 col-lg-3 mb-4">
      <div class="card animated-card card-border-shadow-primary h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2 pb-1">
            <div class="avatar me-2">
              <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-time"></i></span>
            </div>
            <h4 class="ms-1 mb-0">{{ $client->created_at->format('d/m/Y') }}</h4>
          </div>
            <p class="mb-1 fw-medium me-1">Fecha de Creación</p>

          <p class="mb-0">
            {{-- <span class="fw-medium me-1 text-success">+18.2%</span> --}}
          </p>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3 mb-4">
      <div class="card animated-card card-border-shadow-warning h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2 pb-1">
            <div class="avatar me-2">
              <span class="avatar-initial rounded bg-label-warning"><i class='bx bx-package'></i></span>
            </div>
            <h4 class="ms-1 mb-0">{{ $client->orders->count() }}</h4>
          </div>
            @if($client->orders->count() !== 1)
              <p class="mb-1 fw-medium me-1">Compras Realizadas</p>
            @else
              <p class="mb-1 fw-medium me-1">Compra Realizada</p>
            @endif
          <p class="mb-0">
            {{-- <span class="fw-medium me-1 text-danger">-8.7%</span> --}}
          </p>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3 mb-4">
      <div class="card animated-card card-border-shadow-danger h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2 pb-1">
            <div class="avatar me-2">
              <span class="avatar-initial rounded bg-label-danger"><i class='bx bx-money'></i></span>
            </div>
            <h4 class="ms-1 mb-0">{{ $settings->currency_symbol }}{{ number_format($client->orders->sum('total'), 2) }}</h4>
          </div>
            <p class="mb-1 fw-medium me-1">Total Gastado</p>

          <p class="mb-0">
            {{-- <span class="fw-medium me-1 text-success">+4.3%</span> --}}
          </p>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3 mb-4">
      <div class="card animated-card card-border-shadow-info h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2 pb-1">
            <div class="avatar me-2">
              <span class="avatar-initial rounded bg-label-info"><i class='bx bx-line-chart'></i></span>
            </div>
          @if($client->orders->sum('total') !== 0 && $client->orders->count() !== 0)
            <h4 class="ms-1 mb-0">{{ $settings->currency_symbol }}{{ number_format($client->orders->sum('total') / $client->orders->count(), 2) }}</h4>
          @else
            <h4 class="ms-1 mb-0">{{ $settings->currency_symbol }}0</h4>
          @endif
          </div>
          <p class="mb-1">Ticket medio</p>
          <p class="mb-0">
            {{-- <span class="fw-medium me-1 text-danger">-2.5%</span> --}}
          </p>
        </div>
      </div>
    </div>
  </div>
  <!-- User Card -->
  <div class="card mb-4">
    <div class="card-body">
      <h5 class="pb-2 border-bottom mb-4">Detalles</h5>
      <div class="info-container">
        <ul class="list-unstyled">
          <li class="mb-3">
            <span class="fw-medium me-2">Nombre y Apellido:</span>
            <span>{{ $client->name }} {{ $client->lastname }}</span>
          </li>
          <li class="mb-3">
            <span class="fw-medium me-2">Tipo de Cliente:</span>
            <span>
              @if($client->type == 'individual')
                Persona Física
              @elseif($client->type == 'company')
                Empresa
              @endif
            </span>
          </li>
          @if(Auth::user()->can('access_client-sensitive-data'))
            @if($client->type == 'individual')
              <li class="mb-3">
                <span class="fw-medium me-2">CI:</span>
                <span>{{ $client->ci }}</span>
              </li>
            @endif
            @if($client->type == 'company')
              <li class="mb-3">
                <span class="fw-medium me-2">Razón Social:</span>
                <span>{{ $client->company_name }}</span>
              </li>
              <li class="mb-3">
                <span class="fw-medium me-2">RUT:</span>
                <span>{{ $client->rut }}</span>
              </li>
            @endif
          @endif
          <li class="mb-3">
            <span class="fw-medium me-2">Email:</span>
            <span>{{ $client->email }}</span>
          </li>
          <li class="mb-3">
            <span class="fw-medium me-2">Estado:</span>
            <span class="badge bg-label-success">Activo</span>
          </li>
          <li class="mb-3">
            <span class="fw-medium me-2">Dirección:</span>
            <span>{{ $client->address }}
              @if($client->city)
                {{ ', ' . $client->city }}
              @endif
              @if($client->country)
                {{ ', ' . $client->country }}
              @endif
            </span>
          </li>
          <li class="mb-3">
            <span class="fw-medium me-2">Teléfono:</span>
            <span>{{ $client->phone }}</span>
          </li>
          <li class="mb-3">
            <span class="fw-medium me-2">Listas de Precios:</span>
            <span>{{ $priceListName }}</span>
          </li>
          
        </ul>
        <div class="d-flex justify-content-center pt-3">
          <a href="javascript:;" class="btn btn-primary me-3" data-bs-target="#editUser" data-bs-toggle="modal">Editar</a>
        </div>
      </div>
    </div>
  </div>
  <!-- /User Card -->
</div>

<!-- Modal -->
<div class="modal fade" id="editUser" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-simple modal-edit-user">
    <div class="modal-content p-3 p-md-5">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center mb-4">
          <h3>Editar información del cliente</h3>
          <p>Actualizar los detalles del cliente</p>
        </div>
        <form id="editClientForm" class="row g-3" data-client-id="{{ $client->id }}">
          @csrf
          @method('PUT')
          <div class="col-12 col-md-6">
            <label class="form-label" for="modalEditUserName">Nombre</label>
            <input type="text" id="modalEditUserName" name="name" class="form-control" value="{{ $client->name }}" />
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="modalEditUserLastName">Apellido</label>
            <input type="text" id="modalEditUserLastName" name="lastname" class="form-control" value="{{ $client->lastname }}" />
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="modalEditUserEmail">Email</label>
            <input type="text" id="modalEditUserEmail" name="email" class="form-control" value="{{ $client->email }}" />
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="modalEditUserPhone">Teléfono</label>
            <input type="text" id="modalEditUserPhone" name="phone" class="form-control" value="{{ $client->phone }}" />
          </div>
          <div class="col-12">
            <label class="form-label" for="modalEditUserAddress">Dirección</label>
            <input type="text" id="modalEditUserAddress" name="address" class="form-control" value="{{ $client->address }}" />
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="modalEditUserCity">Ciudad/Barrio</label>
            <input type="text" id="modalEditUserCity" name="city" class="form-control" value="{{ $client->city }}" />
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="modalEditUserCountry">País</label>
            <input type="text" id="modalEditUserCountry" name="country" class="form-control" value="{{ $client->country }}" />
          </div>
          <div class="col-12">
            <label class="form-label" for="modalEditUserPriceList">Lista de Precios</label>
            <select id="modalEditUserPriceList" name="price_list_id" class="form-control">
                @if($client->priceLists->count() == 0)
                    <option value="" disabled selected>No hay listas de precios disponibles</option>
                @else 
                    <option value="" disabled selected>Seleccione una lista de precios</option>
                @endif
                @foreach($priceLists as $priceList)
                    <option value="{{ $priceList->id }}" {{ $client->priceLists->contains($priceList->id) ? 'selected' : '' }}>
                        {{ $priceList->name }}
                    </option>
                @endforeach
            </select>
          </div>
          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary me-sm-3 me-1">Guardar cambios</button>
            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ Modal -->

@endsection
