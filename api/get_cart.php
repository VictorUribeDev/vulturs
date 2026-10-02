<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

try {
    include '../includes/db.php';

    $cart_items = [];
    $total = 0;

    // Recoger todos los IDs de extras y componentes
    $all_extra_ids = [];
    $all_component_ids = [];

    foreach ($_SESSION['cart'] as $item) {
        if (!empty($item['extras']) && is_array($item['extras'])) {
            $all_extra_ids = array_merge($all_extra_ids, $item['extras']);
        }
        if (!empty($item['components']) && is_array($item['components'])) {
            $all_component_ids = array_merge($all_component_ids, $item['components']);
        }
    }

    // Eliminar duplicados
    $all_extra_ids = array_unique($all_extra_ids);
    $all_component_ids = array_unique($all_component_ids);

    // Obtener precios de extras
    $extra_prices = [];
    if ($all_extra_ids) {
        $placeholders = implode(',', array_fill(0, count($all_extra_ids), '?'));
        $stmt = $pdo->prepare("SELECT id, price FROM extras WHERE id IN ($placeholders)");
        $stmt->execute($all_extra_ids);
        foreach ($stmt->fetchAll() as $row) {
            $extra_prices[$row['id']] = $row['price'];
        }
    }

    // Obtener precios de componentes
    $component_prices = [];
    if ($all_component_ids) {
        $placeholders = implode(',', array_fill(0, count($all_component_ids), '?'));
        $stmt = $pdo->prepare("SELECT id, price FROM sandwich_components WHERE id IN ($placeholders)");
        $stmt->execute($all_component_ids);
        foreach ($stmt->fetchAll() as $row) {
            $component_prices[$row['id']] = $row['price'];
        }
    }

    // Construir carrito
    foreach ($_SESSION['cart'] as $key => $item) {
        $price = is_numeric($item['price']) ? floatval($item['price']) : 0;
        $quantity = is_numeric($item['quantity']) ? intval($item['quantity']) : 1;
        $item_total = $price * $quantity;

        if (!empty($item['extras']) && is_array($item['extras'])) {
            foreach ($item['extras'] as $extra_id) {
                if (isset($extra_prices[$extra_id])) {
                    $item_total += $extra_prices[$extra_id] * $quantity;
                }
            }
        }

        if (!empty($item['components']) && is_array($item['components'])) {
            foreach ($item['components'] as $component_id) {
                if (isset($component_prices[$component_id])) {
                    $item_total += $component_prices[$component_id] * $quantity;
                }
            }
        }

        $cart_items[] = [
            'key' => $key,
            'item_id' => $item['item_id'],
            'name' => $item['name'],
            'size' => $item['size'],
            'price' => $price,
            'quantity' => $quantity,
            'extras' => $item['extras'] ?? [],
            'components' => $item['components'] ?? [],
            'category' => $item['category'],
            'subtotal' => round($item_total, 2)
        ];

        $total += $item_total;
    }

    echo json_encode([
        'success' => true,
        'items' => $cart_items,
        'total' => round($total, 2),
        'count' => count($_SESSION['cart'])
    ]);
} catch (Exception $e) {
    error_log("Get cart error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al obtener el carrito']);
}
?>
