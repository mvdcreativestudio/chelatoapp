@extends('content.e-commerce.front.layouts.ecommerce-layout')

@section('title', 'Store')

@section('content')

<div class="video-container">
  <video autoplay muted loop id="myVideo" class="video-background">
      <source src="../assets/img/videos/back-dali.mp4" type="video/mp4">
  </video>
  <div class="video-overlay-store">
    <h2 class="header-title-store">{{ $store->name }}</h2>
    <img src="../assets/img/branding/dali-white.png" class="logo-header-store" alt="">
  </div>
</div>

<div class="container store-container">
  @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
  @endif

  @if(session('success'))
    <script>
      let timerInterval;
      Swal.fire({
        title: "¡Correcto!",
        position: "top-end",
        text: '{{ session('success')}}',
        timer: 2000,
        icon: 'success',
        timerProgressBar: true,
        showConfirmButton: false,
        customClass: {
              popup: 'added-to-cart-popup'
          },
        didOpen: () => {
          const timer = Swal.getPopup().querySelector("b");
          timerInterval = setInterval(() => {
            timer.textContent = `${Swal.getTimerLeft()}`;
          }, 100);
        },
        willClose: () => {
          clearInterval(timerInterval);
        }
      }).then((result) => {
        if (result.dismiss === Swal.DismissReason.timer) {
          console.log("I was closed by the timer");
        }
      });
    </script>
  @endif

  @if(session('error'))
    <script>
        Swal.fire({
            title: 'Error',
            text: '{{ session('error') }}',
            icon: 'error',
            confirmButtonText: 'Ok'
        });
    </script>
  @endif

  <div class="container vh-100">
    <!-- Categories -->
    <div class="mt-4">
      <div class="row row-cols-1 row-cols-md-4 g-">
        @foreach ($categories as $category)
        <div class="col mb-4">
          <a href="#{{ $category->slug }}" class="text-decoration-none">
            <div class="card card-category">
              <img src="{{ asset($category->image_url) }}">
              <div class="category-card-text">
                <h5 class="category-name light">{{ $category->name }}</h5>
              </div>
            </div>
          </a>
        </div>
        @endforeach
      </div>
    </div>
    <!-- End Categories -->

    <!-- Categories and Products -->
    @foreach($categories as $category)
      @if($category->products->isNotEmpty())
        <div id="{{ $category->slug }}" class="category-section mt-5">
          <h2 class="store-category-title bold text-center mb-5">{{ $category->name }}</h2>
          <div class="products-container">
            <div class="row">
              @foreach ($category->products as $product)
                <div class="col-md-2 col-6" data-bs-toggle="modal" data-bs-target="#modalCenter"
                    data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-img="{{ $product->image }}" data-price="{{ $product->price }}" data-max-flavors="{{$product->max_flavors}}" data-description="{{ $product->description }}">
                  <div class="card card-product">
                    <img src="{{ asset($product->image) }}" class="shop-product-image" alt="Product">
                    <div class="product-card-text">
                      <h5 class="product-name light">{{ $product->name }}</h5>
                      <div class="d-flex justify-content-center">
                        @if($product->price == null)
                          <p class="product-price bold mx-1">${{ $product->old_price }}</p>
                        @else
                          <s class="text-muted mx-1">${{ $product->old_price }}</s>
                          <p class="product-price bold mx-1">${{ $product->price }}</p>
                        @endif
                      </div>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      @endif
    @endforeach

    <div class="whatsapp-box">
      <a href="https://wa.me/59899999999" target="_blank">
        <img class="whatsapp-box-img" src="..\assets\img\ecommerce\whatsapp-icon.png" alt="Whatsapp">
      </a>
    </div>

    <div class="col-lg-3 col-md-6">
      <div class="mt-3 cart-box">
        <img class="cart-box-img" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEnd" aria-controls="offcanvasEnd" src="{{ asset('assets/img/ecommerce/cart-icon.png') }}" alt="Cart">
        @if (session('cart'))
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
              {{ count(session('cart')) }}
              <span class="visually-hidden">productos en el carrito</span>
          </span>
        @endif

        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEnd" aria-labelledby="offcanvasEndLabel">
          <div class="offcanvas-header">
            <h5 id="offcanvasEndLabel" class="offcanvas-title">Carrito</h5>
          </div>
          <div class="offcanvas-body mx-0 flex-grow-0">
            <div class="cart-product-list">
              @if(session('cart'))
              @foreach(session('cart') as $id => $details)
              <!-- Dentro de cada artículo del carrito -->
              <div class="card cart-product-item position-relative">
                  <!-- Formulario para eliminar el artículo -->
                  <form action="{{ route('cart.removeItem') }}" method="POST" class="position-absolute top-0 end-0">
                      @csrf
                      <!-- Clave identificadora del artículo -->
                      <input type="hidden" name="key" value="{{ $id }}">
                      <!-- Botón de eliminación -->
                      <button type="submit" class="btn btn-link p-0 m-2" title="Eliminar">
                          &times;
                      </button>
                  </form>

                  <img src="../{{ $details['image'] }}" alt="{{ $details['name'] }}" class="cart-product-img">
                  <div class="product-card-text">
                      <h5 class="product-name">{{ $details['name'] }}</h5>
                      <div class="cart-product-variants-container text-center justify-content-center">
                          @if ($details['price'] == null)
                              <small class="cart-product-variants">{{ $details['quantity'] }} x ${{ $details['old_price'] }}</small>
                          @else
                              <small class="cart-product-variants">{{ $details['quantity'] }} x ${{ $details['price'] }}</small>
                          @endif
                      </div>
                      <!-- Si hay variaciones asociados al producto -->
                      @if(isset($details['flavors']) && is_array($details['flavors']) && count($details['flavors']) > 0)
                          <div class="cart-flavors">
                              @foreach($details['flavors'] as $flavorId => $flavorDetails)
                                  @if($flavorDetails['quantity'] > 1)
                                      <p class="text-muted m-0 p-0">{{ $flavorDetails['name'] }} (x{{ $flavorDetails['quantity'] }})</p>
                                  @else
                                      <p class="text-muted m-0 p-0">{{ $flavorDetails['name'] }}</p>
                                  @endif
                              @endforeach
                          </div>
                      @endif

                      <!-- Precio Total del artículo -->
                      @if ($details['price'] == null)
                          <p class="product-price">${{ $details['old_price'] * $details['quantity'] }}</p>
                      @else
                          <p class="product-price">${{ $details['price'] * $details['quantity'] }}</p>
                      @endif
                  </div>
              </div>
              @endforeach

              @else
                  <p>Tu carrito está vacío.</p>
              @endif
          </div>

          </div>
          <div class="offcanvas-footer offcanvas-cart-footer">
            @if(session('cart'))
                <div class="cart-total-price">
                    @php $total = 0; @endphp
                    @foreach(session('cart') as $id => $details)
                        @php
                            $priceToUse = isset($details['price']) && $details['price'] != null ? $details['price'] : $details['old_price'];
                            $total += $priceToUse * $details['quantity'];
                        @endphp
                    @endforeach
                    <h5 class="bold">Subtotal: ${{ $total }}</h5>
                </div>
            @endif
            <button type="button" class="btn btn-label-secondary d-grid offcanvas-cart-button" data-bs-dismiss="offcanvas">Continuar comprando</button>
            @if(session('cart') && count(session('cart')) > 0)
              <a href="{{ route('checkout.index') }}"><button type="button" class="btn btn-primary mb-2 d-grid offcanvas-cart-button">Finalizar compra</button></a>
            @else
              <button type="button" class="btn btn-primary mb-2 d-grid offcanvas-cart-button" disabled>Finalizar compra</button>
            @endif
            @if(session()->has('store'))
              <h6 class="text-center mt-3">Empresa seleccionada: <b>{{ session('store')['name'] }}</b></h6>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal add to cart genérico -->
<div class="modal fade" id="modalCenter" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <form action="" method="POST" id="addToCartForm">
        @csrf
        <input type="hidden" name="productId" id="modalProductId">
        <div class="modal-header">
          <h5 class="modal-title" id="modalCenterTitle">Nombre del Producto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="add-to-cart-img-container justify-content-center text-center mb-5">
            <img src="" alt="Product" class="add-to-cart-img" id="modalProductImage">
          </div>
          <div>
            <p id="modal-description" class="text-start">Descripción del producto</p>
          </div>
          <div id="flavorSelectors"></div>

          <div>
            <p class="text-center">Cantidad</p>
          </div>
          <div class="quantity-selector-container text-center">
            <button type="button" class="btn btn-secondary btn-sm" id="decreaseQuantity">-</button>
            <input type="text" name="quantity" id="modalProductQuantity" value="1" class="quantity-show" readonly>
            <button type="button" class="btn btn-secondary btn-sm" id="increaseQuantity">+</button>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cerrar</button>
          <div class="text-center">
            <button type="submit" class="btn btn-primary">Añadir al Carrito</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var modal = document.getElementById('modalCenter');
  modal.addEventListener('show.bs.modal', function(event) {
    var button = event.relatedTarget;
    var productId = button.getAttribute('data-id');
    var productName = button.getAttribute('data-name');
    var productDescription = button.getAttribute('data-description');
    var productImage = button.getAttribute('data-img');
    var maxFlavors = parseInt(button.getAttribute('data-max-flavors'), 10);

    var modalForm = document.getElementById('addToCartForm');
    var modalProductId = document.getElementById('modalProductId');
    var modalTitle = document.getElementById('modalCenterTitle');
    var modalDescription = document.getElementById('modal-description');
    var modalImg = document.getElementById('modalProductImage');

    modalForm.action = `../cart/add/${productId}`;
    modalProductId.value = productId;
    modalTitle.textContent = productName;
    modalDescription.textContent = stripHtml(productDescription);
    modalImg.src = `../${productImage}`;

    // Limpia el contenedor de selectores de variaciones antes de añadir nuevos
    var flavorSelectorsContainer = document.getElementById('flavorSelectors');
    flavorSelectorsContainer.innerHTML = '';

    // Genera dinámicamente los selectores de variaciones basados en maxFlavors
    for (let i = 0; i < maxFlavors; i++) {
      let flavorText = getFlavorText(i);
      var selectHTML = `<select name="flavors[]" class="form-select mb-2" required>
                          <option value="" disabled selected>${flavorText}</option>
                          @foreach ($flavors as $flavor)
                              <option value="{{ $flavor->id }}">{{ $flavor->name }}</option>
                          @endforeach
                        </select>`;
      flavorSelectorsContainer.insertAdjacentHTML('beforeend', selectHTML);
    }
  });

  function getFlavorText(index) {
    const flavorTexts = [
      "Primer sabor", "Segundo sabor", "Tercer sabor",
      "Cuarto sabor", "Quinto sabor", "Sexto sabor",
      "Séptimo sabor", "Octavo sabor", "Noveno sabor", "Décimo sabor"
    ];
    return flavorTexts[index] || `Sabor ${index + 1}`;
  }

  function stripHtml(html) {
    var tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;
    return tempDiv.textContent || tempDiv.innerText || '';
  }
});

document.addEventListener('DOMContentLoaded', function () {
  var modal = document.getElementById('modalCenter');
  var quantityInput = document.getElementById('modalProductQuantity');

  function updateQuantity(delta) {
    var currentQuantity = parseInt(quantityInput.value);
    var newQuantity = currentQuantity + delta;
    if (newQuantity < 1) newQuantity = 1;
    quantityInput.value = newQuantity;
  }

  document.getElementById('decreaseQuantity').addEventListener('click', function () {
    updateQuantity(-1);
  });

  document.getElementById('increaseQuantity').addEventListener('click', function () {
    updateQuantity(1);
  });

  modal.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    var productId = button.getAttribute('data-id');
    var productName = button.getAttribute('data-name');
    var productDescription = button.getAttribute('data-description');
    var productImage = button.getAttribute('data-img');
    var maxFlavors = parseInt(button.getAttribute('data-max-flavors'), 10);

    var modalForm = document.getElementById('addToCartForm');
    var modalProductId = document.getElementById('modalProductId');
    var modalTitle = document.getElementById('modalCenterTitle');
    var modalImg = document.getElementById('modalProductImage');

    modalForm.action = `../cart/add/${productId}`;
    modalProductId.value = productId;
    modalTitle.textContent = productName;
    modalDescription.textContent = stripHtml(productDescription);
    modalImg.src = `../${productImage}`;

    quantityInput.value = 1;

    var flavorSelectorsContainer = document.getElementById('flavorSelectors');
    flavorSelectorsContainer.innerHTML = '';

    for (let i = 0; i < maxFlavors; i++) {
      let flavorText = getFlavorText(i);
      var selectHTML = `<select name="flavors[]" class="form-select mb-2" required>
                          <option value="" disabled selected>${flavorText}</option>
                          @foreach ($flavors as $flavor)
                              <option value="{{ $flavor->id }}">{{ $flavor->name }}</option>
                          @endforeach
                        </select>`;
      flavorSelectorsContainer.insertAdjacentHTML('beforeend', selectHTML);
    }
  });

  function getFlavorText(index) {
    const flavorTexts = [
      "Primer sabor", "Segundo sabor", "Tercer sabor",
      "Cuarto sabor", "Quinto sabor", "Sexto sabor",
      "Séptimo sabor", "Octavo sabor", "Noveno sabor", "Décimo sabor"
    ];
    return flavorTexts[index] || `Sabor ${index + 1}`;
  }

  function stripHtml(html) {
    var tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;
    return tempDiv.textContent || tempDiv.innerText || '';
  }
});
</script>

@endsection
