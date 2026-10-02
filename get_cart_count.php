<?php
session_start();
header('Content-Type: application/json');

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

try {
    // Count total items in cart
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['quantity'] ?? 1;
    }

    echo json_encode(['success' => true, 'count' => $count]);
    
} catch (Exception $e) {
    error_log("Get cart count error: " . $e->getMessage());
    echo json_encode(['success' => false, 'count' => 0]);
}
?>
