<div class="whatsapp-button-layout-container">
  <div class="whatsapp-button-layout-content">
    @php
    // Elimina todos los caracteres no numéricos
    $phoneNumber = preg_replace('/\D/', '', $companySettings->phone);

    // Si el número comienza con "0", quítalo
    if (substr($phoneNumber, 0, 1) === '0') {
        $phoneNumber = substr($phoneNumber, 1);
    }
    @endphp
    <a href="https://wa.me/598{{ $phoneNumber }}" target="_blank" class="whatsapp-button-layout-element">
      <i class="fab fa-whatsapp whatsapp-icon-layout"></i>
    </a>
  </div>
</div>
