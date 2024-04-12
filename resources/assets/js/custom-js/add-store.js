document.addEventListener('DOMContentLoaded', function () {
  const mercadoPagoSwitch = document.getElementById('mercadoPagoSwitch');
  const mercadoPagoFields = document.getElementById('mercadoPagoFields');

  mercadoPagoSwitch.addEventListener('change', function() {
      if (this.checked) {
          mercadoPagoFields.style.display = 'block';
      } else {
          mercadoPagoFields.style.display = 'none';
      }
  });
});
