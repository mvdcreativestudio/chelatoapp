@extends('layouts/layoutMaster')

@section('title', 'Editar Lista de Precios')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
          <div class="card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    {{ $priceList->name }}
                </h4>
                <a href="#" id="backButton" class="btn btn-sm btn-primary">
                    <i class="bx bx-arrow-back me-1"></i> Volver
                </a>                  
              </div>
            </div>
          </div>
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

    <div class="card">
        <div class="card-body">
            <div>
                <div class="mb-3">
                    <label for="name" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ $priceList->name }}" required>
                </div>

                <div>
                    <label for="description" class="form-label">Descripción</label>
                    <textarea class="form-control" id="description" name="description">{{ $priceList->description }}</textarea>
                </div>
            </div>
        </div>
    </div>
    <div class="card mt-4">
        <div class="card-body">

            <form id="editPriceListForm" action="{{ route('price-lists.update', $priceList->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Sección para cargar los productos mediante AJAX -->
                <div class="mb-3">
                <label for="products" class="form-label mb-3">Listado de productos</label>
                <table class="table table-striped" id="productsTable">
                    <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio</th>
                    </tr>
                    </thead>
                    <tbody id="productsList">
                    <!-- Aquí se cargarán los productos con AJAX -->
                    </tbody>
                </table>
                </div>
                <!-- Botón "Guardar Cambios" fijo en la parte inferior derecha -->
                <div class="fixed-bottom d-flex justify-content-end p-3 mb-4">
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
  // Cargar productos con AJAX al cargar la página
  $(document).ready(function() {
    $.ajax({
        url: "{{ route('price-lists.products', [$priceList->store_id, $priceList->id]) }}",
        method: 'GET',
        success: function(response) {
            var productsList = $('#productsList');
            productsList.empty();
            response.products.forEach(function(product) {
                var priceValue = product.price ? product.price : '';  // Mostrar el precio si existe, de lo contrario dejar vacío
                productsList.append(`
                    <tr>
                        <td>${product.name}</td>
                        <td>
                            <input type="number" name="prices[${product.id}]" class="form-control" value="${priceValue ? parseFloat(priceValue).toFixed(2) : ''}" placeholder="Agregar precio" step="0.01">
                        </td>
                    </tr>
                `);

            });
        },
        error: function(xhr) {
            console.log('Error al cargar productos:', xhr);
        }
    });
});
</script>

<script>
    document.getElementById('backButton').addEventListener('click', function (event) {
      event.preventDefault();
      
      // Obtener la URL de la página anterior
      var previousPage = document.referrer;
  
      // Verificar si la página anterior es la misma que la actual
      if (previousPage && previousPage !== window.location.href) {
        window.location.href = previousPage; // Redirigir a la página anterior si es diferente
      } else {
        window.location.href = "{{ route('price-lists.index') }}"; // Redirigir a una página específica si es la misma
      }
    });
  </script>
  
@endsection
