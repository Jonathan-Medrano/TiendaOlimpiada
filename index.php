<?php
include 'db.php';
include 'functions.php';

// Manejo de formulario de login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $_SESSION['username'] = $username;
    } else {
        echo "Usuario o contraseña incorrectos";
    }
}
// Manejo de agregar pedido
if (isset($_GET['action']) && $_GET['action'] == 'order') {
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $product = getProductById($conn, $item['id']);
            $userId = getUserIdByUsername($conn, $_SESSION['username']); // Función que debes implementar en functions.php
            $quantity = $item['quantity'];
            $totalPrice = $product['price'] * $quantity;

            $sql = "INSERT INTO orders (user_id, product_id, quantity, total_price) VALUES ('$userId', '{$product['id']}', '$quantity', '$totalPrice')";
            $conn->query($sql);
        }

        // Vaciar el carrito después de realizar el pedido
        unset($_SESSION['cart']);
        echo "<script>alert('Pedido realizado con éxito');</script>";
    } else {
        echo "<script>alert('El carrito está vacío');</script>";
    }
}

// Manejo de formulario de registro
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "INSERT INTO users (username, password) VALUES ('$username', '$password')";
    if ($conn->query($sql) === TRUE) {
        echo "Registro exitoso. Por favor, inicie sesión.";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Manejo de agregar al carrito
if (isset($_GET['action']) && $_GET['action'] == 'add') {
    addToCart($_GET['id']);
}

// Manejo de eliminar o disminuir la cantidad de un producto del carrito
if (isset($_GET['action']) && $_GET['action'] == 'remove') {
    removeFromCart($_GET['id']);
}

// Manejo de agregar producto desde la sección de administración
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_action'])) {
    if ($_SESSION['username'] === 'admin') {
        $name = $_POST['name'];
        $price = $_POST['price'];
        $image_url = $_POST['image_url'];

        $sql = "INSERT INTO products (name, price, image_url) VALUES ('$name', '$price', '$image_url')";
        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Producto agregado exitosamente');</script>";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "No tienes permisos para agregar productos.";
    }
}

// Manejo de eliminación de producto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_product'])) {
    if ($_SESSION['username'] === 'admin') {
        $id = intval($_POST['id']); // Sanitizar el ID

        $sql = "DELETE FROM products WHERE id = $id";
        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Producto eliminado exitosamente');</script>";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "No tienes permisos para eliminar productos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/styles.css">
    <title>Tienda Web Simple</title>
</head>
<body>
<nav>
        <a href="index.php" class="logo">Tienda Web</a>
        <a href="index.php" class="active">Inicio</a>
        <?php if (isset($_SESSION['username'])): ?>
            <a href="checkout.php">Checkout</a>
            <a href="orders.php">Mis Pedidos</a> <!-- Enlace para pedidos del usuario -->
            <?php if ($_SESSION['username'] === 'admin'): ?>
                <a href="admin_orders.php">Administrar Pedidos</a> <!-- Enlace para administración de pedidos -->
            <?php endif; ?>
            <a href="logout.php" class="right">Logout (<?php echo $_SESSION['username']; ?>)</a>
        <?php else: ?>
            <a href="auth.php" class="right">Login/Registro</a>
        <?php endif; ?>
    </nav>
    <div class="container">
        <?php if (isset($_SESSION['username']) && $_SESSION['username'] === 'admin'): ?>
            <div class="admin-section">
                <h2>Agregar Producto</h2>
                <form method="POST" action="">
                    <input type="text" name="name" placeholder="Nombre del Producto" required>
                    <input type="text" name="price" placeholder="Precio" required>
                    <input type="text" name="image_url" placeholder="URL de la Imagen" required>
                </form>
                <br>
                <button class="admin-section-button" type="submit" name="admin_action">Agregar Producto</button>

            </div>
        <?php endif; ?>

        <div class="products">
            <h2>Productos Disponibles</h2>
            <ul>
                <?php
                $products = getProducts($conn);
                while ($product = $products->fetch_assoc()) {
                    echo "<li>";
                    echo "<img src='" . $product['image_url'] . "' alt='" . $product['name'] . "'>";
                    echo $product['name'] . " - $" . $product['price'];
                    echo " <a href='index.php?action=add&id=" . $product['id'] . "'>Agregar al Carrito</a>";

                    // Mostrar botón de eliminar solo para el admin
                    if (isset($_SESSION['username']) && $_SESSION['username'] === 'admin') {
                        echo " <form method='POST' action='' style='display:inline;'>";
                        echo "<input type='hidden' name='id' value='" . $product['id'] . "'>";
                        echo "<button class='admin-section-button' type='submit' name='delete_product' onclick=\"return confirm('¿Estás seguro de que quieres eliminar este producto?')\">Eliminar</button>";
                        echo "</form>";
                    }

                    echo "</li>";
                }
                ?>
            </ul>
        </div>
        <div class="cart">
            <h2>Carrito de Compras</h2>
            <ul>
                <?php
                if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
                    foreach ($_SESSION['cart'] as $item) {
                        $product = getProductById($conn, $item['id']);
                        echo "<li>" . $product['name'] . " - $" . $product['price'] . " x " . $item['quantity'];
                        echo " <a href='index.php?action=remove&id=" . $product['id'] . "'>Eliminar uno</a></li>";
                    }
                    echo "<br>";
                    echo "<br>";
                } else {
                    echo "<li></li>";
                    echo "<li>El carrito está vacío</li>";
                    echo "<li></li>";
                }
                ?>  
            </ul>
            <?php
            echo "<p>Total: $" . calculateCartTotal($conn) . "</p>"; ?>
            <br>
            <!-- Añadir botón de comprar carrito -->
            <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                    <a href="checkout.php" class="botoncompra">Comprar Carrito</a>
                <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php