<!-- Checkout Wizard -->
<div id="wizard-checkout" class="bs-stepper wizard-icons wizard-icons-example mb-5 mt-5">
  <div class="bs-stepper-header m-auto border-0 py-4">
    <div class="step" data-target="#checkout-cart">
      <button type="button" class="step-trigger">
        <span class="bs-stepper-icon">
          <svg viewBox="0 0 58 54">
            <use xlink:href="{{asset('assets/svg/icons/wizard-checkout-cart.svg#wizardCart')}}"></use>
          </svg>
        </span>
        <span class="bs-stepper-label">Carrito</span>
      </button>
    </div>
    <div class="line">
      <i class="bx bx-chevron-right"></i>
    </div>
    <div class="step" data-target="#checkout-address">
      <button type="button" class="step-trigger">
        <span class="bs-stepper-icon">
          <svg viewBox="0 0 54 54">
            <use xlink:href="{{asset('assets/svg/icons/wizard-checkout-address.svg#wizardCheckoutAddress')}}"></use>
          </svg>
        </span>
        <span class="bs-stepper-label">Dirección</span>
      </button>
    </div>
    <div class="line">
      <i class="bx bx-chevron-right"></i>
    </div>
    <div class="step" data-target="#checkout-payment">
      <button type="button" class="step-trigger">
        <span class="bs-stepper-icon">
          <svg viewBox="0 0 58 54">
            <use xlink:href="{{asset('assets/svg/icons/wizard-checkout-payment.svg#wizardPayment')}}"></use>
          </svg>
        </span>
        <span class="bs-stepper-label">Pago</span>
      </button>
    </div>
    <div class="line">
      <i class="bx bx-chevron-right"></i>
    </div>
    <div class="step" data-target="#checkout-confirmation">
      <button type="button" class="step-trigger">
        <span class="bs-stepper-icon">
          <svg viewBox="0 0 58 54">
            <use xlink:href="{{asset('assets/svg/icons/wizard-checkout-confirmation.svg#wizardConfirm')}}"></use>
          </svg>
        </span>
        <span class="bs-stepper-label">Confirmación</span>
      </button>
    </div>
  </div>
  <div class="bs-stepper-content border-top">
    <form id="wizard-checkout-form" onSubmit="return false">

      <!-- Cart -->
      <div id="checkout-cart" class="content">
        <div class="row">
          <!-- Cart left -->
          <div class="col-xl-8 mb-3 mb-xl-0">

            <!-- Offer alert -->
            <div class="alert alert-success mb-3" role="alert">
              <div class="d-flex">
                <span class="badge badge-center rounded-pill bg-success border-label-success p-3 me-2"><i class="bx bx-bookmarks fs-4"></i></span>
                <div class="flex-grow-1 ps-1">
                  <div class="fw-medium fs-5 mb-2">Ofertas disponibles</div>
                  <ul class="list-unstyled mb-0">
                    <li> - 10% de descuento con Santander Crédito / Débito</li>
                    <li> - 25% de reintegro con Itaú Platinum</li>
                  </ul>
                </div>
              </div>
              <button type="button" class="btn-close btn-pinned" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <!-- Shopping bag -->
            <h5>Carrito de compras</h5>
            <ul class="list-group mb-3">
              @if(session('cart') && count(session('cart')) > 0)
                @foreach(session('cart') as $id => $details)
                  <li class="list-group-item p-4">
                    <div class="d-flex gap-3">
                      <div class="flex-shrink-0 d-flex align-items-center">
                        <img src="{{ asset('assets/img/ecommerce-images/' . $details['image']) }}" alt="{{ $details['name'] }}" class="w-px-100">
                      </div>
                      <div class="flex-grow-1">
                        <div class="row">
                          <div class="col-md-8">
                            <p class=" mb-0"><a href="javascript:void(0)" class="text-body">{{ $details['name'] }}</a></p>
                            <small class="mt-0">Tus variaciones aquí</small>
                            <input type="number" class="form-control form-control-sm w-px-100 mt-2" value="{{ $details['quantity'] }}" min="1" max="5">
                          </div>
                          <div class="col-md-4">
                            <div class="text-md-end">
                              <button type="button" class="btn-close btn-pinned" aria-label="Close"></button>
                              <div class="my-2 my-md-4 mb-md-5">
                                @if ($details['old_price'])
                                  <s class="text-muted">${{ $details['old_price'] }}</s>
                                  <span class="text-primary"> ${{ $details['price'] }}</span>
                                @else
                                  <span class="text-primary">${{ $details['price'] }}</span>
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

          <!-- Cart right -->
          <div class="col-xl-4">
            <div class="border rounded p-4 mb-3 pb-3">

              <!-- Offer -->
              <h6>Cupón de descuento</h6>
              <div class="row g-3 mb-3">
                <div class="col-8 col-xxl-8 col-xl-12">
                  <input type="text" class="form-control" placeholder="Ingresar cupón" aria-label="Enter Promo Code">
                </div>
                <div class="col-4 col-xxl-4 col-xl-12">
                  <div class="d-grid">
                    <button type="button" class="btn btn-label-primary">Aplicar</button>
                  </div>
                </div>
              </div>

              <!-- Gift wrap -->
              <div class="bg-lighter rounded p-3">
                <p class="fw-medium mb-2">¿Es un regalo?</p>
                <p class="mb-2">Haz que reciba su helado con una nota</p>
                <a href="javascript:void(0)" class="fw-medium">Enviar una nota</a>
              </div>
              <hr class="mx-n4">

              <!-- Price Details -->
              <h6>Detalles</h6>
              <dl class="row mb-0">
                  <dt class="col-6 fw-normal">Total en productos</dt>
                  <dd class="col-6 text-end">${{ $totalProductos }}</dd>

                  @if(session('cart') && isset(session('cart')['coupon']))
                      <dt class="col-sm-6 fw-normal">Cupones</dt>
                      <dd class="col-sm-6 text-end">-{{ session('cart')['coupon']['discount_value'] }}{{ session('cart')['coupon']['discount_type'] == 'percent' ? '%' : '$' }}</dd>
                  @else
                      <dt class="col-sm-6 fw-normal">Cupones</dt>
                      <dd class="col-sm-6 text-end"><a href="javascript:void(0)">Ingresar cupón</a></dd>
                  @endif

                  <dt class="col-6 fw-normal">Total del pedido</dt>
                  <dd class="col-6 text-end">${{ $totalPedido }}</dd>

                  <dt class="col-6 fw-normal">Costo de envío</dt>
                  <dd class="col-6 text-end">
                      @if($envioGratis)
                          <span class="badge bg-label-success ms-1">Gratis</span>
                      @else
                          <s class="text-muted">${{ $costoEnvio }}</s>
                      @endif
                  </dd>
              </dl>


              <hr class="mx-n4">
              <dl class="row mb-0">
                <dt class="col-6">Total</dt>
                <dd class="col-6 fw-medium text-end mb-0">$598</dd>
              </dl>
            </div>
            <div class="d-grid">
              <button class="btn btn-primary btn-next">Realizar pedido</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Address -->
      <div id="checkout-address" class="content">
        <div class="row">
          <!-- Address left -->
          <div class="col-xl-8  col-xxl-9 mb-3 mb-xl-0">
            <div class="row mb-3">
              {{-- <div class="col-md mb-md-0 mb-2">
                <div class="form-check custom-option custom-option-basic checked">
                  <label class="form-check-label custom-option-content" for="customRadioAddress1">
                    <input name="customRadioTemp" class="form-check-input" type="radio" value="" id="customRadioAddress1" checked="">
                    <span class="custom-option-header mb-2">
                      <span class="fw-medium mb-0">Martín Santamaría (Por defecto)</span>
                      <span class="badge bg-label-primary">Casa</span>
                    </span>
                    <span class="custom-option-body">
                      <small>Rio Paraná M119 S2, Lagomar, Ciudad de la Costa<br> Teléfono: 099807750</small>
                      <span class="my-2 border-bottom d-block"></span>
                      <span class="d-flex">
                        <a class="me-2" href="javascript:void(0)">Editar</a> <a href="javascript:void(0)">Borrar</a>
                      </span>
                    </span>
                  </label>
                </div>
              </div>
              <div class="col-md">
                <div class="form-check custom-option custom-option-basic">
                  <label class="form-check-label custom-option-content" for="customRadioAddress2">
                    <input name="customRadioTemp" class="form-check-input" type="radio" value="" id="customRadioAddress2">
                    <span class="custom-option-header mb-2">
                      <span class="fw-medium mb-0">Sumeria</span>
                      <span class="badge bg-label-success">Oficina</span>
                    </span>
                    <span class="custom-option-body">
                      <small>Av. Luis Alberto de Herrera 2898 - World Trade Center,<br> Montevideo, Uruguay</small>
                      <span class="my-2 border-bottom d-block"></span>
                      <span class="d-flex">
                        <a class="me-2" href="javascript:void(0)">Editar</a> <a href="javascript:void(0)">Eliminar</a>
                      </span>
                    </span>
                  </label>
                </div>
              </div>
            </div>
            <button type="button" class="btn btn-label-primary mb-4" data-bs-toggle="modal" data-bs-target="#addNewAddress">Nueva dirección</button> --}}

            <div class="modal-body">
              <div class="text-center mb-4">
                <h3 class="address-title">Dirección de envío</h3>
              </div>
              <form id="addNewAddressForm" class="row g-3" onsubmit="return false">
                <div class="row">
                  <div class="col-12 col-md-6">
                    <label class="form-label" for="modalAddressFirstName">Nombre</label>
                    <input type="text" id="modalAddressFirstName" name="modalAddressFirstName" class="form-control" placeholder="John" />
                  </div>
                  <div class="col-12 col-md-6">
                    <label class="form-label" for="modalAddressLastName">Apellido</label>
                    <input type="text" id="modalAddressLastName" name="modalAddressLastName" class="form-control" placeholder="Doe" />
                  </div>
                </div>
                <div class="col-12 mt-2">
                  <label class="form-label" for="modalAddressAddress1">Dirección</label>
                  <input type="text" id="modalAddressAddress1" name="modalAddressAddress1" class="form-control" placeholder="12, Business Park" />
                </div>
                <div class="col-12 mt-2">
                  <label class="form-label" for="modalAddressLandmark">Barrio</label>
                  <input type="text" id="modalAddressState" name="modalAddressState" class="form-control" placeholder="California" />
                </div>
              </form>
            </div>
          </div>


            <!-- Choose Delivery -->
            <p>Seleccione tipo de entrega</p>
            <div class="row mt-2">
              <div class="col-md col-6 mb-md-0 mb-2">
                <div class="form-check custom-option custom-option-icon position-relative checked">
                  <label class="form-check-label custom-option-content" for="customRadioDelivery1">
                    <span class="custom-option-body">
                      <i class="bx bx-user bx-lg"></i>
                      <span class="custom-option-title mb-1">Retiro en el local</span>
                      <span class="badge bg-label-success btn-pinned">Gratis</span>
                      <small>Puedes retirarlo cuando desees</small>
                    </span>
                    <input name="customRadioIcon" class="form-check-input" type="radio" value="" id="customRadioDelivery1" checked="">
                  </label>
                </div>
              </div>
              <div class="col-md col-6 mb-md-0 mb-2">
                <div class="form-check custom-option custom-option-icon position-relative">
                  <label class="form-check-label custom-option-content" for="customRadioDelivery2">
                    <span class="custom-option-body">
                      <i class="bx bx-crown bx-lg"></i>
                      <span class="custom-option-title mb-1">Pedidos Ya</span>
                      <span class="badge bg-label-secondary btn-pinned">$60</span>
                      <small>Entrega estimada: 30 a 45 minutos</small>
                    </span>
                    <input name="customRadioIcon" class="form-check-input" type="radio" value="" id="customRadioDelivery2">
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- Address right -->
          <div class="col-xl-4 col-xxl-3">
            <div class="border rounded p-4 pb-3 mb-3">
              <!-- acaa -->
              <!-- Estimated Delivery -->
              <h6>Productos de la orden</h6>
              <ul class="list-unstyled">
                <li class="d-flex gap-3 align-items-center">
                  <div class="flex-shrink-0">
                    <img src="{{asset('assets/img/products/1.png')}}" alt="google home" class="w-px-50">
                  </div>
                  <div class="flex-grow-1">
                    <p class="mb-0"><a class="text-body" href="javascript:void(0)">Helado 1 Litro</a></p>
                    <p class="variaciones-carrito">Chocolate - Dulce de leche - Frutilla - Limón</p>
                  </div>
                </li>
                <li class="d-flex gap-3 align-items-center">
                  <div class="flex-shrink-0">
                    <img src="{{asset('assets/img/products/2.png')}}" alt="google home" class="w-px-50">
                  </div>
                  <div class="flex-grow-1">
                    <p class="mb-0"><a class="text-body" href="javascript:void(0)">Helado 2 Litros</a></p>
                    <p class="variaciones-carrito">Chocolate - Dulce de leche - Frutilla - Limón</p>
                  </div>
                </li>
              </ul>

              <hr class="mx-n4">

              <!-- Price Details -->
              <h6>Detalles del pago</h6>
              <dl class="row mb-0">

                <dt class="col-6 fw-normal">Productos</dt>
                <dd class="col-6 text-end">$598</dd>

                <dt class="col-6 fw-normal">Costos de envío</dt>
                <dd class="col-6 text-end"><s class="text-muted">$60</s> <span class="badge bg-label-success ms-1">Gratis</span></dd>

              </dl>
              <hr class="mx-n4">
              <dl class="row mb-0">
                <dt class="col-6">Total</dt>
                <dd class="col-6 fw-medium text-end mb-0">$598</dd>
              </dl>
            </div>
            <div class="d-grid">
              <button class="btn btn-primary btn-next">Confirmar pedido</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Payment -->
      <div id="checkout-payment" class="content">
        <div class="row">
          <!-- Payment left -->
          <div class="col-xl-8 col-xxl-9 mb-3 mb-xl-0">
            <!-- Offer alert -->
            <div class="alert alert-success" role="alert">
              <div class="d-flex">
                <span class="badge badge-center rounded-pill bg-success border-label-success p-3 me-2"><i class="bx bx-bookmarks fs-4"></i></span>
                <div class="flex-grow-1 ps-1">
                  <div class="fw-medium fs-5 mb-2">Descuentos disponibles</div>
                  <ul class="list-unstyled mb-0">
                    <li> - 10% de descuento con Santander Crédito / Débito</li>
                    <li> - 25% de reintegro con Itaú Platinum</li>
                  </ul>
                </div>
              </div>
              <button type="button" class="btn-close btn-pinned" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <!-- Payment Tabs -->
            <div class="col-xxl-6 col-lg-8">
              <ul class="nav nav-pills mb-3" id="paymentTabs" role="tablist">
                <li class="nav-item mx-2" role="presentation">
                  <button class="nav-link active" id="pills-cc-tab" data-bs-toggle="pill" data-bs-target="#pills-cc" type="button" role="tab" aria-controls="pills-cc" aria-selected="true">Tarjeta</button>
                </li>
                <li class="nav-item mx-2" role="presentation">
                  <button class="nav-link active" id="pills-cod-tab" data-bs-toggle="pill" data-bs-target="#pills-cod" type="button" role="tab" aria-controls="pills-cod" aria-selected="false">Efectivo</button>
                </li>
              </ul>
              <div class="tab-content px-0 border-0" id="paymentTabsContent">
                <!-- Credit card -->
                <div class="tab-pane fade show active" id="pills-cc" role="tabpanel" aria-labelledby="pills-cc-tab">
                  <div class="row g-3">
                    <div class="col-12">
                      <label class="form-label w-100" for="paymentCard">N° de tarjeta</label>
                      <div class="input-group input-group-merge">
                        <input id="paymentCard" name="paymentCard" class="form-control credit-card-mask" type="text" placeholder="1356 3215 6548 7898" aria-describedby="paymentCard2" />
                        <span class="input-group-text cursor-pointer p-1" id="paymentCard2"><span class="card-type"></span></span>
                      </div>
                    </div>
                    <div class="col-12 col-md-6">
                      <label class="form-label" for="paymentCardName">Nombre</label>
                      <input type="text" id="paymentCardName" class="form-control" placeholder="John Doe" />
                    </div>
                    <div class="col-6 col-md-3">
                      <label class="form-label" for="paymentCardExpiryDate">Vencimiento</label>
                      <input type="text" id="paymentCardExpiryDate" class="form-control expiry-date-mask" placeholder="MM/YY" />
                    </div>
                    <div class="col-6 col-md-3">
                      <label class="form-label" for="paymentCardCvv">CVV</label>
                      <div class="input-group input-group-merge">
                        <input type="text" id="paymentCardCvv" class="form-control cvv-code-mask" maxlength="3" placeholder="654" />
                        <span class="input-group-text cursor-pointer" id="paymentCardCvv2"><i class="bx bx-help-circle text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="Card Verification Value"></i></span>
                      </div>
                    </div>
                    <div class="col-12">
                      <label class="switch">
                        <input type="checkbox" class="switch-input">
                        <span class="switch-toggle-slider">
                          <span class="switch-on"></span>
                          <span class="switch-off"></span>
                        </span>
                        <span class="switch-label">¿Guardar tarjeta para tu próxima compra?</span>
                      </label>
                    </div>
                    <div class="col-12">
                      <button type="button" class="btn btn-primary btn-next me-sm-3 me-1">Pagar</button>
                      <button type="reset" class="btn btn-label-secondary">Cancelar</button>
                    </div>
                  </div>
                </div>

                <!-- COD -->
                <div class="tab-pane fade" id="pills-cod" role="tabpanel" aria-labelledby="pills-cod-tab">
                  <p>Cash on Delivery is a type of payment method where the recipient make payment for the order at the time of delivery rather than in advance.</p>
                  <button type="button" class="btn btn-primary btn-next">Pay On Delivery</button>
                </div>
              </div>
            </div>

          </div>
          <!-- Address right -->
          <div class="col-xl-4 col-xxl-3">
            <div class="border rounded p-4">

              <!-- Price Details -->
              <h6>Detalles del pago</h6>
              <dl class="row">

                <dt class="col-6 fw-normal">Productos</dt>
                <dd class="col-6 text-end">$598</dd>

                <dt class="col-6 fw-normal">Costos de envío</dt>
                <dd class="col-6 text-end"><s class="text-muted">$60</s> <span class="badge bg-label-success ms-1">Gratis</span></dd>
              </dl>
              <hr class="mx-n4">
              <dl class="row">
                <dt class="col-6 mb-3">Total</dt>
                <dd class="col-6 fw-medium text-end mb-0">$598</dd>

                <dt class="col-6 fw-normal">Enviar a:</dt>
                <dd class="col-6 fw-medium text-end mb-0"><span class="badge bg-label-primary">Casa</span></dd>
              </dl>
              <!-- Address Details -->
              <address class="text-heading">
                <span> Martín Santamaría,</span>
                Rio Paraná M119 S2,
                Ciudad de la Costa, Lagomar.<br>
                Teléfono: 099807750
              </address>
              <a href="javascript:void(0)">Cambiar dirección</a>
            </div>
          </div>
        </div>
      </div>

      <!-- Confirmation -->
      <div id="checkout-confirmation" class="content">
        <div class="row mb-3">
          <div class="col-12 col-lg-8 mx-auto text-center mb-3">
            <h4 class="mt-2">¡Gracias! 😇</h4>
            <p>¡El pedido <a href="javascript:void(0)">#1536548131</a> ha sido creado!</p>
            <p>Enviamos un correo a <a href="mailto:john.doe@example.com">msantamaria@mvdstudio.com.uy</a> con la confirmación de tu pedido y el recibo de pago.</p>
            <p><span class="fw-medium"><i class="bx bx-time-five me-1"></i> Fecha de creación:&nbsp;</span> 22/02/2024 14:17pm</p>
          </div>
          <!-- Confirmation details -->
          <div class="col-12">
            <ul class="list-group list-group-horizontal-md">
              <li class="list-group-item flex-fill p-4 text-heading">
                <h6 class="d-flex align-items-center gap-1"><i class="bx bx-map"></i> Envío</h6>
                <address class="mb-0">
                  Martín Santamaría <br />
                  Rio Paraná M119 S2,<br />
                  Lagomar, Ciudad de la Costa,<br />
                  Uruguay
                </address>
                <p class="mb-0 mt-3">
                  +59899807750
                </p>
              </li>
              <li class="list-group-item flex-fill p-4 text-heading">
                <h6 class="d-flex align-items-center gap-1"><i class="bx bx-credit-card"></i> Facturación</h6>
                <address class="mb-0">
                  Martín Santamaría <br />
                  Rio Paraná M119 S2,<br />
                  Lagomar, Ciudad de la Costa,<br />
                  Uruguay
                </address>
                <p class="mb-0 mt-3">
                  +59899807750
                </p>
              </li>
              <li class="list-group-item flex-fill p-4 text-heading">
                <h6 class="d-flex align-items-center gap-1"><i class="bx bxs-ship"></i> Método de envío</h6>
                Pedidos Ya<br />
                (Estimado 25 a 35 minutos)
              </li>
            </ul>
          </div>
        </div>

        <div class="row">
          <!-- Confirmation items -->
          <div class="col-xl-9 mb-3 mb-xl-0">
            <ul class="list-group">
              <li class="list-group-item p-4">
                <div class="d-flex gap-3">
                  <div class="flex-shrink-0">
                    <img src="{{asset('assets/img/products/1.png')}}" alt="google home" class="w-px-75">
                  </div>
                  <div class="flex-grow-1">
                    <div class="row">
                      <div class="col-md-8">
                        <a href="javascript:void(0)" class="text-body">
                          <p>Helado 1 Litro</p>
                        </a>
                      </div>
                      <div class="col-md-4">
                        <div class="text-md-end">
                          <div class="my-2 my-lg-4"><span class="text-primary">$299/</span><s class="text-muted">$359</s></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </li>
              <li class="list-group-item p-4">
                <div class="d-flex gap-3">
                  <div class="flex-shrink-0">
                    <img src="{{asset('assets/img/products/2.png')}}" alt="google home" class="w-px-75">
                  </div>
                  <div class="flex-grow-1">
                    <div class="row">
                      <div class="col-md-8">
                        <a href="javascript:void(0)" class="text-body">
                          <p>Helado 1 Litro</p>
                        </a>
                      </div>
                      <div class="col-md-4">
                        <div class="text-md-end">
                          <div class="my-2 my-lg-4"><span class="text-primary">$299/</span><s class="text-muted">$359</s></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </li>
            </ul>
          </div>
          <!-- Confirmation total -->
          <div class="col-xl-3">
            <div class="border rounded p-4 pb-3">
              <!-- Price Details -->
              <h6>Price Details</h6>
              <dl class="row mb-0">

                <dt class="col-6 fw-normal">Order Total</dt>
                <dd class="col-6 text-end">$1198.00</dd>

                <dt class="col-sm-6 fw-normal">Delivery Charges</dt>
                <dd class="col-sm-6 text-end"><s class="text-muted">$5.00</s> <span class="badge bg-label-success ms-1">Free</span></dd>
              </dl>
              <hr class="mx-n4">
              <dl class="row mb-0">
                <dt class="col-6">Total</dt>
                <dd class="col-6 fw-medium text-end mb-0">$1198.00</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
<!--/ Checkout Wizard -->
