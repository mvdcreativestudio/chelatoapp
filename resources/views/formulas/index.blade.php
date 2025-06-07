@extends('layouts/layoutMaster')

@section('title', 'Listado de Fórmulas')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/assets/vendor/libs/select2/select2.js'
])
@endsection

@section('page-script')
<script type="text/javascript">
  window.csrfToken = "{{ csrf_token() }}";
  var formulas = @json($formulas);
</script>
@vite(['resources/assets/js/app-formula-list.js'])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Fórmulas /</span> Listado
</h4>

@if (session('success'))
<div class="alert alert-success mt-3 mb-3">
  {{ session('success') }}
</div>
@endif

@if (session('error'))
<div class="alert alert-danger mt-3 mb-3">
  {{ session('error') }}
</div>
@endif

@if ($errors->any())
@foreach ($errors->all() as $error)
<div class="alert alert-danger">
  {{ $error }}
</div>
@endforeach
@endif

<!-- Card mostrando la cantidad total de fórmulas -->
<div class="card mb-4">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-start">
      <div>
        <h6 class="mb-2">Cantidad total de fórmulas</h6>
        <h4 class="mb-2">{{ $formulas->count() }}</h4>
        <p class="mb-0 text-muted">Total de fórmulas registradas</p>
      </div>
      <div class="avatar">
        <span class="avatar-initial rounded bg-label-primary">
          <i class="fas fa-flask fa-sm"></i>
        </span>
      </div>
    </div>
  </div>
</div>

<!-- Tabla de fórmulas -->
<div class="card">
  <div class="card-header">
    <div class="d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0">Listado de Formulas</h5>
      <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#addFormulaOffCanvas">Agregar Formula</button>
    </div>
    <div class="d-flex">
      <p class="text-muted small">
        <a href="#" class="toggle-switches" data-bs-toggle="collapse" data-bs-target="#columnSwitches" aria-expanded="false" aria-controls="columnSwitches">Ver / Ocultar columnas de la tabla</a>
      </p>
    </div>
  </div>
  <div class="collapse" id="columnSwitches">
    <div class="mt-0 d-flex flex-wrap">
      @foreach (['ID', 'Nombre', 'Descripción', 'Producto final', 'Pasos', 'Unidad de medida', 'Cantidad', 'Acciones'] as $index => $label)
      <div class="mx-3">
        <label class="switch switch-square">
          <input type="checkbox" class="toggle-column switch-input" data-column="{{ $index }}" checked>
          <span class="switch-toggle-slider">
            <span class="switch-on"><i class="bx bx-check"></i></span>
            <span class="switch-off"><i class="bx bx-x"></i></span>
          </span>
          <span class="switch-label">{{ $label }}</span>
        </label>
      </div>
      @endforeach
    </div>
  </div>
  <div class="card-datatable table-responsive mx-4 my-3">
    <table class="table datatables-formulas border-top">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Descripción</th>
          <th>Producto Final</th>
          <th>Unidad de medida</th>
          <th>Cantidad</th>
          <th>Pasos</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        @foreach($formulas as $formula)
        <tr>
          <td>{{ $formula->id }}</td>
          <td>{{ $formula->name }}</td>
          <td>{{ $formula->description }}</td>
          <td>
            @php
            $finalProduct = $products->firstWhere('id', $formula->final_product_id);
            @endphp
            {{ $finalProduct ? $finalProduct->name : 'Producto no encontrado' }}
          </td>
          <td>{{ $formula->unit_of_measure }}</td>
          <td>{{ $formula->quantity }}</td>
          <td></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
<div class="offcanvas offcanvas-end" tabindex="-1" id="addFormulaOffCanvas" aria-labelledby="addFormulaOffCanvasLabel">
  <div class="offcanvas-header">
    <h5 id="addFormulaOffCanvasLabel">Agregar Nueva Fórmula</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <form id="addFormulaForm">
      @csrf
      <div class="mb-3">
        <label for="name" class="form-label">Nombre</label>
        <input type="text" class="form-control" id="name" name="name" required>
      </div>

      <div class="mb-3">
        <label for="final_product_id" class="form-label">Producto final</label>
        <select class="form-control select2" id="final_product_id" name="final_product_id" required>
          <option value="">Selecciona un producto</option>
          @foreach ($products as $product)
          <option value="{{ $product->id }}">{{ $product->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="mb-3">
        <label for="description" class="form-label">Descripción</label>
        <textarea class="form-control" id="description" name="description"></textarea>
      </div>

       <div class="mb-3">
        <label for="unit_of_measure" class="form-label">Unidad de Medida</label>
        <select class="form-control" id="unit_of_measure" name="unit_of_measure" required>
          <option value="L">Litro (L)</option>
          <option value="ml">Mililitro (ml)</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="quantity" class="form-label">Cantidad</label>
        <input type="number" step="0.01" class="form-control" id="quantity" name="quantity" required>
      </div>



      <button type="submit" class="btn btn-success">Guardar</button>
    </form>
  </div>
</div>

@endsection