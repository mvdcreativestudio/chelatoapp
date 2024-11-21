document.addEventListener('DOMContentLoaded', () => {
    const filterButtons = document.querySelectorAll('.filter-button');
    const productsContainer = document.getElementById('products-container');
    const globalSpinner = document.getElementById('global-spinner');

    filterButtons.forEach(button => {
        button.addEventListener('click', function () {
            const categoryId = this.dataset.categoryId;

            // Elimina la clase activa de todos los botones
            filterButtons.forEach(btn => btn.classList.remove('active-category'));

            // Agrega la clase activa al botón clicado
            this.classList.add('active-category');

            // Muestra el spinner global
            globalSpinner.style.display = 'flex';

            const spinnerStartTime = Date.now(); // Registra el momento en que se muestra el spinner

            // Realiza la solicitud Ajax
            fetch(`filter-products/${categoryId || ''}`)
                .then(response => response.json())
                .then(data => {
                    // Actualiza el contenido de los productos
                    productsContainer.innerHTML = data.html;
                })
                .catch(error => {
                    console.error('Error al filtrar productos:', error);
                    productsContainer.innerHTML = '<p>Hubo un error al cargar los productos.</p>';
                })
                .finally(() => {
                    const elapsedTime = Date.now() - spinnerStartTime; // Calcula el tiempo transcurrido
                    const remainingTime = 300 - elapsedTime; // Calcula cuánto tiempo falta para llegar a 1 segundo

                    if (remainingTime > 0) {
                        // Si el tiempo transcurrido es menor a 1 segundo, espera el tiempo restante
                        setTimeout(() => {
                            globalSpinner.style.display = 'none';
                        }, remainingTime);
                    } else {
                        // Si ya pasó al menos 1 segundo, oculta el spinner inmediatamente
                        globalSpinner.style.display = 'none';
                    }
                });
        });
    });
});
