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


  document.getElementById('invoicesEnabledSwitch').addEventListener('change', function() {
    var pymoFields = document.getElementById('pymoFields');
    if (this.checked) {
      pymoFields.style.display = 'block';
    } else {
      pymoFields.style.display = 'none';
    }
  });
});


document.addEventListener('DOMContentLoaded', function () {
  const peyaEnviosSwitch = document.getElementById('peyaEnviosSwitch');
  const peyaEnviosFields = document.getElementById('peyaEnviosFields');

  peyaEnviosSwitch.addEventListener('change', function() {
    if (this.checked) {
      peyaEnviosFields.style.display = 'block';
    } else {
      peyaEnviosFields.style.display = 'none';
    }
  }
 );
});
