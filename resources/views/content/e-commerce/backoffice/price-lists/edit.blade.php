@extends('layouts/layoutMaster')

@section('title', 'Editar Lista de Precios')

@section('content')

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <a href="#" id="backButton" class="btn btn-sm btn-primary me-2">
                                <i class="bx bx-arrow-back me-1"></i> Volver
                            </a>
                            @can('access_delete-price-lists')
                            <form id="delete-form" action="{{ route('price-lists.destroy', $priceList->id) }}" method="POST" class="m-0">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmDelete()">
                                    <i class="bx bx-trash"></i> Eliminar
                                </button>
                            </form>
                            @endcan
                        </div>
                        <h4 class="mb-0">{{ $priceList->name }}</h4>
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

    <form id="editPriceListForm" action="{{ route('price-lists.update', $priceList->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-body">
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

        <div class="card mt-4">
            <div class="card-body">
                <div class="mb-3">
                    <label for="products" class="form-label mb-3">Listado de productos</label>
                    <table class="table table-striped" id="productsTable">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Moneda</th>
                                <th>Precio</th>
                            </tr>
                        </thead>
                        <tbody id="productsList">
                            <!-- Aquí se cargarán los productos con AJAX -->
                        </tbody>
                    </table>
                </div>

                <div class="fixed-bottom d-flex justify-content-end p-3 mb-4">
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection

@section('page-script')
<script>
    $(document).ready(function() {
        $.ajax({
            url: "{{ route('price-lists.products', [$priceList->store_id, $priceList->id]) }}",
            method: 'GET',
            success: function(response) {
                var productsList = $('#productsList');
                productsList.empty();
                response.products.forEach(function(product) {
                    var priceValue = product.price ? parseFloat(product.price).toFixed(2) : '';
                    var currencySymbol = product.currency === 'Dólar' ? 'USD' : 'UYU';

                    productsList.append(`
                        <tr>
                            <td>${product.name}</td>
                            <td>
                                <select class="form-select currency-selector" name="currencies[${product.id}]">
                                    <option value="UYU" ${product.currency === 'Peso' ? 'selected' : ''}>UYU</option>
                                    <option value="USD" ${product.currency === 'Dólar' ? 'selected' : ''}>USD</option>
                                </select>
                            </td>
                            <td>
                                <div class="input-group">
                                    <span class="input-group-text">${currencySymbol}</span>
                                    <input type="number" 
                                        name="prices[${product.id}]" 
                                        class="form-control price-input" 
                                        value="${priceValue}" 
                                        placeholder="Agregar precio" 
                                        step="0.01">
                                </div>
                            </td>
                        </tr>
                    `);
                });
            },
            error: function(xhr) {
                console.log('Error al cargar productos:', xhr);
            }
        });

        window.confirmDelete = function() {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Se perderá toda la información de esta lista de precios.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form').submit();
                }
            });
        };

        document.getElementById('backButton').addEventListener('click', function(event) {
            event.preventDefault();
            var previousPage = document.referrer;
            if (previousPage && previousPage !== window.location.href) {
                window.location.href = previousPage;
            } else {
                window.location.href = "{{ route('price-lists.index') }}";
            }
        });
    });
</script>
@endsection
