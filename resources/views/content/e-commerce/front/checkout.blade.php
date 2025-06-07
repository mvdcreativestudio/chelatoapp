@extends('content.e-commerce.front.layouts.ecommerce-layout')

@section('title', 'Finalizar compra')

<!-- Page Styles -->
@section('page-style')
@vite(['resources/assets/vendor/scss/pages/front-page-payment.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
@vite(['resources/assets/vendor/libs/cleavejs/cleave.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
@vite([
  'resources/assets/js/pages-pricing.js',
  'resources/assets/js/front-page-payment.js'
])
<script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places&callback=initAutocomplete" async defer></script>
@endsection

@section('content')

@php
    // Recuperar el ID de la tienda desde la sesión
    $store_id = session('store.id');
@endphp

<script>
    // Define el store_id en JavaScript desde la sesión de Laravel
    const storeId = "{{ session('store.id') }}";
</script>

<div class="video-container">
  <video autoplay muted loop id="myVideo" class="video-background">
    <source src="./assets/img/videos/back-dali.mp4" type="video/mp4">
  </video>
  <div class="video-overlay-store">
    <h2 class="header-title-store">Finalizar Compra</h2>
    <img src="./assets/img/branding/dali-white.png" class="logo-header-store" alt="">
  </div>
</div>

<div class="container mt-4">
  @if(session('success'))
    <div class="alert alert-success d-flex" role="alert">
      <span class="badge badge-center rounded-pill bg-success border-label-success p-3 me-2"><i class="bx bx-user fs-6"></i></span>
      <div class="d-flex flex-column ps-1">
        <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">¡Correcto!</h6>
        <span>{{ session('success') }}</span>
      </div>
    </div>
  @elseif(session('error'))
    <div class="alert alert-danger d-flex" role="alert">
      <span class="badge badge-center rounded-pill bg-danger border-label-danger p-3 me-2"><i class="bx bx-user fs-6"></i></span>
      <div class="d-flex flex-column ps-1">
        <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">¡Error!</h6>
        <span>{{ session('error') }}</span>
      </div>
    </div>
  @endif
  <div id="alert-container-location" class="alert alert-danger d-none" role="alert">
    <span class="badge badge-center rounded-pill bg-danger border-label-danger p-3 me-2"><i class="bx bx-map-pin fs-6"></i></span>
    <div class="d-flex flex-column ps-1">
      <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">¡Error!</h6>
      <span id="alert-message-location">Aaaa</span>
    </div>
  </div>

  <!-- New alert for RUC/CI validation -->
  <div id="alert-container-doc" class="alert alert-danger d-none" role="alert">
    <span class="badge badge-center rounded-pill bg-danger border-label-danger p-3 me-2"><i class="bx bx-error fs-6"></i></span>
    <div class="d-flex flex-column ps-1">
      <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">¡Error en el Documento!</h6>
      <span id="alert-message-doc"></span>
    </div>
  </div>
</div>

<section class="section-py bg-body first-section-pt mt-5 vh-100">
  <div class="container">
    <!-- Coupon Application Form -->
    @if($settings->enable_coupons)
      <form action="{{ route('apply.coupon') }}" method="POST" class="mb-4">
        @csrf
        <div class="input-group mb-3">
          <input type="text" class="form-control" placeholder="Código del cupón" name="coupon_code" aria-label="Código del cupón" aria-describedby="button-addon2">
          <button class="btn btn-outline-secondary" type="submit" id="button-addon2">Aplicar cupón</button>
        </div>
      </form>
    @endif

    <form action="{{ route('checkout.store') }}" id="checkout-form" method="POST">
      @csrf

      <input type="hidden" name="shipping_cost" id="shippingCostInput" value="0">
      <input type="hidden" name="estimate_id" id="estimateIdInput" value="">
      <input type="hidden" name="city" id="city" />
      <input type="hidden" name="department" id="department" />
      <input type="hidden" name="store_id" id="storeId" value="{{ $store_id }}" />

      <div class="card px-3">
        <div class="row">
          <div class="col-lg-7 card-body border-end">
            <h4 class="mb-2">Finalizar Compra</h4>
            <div class="row pt-4 my-2">
              <h6>Método de pago</h6>
              <div class="col-md mb-md-0 mb-2">
                <div class="form-check custom-option custom-option-basic checked">
                  <label class="form-check-label custom-option-content form-check-input-payment d-flex gap-3 align-items-center" for="customRadioVisa">
                    <input name="payment_method" class="form-check-input" type="radio" value="card" id="customRadioVisa" checked />
                    <span class="custom-option-body">
                      <img src="{{ asset('assets/img/icons/payments/mercadopago.png') }}" alt="paypal" width="58">
                      <span class="ms-3">Mercado Pago</span>
                    </span>
                  </label>
                </div>
              </div>
              <div class="col-md mb-md-0 mb-2">
                <div class="form-check custom-option custom-option-basic">
                  <label class="form-check-label custom-option-content form-check-input-payment d-flex gap-3 align-items-center" for="customRadioEfectivo">
                    <input name="payment_method" class="form-check-input" type="radio" value="efectivo" id="customRadioEfectivo" />
                    <span class="custom-option-body">
                      <img src="{{asset('assets/img/icons/payments/cash.png') }}" alt="paypal" width="58">
                      <span class="ms-3">Efectivo</span>
                    </span>
                  </label>
                </div>
              </div>
            </div>
            <div class="row py-3 my-2">
              <h6>Método de envío</h6>
              <div class="col-md mb-md-0 mb-2">
                  <div class="form-check custom-option custom-option-basic checked">
                      <label class="form-check-label custom-option-content form-check-input-payment d-flex gap-3 align-items-center" for="customRadioPedidosYa">
                          <input name="shipping_method" class="form-check-input" type="radio" value="peya" id="customRadioPedidosYa" checked />
                          <span class="custom-option-body">
                            <img src="{{ asset('assets/img/icons/payments/peya.png') }}" alt="paypal" width="58">
                            <span class="ms-3">Pedidos Ya</span>
                          </span>
                      </label>
                  </div>
              </div>
              <div class="col-md mb-md-0 mb-2">
                  <div class="form-check custom-option custom-option-basic">
                      <label class="form-check-label custom-option-content form-check-input-payment d-flex gap-3 align-items-center" for="customRadioRetiroLocal">
                          <input name="shipping_method" class="form-check-input" type="radio" value="pickup" id="customRadioRetiroLocal" />
                          <span class="custom-option-body">
                              <img src="{{ asset('assets/img/icons/payments/pickup.png') }}" alt="paypal" width="58">
                              <span class="ms-3">Retiro en el local</span>
                          </span>
                      </label>
                  </div>
              </div>
            </div>
            <h4 class="mt-2 mb-4">Completa tus datos</h4>
              <div class="row">
                <div class="col-12 col-md-6">
                  <label class="form-label" for="name">Nombre</label>
                  <input type="text" id="name" name="name" class="form-control" placeholder="Introduzca su nombre" required/>
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label" for="lastname">Apellido</label>
                  <input type="text" id="lastname" name="lastname" class="form-control" placeholder="Introduzca su apellido" required/>
                </div>

                <div class="col-12 mt-3" id="address-container">
                  <label class="form-label" for="address">Dirección</label>
                  <input id="address" name="address" class="form-control" placeholder="Calle, esquina, número de puerta" onFocus="geolocate()" role="presentation" autocomplete="off">
                </div>

                <div class="col-12 col-md-6 mt-3">
                  <label class="form-label" for="phone">Teléfono</label>
                  <input type="text" id="phone" name="phone" class="form-control" placeholder="Introduzca su teléfono" required />
                </div>
                <div class="col-12 col-md-6 mt-3">
                  <label class="form-label" for="email">Correo Electrónico</label>
                  <input type="text" id="email" name="email" class="form-control" placeholder="Introduzca su correo electrónico" required/>
                </div>
                <div class="col-12 col-md-6 mt-3">
                  <label class="form-label" for="doc_type">Tipo de Documento</label>
                  <select id="doc_type" name="doc_type" class="form-control" required>
                    <option value="2">RUC</option>
                    <option value="3">CI</option>
                  </select>
                </div>
                <div class="col-12 col-md-6 mt-3">
                  <label class="form-label" for="doc_recep">Número de Documento</label>
                  <input type="text" id="doc_recep" name="doc_recep" class="form-control" placeholder="Introduzca su RUC o CI" required />
                </div>
              </div>
          </div>
          <div class="col-lg-5 card-body">
            <h4 class="mb-2">Resumen del pedido</h4>
            <div class="mt-4 mb-4">
              <ul class="list-group mb-3">
                @if(session('cart') && count(session('cart')) > 0)
                  @foreach(session('cart') as $id => $details)
                    <li class="list-group-item p-4">
                      <div class="d-flex gap-3">
                        <div class="flex-shrink-0 d-flex align-items-center">
                          <img src="{{ asset($details['image']) }}" alt="{{ $details['name'] }}" class="w-px-100 shop-product-image">
                        </div>
                        <div class="flex-grow-1">
                          <div class="row">
                            <div class="col-md-8">
                              <p class=" mb-0 bold"><span class="text-body">{{ $details['name'] }}</span></p>
                              @if (!empty($details['flavors']))
                              <small class="mt-0">
                                @foreach($details['flavors'] as $flavorId => $flavorDetails)
                                @if($flavorDetails['quantity'] > 1)
                                  <p class="text-muted m-0 p-0">{{ $flavorDetails['name'] }} (x{{ $flavorDetails['quantity'] }})</p>
                                @else
                                  <p class="text-muted m-0 p-0">{{ $flavorDetails['name'] }}</p>
                                @endif
                                @endforeach
                              </small>
                              @endif
                              @if($details['quantity'] == 1)
                                <p class=" mb-0"><span class="text-body">{{ $details['quantity'] }} unidad</span></p>
                              @else
                                <p class=" mb-0"><span class="text-body">{{ $details['quantity'] }} unidades</span></p>
                              @endif
                            </div>
                            <div class="col-md-4">
                              <div class="text-md-end">
                                <div class="my-2 my-md-4 mb-md-5">
                                  @if ($details['price'] != $details['old_price'])
                                    <s class="text-muted">${{ $details['old_price'] }}</s>
                                    <span class="text-primary bold"> {{ $settings->currency_symbol }}{{ $details['price'] }}</span>
                                  @else
                                    <span class="text-primary bold">{{ $settings->currency_symbol }}{{ $details['price'] }}</span>
                                  @endif
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </li>
                  @endforeach
                @else
                  <li class="list-group-item">Tu carrito está vacío.</li>
                @endif
              </ul>
          </div>

          @if(session('cart') && count(session('cart')) > 0)
            <div>
              <div class="d-flex justify-content-between align-items-center mt-3">
                <p class="mb-0">Dirección de la tienda</p>
                <h6 class="mb-0">{{ session('store')['address'] }}</h6>
              </div>
              <div class="d-flex justify-content-between align-items-center mt-3">
                <p class="mb-0">Subtotal</p>
                <h6 class="mb-0">{{ $settings->currency_symbol }}{{$subtotal}}</h6>
              </div>
              @if($discount > 0)
                <div class="d-flex justify-content-between align-items-center mt-3">
                  <p class="mb-0">Cupón de descuento</p>
                  <h6 class="mb-0">{{ $settings->currency_symbol }}{{$discount}}</h6>
                </div>
              @endif
              <div class="d-flex justify-content-between align-items-center mt-3">
                <p class="mb-0">Envío</p>
                <h6 class="mb-0" id="orderShippingCost">A calcular</h6>
              </div>
              <hr>
              <div class="d-flex justify-content-between align-items-center mt-3 pb-1">
                <p class="mb-0">Total</p>
                <h6 class="mb-0" id="orderTotal">A calcular</h6>
              </div>
              <div class="d-grid mt-3">
                @if(session('cart') && count(session('cart')) > 0)
                  <button type="button" id="validate-address" class="btn btn-primary mb-2" type="button">
                    <span class="me-2">Calcular envío</span>
                    <i class="bx bx-calculator scaleX-n1-rtl"></i>
                  </button>
                  <button class="btn btn-success" disabled id="orderConfirm">
                    <span class="me-2">Confirmar pedido</span>
                    <i class="bx bx-right-arrow-alt scaleX-n1-rtl"></i>
                  </button>
                @else
                  <button class="btn btn-primary" disabled>
                    <span class="me-2">Confirmar pedido</span>
                    <i class="bx bx-right-arrow-alt scaleX-n1-rtl"></i>
                  </button>
                @endif
              </div>
            </div>
          </div>
          @endif
        </div>
      </div>
    </form>
  </div>
</section>

<script>
let autocomplete;

function initAutocomplete() {
    autocomplete = new google.maps.places.Autocomplete(
        document.getElementById('address'), { types: ['geocode'] }
    );

    autocomplete.addListener('place_changed', function() {
        const place = autocomplete.getPlace();
        const addressComponents = getAddressComponents(place.address_components);

        document.getElementById('city').value = addressComponents.city;
        document.getElementById('department').value = addressComponents.department;
    });
}

function geolocate() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const geolocation = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };
            const circle = new google.maps.Circle({
                center: geolocation,
                radius: position.coords.accuracy
            });
            autocomplete.setBounds(circle.getBounds());
        });
    }
}

function getAddressComponents(components) {
    let city = '', department = '';

    components.forEach(component => {
        if (component.types.includes('locality')) {
            city = component.long_name;
        }
        if (component.types.includes('administrative_area_level_1')) {
            department = component.long_name;
        }
    });

    return { city, department };
}

document.querySelectorAll('input[name="shipping_method"]').forEach((elem) => {
    elem.addEventListener('change', function(event) {
        const addressContainer = document.getElementById('address-container');
        const validateAddressButton = document.getElementById('validate-address');
        const orderConfirmButton = document.getElementById('orderConfirm');
        const orderShippingCost = document.getElementById('orderShippingCost');
        const orderTotal = document.getElementById('orderTotal');
        const subtotal = parseFloat('{{ $subtotal }}');
        const discount = parseFloat('{{ $discount }}');

        if (this.value === 'pickup') {
            addressContainer.style.display = 'none';
            validateAddressButton.style.display = 'none';
            orderShippingCost.innerText = '$0';
            orderConfirmButton.removeAttribute('disabled');
            orderTotal.innerText = '{{ $settings->currency_symbol }}' + ('{{ $subtotal }}' - '{{ $discount }}');
        } else {
            addressContainer.style.display = 'block';
            validateAddressButton.style.display = 'block';
            orderShippingCost.style.display = 'block';
            orderConfirmButton.setAttribute('disabled', 'disabled');
            orderTotal.innerText = 'A calcular';
        }
    });
});

document.querySelectorAll('input[name="payment_method"]').forEach((elem) => {
    elem.addEventListener('change', function(event) {
        const orderConfirmButton = document.getElementById('orderConfirm');
        orderConfirmButton.setAttribute('disabled', 'disabled');
        document.getElementById('orderShippingCost').innerText = 'A calcular';
        document.getElementById('orderTotal').innerText = 'A calcular';
        document.getElementById('estimateIdInput').value = '';
        document.getElementById('shippingCostInput').value = '0';
    });
});




async function getPedidosYaApiKey(storeId) {
    try {
        const response = await fetch(`/api/get-pedidosya-key/${storeId}`);
        const data = await response.json();
        return data.api_key;
    } catch (error) {
        console.error('Error fetching API key:', error);
        return null;
    }
}

document.getElementById('validate-address').addEventListener('click', async function(event) {
  if (document.getElementById('customRadioPedidosYa').checked) {
    event.preventDefault();

    // Utiliza la variable JavaScript storeId directamente
    const apiKey = await getPedidosYaApiKey(storeId)
    if (!apiKey) {
        alert('Error al obtener la API Key de PedidosYa.');
        return;
    }


    const googleMapsApiKey = '{{ $googleMapsApiKey }}';
    const referenceId = 'Chelato_PeYa_REF-' + Date.now();
    const userAddress = document.getElementById('address').value;
    const storeAddress = '{{ session("store")["address"] }}';

    async function getAddressDetails(address) {
      if (!address) {
        alertDiv = document.getElementById('alert-container-location');
        alertDiv.classList.remove('d-none');
        alertDiv.classList.add('d-flex');
        document.getElementById('alert-message-location').innerText = 'Por favor, complete todos los datos.';
        return;
      }

      const response = await fetch(`https://maps.googleapis.com/maps/api/geocode/json?address=${encodeURI(address)}&key=${googleMapsApiKey}`);
      const data = await response.json();

      if (data.status === 'ZERO_RESULTS') {
        alertDiv = document.getElementById('alert-container-location');
        alertDiv.classList.remove('d-none');
        alertDiv.classList.add('d-flex');
        document.getElementById('alert-message-location').innerText = 'No se encontraron resultados para la dirección ingresada.';
        return;
      }

      return {
        addressStreet: data.results[0].formatted_address,
        city: data.results[0].address_components.find(comp => comp.types.includes("locality")).long_name,
        latitude: data.results[0].geometry.location.lat,
        longitude: data.results[0].geometry.location.lng
      };
    }

    const userDetails = await getAddressDetails(userAddress);
    const storeDetails = await getAddressDetails(storeAddress);

    if (!userDetails || !storeDetails) {
      return;
    }

    const itemsJson = '{!! json_encode(session("cart")) !!}';

    const itemsParsed = JSON.parse(itemsJson);

    const items = Object.values(itemsParsed).map(item => {
      return {
        type: "STANDARD",
        description: item.name,
        value: item.price,
        sku: item.id,
        quantity: item.quantity,
        volume: 2500,
        weight: 1
      };
    });

    var requestData = {
      store_id: storeId, // Asegúrate de incluir store_id
      referenceId: referenceId,
      items: items,
      isTest: true,
      notificationMail: document.getElementById('email').value || null,
      waypoints: [{
        type: "PICK_UP",
        ...storeDetails,
        phone: "+541234567890",
        name: '{{ session("store")["name"] }}',
      }, {
        type: "DROP_OFF",
        ...userDetails,
        collectMoney: document.getElementById('customRadioEfectivo').checked ? parseFloat(document.getElementById('orderTotal').innerText.replace('{{ $settings->currency_symbol }}', '')) : null,
        phone: document.getElementById('phone').value,
        name: document.getElementById('name').value + " " + document.getElementById('lastname').value
      }]
    };

    fetch('/api/pedidos-ya/estimate-order', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(requestData)
    })
    .then(response => {
      if (!response.ok) {
        // Manejo del error
        throw new Error('Network response was not ok: ' + response.statusText);
      }
      return response.json();
    })
    .then(data => {
      if (data.status == 400) {
        handleApiReturns(data.code, data.status);
      } else {
        if (data.estimateId) {
          handleApiReturns('Tu envío fue calculado con éxito.', 200);

          // Habilita el botón de confirmar pedido
          document.getElementById('orderConfirm').removeAttribute('disabled');

          // Actualiza el costo de envío en el input hidden
          document.getElementById('shippingCostInput').value = data.deliveryOffers[0].pricing.total;

          // Actualiza el id de la estimación
          document.getElementById('estimateIdInput').value = data.estimateId;

          // Actualiza el costo de envío
          document.getElementById('orderShippingCost').innerText = '$' + data.deliveryOffers[0].pricing.total;

          // Actualiza el total
          document.getElementById('orderTotal').innerText = '$' + (parseFloat(data.deliveryOffers[0].pricing.total) + parseFloat('200' - '0'));
        }
        else {
          handleApiReturns('Hubo un error al calcular el envío.');
        }
      }
    })
    .catch(error => {
      console.error('Error:', error);
    });


    function handleApiReturns(returnMessage, status = 400) {
      const alertDiv = document.getElementById('alert-container-location');
      let message = '';

      if (status === 200) {
        alertDiv.classList.remove('d-none');
        alertDiv.classList.add('d-flex');
        alertDiv.classList.remove('alert-danger');
        alertDiv.classList.add('alert-success');

        alertDiv.querySelector('.badge').classList.remove('bg-danger');
        alertDiv.querySelector('.badge').classList.add('bg-success');

        alertDiv.querySelector('.badge').classList.remove('border-label-danger');
        alertDiv.querySelector('.badge').classList.add('border-label-success');

        alertDiv.querySelector('h6').innerText = '¡Correcto!';
        document.getElementById('alert-message-location').innerText = returnMessage;
        return;
      }

      switch (returnMessage) {
        case 'WAYPOINTS_OUT_OF_ZONE':
          message = 'La dirección ingresada está fuera de la zona de entrega.';
          break;
        case 'WAYPOINTS_NOT_FOUND':
          message = 'No se pudo encontrar la latitud/longitud para uno o más puntos.';
          break;
        case 'MAX_DISTANCE_EXCEEDED':
          message = 'La distancia máxima permitida fue superada.';
          break;
        case 'MAX_WAYPOINTS_EXCEEDED':
          message = 'Se excedió el número máximo de puntos permitidos.';
          break;
        case 'MAX_VALUE_EXCEEDED':
          message = 'El valor total de los artículos excede el seguro.';
          break;
        case 'MAX_VOLUME_EXCEEDED':
          message = 'El límite de volumen fue excedido.';
          break;
        case 'MAX_WEIGHT_EXCEEDED':
          message = 'El límite de peso fue excedido.';
          break;
        case 'NOT_SUPPORTED_COLLECT_MONEY':
          message = 'La opción de cobrar en efectivo no está disponible. Contacte a su gestor de cuenta de PedidosYa.';
          break;
        case 'COLLECT_MONEY_EXCEEDED':
          message = 'El monto especificado para cobrar en el punto de entrega supera el máximo permitido.';
          break;
        case 'INVALID_DELIVERY_TIME':
          message = 'El tiempo de entrega propuesto debe estar dentro del horario programado.';
          break;
        case 'JSON_INVALID':
          message = 'JSON inválido.';
          break;
        default:
          message = 'Falta uno de los datos o existe un error en la solicitud.';
          break;
      }

      alertDiv.classList.remove('d-none');
      alertDiv.classList.add('d-flex');
      document.getElementById('alert-message-location').innerText = message;
    }

  }
});

// Validación del RUC/CI antes de confirmar el pedido
document.getElementById('orderConfirm').addEventListener('click', function(event) {
    const docType = document.getElementById('doc_type').value;
    const docRecep = document.getElementById('doc_recep').value;

    let isValid = true;
    let errorMessage = '';

    if (docType === '2' && docRecep.length !== 12) { // RUC
        isValid = false;
        errorMessage = 'El RUC debe tener 12 caracteres.';
    } else if (docType === '3' && docRecep.length !== 8) { // CI
        isValid = false;
        errorMessage = 'La CI debe tener 8 caracteres.';
    }

    if (!isValid) {
        event.preventDefault();
        const alertDiv = document.getElementById('alert-container-doc');
        alertDiv.classList.remove('d-none');
        alertDiv.classList.add('d-flex');
        document.getElementById('alert-message-doc').innerText = errorMessage;
    }
});
</script>

@endsection
