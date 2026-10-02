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

if (empty($cart_key)) {
    echo json_encode(['success' => false, 'message' => 'Clave del producto inválida']);
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
    
    // Remove item from cart
    unset($_SESSION['cart'][$cart_key]);
    
    // Calculate total items in cart
    $total_items = 0;
    foreach ($_SESSION['cart'] as $cart_item) {
        $total_items += $cart_item['quantity'];
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Producto eliminado del carrito',
        'cart_count' => $total_items
    ]);
    
} catch (Exception $e) {
    error_log("Remove from cart error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al eliminar el producto']);
}
?>
