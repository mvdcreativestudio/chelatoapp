document.addEventListener('DOMContentLoaded', function () {
    // Configuración de Toastr
    toastr.options = {
        closeButton: true,
        progressBar: true,
        newestOnTop: true,
        positionClass: 'toast-top-right',
        showEasing: 'swing',
        hideEasing: 'linear',
        showMethod: 'fadeIn',
        hideMethod: 'fadeOut',
        showDuration: 300,
        hideDuration: 1000,
        timeOut: 2000,
        extendedTimeOut: 1000
    };

    const galleryInput = document.getElementById('galleryImages');
    const galleryPreview = document.getElementById('galleryPreview');

    if (galleryInput && galleryPreview) {
        galleryInput.addEventListener('change', function () {
            galleryPreview.innerHTML = ''; // Limpia las previsualizaciones anteriores
            Array.from(this.files).forEach(file => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const col = document.createElement('div');
                        col.className = 'col-12 col-sm-6 col-md-4 col-lg-3'; // Grilla responsiva
                        col.innerHTML = `
                            <div class="card shadow-sm border-0 w-100 text-center">
                                <div class="position-relative d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <img src="${e.target.result}" class="card-img-top rounded mx-auto d-block" alt="Nueva imagen" style="max-height: 200px; object-fit: cover; width: 100%;">
                                    <button type="button" class="btn btn-sm btn-danger remove-image position-absolute top-0 end-0 m-2" style="border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </div>
                                <div class="card-body text-center">
                                    <span class="text-muted">Nueva</span>
                                </div>
                            </div>`;
                        galleryPreview.appendChild(col);
                    };
                    reader.readAsDataURL(file);
                }
            });
        });

        // Eliminar imágenes existentes con AJAX
        galleryPreview.addEventListener('click', function (e) {
            const deleteButton = e.target.closest('.remove-image');
            if (deleteButton) {
                const url = deleteButton.getAttribute('data-url'); // Obtén la URL completa desde el atributo data-url
                if (url && confirm('¿Desea eliminar esta imagen?')) {
                    fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                deleteButton.closest('.col-12').remove(); // Elimina el contenedor de la imagen
                                toastr.success(data.message);
                            } else {
                                toastr.error(data.message || 'Error al eliminar la imagen');
                            }
                        })
                        .catch(err => console.error('Error:', err));
                }
            }
        });
    }
});
