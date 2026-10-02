<?php
session_start();

try {
    // Verificar método
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    // Obtener datos
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        throw new Exception('Datos inválidos');
    }

    $components = $input['components'] ?? [];
    $extras = $input['extras'] ?? [];
    $quantity = (int)($input['quantity'] ?? 1);

    if (empty($components) || $quantity < 1) {
        throw new Exception('Debe seleccionar al menos un ingrediente');
    }

    // Incluir base de datos
    include __DIR__ . '/../includes/db.php';
    
    // Validar componentes
    $basePrice = 5000;
    $totalPrice = $basePrice;
    $ingredientNames = [];
    $component_details = [];
    
    foreach ($components as $component_id) {
        $stmt = $pdo->prepare("SELECT * FROM sandwich_components WHERE id = ?");
        $stmt->execute([$component_id]);
        $component = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$component) {
            throw new Exception("Componente inválido");
        }
        
        $totalPrice += $component['price'];
        $ingredientNames[] = $component['name'];
        $component_details[] = [
            'id' => $component['id'],
            'name' => $component['name'],
            'price' => $component['price'],
            'category' => $component['category']
        ];
    }
    
    // Validar extras
    $drinkNames = [];
    $extra_details = [];
    foreach ($extras as $extra_id) {
        $stmt = $pdo->prepare("SELECT * FROM extras WHERE id = ?");
        $stmt->execute([$extra_id]);
        $extra = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$extra) {
            throw new Exception("Extra inválido");
        }
        
        $drinkNames[] = $extra['name'];
        $extra_details[] = [
            'id' => $extra['id'],
            'name' => $extra['name'],
            'price' => $extra['price']
        ];
    }
    
    // Crear nombre único
    $sandwichName = 'Sandwich Personalizado (' . implode(', ', $ingredientNames) . ')';
    if (!empty($drinkNames)) {
        $sandwichName .= ' + ' . implode(', ', $drinkNames);
    }
    
    // Agregar al carrito
    $cart_key = 'custom_' . md5(serialize($components) . serialize($extras));
    
    $_SESSION['cart'][$cart_key] = [
        'id' => null,
        'name' => $sandwichName,
        'price' => $totalPrice,
        'quantity' => $quantity,
        'extras' => $extra_details,
        'components' => $component_details
    ];
    
    // Calcular total de ítems
    $total_items = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total_items += $item['quantity'];
    }
    
    // Respuesta exitosa
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => 'Sandwich agregado al carrito',
        'cart_count' => $total_items
    ]);
    
} catch (Exception $e) {
    // Respuesta de error
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}