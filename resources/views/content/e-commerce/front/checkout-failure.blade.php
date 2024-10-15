@php
$configData = Helper::appClasses();
@endphp

@extends('content.e-commerce.front.layouts.ecommerce-layout')

@section('title', 'Pedido Fallido')

@section('content')

<div class="container mt-5 vh-100">
  <div class="d-flex justify-content-center">
    <div class="card">
      <div id="checkout-confirmation" class="content">
        <div class="row m-4">
          <div class="col-12 col-lg-8 mx-auto text-center mb-3">
            <h4 class="mt-5">¡Tenemos un problema! 😓</h4>
            <p>¡El pedido #{{ $order->id }} ha fallado!</p>
            <p>Lamentablemente, tu pago no ha podido ser procesado, pero no te preocupes, puedes intentarlo nuevamente</p>
            <a href="{{route('shop')}}" class="btn btn-primary">Regresar a la tienda</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection
