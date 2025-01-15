const galleryContainer = document.querySelector('.gallery-container');
const galleryImages = document.querySelectorAll('.gallery-image');
const prevButton = document.querySelector('.gallery-button.prev');
const nextButton = document.querySelector('.gallery-button.next');
let currentIndex = 0;

function showSlide(index) {
    const offset = index * -100;
    galleryContainer.style.transform = `translateX(${offset}%)`;
}

prevButton.addEventListener('click', () => {
    currentIndex = (currentIndex - 1 + galleryImages.length) % galleryImages.length;
    showSlide(currentIndex);
});

nextButton.addEventListener('click', () => {
    currentIndex = (currentIndex + 1) % galleryImages.length;
    showSlide(currentIndex);
});
