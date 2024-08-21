<?php
include 'db.php';
include 'functions.php';

if (!isset($_SESSION['username'])) {
    header("Location: auth.php");
    exit();
}

if (empty($_SESSION['cart'])) {
    echo "<script>alert('El carrito está vacío.'); window.location.href='index.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $country = $_POST['country'];
    $state = $_POST['state'];
    $city = $_POST['city'];
    $postal_code = $_POST['postal_code'];
    $street = $_POST['street'];
    $number = $_POST['number'];
    $apartment = isset($_POST['apartment']) ? $_POST['apartment'] : null;
    $floor = isset($_POST['floor']) ? $_POST['floor'] : null;
    $userId = getUserIdByUsername($conn, $_SESSION['username']);
    $total = calculateCartTotal($conn);
    $orderId = createOrder($conn, $userId, $total);
    addOrderItems($conn, $orderId, $_SESSION['cart']);
    unset($_SESSION['cart']);
    echo "<script>alert('Compra realizada con éxito. Serás redirigido a la página principal.'); window.location.href='index.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <link rel="stylesheet" href="styles/checkout.css">
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
        <h1>Completar Compra</h1>

        <div class="cart">
            <ul>
                <h2>Productos en el Carrito</h2>
                <?php
                foreach ($_SESSION['cart'] as $item) {
                    $product = getProductById($conn, $item['id']);
                    echo "<li>" . $product['name'] . " - $" . $product['price'] . " x " . $item['quantity'] . "</li>";
                }
                echo "<h3>Total: $" . calculateCartTotal($conn) . "</h3>";
                ?>
            </ul>
        </div>
        <form method="POST" action="">
            <h2>FORMULARIO DE ENVIO</h2>
            <input type="text" name="name" placeholder="Apellido" required>
            <input type="text" name="surname" placeholder="Nombre" required>
            <input type="text" name="country" placeholder="País" required>
            <input type="text" name="state" placeholder="Provincia" required>
            <input type="text" name="city" placeholder="Ciudad" required>
            <input type="number" name="postal_code" placeholder="Código Postal" required>
            <input type="text" name="street" placeholder="Calle" required>
            <input type="number" name="number" placeholder="Número" required>
            <input type="number" name="apartment" placeholder="Departamento (Opcional)">
            <input type="number" name="floor" placeholder="Piso (Opcional)">
            <button type="submit">Realizar Pedido</button>
        </form>
    </div>
</body>
</html>
