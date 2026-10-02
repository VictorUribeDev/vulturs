<?php
session_start();
ob_start();

// Check admin authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

include 'includes/header.php';
include '../includes/db.php';

// Handle order status update
if (isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status'];

    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        header("Location: dashboard.php?success=status_updated");
        exit;
    } catch (Exception $e) {
        header("Location: dashboard.php?error=update_failed");
        exit;
    }
}

// Handle order deletion
if (isset($_POST['delete_order'])) {
    $order_id = (int)$_POST['order_id'];
    
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("DELETE FROM order_details WHERE order_id = ?");
        $stmt->execute([$order_id]);
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
        $pdo->commit();
        header("Location: dashboard.php?success=order_deleted");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: dashboard.php?error=delete_failed");
        exit;
    }
}

// Get orders
$stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 10");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get order details for each order
$order_details = [];
foreach ($orders as $order) {
    // Reemplazar la consulta actual con esta versión mejorada
$stmt = $pdo->prepare("
    SELECT od.*, 
           CASE 
               WHEN od.is_extra = 1 THEN e.name
               WHEN od.is_component = 1 THEN od.component_name
               ELSE mi.name
           END AS name,
           od.is_combo_drink,
           mi.id AS menu_item_id
    FROM order_details od
    LEFT JOIN menu_items mi 
        ON od.item_id = mi.id AND od.is_extra = 0 AND od.is_component = 0
    LEFT JOIN extras e 
        ON od.item_id = e.id AND od.is_extra = 1
    WHERE od.order_id = ?
");


    $stmt->execute([$order['id']]);
    $order_details[$order['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get components for custom sandwiches
$componentes_por_detalle = [];
foreach ($order_details as $pedido_id => $detalles) {
    foreach ($detalles as $detalle) {
        // Improved sandwich detection
        $is_sandwich = ($detalle['menu_category'] === 'sandwiches') || 
                        ($detalle['menu_item_id'] == 6);  // ID 6 is custom sandwich
        
        if ($is_sandwich && !$detalle['is_component'] && !$detalle['is_extra']) {
            $stmt = $pdo->prepare("
                SELECT component_name, component_category 
                FROM order_details 
                WHERE parent_id = ? AND is_component = 1
            ");
            $stmt->execute([$detalle['id']]);
            $componentes_por_detalle[$detalle['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}

// Get statistics
$stats = $pdo->query("
    SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'pendiente' THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN status = 'completado' THEN 1 ELSE 0 END) as completed_orders,
        SUM(CASE WHEN status = 'cancelado' THEN 1 ELSE 0 END) as cancelled_orders,
        SUM(CASE WHEN status IN ('en preparación', 'en camino') THEN 1 ELSE 0 END) as in_progress_orders
    FROM orders
")->fetch(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </h1>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i>
                <?php if ($_GET['success'] === 'status_updated'): ?>
                    Estado del pedido actualizado correctamente.
                <?php elseif ($_GET['success'] === 'order_deleted'): ?>
                    Pedido eliminado correctamente.
                <?php endif; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i>
                <?php if ($_GET['error'] === 'update_failed'): ?>
                    Error al actualizar el estado del pedido.
                <?php elseif ($_GET['error'] === 'delete_failed'): ?>
                    Error al eliminar el pedido.
                <?php endif; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?= $stats['total_orders'] ?></div>
                    <div class="stat-label">Pedidos Totales</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?= $stats['pending_orders'] ?></div>
                    <div class="stat-label">Pendientes</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-cog"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?= $stats['in_progress_orders'] ?></div>
                    <div class="stat-label">En Proceso</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?= $stats['completed_orders'] ?></div>
                    <div class="stat-label">Completados</div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-receipt"></i> Pedidos Recientes
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($orders)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5>No hay pedidos</h5>
                        <p class="text-muted">No se han registrado pedidos aún.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($orders as $order): 
                        $status_class = 'status-' . str_replace(' ', '-', $order['status']);
                    ?>
                    <div class="order-card" id="order-<?= $order['id'] ?>">
                        <div class="order-header">
                            <div>
                                <div class="order-id">Pedido #<?= $order['id'] ?></div>
                                <div class="order-date"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></div>
                            </div>
                            <div class="order-status <?= $status_class ?>"><?= ucfirst($order['status']) ?></div>
                        </div>

                        <div class="order-body">
                            <div class="customer-info">
                                <h3><i class="fas fa-user"></i> Información del Cliente</h3>
                                <div class="info-group">
                                    <div class="info-label">Nombre:</div>
                                    <div class="info-value"><?= htmlspecialchars($order['customer_name']) ?></div>
                                </div>
                                <div class="info-group">
                                    <div class="info-label">Email:</div>
                                    <div class="info-value"><?= htmlspecialchars($order['email']) ?></div>
                                </div>
                                <div class="info-group">
                                    <div class="info-label">Teléfono:</div>
                                    <div class="info-value"><?= htmlspecialchars($order['phone']) ?></div>
                                </div>
                                <div class="info-group">
                                    <div class="info-label">Tipo:</div>
                                    <div class="info-value"><?= htmlspecialchars($order['order_type']) ?></div>
                                </div>
                                <?php if ($order['order_type'] === 'delivery'): ?>
                                <div class="info-group">
                                    <div class="info-label">Dirección:</div>
                                    <div class="info-value"><?= htmlspecialchars($order['address']) ?></div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="order-summary">
                                <h3><i class="fas fa-list"></i> Resumen del Pedido</h3>
                                <div class="order-items">
                                    <ul class="item-list">
                                        <?php foreach ($order_details[$order['id']] as $detail): ?>
                                        <!-- In the display section: -->
<li class="item">
    <div class="item-name">
        <!-- Add combo identification -->
        <?php if ($detail['is_combo_item']): ?>
            <span class="combo-badge">COMBO</span>
        <?php endif; ?>
        
        <?= htmlspecialchars($detail['display_name']) ?>
        
        <?php if ($detail['is_extra']): ?>
            <?php if ($detail['is_combo_drink'] == 1): ?>
                <span class="item-extra">(bebida del combo)</span>
            <?php else: ?>
                <span class="item-extra">(extra)</span>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Custom sandwich components -->
        <?php if (isset($componentes_por_detalle[$detail['id']])): ?>
            <div class="sandwich-components">
                <strong>Sandwich Personalizado:</strong>
                <?php foreach ($componentes_por_detalle[$detail['id']] as $component): ?>
                    <div class="component">
                        <?= htmlspecialchars($component['component_name']) ?> 
                        (<?= $component['component_category'] ?>)
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Combo included drink -->
        <?php if ($detail['is_combo_item'] && !$detail['is_component'] && !$detail['is_extra']): ?>
            <?php
                $comboStmt = $pdo->prepare("
                    SELECT e.name 
                    FROM combos c
                    JOIN extras e ON e.id = c.included_drink_id
                    WHERE c.menu_item_id = ?
                ");
                $comboStmt->execute([$detail['menu_item_id']]);
                $comboDrink = $comboStmt->fetchColumn();
                
                if ($comboDrink) {
                    echo '<div class="combo-drink">';
                    echo '<span class="item-extra">Bebida incluida: ' . htmlspecialchars($comboDrink) . '</span>';
                    echo '</div>';
                }
            ?>
        <?php endif; ?>
    </div>
    <div>
        <span class="item-quantity"><?= $detail['quantity'] ?>x</span>
        <span class="item-price">$<?= number_format($detail['price'], 0, ',', '.') ?></span>
    </div>
</li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <div class="order-total">
                                    <div>Total:</div>
                                    <div>$<?= number_format($order['total'], 0, ',', '.') ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="order-footer">
                            <div class="d-flex gap-2">
                                <form method="POST" class="status-form">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <select name="status" class="status-select form-select form-select-sm">
                                        <option value="pendiente" <?= $order['status'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                        <option value="en preparación" <?= $order['status'] === 'en preparación' ? 'selected' : '' ?>>En preparación</option>
                                        <option value="en camino" <?= $order['status'] === 'en camino' ? 'selected' : '' ?>>En camino</option>
                                        <option value="completado" <?= $order['status'] === 'completado' ? 'selected' : '' ?>>Completado</option>
                                        <option value="cancelado" <?= $order['status'] === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                                    </select>
                                    <button type="submit" name="update_status" class="update-btn btn btn-primary btn-sm">
                                        <i class="fas fa-save"></i> Actualizar
                                    </button>
                                </form>
                                
                                <button class="btn btn-primary" onclick="exportOrderToCSV(<?= $order['id'] ?>)">
                                    <i class="fas fa-file-export"></i> Exportar CSV
                                </button>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <form method="POST" onsubmit="return confirm('¿Estás seguro?');">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <button type="submit" name="delete_order" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash-alt"></i> Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert.classList.contains('show')) {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }
        }, 5000);
    });
});
function exportOrderToCSV(orderId) {
    try {
        const card = document.getElementById(`order-${orderId}`);
        if (!card) {
            throw new Error('No se encontró el pedido');
        }

        // Función segura para obtener texto
        const safeText = (element, selector, defaultValue = '') => {
            const el = element.querySelector(selector);
            return el ? el.textContent.trim() : defaultValue;
        };

        // Obtener información del cliente
        const customerName = safeText(card, '.customer-info .info-value:nth-child(1)');
        const email = safeText(card, '.customer-info .info-value:nth-child(2)');
        const phone = safeText(card, '.customer-info .info-value:nth-child(3)');
        const orderType = safeText(card, '.customer-info .info-value:nth-child(4)');
        const address = safeText(card, '.customer-info .info-value:nth-child(5)', '');
        
        // Obtener detalles del pedido
        const orderIdText = safeText(card, '.order-id', '').replace('Pedido #', '');
        const orderDate = safeText(card, '.order-date');
        const status = safeText(card, '.order-status');
        const totalElement = card.querySelector('.order-total div:last-child');
        const total = totalElement ? totalElement.textContent.trim() : '0';
        
        // Construir contenido CSV
        let csvContent = "data:text/csv;charset=utf-8,";
        csvContent += "INFORMACIÓN DEL CLIENTE\n";
        csvContent += `Nombre,${escapeCSV(customerName)}\n`;
        csvContent += `Email,${escapeCSV(email)}\n`;
        csvContent += `Teléfono,${escapeCSV(phone)}\n`;
        csvContent += `Tipo de pedido,${escapeCSV(orderType)}\n`;
        
        if (address) {
            csvContent += `Dirección,${escapeCSV(address)}\n`;
        }
        
        csvContent += "\nDETALLES DEL PEDIDO\n";
        csvContent += `ID Pedido,${escapeCSV(orderIdText)}\n`;
        csvContent += `Fecha,${escapeCSV(orderDate)}\n`;
        csvContent += `Estado,${escapeCSV(status)}\n`;
        csvContent += `Total,${escapeCSV(total)}\n`;
        
        csvContent += "\nPRODUCTOS\n";
        csvContent += "Nombre,Cantidad,Precio,Componentes/Extras\n";
        
        // Recorrer todos los items del pedido
        const items = card.querySelectorAll('.item-list .item');
        items.forEach(item => {
            // Nombre principal con manejo de seguridad
            const nameElement = item.querySelector('.item-name');
            let name = '';
            if (nameElement) {
                // Clonar el elemento para no alterar el original
                const clone = nameElement.cloneNode(true);
                // Eliminar los elementos de extras y componentes
                const extras = clone.querySelectorAll('.item-extra, .sandwich-components, .combo-drink');
                extras.forEach(extra => extra.remove());
                name = clone.textContent.trim();
            }
            
            const quantity = safeText(item, '.item-quantity', '1');
            const price = safeText(item, '.item-price', '0');
            
            let extras = [];
            
            // Extraer componentes de sándwich
            const componentsContainer = item.querySelector('.sandwich-components');
            if (componentsContainer) {
                const components = componentsContainer.querySelectorAll('.component');
                components.forEach(comp => {
                    if (comp.textContent) extras.push(comp.textContent.trim());
                });
            }
            
            // Extraer bebidas de combo
            const comboDrink = item.querySelector('.combo-drink');
            if (comboDrink && comboDrink.textContent) {
                extras.push(comboDrink.textContent.trim());
            }
            
            // Extraer etiquetas de extras
            const extraTags = item.querySelectorAll('.item-extra');
            extraTags.forEach(tag => {
                if (tag.textContent) extras.push(tag.textContent.trim());
            });
            
            const extrasText = extras.join('; ');
            
            csvContent += `${escapeCSV(name)},${escapeCSV(quantity)},${escapeCSV(price)},${escapeCSV(extrasText)}\n`;
        });

        // Crear y descargar archivo
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", `pedido_${orderIdText}_${customerName.replace(/\s+/g, '_')}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } catch (error) {
        console.error('Error al exportar a CSV:', error);
        alert(`Error al exportar: ${error.message}`);
    }
}

// Función para escapar valores CSV
function escapeCSV(value) {
    if (value === null || value === undefined) return '';
    const stringValue = String(value);
    if (stringValue.includes(',') || stringValue.includes('"') || stringValue.includes('\n')) {
        return `"${stringValue.replace(/"/g, '""')}"`;
    }
    return stringValue;
}
</script>

</body>
</html>