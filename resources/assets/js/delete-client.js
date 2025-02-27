document.getElementById('deleteClientButton').addEventListener('click', function () {
  const form = document.getElementById('deleteClientForm');
  const url = form.getAttribute('data-url');

  Swal.fire({
    title: '¿Estás seguro?',
    text: "¡No podrás revertir esta acción!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      fetch(url, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          Swal.fire(
            'Eliminado',
            data.message,
            'success'
          ).then(() => {
            // Redirigir al índice de clientes
            window.location.href = "/admin/clients";
          });
        } else {
          Swal.fire(
            'Error',
            data.message,
            'error'
          );
        }
      })
      .catch(error => {
        Swal.fire(
          'Error',
          'Ocurrió un problema al intentar eliminar el cliente.',
          'error'
        );
        console.error(error);
      });
    }
  });
});
