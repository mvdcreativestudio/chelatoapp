@extends('layouts/layoutMaster')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/select2/select2.scss',
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/select2/select2.js',
])
@endsection

@section('content')
<div class="container-fluid mt-5">
    <h1 class="text-center mb-4">Editar Productos en Masa</h1>

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

    <form action="{{ route('products.updateBulk') }}" method="POST">
        @csrf
        @foreach($products as $product)
            <div class="card mb-3">
                <div class="card-body d-flex flex-wrap">
                    <div class="form-group mb-3 me-3 flex-fill">
                        <label for="name_{{ $product->id }}" class="form-label">Nombre</label>
                        <input type="text" name="products[{{ $product->id }}][name]" value="{{ $product->name }}" class="form-control" id="name_{{ $product->id }}">
                    </div>

                    <div class="form-group mb-3 me-3 flex-fill">
                        <label for="old_price_{{ $product->id }}" class="form-label">Precio Normal</label>
                        <input type="text" name="products[{{ $product->id }}][old_price]" value="{{ $product->old_price }}" class="form-control" id="old_price_{{ $product->id }}">
                    </div>

                    <div class="form-group mb-3 me-3 flex-fill">
                        <label for="price_{{ $product->id }}" class="form-label">Precio Rebajado</label>
                        <input type="text" name="products[{{ $product->id }}][price]" value="{{ $product->price }}" class="form-control" id="price_{{ $product->id }}">
                    </div>

                    <div class="form-group mb-3 flex-fill">
                        <label for="categories_{{ $product->id }}" class="form-label">Categoría</label>
                        <select name="products[{{ $product->id }}][categories][]" class="form-control select2" id="categories_{{ $product->id }}" multiple>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ in_array($category->id, $product->categories->pluck('id')->toArray()) ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <input type="hidden" name="products[{{ $product->id }}][id]" value="{{ $product->id }}">
                </div>
            </div>
        @endforeach

        <div class="col-12 position-fixed bottom-0 mb-3 end-0 me-3">
            <div class="d-flex justify-content-end align-items-center">
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </div>
    </form>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    $('.select2').select2({
      placeholder: 'Seleccione categorías',
      allowClear: true
    });
  });
</script>
@endsection
