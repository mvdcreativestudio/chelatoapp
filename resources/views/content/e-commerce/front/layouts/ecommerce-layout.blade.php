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


@include('content/e-commerce/front/layouts/navbar')
@include('content/e-commerce/front/layouts/spinner')

<!-- Content -->

@yield('content')

<!--/ Content -->



@endsection

