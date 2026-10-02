<?php
session_start();
include 'includes/db.php';
header('Content-Type: application/json');

$type = $_GET['type'] ?? '';
$allowed_types = ['bebida', 'papas', 'salsa'];

if (!in_array($type, $allowed_types)) {
    echo json_encode(['success' => false, 'extras' => []]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM extras WHERE type = ? ORDER BY name");
    $stmt->execute([$type]);
    $extras = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'extras' => $extras]);
    
} catch (Exception $e) {
    error_log("Get extras error: " . $e->getMessage());
    echo json_encode(['success' => false, 'extras' => []]);
}
?>
