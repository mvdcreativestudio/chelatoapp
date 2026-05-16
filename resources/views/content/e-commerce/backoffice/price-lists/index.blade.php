@extends('layouts/layoutMaster')

@section('title', 'Listas de precios')

@section('content')
<div class="d-flex align-items-center justify-content-between bg-white p-4 mb-3 rounded shadow-lg sticky-top border-bottom border-light">
  <div class="d-flex flex-column justify-content-center">
    <h4 class="mb-0 page-title">
      <i class="bx bx-list-ul me-2"></i> Listas de precios
    </h4>
  </div>

  <div class="d-flex align-items-center justify-content-center flex-grow-1 gap-3">
    <div class="input-group w-50 shadow-sm">
      <span class="input-group-text bg-white"><i class="bx bx-search"></i></span>
      <input type="text" id="searchPriceList" class="form-control" placeholder="Buscar lista por nombre...">
    </div>
  </div>

  <div class="text-end d-flex gap-2">
    @can('access_create_price-lists')
      <a href="{{ route('price-lists.create') }}" class="btn btn-success btn-sm shadow-sm d-flex align-items-center gap-1">
        <i class="bx bx-plus"></i> Nueva lista
      </a>
    @endcan
  </div>
</div>

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
  <div class="card-body">
    @if($stores->count() > 1)
      <div class="row mb-3">
        <div class="col-md-4">
          <label class="form-label">Tienda</label>
          <select id="filterStore" class="form-select">
            <option value="">Todas</option>
            @foreach($stores as $store)
              <option value="{{ $store->id }}">{{ $store->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
    @endif

    <div class="table-responsive">
      <table class="table table-striped" id="priceListsTable">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Tienda</th>
            <th>Moneda</th>
            <th class="text-center">Productos</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<script>
(function() {
  const tbody = document.querySelector('#priceListsTable tbody');
  const searchInput = document.getElementById('searchPriceList');
  const storeFilter = document.getElementById('filterStore');
  const editBase = "{{ url('admin/price-lists') }}";
  const csrf = "{{ csrf_token() }}";
  const canEdit = @json(auth()->user()->can('access_edit_price-lists'));
  const canDelete = @json(auth()->user()->can('access_delete_price-lists'));

  function render(rows) {
    if (!rows.length) {
      tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No hay listas de precios.</td></tr>';
      return;
    }
    tbody.innerHTML = rows.map(r => `
      <tr>
        <td>${escapeHtml(r.name)}</td>
        <td>${escapeHtml(r.description || '')}</td>
        <td>${escapeHtml(r.store_name || '')}</td>
        <td>${escapeHtml(r.currency || '')}</td>
        <td class="text-center"><span class="badge bg-label-primary">${r.products_count ?? 0}</span></td>
        <td class="text-end">
          ${canEdit ? `<a href="${editBase}/${r.id}/edit" class="btn btn-sm btn-outline-primary"><i class="bx bx-edit"></i></a>` : ''}
          ${canDelete ? `<button class="btn btn-sm btn-outline-danger" data-delete="${r.id}"><i class="bx bx-trash"></i></button>` : ''}
        </td>
      </tr>
    `).join('');
  }

  function escapeHtml(s) {
    return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c]);
  }

  function fetchData() {
    const params = new URLSearchParams();
    if (searchInput.value) params.set('search', searchInput.value);
    if (storeFilter && storeFilter.value) params.set('store_id', storeFilter.value);

    fetch("{{ route('price-lists.datatable') }}?" + params.toString(), {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(j => render(j.data || []));
  }

  tbody.addEventListener('click', e => {
    const btn = e.target.closest('[data-delete]');
    if (!btn) return;
    if (!confirm('¿Eliminar esta lista de precios?')) return;
    fetch(`${editBase}/${btn.dataset.delete}`, {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    }).then(() => fetchData());
  });

  let timer;
  searchInput.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(fetchData, 300);
  });
  if (storeFilter) storeFilter.addEventListener('change', fetchData);

  fetchData();
})();
</script>
@endsection
