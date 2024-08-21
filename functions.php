<?php
session_start();

// Función para obtener el ID de usuario por nombre de usuario
function getUserIdByUsername($conn, $username) {
    $sql = "SELECT id FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['id'];
    } else {
        return null;
    }
}

// Agregar otras funciones aquí...

// Crear un pedido en la base de datos
function createOrder($conn, $userId, $total) {
    $sql = "INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'En preparación')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('id', $userId, $total);
    $stmt->execute();
    return $stmt->insert_id;
}

// Agregar los productos al pedido
function addOrderItems($conn, $orderId, $cart) {
    foreach ($cart as $item) {
        $product = getProductById($conn, $item['id']);
        $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iiid', $orderId, $item['id'], $item['quantity'], $product['price']);
        $stmt->execute();
    }
}

// Obtener los pedidos de un usuario por ID
function getOrdersByUserId($conn, $userId) {
    $sql = "SELECT * FROM orders WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    return $stmt->get_result();
}

// Obtener los productos de un pedido por ID de pedido
function getOrderItems($conn, $orderId) {
    $sql = "SELECT * FROM order_items WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    return $stmt->get_result();
}

// Agregar producto al carrito (incrementa cantidad si ya existe)
function addToCart($productId) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity'] += 1;
    } else {
        $_SESSION['cart'][$productId] = ['id' => $productId, 'quantity' => 1];
    }
}

// Eliminar o disminuir la cantidad de un producto del carrito
function removeFromCart($productId) {
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity'] -= 1;
        if ($_SESSION['cart'][$productId]['quantity'] <= 0) {
            unset($_SESSION['cart'][$productId]);
        }
    }
}

// Obtener productos desde la base de datos
function getProducts($conn) {
    $sql = "SELECT * FROM products";
    $result = $conn->query($sql);
    return $result;
}

// Obtener detalles del producto por ID
function getProductById($conn, $id) {
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Calcular el total del carrito
function calculateCartTotal($conn) {
    $total = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $product = getProductById($conn, $item['id']);
            if ($product) {
                $total += $product['price'] * $item['quantity'];
            }
        }
    }
    return $total;
}
// Actualizar el estado del pedido
function updateOrderStatus($conn, $orderId, $status) {
    $sql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $status, $orderId);
    $stmt->execute();
    return $stmt->affected_rows > 0;
}
// Obtener todos los pedidos
function getAllOrders($conn) {
    $sql = "SELECT * FROM orders";
    $result = $conn->query($sql);
    return $result;
}

?>
