@php
  $stores = App\Models\Store::all();
  $selectedStoreId = session('store')['id'] ?? null;
@endphp

<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
    <img src="{{ asset($companySettings->logo_white) }}" alt="" class="navbar-logo">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarScroll">
      <ul class="navbar-nav me-auto my-2 my-lg-0 navbar-nav-scroll" style="--bs-scroll-height: 100px;">
        @if(session('store'))
        <li class="nav-item">
          <a class="nav-link nav-link-menu" href="#" data-bs-toggle="modal" data-bs-target="#selectStoreModal">
            <i class="fa-solid fa-location-dot"></i> {{ session('store')['name'] }}
          </a>
        </li>
        @endif
      </ul>
      <ul class="navbar-nav navbar-nav-scroll">
        <li>
          @if(session('store') && session('store')['id'] == $selectedStoreId)
          <a href="#" class="nav-link nav-link-menu" onclick="confirmChangeStore()">Cambiar de tienda</a>
          @else
          <a href="#" class="nav-link nav-link-menu" data-bs-toggle="modal" data-bs-target="#selectStoreModal">Seleccionar tienda</a>
          @endif
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Modal -->
<div class="modal fade" id="selectStoreModal" tabindex="-1" aria-labelledby="selectStoreModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="selectStoreModalLabel">Cambiar de tienda</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-2">
        @if(session('cart') && count(session('cart')) > 0)
          <div class="alert alert-danger" role="alert">
            Perderás los productos que hayas agregado al carrito.
          </div>
        @endif
        <form id="changeStoreForm" action="{{ route('cart.selectStore') }}" method="POST">
          @csrf
          <div class="mb-3">
            <label for="storeId" class="form-label">Seleccionar tienda:</label>
            <select class="form-select" name="slug" id="storeId">
              @foreach($stores as $store)
                <option value="{{ $store->slug }}" {{ $selectedStoreId == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
              @endforeach
            </select>
          </div>
          @if(session('store') && session('store')['id'] == $selectedStoreId)
            <button type="submit" class="btn btn-primary">Cambiar tienda</button>
          @else
            <button type="submit" class="btn btn-primary">Seleccionar tienda</button>
          @endif
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    function confirmChangeStore() {
      $('#selectStoreModal').modal('show');
    }

    // Reinicializar el formulario de cambio de tienda cada vez que se muestre el modal
    $('#selectStoreModal').on('show.bs.modal', function () {
      $('#changeStoreForm')[0].reset(); // Reiniciar el formulario
    });
  });
</script>
