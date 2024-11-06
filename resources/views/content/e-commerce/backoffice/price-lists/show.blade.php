@extends('layouts/layoutMaster')

@section('title', 'Ver Lista de Precios')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
])
@endsection

@section('page-script')
<script>
    // Define la URL de productos para el archivo JS
    const productsUrl = "{{ route('price-lists.products', [$priceList->store_id, $priceList->id]) }}";
</script>
@vite([
  'resources/assets/js/app-show-price-lists.js'
])
@endsection

@section('content')
<div>
    <div class="d-flex align-items-center justify-content-between bg-white p-4 mb-3 rounded shadow-lg sticky-top border-bottom border-light">
        <div class="d-flex flex-column justify-content-center">
            <h4 class="mb-0 page-title">
                <i class="bx bx-list-ul me-2"></i> {{ $priceList->name }}
            </h4>
        </div>
        <div class="d-flex">
            @can('access_edit-price-lists')
                <form action="{{ route('price-lists.edit', $priceList->id) }}" method="GET" style="display: inline;">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="bx bx-edit-alt"></i>
                    </button>
                </form>
            @endcan
            @can('access_delete-price-lists')
                <form id="delete-form" action="{{ route('price-lists.destroy', $priceList->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-outline-danger m-0" onclick="confirmDelete()">
                        <i class="bx bx-trash"></i>
                    </button>
                </form>
            @endcan
        
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">

            <div class="mb-3">
                <label for="name" class="form-label">Nombre</label>
                <p class="form-control-plaintext">{{ $priceList->name }}</p>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Descripción</label>
                <p class="form-control-plaintext">{{ $priceList->description ?: 'Sin descripción' }}</p>
            </div>
            <div class="mb-3">
                <label for="store" class="form-label">Tienda</label>
                <p class="form-control-plaintext">{{ $priceList->store->name }}</p>
            </div>
        </div>
    </div>
    <div class="card mt-4">
        <div class="card-body">
            <!-- Sección para mostrar los productos y sus precios -->
            <div class="mb-3">
                <label for="products" class="form-label">Productos</label>
                <table class="table table-striped" id="productsTable">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio</th>
                        </tr>
                    </thead>
                    <tbody id="productsList">
                        <!-- Aquí se cargarán los productos con sus precios -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
