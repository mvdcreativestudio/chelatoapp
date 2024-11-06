'use strict';

$(function () {
    // Función para mostrar la confirmación de eliminación con SweetAlert
    window.confirmDelete = function() {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Se perderá toda la información de esta lista de precios.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form').submit();
            }
        });
    };

    // Cargar productos con AJAX al cargar la página
    $.ajax({
        url: productsUrl, // Define la URL en la vista
        method: 'GET',
        success: function(response) {
            var productsList = $('#productsList');
            productsList.empty();
            response.products.forEach(function(product) {
                var priceValue = product.price ? parseFloat(product.price).toFixed(2) : 'No asignado';
                productsList.append(`
                    <tr>
                        <td>${product.name}</td>
                        <td>${priceValue}</td>
                    </tr>
                `);
            });
        },
        error: function(xhr) {
            console.log('Error al cargar productos:', xhr);
        }
    });
});
