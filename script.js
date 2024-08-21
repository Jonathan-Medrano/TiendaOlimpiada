document.addEventListener('DOMContentLoaded', () => {
    // Añadir producto al carrito
    const addToCartLinks = document.querySelectorAll('.products a');

    addToCartLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            const productId = this.getAttribute('href').split('=')[1];
            addToCart(productId);
        });
    });

    // Eliminar o disminuir la cantidad de un producto del carrito
    const removeFromCartLinks = document.querySelectorAll('.cart a');

    removeFromCartLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            const productId = this.getAttribute('href').split('=')[1];
            removeFromCart(productId);
        });
    });

    // Función para añadir al carrito
    function addToCart(productId) {
        // Aquí podrías agregar lógica para añadir al carrito usando AJAX o modificar el DOM directamente.
        console.log(`Producto ${productId} añadido al carrito`);

        // Simulación de actualización visual del carrito
        alert('Producto añadido al carrito');
        // Aquí puedes actualizar el contenido del carrito dinámicamente.
    }

    // Función para eliminar o disminuir cantidad en el carrito
    function removeFromCart(productId) {
        // Aquí podrías agregar lógica para eliminar o disminuir la cantidad del producto en el carrito usando AJAX o modificar el DOM directamente.
        console.log(`Producto ${productId} eliminado del carrito`);

        // Simulación de actualización visual del carrito
        alert('Producto eliminado del carrito');
        // Aquí puedes actualizar el contenido del carrito dinámicamente.
    }

    // Animación para botones de añadir al carrito
    const buttons = document.querySelectorAll('.login-register button, .products a');

    buttons.forEach(button => {
        button.addEventListener('mouseover', function() {
            this.style.backgroundColor = '#e60000'; // Rojo oscuro al pasar el mouse
        });

        button.addEventListener('mouseout', function() {
            this.style.backgroundColor = '#0056b3'; // Color original
        });
    });

    // Funcionalidad para togglear la vista del carrito (si deseas hacer un dropdown)
    const cartIcon = document.querySelector('.cart-icon');
    const cartDetails = document.querySelector('.cart');

    if (cartIcon && cartDetails) {
        cartIcon.addEventListener('click', () => {
            cartDetails.classList.toggle('visible');
        });
    }

    // Funcionalidad para redirección al checkout
    const buyCartButton = document.querySelector('.buy-cart');
    if (buyCartButton) {
        buyCartButton.addEventListener('click', function(event) {
            event.preventDefault();
            if (confirm('¿Está seguro de que desea comprar estos artículos?')) {
                window.location.href = 'checkout.php';
            }
        });
    }
});
