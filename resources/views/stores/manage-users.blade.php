@extends('layouts/layoutMaster')

@section('title', 'Administrar Usuarios de la Empresa - ' . $store->name)

@section('vendor-style')
@vite(['resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('page-script')
<script>
$(document).ready(function() {
    $('.select2').select2();
});
</script>
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Empresas /</span> Administrar Usuarios
</h4>

<div class="card mb-4">
  <div class="card-body">
    <h5 class="card-title">Agregar usuarios a {{ $store->name }}:</h5>
    <form action="{{ route('stores.associateUser', $store) }}" method="POST">
      @csrf
      <div class="mb-3">
        <label for="user_id" class="form-label">Usuarios Disponibles</label>
        <select id="user_id" name="user_id" class="form-select select2">
          @if ($unassociatedUsers->isEmpty())
            <option value="" class="disabled">No hay usuarios disponibles</option>
          @endif
          @foreach($unassociatedUsers as $user)
            <option value="{{ $user->id }}">{{ $user->name }}</option>
          @endforeach
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Asociar Usuario</button>
    </form>
    @if (session('success'))
      <div class="alert alert-success mt-3">
        {{ session('success') }}
      </div>
    @endif
    @if ($errors->any())
      @foreach ($errors->all() as $error)
        <div class="alert alert-danger">
          {{ $error }}
        </div>
      @endforeach
    @endif
  </div>
</div>

<div class="card">
  <div class="card-body">
    <h5 class="card-title">Usuarios asociados a {{ $store->name }}: </h5>
    <table class="table">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Email</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        @foreach($associatedUsers as $user)
        <tr>
          <td>{{ $user->name }}</td>
          <td>{{ $user->email }}</td>
          <td>
            <form action="{{ route('stores.disassociateUser', ['store' => $store, 'user_id' => $user->id]) }}" method="POST">
              @csrf
              <button type="submit" class="btn btn-danger btn-sm">Desasociar</button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
