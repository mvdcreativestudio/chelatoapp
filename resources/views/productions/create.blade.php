@extends('layouts/layoutMaster')

@section('title', 'Crear Elaboración')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('page-script')
@vite(['resources/assets/js/app-productions-add.js'])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Elaboraciones /</span><span> Crear elaboración</span>
</h4>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<script>
    const products = @json($products);
    const flavors = @json($flavors);
</script>

<form action="{{ route('productions.store') }}" method="POST" id="productionForm">
    @csrf
    <div class="row">
        <div class="col-12 col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Detalles de la Elaboración</h5>
                </div>
                <div class="card-body">
                    <div id="elaborations-container">
                        <p id="instruction-text">Empieza a agregar productos o variaciones para continuar con la elaboración.</p>
                    </div>
                    <button type="button" class="btn btn-primary" id="add-elaboration">Agregar Producto o Sabor</button>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Acciones</h5>
                </div>
                <div class="card-body">
                    <button type="submit" class="btn btn-primary" style="margin-right: 10px;">Guardar Elaboración</button>
                    <a href="{{ route('productions.index') }}" class="btn btn-label-secondary">Cancelar</a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
