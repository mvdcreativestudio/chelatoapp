@extends('layouts/layoutMaster')

@section('title', 'Editar Materia Prima')

@section('page-script')
@vite([
  'resources/assets/js/app-raw-material-edit.js'
])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Materias Primas /</span><span> Editar Materia Prima</span>
</h4>

<div class="app-ecommerce">

  <!-- Formulario para editar materia prima -->
  <form action="{{ route('raw-materials.update', $rawMaterial->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row">

      <!-- Columna de información de la materia prima -->
      <div class="col-12">
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0">Información de la Materia Prima</h5>
          </div>
          <div class="card-body">
            <!-- Nombre -->
            <div class="mb-3">
              <label class="form-label" for="raw-material-name">Nombre</label>
              <input type="text" class="form-control" id="raw-material-name" name="name" required placeholder="Nombre de la materia prima" value="{{ $rawMaterial->name }}">
            </div>

            <!-- Descripción -->
            <div class="mb-3">
              <label class="form-label">Descripción <span class="text-muted">(Opcional)</span></label>
              <textarea id="raw-material-description" class="form-control" name="description">{{ $rawMaterial->description }}</textarea>
            </div>

            <!-- Unidad de Medida -->
            <div class="mb-3">
              <label class="form-label" for="unit_of_measure">Unidad de Medida</label>
              <select class="form-select" id="unit_of_measure" name="unit_of_measure" required>
                <option value="">Seleccione una unidad</option>
                <option value="KG" @if($rawMaterial->unit_of_measure == 'KG') selected @endif>Kilogramos (KG)</option>
                <option value="Litros" @if($rawMaterial->unit_of_measure == 'Litros') selected @endif>Litros (L)</option>
                <option value="Unidades" @if($rawMaterial->unit_of_measure == 'Unidades') selected @endif>Unidades (U)</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label" for="raw-material-stock">Stock</label>
              <input type="number" class="form-control" id="raw-material-stock" name="stock" value="{{ $rawMaterial->stock ?? '' }}" placeholder="Cantidad en stock">
            </div>
            @if ($errors->any())
              @foreach ($errors->all() as $error)
                <div class="alert alert-danger">
                  {{ $error }}
                </div>
              @endforeach
            @endif
          </div>
        </div>

        <!-- Botones -->
        <div class="d-flex justify-content-end">
          <button type="submit" class="btn btn-primary">Actualizar Materia Prima</button>
        </div>
      </div>
    </div>
  </form>
</div>
@endsection
