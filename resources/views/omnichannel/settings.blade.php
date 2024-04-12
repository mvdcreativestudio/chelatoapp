@extends('layouts.layoutMaster')

@section('title', 'Configuraciones de Omnicanalidad')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('content')
<div class="container-fluid py-4">
    <h4 class="mb-4"><span class="text-muted fw-light">Omnicanalidad /</span> Configuración</h4>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
    @foreach ($errors->all() as $error)
      <div class="alert alert-danger">
        {{ $error }}
      </div>
    @endforeach
    @endif

    <!-- Actualización de Configuraciones -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">ID del Negocio y Token de Administrador</div>
                <div class="card-body">
                    <!-- ID del Negocio -->
                    <form action="{{ route('omnichannel.update.meta.business.id') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="metaBusinessId" class="form-label">ID del Negocio (business_id)</label>
                            <input type="text" class="form-control" name="metaBusinessId" id="metaBusinessId" value="{{ $metaBusinessId ?? '' }}" placeholder="Ingrese business_id proporcionado por Meta" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Actualizar ID del Negocio</button>
                    </form>

                    <!-- Token de Administrador -->
                    <form action="{{ route('omnichannel.update.admin.token') }}" method="POST" class="mt-4">
                        @csrf
                        <div class="mb-3">
                            <label for="metaAdminToken" class="form-label">Token de Administrador</label>
                            <input type="text" class="form-control" name="metaAdminToken" id="metaAdminToken" value="{{ $metaAdminToken ?? '' }}" placeholder="Ingrese el token de administrador" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Actualizar Token</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Listado de Cuentas de WhatsApp Business -->
    <div class="row">
      <div class="col-12">
          <div class="card">
              <div class="card-header">Cuentas de WhatsApp Business</div>
              <div class="card-body">
                  <div class="table-responsive">
                      <table class="table">
                          <thead>
                              <tr>
                                  <th>Nombre</th>
                                  <th>ID</th>
                                  <th>Números de Teléfono</th>
                                  <th>Acciones</th>
                              </tr>
                          </thead>
                          <tbody>
                              @foreach($whatsAppBusinessData as $account)
                                  <tr>
                                      <td>{{ $account['name'] }}</td>
                                      <td>{{ $account['id'] }}</td>
                                      <td>
                                          <ul>
                                              @foreach($account['phone_numbers'] as $phone)
                                                  <li>{{ $phone['display_phone_number'] }}</li>
                                              @endforeach
                                          </ul>
                                      </td>
                                      <td>
                                        @foreach($account['phone_numbers'] as $phone)
                                          @if(isset($phone['store']))
                                              <form action="{{ route('omnichannel.disassociate', ['phone_id' => $phone['id']]) }}" method="POST" style="display: inline;">
                                                  @csrf
                                                  <button type="submit" class="btn btn-primary">
                                                      Desasociar de {{ $phone['store']->name }}
                                                  </button>
                                              </form>
                                          @else
                                              <form action="{{ route('omnichannel.associate.phone') }}" method="POST" style="display: inline-flex; align-items: center;">
                                                  @csrf
                                                  <input type="hidden" name="phone_id" value="{{ $phone['id'] }}">
                                                  <input type="hidden" name="phone_number" value="{{ $phone['display_phone_number'] }}">
                                                  <select name="store_id" class="form-control mr-2 select2" required style="border-radius: 5px 0px 0px 5px;">
                                                      <option value="">Seleccionar Tienda</option>
                                                      @foreach($storesNotAssociated as $store)
                                                          <option value="{{ $store->id }}">{{ $store->name }}</option>
                                                      @endforeach
                                                  </select>
                                                  <button type="submit" class="btn btn-primary" style="border-radius: 0px 5px 5px 0px;">
                                                      <i class='bx bx-right-arrow-alt'></i>
                                                  </button>
                                              </form>
                                          @endif
                                        @endforeach
                                      </td>
                                  </tr>
                              @endforeach
                          </tbody>
                      </table>
                  </div>
              </div>
          </div>
      </div>
  </div>
</div>
@endsection
