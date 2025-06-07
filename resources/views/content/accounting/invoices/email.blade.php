@extends('layouts.email')

@section('title', 'Factura de Compra')

@section('header')
    <h1 class="text-primary text-center">¡Gracias por tu compra!</h1>
@endsection

@section('content')
    <div class="text-muted">
        <p>Adjunta encontrarás la factura de tu compra realizada.</p>
        <p>Para cualquier consulta, no dudes en responder a este correo.</p>
    </div>
@endsection

@section('footer')
    <div class="text-center text-muted">
        <p>Si tienes alguna duda, contacta con nosotros en: <a href="mailto:{{ $data['replyTo'] }}" class="text-primary">{{ $data['replyTo'] }}</a></p>
        {{-- <p class="small">{{ config('app.name') }} | {{ date('Y') }}</p> --}}
        <p class="small">{{ date('Y') }}</p>
    </div>
@endsection
