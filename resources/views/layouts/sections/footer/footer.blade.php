@php
$containerFooter = (isset($configData['contentLayout']) && $configData['contentLayout'] === 'compact') ? 'container-xxl' : 'container-fluid';
@endphp

<!-- Footer-->
<footer class="content-footer footer bg-footer-theme">
  <div class="{{ $containerFooter }} d-flex flex-wrap justify-content-end py-2 flex-md-row flex-column">
    <div class="mb-2 mb-md-0">
      Desarrollado por <a href="https://sumeria.com.uy"><b>Sumeria</b></a>
    </div>
  </div>
</footer>
<!--/ Footer-->
