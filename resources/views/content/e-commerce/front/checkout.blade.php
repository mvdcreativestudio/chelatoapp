@php
$configData = Helper::appClasses();
@endphp

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
@endsection




@section('content')

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
@endif


<section class="section-py bg-body first-section-pt mt-5">
  <div class="container">
    <form action="{{ route('checkout.store') }}" method="POST">
      @csrf
      <div class="card px-3">
        <div class="row">
          <div class="col-lg-7 card-body border-end">
            <h4 class="mb-2">Finalizar Compra</h4>
            <div class="row py-4 my-2">
              <div class="col-md mb-md-0 mb-2">
                <div class="form-check custom-option custom-option-basic checked">
                  <label class="form-check-label custom-option-content form-check-input-payment d-flex gap-3 align-items-center" for="customRadioVisa">
                    <input name="payment_method" class="form-check-input" type="radio" value="card" id="customRadioVisa" checked />
                    <span class="custom-option-body">
                      <img src="{{asset('assets/img/icons/payments/visa-'.$configData['style'].'.png') }}" alt="visa-card" width="58" data-app-light-img="icons/payments/visa-light.png" data-app-dark-img="icons/payments/visa-dark.png">
                      <span class="ms-3">Mercado Pago</span>
                    </span>
                  </label>
                </div>
              </div>
              <div class="col-md mb-md-0 mb-2">
                <div class="form-check custom-option custom-option-basic">
                  <label class="form-check-label custom-option-content form-check-input-payment d-flex gap-3 align-items-center" for="customRadioPaypal">
                    <input name="payment_method" class="form-check-input" type="radio" value="efectivo" id="customRadioPaypal" />
                    <span class="custom-option-body">
                      <img src="{{asset('assets/img/icons/payments/cash.png') }}" alt="paypal" width="58">
                      <span class="ms-3">Efectivo</span>
                    </span>
                  </label>
                </div>
              </div>
            </div>
            <h4 class="mt-2 mb-4">Datos de envío</h4>
              <div class="row">
                <div class="col-12 col-md-6">
                  <label class="form-label" for="name">Nombre</label>
                  <input type="text" id="name" name="name" class="form-control" placeholder="John" />
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label" for="lastname">Apellido</label>
                  <input type="text" id="lastname" name="lastname" class="form-control" placeholder="Doe" />
                </div>

                <div class="col-12 mt-3">
                  <label class="form-label" for="address">Dirección</label>
                  <input type="text" id="address" name="address" class="form-control" placeholder="Calle, esquina, número de puerta" />
                </div>

                <div class="col-12 col-md-6 mt-3">
                  <label class="form-label" for="phone">Teléfono</label>
                  <input type="text" id="phone" name="phone" class="form-control" placeholder="099 112 223" />
                </div>
                <div class="col-12 col-md-6 mt-3">
                  <label class="form-label" for="email">Correo Electrónico</label>
                  <input type="text" id="email" name="email" class="form-control" placeholder="Introduzca su correo electrónico" />
                </div>
              </div>

            <div id="form-credit-card">
              <h4 class="mt-4 pt-2">Método de pago</h4>
                <div class="row g-3">
                  <div class="col-12">
                    <label class="form-label" for="billings-card-num">Número de tarjeta</label>
                    <div class="input-group input-group-merge">
                      <input type="text" id="billings-card-num" class="form-control billing-card-mask" placeholder="7465 8374 5837 5067" aria-describedby="paymentCard" />
                      <span class="input-group-text cursor-pointer p-1" id="paymentCard"><span class="card-type"></span></span>

                    </div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label" for="billings-card-name">Nombre</label>
                    <input type="text" id="billings-card-name" class="form-control" placeholder="John Doe" />
                  </div>
                  <div class="col-md-3">
                    <label class="form-label" for="billings-card-date">Vencimiento</label>
                    <input type="text" id="billings-card-date" class="form-control billing-expiry-date-mask" placeholder="MM/YY" />
                  </div>
                  <div class="col-md-3">
                    <label class="form-label" for="billings-card-cvv">CVV</label>
                    <input type="text" id="billings-card-cvv" class="form-control billing-cvv-mask" maxlength="3" placeholder="965" />
                  </div>
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
                          <img src="{{ asset($details['image']) }}" alt="{{ $details['name'] }}" class="w-px-100">
                        </div>
                        <div class="flex-grow-1">
                          <div class="row">
                            <div class="col-md-8">
                              <p class=" mb-0"><a href="javascript:void(0)" class="text-body">{{ $details['name'] }}</a></p>
                              @if (!empty($details['flavors']))
                              <small class="mt-0">
                                @foreach($details['flavors'] as $flavor)
                                  - {{ $flavor['name'] }}
                                @endforeach
                              </small>
                              @endif
                              <input type="number" class="form-control form-control-sm w-px-100 mt-2" value="{{ $details['quantity'] }}" min="1" max="5">
                            </div>
                            <div class="col-md-4">
                              <div class="text-md-end">
                                <div class="my-2 my-md-4 mb-md-5">
                                  @if ($details['price'])
                                    <s class="text-muted">${{ $details['old_price'] }}</s>
                                    <span class="text-primary"> ${{ $details['price'] }}</span>
                                  @else
                                    <span class="text-primary">${{ $details['old_price'] }}</span>
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


            <div>
              <div class="d-flex justify-content-between align-items-center mt-3">
                <p class="mb-0">Subtotal</p>
                <h6 class="mb-0">${{$subtotal}}</h6>
              </div>
              <div class="d-flex justify-content-between align-items-center mt-3">
                <p class="mb-0">Envío</p>
                <h6 class="mb-0">${{$costoEnvio}}</h6>
              </div>
              <hr>
              <div class="d-flex justify-content-between align-items-center mt-3 pb-1">
                <p class="mb-0">Total</p>
                <h6 class="mb-0">${{$totalPedido}}</h6>
              </div>
              <div class="d-grid mt-3">
                @if(session('cart') && count(session('cart')) > 0)
                  <button class="btn btn-success">
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
              {{-- <p class="mt-4 pt-2">By continuing, you accept to our Terms of Services and Privacy Policy. Please note that payments are non-refundable.</p> --}}
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</section>



<!-- Modal -->
@include('_partials/_modals/modal-pricing')
<!-- /Modal -->
@endsection
