document.addEventListener('DOMContentLoaded', function() {
    const dashboardImages = document.querySelectorAll('.img-fluid');
    
    dashboardImages.forEach(image => {
        image.style.cursor = 'pointer';
        image.addEventListener('click', () => {
            window.location.href = window.baseUrl + 'admin/integrations';
        });
    });
});