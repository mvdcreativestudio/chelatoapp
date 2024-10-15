@extends('layouts/layoutMaster')

@section('title', 'Elaboraciones')

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
  'resources/assets/vendor/libs/select2/select2.js'
])
@endsection

@section('page-script')
<script type="text/javascript">
    window.productions = @json($productions);
    window.productionActivateTemplate = "{{ route('productions.activate', ':id') }}";
    window.productionDeactivateTemplate = "{{ route('productions.deactivate', ':id') }}";
    window.csrfToken = "{{ csrf_token() }}";
</script>
@vite(['resources/assets/js/app-productions-list.js'])
@endsection

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Producción /</span> Elaboraciones
</h4>

<div class="card mb-4">
  <div class="card-widget-separator-wrapper">
    <div class="card-body card-widget-separator">
      <div class="row gy-4 gy-sm-1">
        <div class="col-sm-6 col-lg-4">
          <div class="d-flex justify-content-between align-items-start card-widget-1 border-end pb-3 pb-sm-0">
            <div>
              <h6 class="mb-2">Total de Producciones</h6>
              <h4 class="mb-2">{{ $productions->count() }}</h4>
              <p class="mb-0"><span class="text-muted me-2">Total</span></p>
            </div>
            <div class="avatar me-sm-4">
              <span class="avatar-initial rounded bg-label-secondary">
                <i class="bx bx-layer bx-sm"></i>
              </span>
            </div>
          </div>
          <hr class="d-none d-sm-block d-lg-none me-4">
        </div>
        <div class="col-sm-6 col-lg-4">
          <div class="d-flex justify-content-between align-items-start card-widget-2 border-end pb-3 pb-sm-0">
            <div>
              <h6 class="mb-2">Producciones Recientes</h6>
              <h4 class="mb-2">{{ $productions->where('created_at', '>=', now()->subMonth())->count() }}</h4>
              <p class="mb-0"><span class="text-muted me-2">Último mes</span></p>
            </div>
            <div class="avatar me-lg-4">
              <span class="avatar-initial rounded bg-label-secondary">
                <i class="bx bx-list-ol bx-sm"></i>
              </span>
            </div>
          </div>
          <hr class="d-none d-sm-block d-lg-none">
        </div>
        <div class="col-sm-12 col-lg-4">
          <div class="d-flex justify-content-between align-items-start pb-3 pb-sm-0 card-widget-3">
            <div>
              <h6 class="mb-2">Producción del día</h6>
              <h4 class="mb-2">{{ $productions->where('created_at', '>=', now()->startOfDay())->count() }}</h4>
              <p class="mb-0 text-muted">Hoy</p>
            </div>
            <div class="avatar me-sm-4">
              <span class="avatar-initial rounded bg-label-secondary">
                <i class="bx bx-bar-chart-alt bx-sm"></i>
              </span>
            </div>
          </div>
          <hr class="d-none d-sm-block d-lg-none">
        </div>
      </div>
    </div>
  </div>
</div>

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

<div class="card">
  <div class="card-header">
    <h5 class="card-title">Elaboraciones</h5>
    <div class="d-flex">
        <p class="text-muted small">
          <a href="" class="toggle-switches" data-bs-toggle="collapse" data-bs-target="#columnSwitches" aria-expanded="false" aria-controls="columnSwitches">Ver / Ocultar columnas de la tabla</a>
        </p>
      </div>
      <div class="collapse" id="columnSwitches">
      <div class="mt-0 d-flex flex-wrap">
        <div class="mx-0">
          <label class="switch switch-square">
            <input type="checkbox" class="toggle-column switch-input" data-column="0" checked>
            <span class="switch-toggle-slider">
              <span class="switch-on"><i class="bx bx-check"></i></span>
              <span class="switch-off"><i class="bx bx-x"></i></span>
            </span>
            <span class="switch-label">ID</span>
          </label>
        </div>
        <div class="mx-3">
          <label class="switch switch-square">
            <input type="checkbox" class="toggle-column switch-input" data-column="1" checked>
            <span class="switch-toggle-slider">
              <span class="switch-on"><i class="bx bx-check"></i></span>
              <span class="switch-off"><i class="bx bx-x"></i></span>
            </span>
            <span class="switch-label">Producto</span>
          </label>
        </div>
        <div class="mx-3">
          <label class="switch switch-square">
            <input type="checkbox" class="toggle-column switch-input" data-column="2" checked>
            <span class="switch-toggle-slider">
              <span class="switch-on"><i class="bx bx-check"></i></span>
              <span class="switch-off"><i class="bx bx-x"></i></span>
            </span>
            <span class="switch-label">Sabor</span>
          </label>
        </div>
        <div class="mx-3">
          <label class="switch switch-square">
            <input type="checkbox" class="toggle-column switch-input" data-column="3" checked>
            <span class="switch-toggle-slider">
              <span class="switch-on"><i class="bx bx-check"></i></span>
              <span class="switch-off"><i class="bx bx-x"></i></span>
            </span>
            <span class="switch-label">Cantidad</span>
          </label>
        </div>
        <div class="mx-3">
          <label class="switch switch-square">
            <input type="checkbox" class="toggle-column switch-input" data-column="4" checked>
            <span class="switch-toggle-slider">
              <span class="switch-on"><i class="bx bx-check"></i></span>
              <span class="switch-off"><i class="bx bx-x"></i></span>
            </span>
            <span class="switch-label">Fecha de elaboración</span>
          </label>
        </div>
        <div class="mx-3">
          <label class="switch switch-square">
            <input type="checkbox" class="toggle-column switch-input" data-column="5" checked>
            <span class="switch-toggle-slider">
              <span class="switch-on"><i class="bx bx-check"></i></span>
              <span class="switch-off"><i class="bx bx-x"></i></span>
            </span>
            <span class="switch-label">Estado</span>
          </label>
        </div>
        <div class="mx-3">
          <label class="switch switch-square">
            <input type="checkbox" class="toggle-column switch-input" data-column="6" checked>
            <span class="switch-toggle-slider">
              <span class="switch-on"><i class="bx bx-check"></i></span>
              <span class="switch-off"><i class="bx bx-x"></i></span>
            </span>
            <span class="switch-label">Acciones</span>
          </label>
        </div>
</div>
</div>
  </div>
  <div class="card-datatable table-responsive">
    <table class="table datatables-productions border-top">
      <thead>
        <tr>
          <th>ID</th>
          <th>Producto</th>
          <th>Sabor</th>
          <th>Cantidad</th>
          <th>Fecha de Elaboración</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {
    var dt_productions_table = $('.datatables-productions');

    var productionActivate = window.productionActivateTemplate;
    var productionDeactivate = window.productionDeactivateTemplate;
    var productions = window.productions;

    var columns = [
        { data: 'id' },
        { data: 'product.name', defaultContent: 'N/A' },
        { data: 'flavor.name', defaultContent: 'N/A' },
        { data: 'quantity' },
        { data: 'created_at' },
        { data: 'status' }
    ];

    var columnsDefs = [
        {
            targets: 0,
            searchable: false,
            orderable: true,
            render: function (data, type, row) {
                return data;
            }
        },
        {
            targets: 1,
            searchable: true,
            responsivePriority: 1,
            orderable: true,
            render: function (data, type, row, meta) {
                return data ? data : 'N/A';
            }
        },
        {
            targets: 2,
            searchable: true,
            responsivePriority: 2,
            orderable: true,
            render: function (data, type, row, meta) {
                return data ? data : 'N/A';
            }
        },
        {
            targets: 3,
            searchable: true,
            responsivePriority: 3,
            orderable: true,
            render: function (data, type, row, meta) {
                return data;
            }
        },
        {
            targets: 4,
            searchable: true,
            responsivePriority: 4,
            orderable: true,
            render: function (data, type, row, meta) {
                if (type === 'display' || type === 'filter') {
                    var date = new Date(data);
                    var options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
                    return date.toLocaleDateString('es-ES', options);
                }
                return data;
            }
        },
        {
            targets: 5,
            searchable: true,
            responsivePriority: 5,
            orderable: true,
            render: function (data, type, row, meta) {
                var badgeClass = data === 'active' ? 'bg-success' : 'bg-danger';
                var badgeText = data === 'active' ? 'Activa' : 'Inactiva';
                return '<span class="badge ' + badgeClass + '">' + badgeText + '</span>';
            }
        },
        {
            targets: 6,
            searchable: false,
            responsivePriority: 6,
            orderable: false,
            render: function (data, type, row, meta) {
                var actionUrl = row.status === 'active' ? productionDeactivate.replace(':id', row.id) : productionActivate.replace(':id', row.id);
                var actionText = row.status === 'active' ? 'Desactivar' : 'Activar';
                return `
                    <div class="dropdown">
                        <button class="btn btn-icon btn-icon-only" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-horizontal-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <form class="status-form-${row.id}" action="${actionUrl}" method="POST">
                                <input type="hidden" name="_token" value="${window.csrfToken}">
                                <input type="hidden" name="production_id" value="${row.id}">
                                <div class="dropdown-item text-${row.status === 'active' ? 'danger' : 'success'} status-button" style="cursor: pointer;">
                                    <i class="bx bx-${row.status === 'active' ? 'trash' : 'check'}"></i> ${actionText}
                                </div>
                            </form>
                        </div>
                    </div>
                `;
            }
        }
    ];

    if (dt_productions_table.length) {
        var table = dt_productions_table.DataTable({
            data: productions,
            columns: columns,
            columnDefs: columnsDefs,
            language: {
                searchPlaceholder: 'Buscar...',
                sLengthMenu: '_MENU_',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ Elaboraciones',
                paginate: {
                    first: 'Primero',
                    last: 'Último',
                    next: '<span class="mx-2">Siguiente</span>',
                    previous: '<span class="mx-2">Anterior</span>'
                },
                aria: {
                    sortAscending: ': activar para ordenar la columna ascendente',
                    sortDescending: ': activar para ordenar la columna descendente'
                },
                emptyTable: 'No hay datos disponibles en la tabla',
                zeroRecords: 'No se encontraron coincidencias',
                lengthMenu: '_MENU_',
                loadingRecords: 'Cargando...',
                processing: 'Procesando...',
                search: '',
                infoFiltered: '(filtrado de un total de _MAX_ Elaboraciones)',
                infoEmpty: 'Mostrando 0 a 0 de 0 Elaboraciones'
            },
            dom:
                '<"card-header d-flex border-top rounded-0 flex-wrap py-md-0"' +
                '<"me-5 ms-n2 pe-5"f>' +
                '<"d-flex justify-content-start justify-content-md-end align-items-baseline"<"dt-action-buttons d-flex align-items-start align-items-md-center justify-content-sm-center mb-3 mb-sm-0"lB>>' +
                '>t' +
                '<"row mx-2"' +
                '<"col-sm-12 col-md-6"i>' +
                '<"col-sm-12 col-md-6"p>' +
                '>',
            buttons: [
                {
                    text: '<i class="bx bx-plus me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Crear</span>',
                    className: 'btn btn-primary ml-3',
                    action: function () {
                        window.location.href = 'productions/create';
                    }
                }
            ]
        });
        $('.toggle-column').on('change', function() {
      var column = table.column($(this).attr('data-column'));
      column.visible(!column.visible());
  });
    }

    $('.dataTables_length').addClass('mt-0 mt-md-3 me-3');
    $('.dt-buttons > .btn-group > button').removeClass('btn-secondary');
    $('.dt-buttons').addClass('d-flex flex-wrap');

    $('div.dataTables_filter input').addClass('form-control');
    $('div.dataTables_length select').addClass('form-select');

    $('.datatables-productions tbody').on('click', '.status-button', function () {
        var form = $(this).closest('form');

        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción cambiará el estado de la elaboración.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, continuar!',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
