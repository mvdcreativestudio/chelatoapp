@extends('layouts/layoutMaster')

@section('title', 'Envíos')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/select2/select2.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/jquery-repeater/jquery-repeater.js'
])
@endsection

@section('page-script')
<script type="text/javascript">
    window.baseUrl = "{{ url('') }}/";
    window.csrfToken = "{{ csrf_token() }}";
    var noteDeliveries = @json($noteDeliveries);
</script>
@vite(['resources/assets/js/app-note-deliveries-list.js'])
@endsection

@section('content')
<!-- Alerts -->
@if (session('success'))
<div class="alert alert-success" role="alert">{{ session('success') }}</div>
@endif

@if (session('error'))
<div class="alert alert-danger" role="alert">{{ session('error') }}</div>
@endif

@if ($errors->any())
@foreach ($errors->all() as $error)
<div class="alert alert-danger">{{ $error }}</div>
@endforeach
@endif

<div class="d-flex flex-column flex-md-row align-items-center justify-content-between bg-white p-4 mb-3 rounded shadow-lg sticky-top border-bottom border-light">
    <div class="d-flex flex-column justify-content-center mb-3 mb-md-0">
        <h4 class="mb-0 page-title">
            <i class="bx bx-package me-2"></i> Notas de envíos
        </h4>
    </div>

    <div class="d-flex align-items-center justify-content-center flex-grow-1 gap-3 mb-3 mb-md-0 mx-md-4">
        <div class="input-group w-100 w-md-75 shadow-sm">
            <span class="input-group-text bg-white">
                <i class="bx bx-search"></i>
            </span>
            <input type="text" id="searchNoteDelivery" class="form-control" placeholder="Buscar nota de entrega..." aria-label="Buscar nota de entrega">
        </div>
    </div>

    <a href="{{ route('note-deliveries.export') }}" class="btn btn-primary btn-sm shadow-sm d-flex align-items-center gap-1 w-10" color="white">
        <i class="bx bx-download"></i>
        Exportar Excel
    </a>
</div>
<div class="row note-delivery-list-container">
</div>

@endsection