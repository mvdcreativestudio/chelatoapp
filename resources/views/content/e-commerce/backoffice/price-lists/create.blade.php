@extends('layouts/layoutMaster')

@section('title', 'Nueva lista de precios')

@section('content')
<div class="d-flex align-items-center justify-content-between bg-white p-4 mb-3 rounded shadow-lg sticky-top border-bottom border-light">
  <h4 class="mb-0 page-title"><i class="bx bx-list-plus me-2"></i> Nueva lista de precios</h4>
  <a href="{{ route('price-lists.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bx bx-arrow-back"></i> Volver</a>
</div>

@if($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
    </ul>
  </div>
@endif

<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ route('price-lists.store') }}">
      @csrf
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Nombre <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
        </div>
        <div class="col-md-3">
          <label class="form-label">Tienda <span class="text-danger">*</span></label>
          <select name="store_id" class="form-select" required>
            <option value="">Seleccionar...</option>
            @foreach($stores as $store)
              <option value="{{ $store->id }}" @selected(old('store_id') == $store->id)>{{ $store->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Moneda <span class="text-danger">*</span></label>
          <select name="currency" class="form-select" required>
            <option value="Peso" @selected(old('currency', 'Peso') == 'Peso')>Peso</option>
            <option value="Dólar" @selected(old('currency') == 'Dólar')>Dólar</option>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Descripción</label>
          <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
        </div>
      </div>

      <div class="mt-4 text-end">
        <button type="submit" class="btn btn-success"><i class="bx bx-save"></i> Crear lista</button>
      </div>
    </form>
  </div>
</div>
@endsection
