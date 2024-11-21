<div class="row row-cols-1 row-cols-xl-5 row-cols-md-4 row-cols-sm-2 g-4">
    @forelse($products as $product)
        <div class="col">
            <div class="card h-100 text-center custom-card-colchones">
                <img src="{{ asset($product->image) }}" class="card-img-top custom-image-colchones" alt="{{ $product->name }}">
                <div class="card-body">
                    <h5 class="card-title text-primary font-weight-bold custom-title-colchones">{{ strtoupper($product->name) }}</h5>
                </div>
                <div class="card-footer bg-white border-0 custom-footer-colchones">
                    <a href="{{ route('landing-page.producto', $product->id) }}" class="btn custom-button-colchones">
                        VER PRODUCTO
                    </a>
                </div>
            </div>
        </div>
    @empty
        <p class="text-center w-100">No hay productos disponibles en esta categoría.</p>
    @endforelse
</div>
