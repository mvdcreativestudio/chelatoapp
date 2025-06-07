document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('rawMaterialForm');

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const previousPage = document.referrer;
        const formData = new FormData(form);

        $.ajax({
            url: `${window.location.origin}/admin/raw-materials`,
            type: 'POST',
            headers: {
                'X-Freemium-Request': 'true',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                Swal.fire(
                    'Éxito',
                    response.message,
                    'success'
                ).then(() => {
                    window.location.href = `${window.location.origin}/admin/products`;
                });
            },
            error: function (xhr, status, error) {
                let errorMessage = 'Ocurrió un error al crear la materia prima.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    }
                }
                Swal.fire(
                    'Error',
                    errorMessage,
                    'error'
                );
            }
        });
    });
});