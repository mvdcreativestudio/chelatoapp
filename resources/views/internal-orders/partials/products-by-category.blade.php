@foreach($grouped as $categoryName => $products)
  <div class="card mb-3 border">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
      <strong>{{ $categoryName }}</strong>
    </div>

    <div class="card-body">
      <div class="row">
        @foreach($products as $product)
          <div class="col-md-12 mb-3">
            <div class="border rounded p-3 h-100 d-flex flex-column justify-content-between">
              <div class="mb-2">
                <strong>{{ $product->name }}</strong>
              </div>

              <div class="mb-2">
                <label class="form-label">Stock Actual</label>
                <input type="text" class="form-control" value="{{ $product->stock !== null ? $product->stock : 'No definido' }}" readonly disabled>
              </div>

              <div>
                <label class="form-label">Cantidad</label>
                <input type="number" class="form-control" name="products[{{ $product->id }}][quantity]" min="0" placeholder="0">
                <input type="hidden" name="products[{{ $product->id }}][product_id]" value="{{ $product->id }}">
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>
@endforeach

