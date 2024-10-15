@extends('layouts/layoutMaster')

@section('title', 'Cuentas Contables')

@section('vendor-style')
@vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss'
])
@endsection

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/es.min.js"></script>

@section('vendor-script')
@vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'
])
<script>
    window.baseUrl = "{{ url('/') }}";
</script>
@endsection

@section('page-script')
@vite([
    'resources/assets/js/entries/entries-accounts/app-entries-accounts-list.js',
    'resources/assets/js/entries/entries-accounts/app-entries-accounts-add.js',
    'resources/assets/js/entries/entries-accounts/app-entries-accounts-edit.js',
    'resources/assets/js/entries/entries-accounts/app-entries-accounts-delete.js',
])
@endsection

@section('content')
<h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Contabilidad /</span> Cuentas Contables
</h4>

<!-- Cards Section -->
<div class="card mb-4">
    <div class="card-body card-widget-separator">
        <div class="row gy-4 gy-sm-1">
            <div class="col-sm-6 col-lg-3">
                <div class="d-flex justify-content-between align-items-start card-widget-2 border-end pb-3 pb-sm-0">
                    <div>
                        <h6 class="mb-2">Total Cuentas Contables</h6>
                        <h4 class="mb-2">{{ $entryAccounts->count() }}</h4>
                    </div>
                    <div class="avatar me-lg-4">
                        <span class="avatar-initial rounded bg-label-secondary">
                            <i class="bx bx-list-ul bx-sm"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Entry Accounts List Table -->
<div class="card">
    <div class="card pb-3">
        <h5 class="card-header pb-0">
            Cuentas Contables
            <button type="button" class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#addEntryAccountModal">
                Agregar Cuenta Contable
            </button>
            <div class="d-flex">
                <p class="text-muted small">
                    <a href="" class="toggle-switches" data-bs-toggle="collapse" data-bs-target="#columnSwitches"
                       aria-expanded="false" aria-controls="columnSwitches">Ver / Ocultar columnas de la tabla</a>
                </p>
            </div>
            <div class="collapse" id="columnSwitches">
                <div class="mt-0 d-flex flex-wrap">
                    <!-- Selectores de columnas -->
                    <div class="mx-3">
                        <label class="switch switch-square">
                            <input type="checkbox" class="toggle-column switch-input" data-column="1" checked>
                            <span class="switch-toggle-slider">
                                <span class="switch-on"><i class="bx bx-check"></i></span>
                                <span class="switch-off"><i class="bx bx-x"></i></span>
                            </span>
                            <span class="switch-label">ID</span>
                        </label>
                    </div>
                    <div class="mx-3">
                        <label class="switch switch-square">
                            <input type="checkbox" class="toggle-column switch-input" data-column="2" checked>
                            <span class="switch-toggle-slider">
                                <span class="switch-on"><i class="bx bx-check"></i></span>
                                <span class="switch-off"><i class="bx bx-x"></i></span>
                            </span>
                            <span class="switch-label">Código</span>
                        </label>
                    </div>
                    <div class="mx-3">
                        <label class="switch switch-square">
                            <input type="checkbox" class="toggle-column switch-input" data-column="3" checked>
                            <span class="switch-toggle-slider">
                                <span class="switch-on"><i class="bx bx-check"></i></span>
                                <span class="switch-off"><i class="bx bx-x"></i></span>
                            </span>
                            <span class="switch-label">Nombre</span>
                        </label>
                    </div>
                    <div class="mx-3">
                        <label class="switch switch-square">
                            <input type="checkbox" class="toggle-column switch-input" data-column="4" checked>
                            <span class="switch-toggle-slider">
                                <span class="switch-on"><i class="bx bx-check"></i></span>
                                <span class="switch-off"><i class="bx bx-x"></i></span>
                            </span>
                            <span class="switch-label">Descripción</span>
                        </label>
                    </div>
                    <div class="mx-3">
                        <label class="switch switch-square">
                            <input type="checkbox" class="toggle-column switch-input" data-column="5" checked>
                            <span class="switch-toggle-slider">
                                <span class="switch-on"><i class="bx bx-check"></i></span>
                                <span class="switch-off"><i class="bx bx-x"></i></span>
                            </span>
                            <span class="switch-label">Acciones</span>
                        </label>
                    </div>
                </div>
                <div class="dropdown d-inline float-end mx-2">
                    <button class="btn btn-primary dropdown-toggle d-none" type="button" id="dropdownMenuButton"
                            data-bs-toggle="dropdown" aria-expanded="false">
                        Acciones
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <li><a class="dropdown-item" href="#" id="deleteSelected">Eliminar seleccionados</a></li>
                    </ul>
                </div>
            </div>
        </h5>
    </div>

    <div class="card-datatable table-responsive pt-0">
        @if($entryAccounts->count() > 0)
        <table class="table datatables-entry-accounts">
            <thead>
                <tr>
                    <th>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="checkAll">
                        </div>
                    </th>
                    <th>ID</th>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
            </tbody>
        </table>
        @else
        <div class="text-center py-5">
            <h4>No hay cuentas contables</h4>
            <p class="text-muted">Agrega una nueva cuenta contable para comenzar</p>
        </div>
        @endif
    </div>
</div>

@include('content.accounting.entries.entry-accounts.add-entry-account')
@include('content.accounting.entries.entry-accounts.edit-entry-account')

@endsection
