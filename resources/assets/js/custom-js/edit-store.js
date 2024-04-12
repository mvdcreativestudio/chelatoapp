document.addEventListener('DOMContentLoaded', function () {
  const mercadoPagoSwitch = document.getElementById('mercadoPagoSwitch');
  const mercadoPagoFields = document.getElementById('mercadoPagoFields');

  // Verificar el estado inicial del switch al cargar la página
  const initialState = mercadoPagoSwitch.checked;

  if (initialState) {
      mercadoPagoFields.style.display = 'block';
  }

  mercadoPagoSwitch.addEventListener('change', function() {
      if (initialState && !this.checked) {
          // Mostrar Sweet Alert si MercadoPago estaba activado y se desactivó
          Swal.fire({
              title: '¿Estás seguro?',
              text: "Se perderán los datos de la vinculación con MercadoPago y deberá ser realizada nuevamente",
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'Sí, desactivar',
              cancelButtonText: 'Cancelar'
          }).then((result) => {
              if (result.isConfirmed) {
                  // Usuario confirma la acción
                  mercadoPagoFields.style.display = 'none';
              } else {
                  // Usuario cancela la acción, resetear el switch a su estado inicial
                  mercadoPagoSwitch.checked = true;
              }
          });
      } else {
          // Manejar la visibilidad de los campos normalmente si no se cumple la condición anterior
          mercadoPagoFields.style.display = this.checked ? 'block' : 'none';
      }
  });
});
