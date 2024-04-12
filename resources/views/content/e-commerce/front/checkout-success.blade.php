@php
$configData = Helper::appClasses();
@endphp

@extends('content.e-commerce.front.layouts.ecommerce-layout')

@section('title', 'Pedido Realizado')

@section('content')

<div class="container mt-5 vh-100">
  <div class="d-flex justify-content-center">
    <div class="card">
      <div id="checkout-confirmation" class="content">
        <div class="row m-4">
          <div class="col-12 col-lg-8 mx-auto text-center mb-3">
            <h4 class="mt-5">¡Gracias! 😇</h4>
            <p>¡El pedido #{{ $order->id }} ha sido creado!</p>
            <p>Enviamos un correo a <a href="mailto:{{ $order->client->email }}">{{ $order->client->email }}</a> con la confirmación de tu pedido y el recibo de pago.</p>
            <p><span class="fw-medium"><i class="bx bx-time-five me-1"></i> Fecha de creación:</span> {{ $order->created_at->format('d/m/Y - H:i')}}</p>
          </div>
          <!-- Confirmation details -->
          <div class="col-12">
            <ul class="list-group list-group-horizontal-md">
              <li class="list-group-item flex-fill p-4 text-heading">
                <h6 class="d-flex align-items-center gap-1"><i class="bx bx-map"></i> Envío</h6>
                <address class="mb-0">
                  {{$order->client->name}} {{$order->client->lastname}} <br />
                  {{$order->client->address}},<br />
                  {{$order->client->state}}, {{$order->client->country}}<br />
                </address>
                <p class="mb-0 mt-3">
                  +59899807750
                </p>
              </li>
              <li class="list-group-item flex-fill p-4 text-heading">
                <h6 class="d-flex align-items-center gap-1"><i class="bx bxs-plane"></i> Método de envío</h6>
                Pedidos Ya<br />
                (Estimado 25 a 35 minutos)
              </li>
            </ul>
          </div>
        </div>

        <div class="row m-4">
          <!-- Confirmation items -->
          <div class="col-xl-7 mb-3 mb-xl-0">
            <ul class="list-group">
              @foreach($order->products as $product)
              <li class="list-group-item p-4">
                  <div class="d-flex gap-3">
                      <div class="flex-shrink-0">
                          <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="w-px-100">
                      </div>
                      <div class="flex-grow-1">
                          <div class="row">
                              <div class="col-md-8">
                                  <a href="javascript:void(0)" class="text-body">
                                      <h6 class="mb-0">{{ $product->name }}</h6>
                                  </a>
                                  @if($product->flavors->count() > 0)
                                  <small class="mt-0">

                                    @foreach($product->flavors as $flavor)
                                      {{ $flavor->name }}
                                    @endforeach

                                  </small>
                                  @endif
                                  <p>Cantidad: {{ $product->pivot->quantity }}</p>
                              </div>
                              <div class="col-md-4">
                                  <div class="text-md-end">
                                      <div class="my-2 my-lg-4">
                                          @if($product->old_price)
                                          <s class="text-muted">${{ $product->old_price }}</s>
                                          @endif
                                          <span class="text-primary">${{ $product->pivot->price }}</span>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
              </li>
              @endforeach
            </ul>
        </div>

          <!-- Confirmation total -->
          <div class="col-xl-5">
            <div class="border rounded p-4 pb-3">
              <!-- Price Details -->
              <h6>Detalles del pago</h6>
              <dl class="row mb-0">

                <dt class="col-6 fw-normal">Subtotal</dt>
                <dd class="col-6 text-end">${{$order->subtotal}}</dd>

                <dt class="col-sm-6 fw-normal">Envío</dt>
                <dd class="col-sm-6 text-end"><s class="text-muted">$90</s> <span class="badge bg-label-success ms-1">${{$order->shipping}}</span></dd>
              </dl>
              <hr class="mx-n4">
              <dl class="row mb-0">
                <dt class="col-6">Total</dt>
                <dd class="col-6 fw-medium text-end mb-0">${{$order->total}}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection
