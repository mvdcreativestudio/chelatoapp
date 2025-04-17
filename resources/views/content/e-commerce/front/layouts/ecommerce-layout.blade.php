@isset($pageConfigs)
{!! Helper::updatePageConfig($pageConfigs) !!}
@endisset
@php
$configData = Helper::appClasses();

$customizerHidden = ($customizerHidden ?? '');
@endphp

@vite(['resources/assets/vendor/libs/spinkit/spinkit.scss'])



@extends('layouts/commonMaster' )

<!-- Meta Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '249833776197041');
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=249833776197041&ev=PageView&noscript=1"
/></noscript>
<!-- End Meta Pixel Code -->

@section('layoutContent')

<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>


@include('content/e-commerce/front/layouts/navbar')
@include('content/e-commerce/front/layouts/spinner')

<!-- Content -->

@yield('content')

<!--/ Content -->



@endsection

