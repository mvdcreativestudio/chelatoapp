@isset($pageConfigs)
{!! Helper::updatePageConfig($pageConfigs) !!}
@endisset
@php
$configData = Helper::appClasses();

$customizerHidden = ($customizerHidden ?? '');
@endphp

@vite(['resources/assets/vendor/libs/spinkit/spinkit.scss'])



@extends('layouts/commonMaster' )

@section('layoutContent')

<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@include('catalogue/layouts/navbar-catalogue')
@include('content/e-commerce/front/layouts/spinner')

<!-- Contenedor principal -->
<div class="d-flex flex-column min-vh-100">
    <!-- Contenido -->
    <div class="flex-grow-1 pt-5">
        @yield('content')
    </div>
    <!--/ Contenido -->

    <!-- Footer -->
    <footer>
        @include('catalogue/layouts/footer-catalogue')
    </footer>
    <!--/ Footer -->
</div>

@endsection


