@extends('layouts/layoutMaster')

@section('title', 'Editar lista de precios')

@section('content')
<div class="d-flex align-items-center justify-content-between bg-white p-4 mb-3 rounded shadow-lg sticky-top border-bottom border-light">
  <h4 class="mb-0 page-title"><i class="bx bx-edit me-2"></i> Editar: {{ $priceList->name }}</h4>
  <div class="d-flex gap-2">
    <a href="{{ route('price-lists.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bx bx-arrow-back"></i> Volver</a>
  </div>
</div>

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
  </div>
@endif

<form method="POST" action="{{ route('price-lists.update', $priceList->id) }}">
  @csrf
  @method('PUT')

  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Nombre <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control" required value="{{ old('name', $priceList->name) }}">
        </div>
        <div class="col-md-3">
          <label class="form-label">Tienda</label>
          <input type="text" class="form-control" disabled value="{{ optional($priceList->store)->name }}">
        </div>
        <div class="col-md-3">
          <label class="form-label">Moneda <span class="text-danger">*</span></label>
          <select name="currency" class="form-select" required>
            <option value="Peso" @selected(old('currency', $priceList->currency) == 'Peso')>Peso</option>
            <option value="Dólar" @selected(old('currency', $priceList->currency) == 'Dólar')>Dólar</option>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Descripción</label>
          <textarea name="description" class="form-control" rows="2">{{ old('description', $priceList->description) }}</textarea>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Productos</h5>
        <div class="d-flex gap-2 flex-grow-1 justify-content-end" style="max-width: 600px;">
          <input type="text" id="productSearch" class="form-control" placeholder="Buscar producto...">
          <select id="productFilter" class="form-select" style="max-width: 180px;">
            <option value="all">Todos</option>
            <option value="priced">Con precio</option>
            <option value="unpriced">Sin precio</option>
          </select>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th style="width: 60px;"></th>
              <th>Producto</th>
              <th>SKU</th>
              <th class="text-end" style="width: 140px;">Precio base</th>
              <th class="text-end" style="width: 180px;">Precio lista</th>
            </tr>
          </thead>
          <tbody id="productsBody">
            <tr><td colspan="5" class="text-center text-muted py-4">Cargando...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="mt-3 text-end">
    <button type="submit" class="btn btn-success"><i class="bx bx-save"></i> Guardar cambios</button>
  </div>
</form>

<script>
(function() {
  const storeId = {{ (int) $priceList->store_id }};
  const priceListId = {{ (int) $priceList->id }};
  const baseUrl = "{{ url('admin/price-lists') }}";
  const tbody = document.getElementById('productsBody');
  const searchInput = document.getElementById('productSearch');
  const filterSelect = document.getElementById('productFilter');

  function fmt(n) { return n == null ? '' : Number(n).toFixed(2); }
  function escapeHtml(s) { return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c]); }

  function render(products) {
    if (!products.length) {
      tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Sin resultados.</td></tr>';
      return;
    }
    tbody.innerHTML = products.map(p => `
      <tr>
        <td><img src="${p.image ? '{{ asset('') }}' + p.image : '{{ asset('assets/img/ecommerce-images/placeholder.png') }}'}" alt="" style="width:40px; height:40px; object-fit:cover; border-radius:4px;"></td>
        <td>${escapeHtml(p.name)}</td>
        <td><small class="text-muted">${escapeHtml(p.sku || '')}</small></td>
        <td class="text-end text-muted">${fmt(p.base_price)}</td>
        <td class="text-end">
          <input type="number" step="0.01" min="0" name="prices[${p.id}]" class="form-control form-control-sm text-end" value="${p.list_price ?? ''}" placeholder="—">
        </td>
      </tr>
    `).join('');
  }

  function load() {
    const params = new URLSearchParams();
    if (searchInput.value) params.set('query', searchInput.value);
    params.set('filter', filterSelect.value);
    fetch(`${baseUrl}/${storeId}/${priceListId}/products?` + params.toString(), {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(j => render(j.products || []));
  }

  let t;
  searchInput.addEventListener('input', () => { clearTimeout(t); t = setTimeout(load, 300); });
  filterSelect.addEventListener('change', load);

  load();
})();
</script>
@endsection
