<?php
session_start();
error_reporting(0); // Desactivar errores en producción
ini_set('display_errors', 0);

// Verificar carrito
if (empty($_SESSION['cart'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'El carrito está vacío']);
    exit;
}

// Obtener datos JSON
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

try {
    include 'includes/db.php';
    
    // Validar datos
    $required = ['customer_name', 'email', 'phone'];
    foreach ($required as $field) {
        if (empty(trim($input[$field] ?? ''))) {
            throw new Exception('Todos los campos son requeridos');
        }
    }
    
    $order_type = $input['order_type'] ?? 'pickup';
    $address = trim($input['address'] ?? '');
    
    if ($order_type === 'delivery' && empty($address)) {
        throw new Exception('La dirección es requerida para delivery');
    }
    
    // Calcular total
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $item_total = $item['price'] * $item['quantity'];
        
        if (!empty($item['extras'])) {
            foreach ($item['extras'] as $extra) {
                $item_total += $extra['price'] * $item['quantity'];
            }
        }
        
        if (!empty($item['components'])) {
            foreach ($item['components'] as $component) {
                $item_total += $component['price'] * $item['quantity'];
            }
        }
        
        $total += $item_total;
    }
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // Insertar pedido principal
    $stmt = $pdo->prepare("
        INSERT INTO orders (customer_name, email, phone, order_type, address, total, status) 
        VALUES (?, ?, ?, ?, ?, ?, 'pendiente')
    ");
    $stmt->execute([
        $input['customer_name'],
        $input['email'],
        $input['phone'],
        $order_type,
        $address,
        $total
    ]);
    $order_id = $pdo->lastInsertId();
    
    // Insertar detalles
    foreach ($_SESSION['cart'] as $item) {
        // Insertar ítem principal
        $stmt = $pdo->prepare("
            INSERT INTO order_details (
                order_id, item_id, quantity, price, is_extra, is_component
            ) VALUES (?, ?, ?, ?, 0, 0)
        ");
        $stmt->execute([
            $order_id,
            $item['id'] ?? null,
            $item['quantity'],
            $item['price']
        ]);
        $detail_id = $pdo->lastInsertId();
        
        // Insertar componentes
        if (!empty($item['components'])) {
            foreach ($item['components'] as $component) {
                $stmt = $pdo->prepare("
                    INSERT INTO order_details (
                        order_id, parent_id, item_id, quantity, price, 
                        is_component, component_name, component_category
                    ) VALUES (?, ?, ?, ?, ?, 1, ?, ?)
                ");
                $stmt->execute([
                    $order_id,
                    $detail_id,
                    $component['id'] ?? null,
                    $item['quantity'],
                    $component['price'],
                    $component['name'] ?? '',
                    $component['category'] ?? ''
                ]);
            }
        }
        
        // Insertar extras
        if (!empty($item['extras'])) {
            foreach ($item['extras'] as $extra) {
                $stmt = $pdo->prepare("
                    INSERT INTO order_details (
                        order_id, parent_id, item_id, quantity, price, is_extra
                    ) VALUES (?, ?, ?, ?, ?, 1)
                ");
                $stmt->execute([
                    $order_id,
                    $detail_id,
                    $extra['id'] ?? null,
                    $item['quantity'],
                    $extra['price']
                ]);
            }
        }
    }
    
    // Confirmar transacción
    $pdo->commit();
    
    // Limpiar carrito
    $_SESSION['cart'] = [];
    
    // Respuesta exitosa
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => 'Pedido creado exitosamente',
        'order_id' => $order_id
    ]);
    
} catch (Exception $e) {
    // Revertir en caso de error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Registrar error
    error_log("Error en process_order: " . $e->getMessage());
    
    // Respuesta de error
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Error al procesar el pedido: ' . $e->getMessage()
    ]);
}