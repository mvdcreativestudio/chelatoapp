@extends('layouts/layoutMaster')

@section('content')
  <div class="container">
    <h1>Crear Usuario</h1>
    <form action="{{ route('users.store') }}" method="POST">
      @csrf
      <div class="form-group">
        <label for="name">Nombre</label>
        <input type="text" name="name" class="form-control" required>
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <div class="form-group">
        <label for="password">Contraseña</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <div class="form-group">
        <label for="password_confirmation">Confirmar Contraseña</label>
        <input type="password" name="password_confirmation" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary">Crear Usuario</button>
    </form>
  </div>
@endsection
