document.addEventListener('DOMContentLoaded', function() { 
    const produtosCarousel = document.querySelector('.produtos-carousel');
    const slides = document.querySelectorAll('.produtos-slide');
    const totalSlides = Math.ceil(slides.length / 3); // Número de "pantallas" completas que necesitamos mostrar
    let produtosCurrentIndex = 0;

    function rotateProdutosCarousel() {
        // Aumenta el índice de productos y reinicia si es necesario
        produtosCurrentIndex = (produtosCurrentIndex + 1) % totalSlides; // Cambia entre los grupos de 3 imágenes
        const translatePercentage = produtosCurrentIndex * 100 / totalSlides; // Ajusta el porcentaje basado en el número total de "pantallas"
        produtosCarousel.style.transform = `translateX(-${translatePercentage}%)`; // Cambia el translateX de acuerdo al número de grupos
    }

    setInterval(rotateProdutosCarousel, 5000); // Cambiar de imagen cada 5 segundos
});

document.addEventListener('DOMContentLoaded', function () {
    const prevBtn = document.getElementById('anjos-prevBtn');
    const nextBtn = document.getElementById('anjos-nextBtn');
    const carousel = document.getElementById('testimoniosCarousel');
    const items = carousel.querySelectorAll('.anjos-carousel-item');
    let currentIndex = 0;

    function updateCarousel() {
        // Asegurarse de que solo se muestre el elemento actual
        items.forEach((item, index) => {
            item.style.display = index === currentIndex ? 'flex' : 'none';
        });
    }

    prevBtn.addEventListener('click', function () {
        // Mover al testimonio anterior
        currentIndex = (currentIndex > 0) ? currentIndex - 1 : items.length - 1;
        updateCarousel();
    });

    nextBtn.addEventListener('click', function () {
        // Mover al siguiente testimonio
        currentIndex = (currentIndex < items.length - 1) ? currentIndex + 1 : 0;
        updateCarousel();
    });

    // Inicializar el carrusel mostrando solo el primer elemento
    updateCarousel();
});
