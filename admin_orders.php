<?php
include 'db.php';
include 'functions.php';
// Iniciar sesión solo si no está ya activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Asegúrate de que el usuario sea admin
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Buscar pedidos por username o ID
$searchQuery = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search'])) {
    $searchBy = $_POST['search_by'];
    $searchValue = $_POST['search_value'];
    if ($searchBy === 'username') {
        $userId = getUserIdByUsername($conn, $searchValue);
        $searchQuery = "SELECT * FROM orders WHERE user_id = '$userId'";
    } else {
        $searchQuery = "SELECT * FROM orders WHERE id = '$searchValue'";
    }
} else {
    $searchQuery = "SELECT * FROM orders";
}

$ordersResult = $conn->query($searchQuery);

// Modificar estado del pedido
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $orderId = intval($_POST['order_id']);
    $newStatus = $_POST['status'];
    if (updateOrderStatus($conn, $orderId, $newStatus)) {
        echo "<script>alert('Estado del pedido actualizado exitosamente');</script>";
    } else {
        echo "Error al actualizar el estado del pedido.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/styles.css">
    <title>Admin - Pedidos</title>
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
        <h2>Administrar Pedidos</h2>

        <form method="POST" action="">
            <label  for="search_by">Buscar por:</label>
            <select class="input-orders" name="search_by" id="search_by">
                <option value="username">Nombre de Usuario</option>
                <option value="order_id">ID del Pedido</option>
            </select>
            <input class="input-orders" type="text" name="search_value" placeholder="Valor de búsqueda" required>
            <button class="button-orders" type="submit" name="search">Buscar</button>
        </form>

        <div class="orders">
            <h3>Pedidos</h3>
            <ul>
                <?php if ($ordersResult->num_rows > 0): ?>
                    <?php while ($order = $ordersResult->fetch_assoc()): ?>
                        <li>
                            <h4>Pedido #<?php echo $order['id']; ?></h4>
                            <p>Total: $<?php echo $order['total']; ?></p>
                            <p>Estado: <span class="<?php echo 'status-' . strtolower(str_replace(' ', '-', $order['status'])); ?>"><?php echo $order['status']; ?></span></p>
                            <form method="POST" action="">
                                <input class="input-orders" type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select class="input-orders" name="status">
                                    <option value="En preparación" <?php if ($order['status'] == 'En preparación') echo 'selected'; ?>>En preparación</option>
                                    <option value="En camino" <?php if ($order['status'] == 'En camino') echo 'selected'; ?>>En camino</option>
                                    <option value="Entregado" <?php if ($order['status'] == 'Entregado') echo 'selected'; ?>>Entregado</option>
                                </select>
                                <button class="button-orders" type="submit" name="update_status" >Actualizar Estado</button>
                            </form>
                            <ul>
                                <?php
                                $orderItems = getOrderItems($conn, $order['id']);
                                while ($item = $orderItems->fetch_assoc()):
                                    $product = getProductById($conn, $item['product_id']);
                                ?>
                                    <li><?php echo $product['name']; ?> - $<?php echo $item['price']; ?> x <?php echo $item['quantity']; ?></li>
                                <?php endwhile; ?>
                            </ul>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li>No se encontraron pedidos.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</body>
</html>
