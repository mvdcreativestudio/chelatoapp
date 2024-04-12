@php
$configData = Helper::appClasses();
@endphp

@extends('content.e-commerce.front.layouts.ecommerce-layout')

@section('title', 'Payment - Front Pages')

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
  <div id="wallet_container"></div>
</section>

<script src="https://sdk.mercadopago.com/js/v2"></script>

<script>
  // Obtener el ID de la preferencia del backend
  const preferenceId = "{{ $preferenceId }}";
  const publicKey = "{{ $publicKey }}";

  // Verificar si se recibió un ID de preferencia válido
  if (preferenceId) {
      // Si hay un ID de preferencia, inicializar el checkout con ese ID
      const mp = new MercadoPago(publicKey);

      // Crear el botón de pago en el contenedor con id "wallet_container"
      mp.bricks().create("wallet", "wallet_container", {
          initialization: {
              preferenceId: preferenceId,
          },
          customization: {
              texts: {
                  valueProp: 'smart_option',
              },
          },
      });
  } else {
      // Si no se recibe un ID de preferencia válido, mostrar un mensaje de error o realizar otra acción apropiada
      console.error("No se recibió un ID de preferencia válido del backend.");
  }
</script>


<!-- Modal -->
@include('_partials/_modals/modal-pricing')
<!-- /Modal -->
@endsection
