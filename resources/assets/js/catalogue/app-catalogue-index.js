$('#search-input').on('keyup', function() {
    let query = $(this).val();

    // Realiza una solicitud AJAX a /catalogue/search sin importar si query está vacío
    $.ajax({
        url: `/catalogue/search`,
        type: 'GET',
        data: { query: query }, // query puede estar vacío o tener texto
        success: function(data) {
            const productsContainer = $('#products-container');
            productsContainer.empty();

            if (data.products.length > 0) {
                data.products.forEach(product => {
                    let priceDisplay = '';
                    if (product.old_price && product.price) {
                        priceDisplay = `<span class="text-muted text-decoration-line-through">$${parseFloat(product.old_price).toFixed(2)}</span>
                                        <span>$${parseFloat(product.price).toFixed(2)}</span>`;
                    } else if (product.old_price && !product.price) {
                        priceDisplay = `$${parseFloat(product.old_price).toFixed(2)}`;
                    } else if (product.price) {
                        priceDisplay = `<span class="fw-bold">$${parseFloat(product.price).toFixed(2)}</span>`;
                    }

                    productsContainer.append(`
                        <div class="col-xl-2 col-md-3 col-sm-6 col-12 mb-4">
                            <div class="card product">
                                <img src="${product.image}" class="card-img-top" alt="${product.name}">
                                <div class="card-body">
                                    <h5 class="card-title text-center">${product.name}</h5>
                                    <p class="card-text text-center">${priceDisplay}</p>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="/catalogue/${product.id}" class="btn btn-outline-primary btn-sm add-to-cart">Ver producto</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);
                });
            } else {
                productsContainer.html(`<p class="text-center">No se encontraron productos.</p>`);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
        }
    });
});
