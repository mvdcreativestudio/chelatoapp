@isset($pageConfigs)
{!! Helper::updatePageConfig($pageConfigs) !!}
@endisset
@php
$configData = Helper::appClasses();

$customizerHidden = ($customizerHidden ?? '');
@endphp

<!-- Page Styles -->
@section('page-style')
@vite(['resources/assets/css/landing.css'])
@endsection

@vite(['resources/assets/vendor/libs/spinkit/spinkit.scss'])


@extends('layouts/commonMaster' )

@section('layoutContent')

<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@include('content/landing/layouts/navbar')
@include('content/e-commerce/front/layouts/spinner')


<!-- Contenedor principal -->
<div class="d-flex flex-column min-vh-100">
    <!-- Contenido -->
    <div class="flex-grow-1">
        @yield('content')
        @include('content/landing/layouts/whatsapp-button')
    </div>
    <!--/ Contenido -->

    <!-- Footer -->
    <footer>
        @include('content/landing/layouts/footer')
    </footer>
    <!--/ Footer -->
</div>

@endsection


