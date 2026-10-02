<?php
session_start();
header('Content-Type: application/json');

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

$cart_key = $input['key'] ?? '';
$quantity = (int)($input['quantity'] ?? 0);

if (empty($cart_key) || $quantity < 0) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

try {
    if (!isset($_SESSION['cart'][$cart_key])) {
        echo json_encode(['success' => false, 'message' => 'Producto no encontrado en el carrito']);
        exit;
    }
    
    if ($quantity === 0) {
        // Remove item from cart
        unset($_SESSION['cart'][$cart_key]);
        $message = 'Producto eliminado del carrito';
    } else {
        // Update quantity
        $_SESSION['cart'][$cart_key]['quantity'] = $quantity;
        $message = 'Cantidad actualizada';
    }
    
    // Calculate total items in cart
    $total_items = 0;
    foreach ($_SESSION['cart'] as $cart_item) {
        $total_items += $cart_item['quantity'];
    }
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'cart_count' => $total_items
    ]);
    
} catch (Exception $e) {
    error_log("Update cart error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el carrito']);
}
?>
