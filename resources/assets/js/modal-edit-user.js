'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
  (function () {
    const editUserForm = document.getElementById('editClientForm');

    if (editUserForm) {
      editUserForm.addEventListener('submit', function (e) {
        e.preventDefault();
        submitEditClient();
      });
    }

    function submitEditClient() {
      const formData = new FormData(editUserForm);
      const clientId = editUserForm.getAttribute('data-client-id');

      // Verificar si el ID del cliente está presente
      if (!clientId) {
        return;
      }

      // Verificar que el token CSRF está en el DOM
      const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

      const url = `${window.baseUrl}admin/clients/${clientId}`;

      // Recopilar los datos del formulario
      const clientData = {
        name: formData.get('name'),
        lastname: formData.get('lastname'),
        email: formData.get('email'),
        phone: formData.get('phone'),
        address: formData.get('address'),
        city: formData.get('city'),
        country: formData.get('country')
      };

      // Enviar la solicitud AJAX
      fetch(url, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json', // Asegúrate de que Content-Type está presente y correcto
        },
        body: JSON.stringify(clientData) // Enviar los datos como JSON
      })
      .then(response => {
        return response.json();
      })
      .then(data => {
        if (data.success) {
          // Cierra el modal usando Bootstrap
          const modal = bootstrap.Modal.getInstance(document.getElementById('editUser'));
          modal.hide(); // Cerrar el modal

          Swal.fire({
            icon: 'success',
            title: 'Cliente Actualizado',
            text: data.message
          }).then(result => {
            window.location.reload(); // Recargar la página
          });
        } else {
          throw new Error(data.message || 'Error al actualizar el cliente');
        }
      })
      .catch(error => {
        console.error('Error en la solicitud:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error al actualizar',
          text: error.message
        });
      });
    }
  })();
});
