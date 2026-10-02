<?php
session_start();
header('Content-Type: application/json');

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

$item_id = (int)($input['item_id'] ?? 0);
$quantity = (int)($input['quantity'] ?? 1);
$size = $input['size'] ?? 'simple';
$extras = $input['extras'] ?? [];
$components = $input['components'] ?? [];

if ($item_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID o cantidad inválidos']);
    exit;
}

try {
    include '../includes/db.php';
    
    // Get item details
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?");
    $stmt->execute([$item_id]);
    $item = $stmt->fetch();
    
    if (!$item) {
        echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
        exit;
    }
    
    // Calculate price based on size
    $price = $size === 'doble' && $item['price_doble'] ? $item['price_doble'] : $item['price_simple'];
    
    if (!$price) {
        echo json_encode(['success' => false, 'message' => 'Precio no disponible para el tamaño seleccionado']);
        exit;
    }
    
    // Obtener detalles de extras
    $extra_details = [];
    foreach ($extras as $extra_id) {
        $stmt = $pdo->prepare("SELECT id, name, price FROM extras WHERE id = ?");
        $stmt->execute([$extra_id]);
        $extra = $stmt->fetch();
        if ($extra) {
            $extra_details[] = $extra;
        }
    }
    
    // Obtener detalles de componentes
    $component_details = [];
    foreach ($components as $component_id) {
        $stmt = $pdo->prepare("SELECT id, name, price FROM sandwich_components WHERE id = ?");
        $stmt->execute([$component_id]);
        $component = $stmt->fetch();
        if ($component) {
            $component_details[] = $component;
        }
    }
    
    // Create unique cart item key
    $cart_key = $item_id . '_' . $size . '_' . md5(json_encode($extras) . json_encode($components));
    
    // Check if item already exists in cart with same configuration
    if (isset($_SESSION['cart'][$cart_key])) {
        // Update quantity
        $_SESSION['cart'][$cart_key]['quantity'] += $quantity;
    } else {
        // Add new item to cart
        $_SESSION['cart'][$cart_key] = [
            'item_id' => $item_id,
            'name' => $item['name'],
            'size' => $size,
            'price' => $price,
            'quantity' => $quantity,
            'extras' => $extra_details,  // Almacenar detalles completos
            'components' => $component_details,  // Almacenar detalles completos
            'category' => $item['category']
        ];
    }
    
    // Calculate total items in cart
    $total_items = 0;
    foreach ($_SESSION['cart'] as $cart_item) {
        $total_items += $cart_item['quantity'];
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Producto agregado al carrito',
        'cart_count' => $total_items
    ]);
    
} catch (Exception $e) {
    error_log("Add to cart error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}
?>
