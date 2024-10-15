document.addEventListener('DOMContentLoaded', () => {
  window.toggleStoreStatusClosed = async function(storeId) {
    console.log(`Store ID: ${storeId}`);

    const url = `${window.baseUrl}admin/stores/${storeId}/toggle-store-status-closed`;

    try {
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
      });

      if (response.ok) {
        const data = await response.json();
        console.log('Estado de la empresa actualizado con éxito:', data);
        // Actualiza el DOM según sea necesario
        const storeCard = document.querySelector(`#store-${storeId}`);
        if (storeCard) {
          if (data.closed) {
            storeCard.classList.remove('card-border-shadow-success');
            storeCard.classList.add('card-border-shadow-danger');
            storeCard.querySelector('.status-text').textContent = 'Cerrada';
            storeCard.querySelector('.status-text').classList.remove('text-success');
            storeCard.querySelector('.status-text').classList.add('text-danger');
            storeCard.querySelector('.status-icon').classList.remove('bg-label-success');
            storeCard.querySelector('.status-icon').classList.add('bg-label-danger');
          } else {
            storeCard.classList.remove('card-border-shadow-danger');
            storeCard.classList.add('card-border-shadow-success');
            storeCard.querySelector('.status-text').textContent = 'Abierta';
            storeCard.querySelector('.status-text').classList.remove('text-danger');
            storeCard.querySelector('.status-text').classList.add('text-success');
            storeCard.querySelector('.status-icon').classList.remove('bg-label-danger');
            storeCard.querySelector('.status-icon').classList.add('bg-label-success');
          }
        }
        location.reload();
      } else {
        const errorData = await response.json();
        console.error('Error en la respuesta del servidor:', errorData);
      }
    } catch (error) {
      console.error('Hubo un problema al intentar cambiar el estado de la empresa:', error);
    }
  };
});
