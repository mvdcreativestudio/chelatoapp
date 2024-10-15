@extends('layouts/layoutMaster')

@section('title', 'Asiento Contable')

<!-- Vendor Styles -->
@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
  'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
  'resources/assets/vendor/libs/flatpickr/flatpickr.scss'
])
@endsection


@section('page-script')
@vite([
    'resources/assets/js/entries/entries-details/app-entries-details-list.js',
    'resources/assets/js/entries/entries-details/app-entries-details-add.js',
    'resources/assets/js/entries/entries-details/app-entries-details-edit.js',
    'resources/assets/js/entries/entries-details/app-entries-details-delete.js',
])
@endsection

@section('content')

<div class="row">
  <div class="card">
    <div class="card-header">
      <h4>Asiento Contable <span class="text-muted">/ #{{ $entry->id }}</span></h4>
    </div>
    <div class="card-body">
      <div class="d-flex justify-content-between">
          <div class="mb-3">
            <label for="entryDate" class="form-label">Fecha</label>
            <input class="form-control" type="date" value="{{ $entry->entry_date }}" id="entryDate" disabled>
          </div>
          <div class="mb-3 col-4">
            <label for="entryType" class="form-label">Serie</label>
            <input class="form-control" type="text" value="{{ $entry->entryType->name }}" id="entryType" disabled>
          </div>
          <div class="mb-3 col-1">
            <label for="reference" class="form-label">Referencia</label>
            <input class="form-control" type="text" value="{{ $entry->reference }}" id="reference" disabled>
          </div>
          <div class="mb-3 col-2">
            <label for="template" class="form-label">Plantilla</label>
            <input class="form-control" type="text" value="{{ $entry->template ?? 'Sin Plantilla' }}" id="template" disabled>
          </div>
          <div class="mb-3 col-1">
            <label for="currency" class="form-label">Moneda</label>
            <input class="form-control" type="text" value="{{ $entry->currency->name }}" id="currency" disabled>
          </div>
          <div class="mb-3">
            <label for="exchangeRate" class="form-label">Tipo de Cambio</label>
            <input class="form-control" type="text" value="{{ $entry->exchange_rate ?? 'N/A' }}" id="exchangeRate" disabled>
          </div>
      </div>
    </div>

    <div class="card-datatable table-responsive">
      <table class="dt-responsive table border-top datatables-entry-details">
        <thead class="text-center table-dark">
          <tr>
            <th class="font-white">Cuenta contable</th>
            <th class="font-white">IVA</th>
            <th class="font-white">Concepto</th>
            <th class="font-white">Debe</th>
            <th class="font-white">Haber</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody class="text-center">
          @foreach($entryDetails as $detail)
            <tr>
              <td>{{ $detail->entryAccount->code }} - {{ $detail->entryAccount->name }}</td>
              <td>{{ $detail->iva ? $detail->iva . '%' : 'Sin IVA' }}</td>
              <td>{{ $detail->concept ?? '-' }}</td>
              <td>{{ number_format($detail->amount_debit, 2, ',', '.') }}</td>
              <td>{{ number_format($detail->amount_credit, 2, ',', '.') }}</td>
              <td>
                <button type="button" class="btn btn-danger btn-sm delete-record" data-id="{{ $detail->id }}">
                  <i class="bx bx-trash"></i>
                </button>
              </td>
            </tr>
          @endforeach
        </tbody>
        <tfoot class="text-center">
          <tr>
            <th class="bg-gray2"></th>
            <th class="bg-gray2"></th>
            <th class="bg-gray2"></th>
            <th class="font-white table-dark">{{ number_format($entryDetails->sum('amount_debit'), 2, ',', '.') }}</th>
            <th class="font-white table-dark">{{ number_format($entryDetails->sum('amount_credit'), 2, ',', '.') }}</th>
            <th></th>
          </tr>
        </tfoot>
      </table>
    </div>
    <div class="d-flex justify-content-end m-2">
      <h6><a href="{{ route('entries.index') }}">Volver</a></h6>
    </div>
  </div>
</div>

@endsection
