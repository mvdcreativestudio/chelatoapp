document.addEventListener('DOMContentLoaded', function() {
    const decrementButton = document.getElementById('decrement-btn');
    const incrementButton = document.getElementById('increment-btn');
    const quantityInput = document.getElementById('quantity-input');
    const addToOrderButton = document.getElementById('add-to-order-btn');
    const contactWhatsappButton = document.getElementById('contact-whatsapp-btn');

    // Obtén los datos del producto y teléfono desde los atributos de datos del botón "AÑADIR AL PEDIDO"
    const phoneNumber = addToOrderButton.dataset.phone;
    const productName = addToOrderButton.dataset.productName;

    // Evento para el botón de decremento
    decrementButton.addEventListener('click', function() {
        let currentValue = parseInt(quantityInput.value) || 1;
        if (currentValue > 1) {
            quantityInput.value = currentValue - 1;
        }
    });

    // Evento para el botón de incremento
    incrementButton.addEventListener('click', function() {
        let currentValue = parseInt(quantityInput.value) || 1;
        quantityInput.value = currentValue + 1;
    });

    // Evento para el botón "AÑADIR AL PEDIDO"
    addToOrderButton.addEventListener('click', function() {
        const quantity = quantityInput.value;
        const message = `Quisiera pedir ${quantity} ${quantity == 1 ? 'unidad' : 'unidades'} del producto ${productName}`;

        // Abre WhatsApp con el mensaje y el número configurado
        window.open(`https://wa.me/598${phoneNumber}?text=${encodeURIComponent(message)}`, '_blank');
    });

    // Evento para el botón "Contactar por WhatsApp"
    contactWhatsappButton.addEventListener('click', function() {
        const contactMessage = "Hola, me gustaría obtener más información sobre sus productos.";
        
        // Abre WhatsApp con el mensaje de contacto predefinido y el número configurado
        window.open(`https://wa.me/598${phoneNumber}?text=${encodeURIComponent(contactMessage)}`, '_blank');
    });
});
